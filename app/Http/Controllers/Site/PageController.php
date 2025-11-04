<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\AboutUs;
use App\Models\Setting;
use App\Models\BusinessHour;

class PageController extends Controller
{
    public function about()
    {
        $about = AboutUs::first();
        return view('pages.about', compact('about'));
    }

    public function contact()
    {
        $setting = Setting::first();
        $businessHours = BusinessHour::orderBy('id')->get();
        return view('pages.contact', compact('setting', 'businessHours'));
    }
}
