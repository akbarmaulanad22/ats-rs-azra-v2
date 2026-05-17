<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\View\View;

class CandidateStatusController extends Controller
{
    public function show(string $token): View
    {
        $application = Application::where('token', $token)
            ->with(['candidate', 'vacancy.unit', 'stages'])
            ->firstOrFail();

        return view('career.status', compact('application'));
    }
}
