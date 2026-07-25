<?php

namespace Database\Seeders;

use App\Models\FaqItem;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            ['question' => 'Is Bitzlatoview live with real money?', 'answer' => 'Bitzlatoview currently runs in simulation / paper-trading mode while licensing, custody, broker, card-issuing, and KYC/AML provider integrations are finalized. No real funds are moved on-chain or off-chain yet.', 'category' => 'general'],
            ['question' => 'What is KYC and why do I need it?', 'answer' => 'KYC (Know Your Customer) verification is required before accessing higher-risk features like withdrawals, cards, futures, and P2P merchant tools, in line with AML regulations.', 'category' => 'kyc'],
            ['question' => 'How does the double-entry ledger work?', 'answer' => 'Every balance change on Bitzlatoview is recorded as a matched pair of debit/credit ledger entries, ensuring the platform is always fully auditable and reconcilable.', 'category' => 'wallet'],
            ['question' => 'Can I lose money with AI trading bots or copy trading?', 'answer' => 'Yes. AI bots and copy trading are experimental/simulated and carry real risk of loss even in live mode. Bitzlatoview never guarantees returns.', 'category' => 'trading'],
            ['question' => 'What payment methods are supported for P2P?', 'answer' => 'P2P merchants can list bank transfer, mobile money, and other local payment methods. All trades are escrow-protected on-platform.', 'category' => 'p2p'],
            ['question' => 'Are virtual cards issued by Bitzlatoview directly?', 'answer' => 'No — in production, virtual cards would be issued via a licensed bank-partner processor (e.g. Stripe Issuing, Marqeta, or Lithic). The current build uses mock card data for demonstration.', 'category' => 'cards'],
        ];

        foreach ($faqs as $i => $faq) {
            FaqItem::updateOrCreate(['question' => $faq['question']], $faq + ['sort_order' => $i]);
        }
    }
}
