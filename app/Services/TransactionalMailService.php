<?php

namespace App\Services;

use App\Mail\GenericTemplateMail;
use App\Models\EmailLog;
use App\Models\EmailSuppression;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends a stored, admin-editable EmailTemplate (see Admin > Email > Templates) to a user and
 * logs the result to EmailLog (see Admin > Email > Logs). This is the single place real
 * transactional emails (welcome, KYC decisions, deposit/withdrawal updates, etc.) go out from.
 *
 * The actual delivery mechanism is whatever MAIL_MAILER is configured in .env — defaults to
 * the log driver (emails are written to storage/logs/laravel.log) until a real provider
 * (Resend/SendGrid/Postmark/SMTP) is configured for production.
 */
class TransactionalMailService
{
    /**
     * @param  array<string, string>  $replacements  Simple {{placeholder}} substitutions applied to the subject/body.
     */
    public function send(User $user, string $templateKey, array $replacements = []): void
    {
        if (EmailSuppression::where('email', $user->email)->exists()) {
            return;
        }

        $template = EmailTemplate::where('key', $templateKey)->where('is_active', true)->first();

        if (! $template) {
            return;
        }

        $subject = $this->applyReplacements($template->subject, $replacements);
        $body = $this->applyReplacements($template->body_html, $replacements);

        try {
            Mail::to($user->email)->send(new GenericTemplateMail($subject, $body));
            $status = 'sent';
            $error = null;
        } catch (\Throwable $e) {
            $status = 'failed';
            $error = $e->getMessage();
            Log::warning("TransactionalMailService: failed to send '{$templateKey}' to {$user->email}: {$error}");
        }

        EmailLog::create([
            'recipient' => $user->email,
            'subject' => $subject,
            'template_key' => $templateKey,
            'status' => $status,
            'sent_at' => now(),
            'error' => $error,
        ]);
    }

    protected function applyReplacements(string $text, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $text = str_replace('{{'.$key.'}}', (string) $value, $text);
        }

        return $text;
    }
}
