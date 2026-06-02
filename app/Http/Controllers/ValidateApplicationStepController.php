<?php

namespace App\Http\Controllers;

use App\Enums\VacancyStatus;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Models\Vacancy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ValidateApplicationStepController extends Controller
{
    /**
     * Validate a single wizard step without persisting anything.
     *
     * Mirrors the rules of StoreApplicationRequest scoped to the current
     * step. Step 1 additionally checks whether the email has already
     * applied to this vacancy so the candidate learns early.
     */
    public function __invoke(Request $request, Vacancy $vacancy): JsonResponse
    {
        abort_unless(
            $vacancy->status === VacancyStatus::Published
                && $vacancy->tenggat_lamaran->gte(now()->startOfDay()),
            404,
        );

        $step = (int) $request->input('step');

        abort_unless($step >= 1 && $step <= 8, 422);

        $formRequest = StoreApplicationRequest::createFrom($request);

        $validator = Validator::make(
            $request->all(),
            $formRequest->rulesForStep($step),
            [],
            $formRequest->attributes(),
        );

        if ($step === 1) {
            $validator->after(function ($validator) use ($request, $vacancy): void {
                $email = $request->input('email');

                if (! $email) {
                    return;
                }

                $alreadyApplied = Application::where('vacancy_id', $vacancy->id)
                    ->whereHas('candidate', fn ($query) => $query->where('email', $email))
                    ->exists();

                if ($alreadyApplied) {
                    $validator->errors()->add('email', 'Anda sudah pernah melamar lowongan ini.');
                }
            });
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['ok' => true]);
    }
}
