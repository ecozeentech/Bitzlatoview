<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\GenericTemplateMail;
use App\Models\EmailCampaign;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    public function templates()
    {
        $templates = EmailTemplate::orderBy('key')->get();

        return view('admin.email.templates', compact('templates'));
    }

    public function updateTemplate(Request $request, EmailTemplate $template)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body_html' => ['required', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $template->update($data);

        return back()->with('success', 'Template updated.');
    }

    public function campaigns()
    {
        $campaigns = EmailCampaign::latest()->get();

        return view('admin.email.campaigns', compact('campaigns'));
    }

    public function storeCampaign(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'subject' => ['required', 'string', 'max:255'],
            'segment' => ['required', 'in:all_users,verified_users,active_traders'],
            'body_html' => ['required', 'string'],
        ]);

        EmailCampaign::create($data + ['status' => 'draft']);

        return back()->with('success', 'Campaign drafted.');
    }

    public function sendTest(Request $request, EmailCampaign $campaign)
    {
        $data = $request->validate(['test_email' => ['required', 'email']]);

        $this->deliver($data['test_email'], $campaign->subject, $campaign->body_html, 'campaign_test');

        return back()->with('success', "Test email sent to {$data['test_email']} (via log driver by default — configure a real provider in .env).");
    }

    public function send(EmailCampaign $campaign)
    {
        $recipients = match ($campaign->segment) {
            'verified_users' => User::where('kyc_status', 'approved')->where('role', 'user')->pluck('email'),
            'active_traders' => User::where('role', 'user')->pluck('email'),
            default => User::where('role', 'user')->pluck('email'),
        };

        foreach ($recipients as $email) {
            $this->deliver($email, $campaign->subject, $campaign->body_html, 'campaign');
        }

        $campaign->update(['status' => 'sent', 'sent_at' => now()]);

        return back()->with('success', "Campaign sent to {$recipients->count()} recipients.");
    }

    public function logs()
    {
        $logs = EmailLog::latest()->paginate(40);

        return view('admin.email.logs', compact('logs'));
    }

    protected function deliver(string $to, string $subject, string $bodyHtml, string $templateKey): void
    {
        try {
            Mail::to($to)->send(new GenericTemplateMail($subject, $bodyHtml));
            $status = 'sent';
            $error = null;
        } catch (\Throwable $e) {
            $status = 'failed';
            $error = $e->getMessage();
        }

        EmailLog::create([
            'recipient' => $to,
            'subject' => $subject,
            'template_key' => $templateKey,
            'status' => $status,
            'sent_at' => now(),
            'error' => $error,
        ]);
    }
}
