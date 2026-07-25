<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NftCollection;

class NftController extends Controller
{
    public function index()
    {
        $collections = NftCollection::withCount('items')->get();

        return view('admin.nft.index', compact('collections'));
    }
}
