<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Generic transactional/marketing email renderer. Swap the mailer in config/mail.php
 * (Resend/SendGrid/Postmark/SMTP) to move from the local log driver to real delivery.
 */
class GenericTemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $emailSubject, public string $bodyHtml) {}

    public function build()
    {
        return $this->subject($this->emailSubject)
            ->html($this->bodyHtml);
    }
}
