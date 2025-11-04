<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class LayoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('approved');
    }

    public function index()
    {
        return view('admin.layout.index');
    }
}

