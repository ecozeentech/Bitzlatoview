<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxReport;

class TaxController extends Controller
{
    public function index()
    {
        $reports = TaxReport::with('user')->latest()->paginate(30);

        return view('admin.tax.index', compact('reports'));
    }
}
