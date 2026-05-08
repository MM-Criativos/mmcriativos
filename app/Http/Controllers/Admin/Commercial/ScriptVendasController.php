<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Controller;

class ScriptVendasController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'approved', 'can.commercial']);
    }

    public function index()
    {
        return view('admin.commercial.script-vendas.index');
    }
}
