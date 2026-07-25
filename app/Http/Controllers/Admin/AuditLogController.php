<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        $logs = AuditLog::with('actor')->latest()->paginate(40);

        return view('admin.audit-logs.index', compact('logs'));
    }
}
