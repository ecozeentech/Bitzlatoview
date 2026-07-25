<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * PROVIDER: Swap the log driver for Resend / SendGrid / Postmark / SMTP.
 */
class EmailDispatchService
{
    public function sendTemplate(string $templateKey, User|string $recipient, array $vars = []): EmailLog
    {
        $template = EmailTemplate::query()->where('key', $templateKey)->where('is_active', true)->first();
        $email = $recipient instanceof User ? $recipient->email : $recipient;
        $userId = $recipient instanceof User ? $recipient->id : null;

        $subject = $template?->subject ?? ('Bitzlatoview: '.$templateKey);
        $body = $template?->body_html ?? '<p>Notification from Bitzlatoview.</p>';

        foreach ($vars as $key => $value) {
            $subject = str_replace('{{'.$key.'}}', (string) $value, $subject);
            $body = str_replace('{{'.$key.'}}', (string) $value, $body);
        }

        $log = EmailLog::query()->create([
            'user_id' => $userId,
            'recipient' => $email,
            'subject' => $subject,
            'template' => $templateKey,
            'provider' => config('mail.default', 'log'),
            'status' => 'queued',
        ]);

        try {
            // MVP: write to Laravel log mailer / array driver.
            Mail::html($body, function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });

            $log->update([
                'status' => 'sent',
                'sent_at' => now(),
                'provider_message_id' => 'local-'.uniqid(),
            ]);
        } catch (\Throwable $e) {
            $log->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
        }

        return $log->fresh();
    }
}
