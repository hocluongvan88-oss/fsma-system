<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function about()
    {
        $company = config('app.company');
        return view('company.about', compact('company'));
    }

    public function contact()
    {
        $company = config('app.company');
        return view('company.contact', compact('company'));
    }
}
