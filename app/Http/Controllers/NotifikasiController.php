<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    public function index(Request $request): View
    {
        $notifikasi = $request->user()->notifications()->paginate(15);

        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return view('notifikasi.index', compact('notifikasi'));
    }
}
