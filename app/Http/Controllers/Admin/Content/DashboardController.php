<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Skill;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','approved']);
    }

    public function index()
    {
        $stats = [
            'services' => Service::count(),
            'skills' => Skill::count(),
        ];

        return view('admin.content.dashboard', compact('stats'));
    }
}

