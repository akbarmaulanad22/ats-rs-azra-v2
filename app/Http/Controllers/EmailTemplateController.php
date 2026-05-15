<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEmailTemplateRequest;
use App\Models\EmailTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', EmailTemplate::class);

        $templates = EmailTemplate::orderBy('key')->get();

        return view('email-templates.index', compact('templates'));
    }

    public function edit(EmailTemplate $templateEmail): View
    {
        Gate::authorize('update', $templateEmail);

        return view('email-templates.edit', compact('templateEmail'));
    }

    public function update(UpdateEmailTemplateRequest $request, EmailTemplate $templateEmail): RedirectResponse
    {
        $templateEmail->update($request->validated());

        return redirect()
            ->route('template-email.index')
            ->with('status', 'Template email berhasil diperbarui.');
    }
}
