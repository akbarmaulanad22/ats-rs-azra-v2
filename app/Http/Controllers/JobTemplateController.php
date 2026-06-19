<?php

namespace App\Http\Controllers;

use App\Enums\JobTemplateStatus;
use App\Enums\VacancyStatus;
use App\Http\Requests\PublishVacancyRequest;
use App\Http\Requests\StoreJobTemplateRequest;
use App\Http\Requests\UpdateJobTemplateRequest;
use App\Models\JobTemplate;
use App\Models\Unit;
use App\Services\VacancyPublisher;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class JobTemplateController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', JobTemplate::class);

        $jobTemplates = JobTemplate::with('unit')
            ->withCount('vacancies')
            ->when(
                $request->q,
                fn ($q, $search) => $q->whereRaw('LOWER(judul_posisi) LIKE ?', ['%'.strtolower(str_replace(['%', '_'], ['\%', '\_'], $search)).'%']),
            )
            ->when(
                $request->status,
                fn ($q, $status) => $q->where('status', $status),
            )
            ->when(
                $request->unit_id,
                fn ($q, $unitId) => $q->where('unit_id', $unitId),
            )
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $units = Unit::orderBy('nama')->get();

        return view('job-templates.index', compact('jobTemplates', 'units'));
    }

    public function create(): View
    {
        Gate::authorize('create', JobTemplate::class);

        return view('job-templates.create');
    }

    public function store(StoreJobTemplateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = JobTemplateStatus::Active->value;

        JobTemplate::create($data);

        return redirect()->route('template-lowongan.index')
            ->with('status', 'Template lowongan berhasil dibuat.');
    }

    public function edit(JobTemplate $templateLowongan): View
    {
        Gate::authorize('update', $templateLowongan);

        $templateLowongan->load('unit', 'workflowTemplate');
        $statuses = JobTemplateStatus::cases();

        return view('job-templates.edit', [
            'templateLowongan' => $templateLowongan,
            'statuses' => $statuses,
        ]);
    }

    public function update(UpdateJobTemplateRequest $request, JobTemplate $templateLowongan): RedirectResponse
    {
        $templateLowongan->update($request->validated());

        return redirect()->route('template-lowongan.index')
            ->with('status', 'Template lowongan berhasil diperbarui.');
    }

    public function destroy(JobTemplate $templateLowongan): RedirectResponse
    {
        Gate::authorize('delete', $templateLowongan);

        try {
            $templateLowongan->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'Template tidak dapat dihapus karena sudah memiliki lowongan terbit. Arsipkan template ini sebagai gantinya.');
        }

        return redirect()->route('template-lowongan.index')
            ->with('status', 'Template lowongan berhasil dihapus.');
    }

    public function publishForm(JobTemplate $templateLowongan): View
    {
        Gate::authorize('publish', $templateLowongan);

        $templateLowongan->load('unit', 'workflowTemplate.stages', 'jobTemplateTest');
        $statuses = [VacancyStatus::Draft, VacancyStatus::Published];
        $hasTestStage = $templateLowongan->workflowTemplate->stages->contains('key', 'tes_kompetensi');

        return view('job-templates.publish', [
            'templateLowongan' => $templateLowongan,
            'statuses' => $statuses,
            'hasTestStage' => $hasTestStage,
        ]);
    }

    public function publish(PublishVacancyRequest $request, JobTemplate $templateLowongan, VacancyPublisher $publisher): RedirectResponse
    {
        Gate::authorize('publish', $templateLowongan);

        $templateLowongan->load('workflowTemplate.stages', 'jobTemplateTest');

        $hasTestStage = $templateLowongan->workflowTemplate->stages->contains('key', 'tes_kompetensi');

        if ($request->validated('status') === VacancyStatus::Published->value
            && $hasTestStage
            && ! $templateLowongan->jobTemplateTest) {
            return back()->withInput()->withErrors([
                'status' => 'Lowongan tidak dapat dipublikasikan sebelum tes kompetensi dikonfigurasi pada template. Konfigurasi tes terlebih dahulu, atau terbitkan sebagai draf.',
            ]);
        }

        $flyerPath = $request->file('flyer')->store('flyers', 'public');

        try {
            $publisher->publish($templateLowongan, [
                'jumlah_posisi' => $request->validated('jumlah_posisi'),
                'tenggat_lamaran' => $request->validated('tenggat_lamaran'),
                'flyer_path' => $flyerPath,
                'kualifikasi' => $request->validated('kualifikasi') ?: null,
                'status' => $request->validated('status'),
            ]);
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($flyerPath);

            throw $e;
        }

        return redirect()->route('lowongan.index')
            ->with('status', 'Lowongan berhasil diterbitkan dari template.');
    }
}
