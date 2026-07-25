<?php

namespace App\Http\Controllers;

use App\Models\FaqItem;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = FaqItem::orderBy('category')->orderBy('sort_order')->get()->groupBy('category');

        return view('public.faq', compact('faqs'));
    }
}
