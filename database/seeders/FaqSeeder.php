<?php

namespace Database\Seeders;

use App\Models\FaqItem;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            ['question' => 'How do deposits and withdrawals work?', 'answer' => 'Bitzlatoview uses manually verified funding: you deposit to one of our published payment methods and upload proof of payment, which our team confirms before crediting your wallet. Withdrawals are reviewed and sent by an administrator after verification. Availability of specific payment rails may vary by region as we complete licensing in additional markets.', 'category' => 'general'],
            ['question' => 'What is KYC and why do I need it?', 'answer' => 'KYC (Know Your Customer) verification is required before accessing higher-risk features like withdrawals, cards, futures, and P2P merchant tools, in line with AML regulations. Every submission is reviewed by our compliance team.', 'category' => 'kyc'],
            ['question' => 'How does the double-entry ledger work?', 'answer' => 'Every balance change on Bitzlatoview is recorded as a matched pair of debit/credit ledger entries, ensuring the platform is always fully auditable and reconcilable.', 'category' => 'wallet'],
            ['question' => 'Can I lose money with AI trading bots or copy trading?', 'answer' => 'Yes. AI bots and copy trading are experimental and carry real risk of loss. Bitzlatoview never guarantees returns, and past performance does not guarantee future results.', 'category' => 'trading'],
            ['question' => 'What payment methods are supported for P2P?', 'answer' => 'P2P merchants can list bank transfer, mobile money, and other local payment methods. All trades are escrow-protected on-platform.', 'category' => 'p2p'],
            ['question' => 'Are virtual cards issued by Bitzlatoview directly?', 'answer' => 'No — real, spendable cards require a licensed bank-partner processor (e.g. Stripe Issuing, Marqeta, or Lithic). Until that integration is live, virtual cards on this platform are account records only and cannot be used for real purchases.', 'category' => 'cards'],
        ];

        foreach ($faqs as $i => $faq) {
            FaqItem::updateOrCreate(['question' => $faq['question']], $faq + ['sort_order' => $i]);
        }
    }
}
