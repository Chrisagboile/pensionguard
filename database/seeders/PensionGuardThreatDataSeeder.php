<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Modules\PensionAccountGuard\Models\PensionGuardThreatRule;
use App\Modules\PensionAccountGuard\Models\PensionGuardOfficialSource;

class PensionGuardThreatDataSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'rule_key' => 'urgency',
                'title' => 'Pressure to act immediately',
                'description' => 'You are told to transfer money or make a pension decision urgently.',
                'severity' => 'high',
                'recommended_action' => 'Pause. Do not transfer money or make a decision under pressure. Independently contact your pension provider using verified contact details.',
                'official_source_url' => 'https://www.fca.org.uk/scamsmart',
            ],
            [
                'rule_key' => 'unexpected',
                'title' => 'Unexpected contact',
                'description' => 'You receive an unexpected call, message, email or social media approach about your pension.',
                'severity' => 'medium',
                'recommended_action' => 'Do not rely on the sender\'s contact details. Verify the organisation independently before responding.',
                'official_source_url' => 'https://www.fca.org.uk/scamsmart',
            ],
            [
                'rule_key' => 'returns',
                'title' => 'Promises of unusually high returns',
                'description' => 'An offer promises guaranteed, unusually high or risk-free investment returns.',
                'severity' => 'high',
                'recommended_action' => 'Treat the promise cautiously and check the firm and offer independently. Do not assume that registration guarantees an investment is suitable or safe.',
                'official_source_url' => 'https://www.fca.org.uk/scamsmart',
            ],
            [
                'rule_key' => 'secrecy',
                'title' => 'Request to keep the offer secret',
                'description' => 'You are asked not to discuss the offer with family, your pension provider or a trusted professional.',
                'severity' => 'high',
                'recommended_action' => 'Pause and seek independent verification. Do not let secrecy demands prevent you from checking the offer.',
                'official_source_url' => 'https://www.moneyhelper.org.uk/en/pensions-and-retirement',
            ],
            [
                'rule_key' => 'personal_details',
                'title' => 'Request for sensitive information',
                'description' => 'Someone requests passwords, one-time security codes or other sensitive account access information.',
                'severity' => 'high',
                'recommended_action' => 'Never share pension passwords or one-time security codes. Contact your provider through its independently verified website or telephone number.',
                'official_source_url' => 'https://www.ncsc.gov.uk/collection/phishing-scams',
            ],
            [
                'rule_key' => 'unverified_identity',
                'title' => 'Identity or authorisation cannot be verified',
                'description' => 'You cannot independently confirm the identity or authorisation of the person or organisation contacting you.',
                'severity' => 'high',
                'recommended_action' => 'Do not proceed until you have independently verified the organisation and the person contacting you.',
                'official_source_url' => 'https://www.fca.org.uk/scamsmart',
            ],
            [
                'rule_key' => 'unusual_transfer',
                'title' => 'Unexpected pension transfer request',
                'description' => 'A transfer request appears unexpectedly or you are asked to move pension funds to unfamiliar arrangements.',
                'severity' => 'high',
                'recommended_action' => 'Contact your current pension provider using verified contact details and ask them to explain the request before taking action.',
                'official_source_url' => 'https://www.moneyhelper.org.uk/en/pensions-and-retirement',
            ],
            [
                'rule_key' => 'upfront_fee',
                'title' => 'Unexpected upfront fee',
                'description' => 'You are asked to pay an unexpected fee to release pension funds or secure a promised benefit.',
                'severity' => 'high',
                'recommended_action' => 'Do not pay until you have independently verified the request and understood the reason for the charge.',
                'official_source_url' => 'https://www.fca.org.uk/scamsmart',
            ],
        ];

        foreach ($rules as $rule) {
            PensionGuardThreatRule::updateOrCreate(
                ['rule_key' => $rule['rule_key']],
                array_merge($rule, ['is_active' => true])
            );
        }

        $sources = [
            [
                'name' => 'Financial Conduct Authority ScamSmart',
                'url' => 'https://www.fca.org.uk/scamsmart',
                'source_type' => 'scam_warnings',
                'purpose' => 'Information about investment and pension scams and checking firms.',
            ],
            [
                'name' => 'MoneyHelper Pensions and Retirement',
                'url' => 'https://www.moneyhelper.org.uk/en/pensions-and-retirement',
                'source_type' => 'pension_guidance',
                'purpose' => 'Free information about pensions, retirement and getting support.',
            ],
            [
                'name' => 'National Cyber Security Centre',
                'url' => 'https://www.ncsc.gov.uk/collection/phishing-scams',
                'source_type' => 'cybersecurity_guidance',
                'purpose' => 'Guidance on recognising and responding to phishing and suspicious messages.',
            ],
        ];

        foreach ($sources as $source) {
            PensionGuardOfficialSource::updateOrCreate(
                ['name' => $source['name']],
                array_merge($source, ['is_active' => true])
            );
        }
    }
}
