<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Budget;
use App\Models\Extra;
use App\Models\EmailTemplate;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'approved']);
    }

    public function index()
    {
        $stats = [
            'plans' => Plan::count(),
            'budgets' => Budget::count(),
            'extras' => Extra::count(),
            'email_templates' => EmailTemplate::count(),
            'clients' => \App\Models\Client::count(),
            'testimonials' => \App\Models\ClientTestimonial::count(),
        ];

        // View a ser criada na etapa de UI
        return view('admin.commercial.dashboard', compact('stats'));
    }
}
