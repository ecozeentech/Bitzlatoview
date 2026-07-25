<?php

namespace Database\Seeders;

use App\Models\AnalystProfile;
use App\Models\BillingPackage;
use Illuminate\Database\Seeder;

class BillingSeeder extends Seeder
{
    public function run(): void
    {
        $analyst = AnalystProfile::updateOrCreate(
            ['name' => 'J. Whitfield — Senior Market Analyst'],
            [
                'credential' => 'Market Analyst',
                'credential_verified' => false,
                'bio' => 'Senior market analyst covering crypto and macro trends. Credential verification pending — displayed as "Market Analyst" until legally verified.',
            ]
        );

        $packages = [
            [
                'title' => 'Basic Analyst Package',
                'invoice_label' => 'Market Analyst Package — Basic',
                'price' => 19,
                'billing_cycle' => 'monthly',
                'features' => ['Weekly market digest', 'Access to public research archive'],
                'report_access' => true,
                'consultation_minutes' => 0,
            ],
            [
                'title' => 'Pro Analyst Package',
                'invoice_label' => 'Market Analyst Package — Pro',
                'price' => 59,
                'billing_cycle' => 'monthly',
                'features' => ['Daily market digest', 'Full research archive', '15 min monthly consultation'],
                'report_access' => true,
                'consultation_minutes' => 15,
            ],
            [
                'title' => 'Elite Analyst Package',
                'invoice_label' => 'Market Analyst Package — Elite',
                'price' => 149,
                'billing_cycle' => 'monthly',
                'features' => ['Real-time alerts', 'Full research archive', '60 min monthly consultation', 'Priority support'],
                'report_access' => true,
                'consultation_minutes' => 60,
            ],
            [
                'title' => 'Institutional Research Package',
                'invoice_label' => 'Institutional Research Package',
                'price' => 499,
                'billing_cycle' => 'monthly',
                'features' => ['Custom research briefs', 'Dedicated account manager', 'API research feed'],
                'report_access' => true,
                'consultation_minutes' => 120,
            ],
        ];

        foreach ($packages as $package) {
            BillingPackage::updateOrCreate(
                ['title' => $package['title']],
                $package + [
                    'analyst_profile_id' => $analyst->id,
                    'description' => 'Simulated analyst research subscription. Not investment advice. "Analyst" credentials are labeled accurately and are not represented as CFA Institute charterholder credentials unless independently verified.',
                    'risk_disclosure' => 'Research and commentary are for informational purposes only and do not constitute investment advice. Past performance does not guarantee future results.',
                    'status' => 'active',
                ]
            );
        }
    }
}
