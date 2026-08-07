<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['key' => 'welcome', 'name' => 'Welcome Email', 'subject' => 'Welcome to Bitzlatoview'],
            ['key' => 'email_verification', 'name' => 'Email Verification', 'subject' => 'Verify your Bitzlatoview email'],
            ['key' => 'password_reset', 'name' => 'Password Reset', 'subject' => 'Reset your Bitzlatoview password'],
            ['key' => 'login_alert', 'name' => 'Login Alert', 'subject' => 'New login to your Bitzlatoview account'],
            ['key' => 'kyc_submitted', 'name' => 'KYC Submitted', 'subject' => 'We received your verification documents'],
            ['key' => 'kyc_approved', 'name' => 'KYC Approved', 'subject' => 'You are verified on Bitzlatoview'],
            ['key' => 'kyc_rejected', 'name' => 'KYC Rejected', 'subject' => 'Action required on your verification'],
            ['key' => 'deposit_received', 'name' => 'Deposit Received', 'subject' => 'Your deposit has been credited'],
            ['key' => 'withdrawal_requested', 'name' => 'Withdrawal Requested', 'subject' => 'Withdrawal request received'],
            ['key' => 'withdrawal_approved', 'name' => 'Withdrawal Approved', 'subject' => 'Your withdrawal has been approved'],
            ['key' => 'withdrawal_rejected', 'name' => 'Withdrawal Rejected', 'subject' => 'Your withdrawal request was rejected'],
            ['key' => 'p2p_order_opened', 'name' => 'P2P Order Opened', 'subject' => 'New P2P order opened'],
            ['key' => 'p2p_order_paid', 'name' => 'P2P Order Marked Paid', 'subject' => 'Buyer marked P2P order as paid'],
            ['key' => 'p2p_appeal_opened', 'name' => 'P2P Appeal Opened', 'subject' => 'A dispute was opened on your P2P order'],
            ['key' => 'virtual_card_created', 'name' => 'Virtual Card Created', 'subject' => 'Your virtual card is ready'],
            ['key' => 'virtual_card_requested', 'name' => 'Virtual Card Requested', 'subject' => 'Your virtual card request was received'],
            ['key' => 'virtual_card_approved', 'name' => 'Virtual Card Approved', 'subject' => 'Your virtual card has been approved'],
            ['key' => 'virtual_card_rejected', 'name' => 'Virtual Card Rejected', 'subject' => 'Your virtual card request was declined'],
            ['key' => 'bot_started', 'name' => 'Bot Allocation Started', 'subject' => 'Your AI bot allocation has started'],
            ['key' => 'mining_contract_purchased', 'name' => 'Mining Contract Purchased', 'subject' => 'Your mining contract is active'],
            ['key' => 'tax_report_ready', 'name' => 'Tax Report Ready', 'subject' => 'Your tax report is ready to download'],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['key' => $template['key']],
                $template + [
                    'body_html' => '<p>This is the default body for the "'.$template['name'].'" template. Edit it from Admin &gt; Email &gt; Templates.</p>',
                    'type' => 'transactional',
                    'is_active' => true,
                ]
            );
        }
    }
}
