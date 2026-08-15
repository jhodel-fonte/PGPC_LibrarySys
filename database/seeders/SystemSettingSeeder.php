<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\SystemSetting;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Define Policy Content
        $termsContent = "Terms and Conditions...\n\nBy using the PGPC Library System, you agree to abide by the institution's rules.";
        $privacyContent = "Privacy Policy...\n\nYour data is securely stored and used only for library transactional purposes.";
        $cookiesContent = "Cookie Policy...\n\nWe use essential cookies to maintain your active login session.";

        // 2. Define Settings Array
        $settings = [
            // General Settings
            ['setting_key' => 'library_name', 'setting_value' => 'Padre Garcia Polytechnic College Library', 'setting_type' => 'string', 'allowed_role' => 'admin', 'is_critical' => false],
            ['setting_key' => 'system_email', 'setting_value' => 'library@pgpc.edu.ph', 'setting_type' => 'string', 'allowed_role' => 'admin', 'is_critical' => false],
            ['setting_key' => 'system_phone', 'setting_value' => '+63 912 111 6789', 'setting_type' => 'string', 'allowed_role' => 'admin', 'is_critical' => false],
            ['setting_key' => 'operating_hours', 'setting_value' => json_encode(config('pgpc.general.operating_hours', [])), 'setting_type' => 'json', 'allowed_role' => 'admin', 'is_critical' => false],
            ['setting_key' => 'closures', 'setting_value' => json_encode(config('pgpc.general.closures', [])), 'setting_type' => 'json', 'allowed_role' => 'admin', 'is_critical' => false],
            
            // Circulation Rules
            ['setting_key' => 'borrowing_limit_student', 'setting_value' => '3', 'setting_type' => 'integer', 'allowed_role' => 'admin', 'is_critical' => false],
            ['setting_key' => 'borrowing_limit_faculty', 'setting_value' => '5', 'setting_type' => 'integer', 'allowed_role' => 'admin', 'is_critical' => false],
            ['setting_key' => 'loan_duration_textbooks', 'setting_value' => '3', 'setting_type' => 'integer', 'allowed_role' => 'admin', 'is_critical' => false],
            ['setting_key' => 'loan_duration_general', 'setting_value' => '7', 'setting_type' => 'integer', 'allowed_role' => 'admin', 'is_critical' => false],
            ['setting_key' => 'loan_duration_reference', 'setting_value' => '1', 'setting_type' => 'integer', 'allowed_role' => 'admin', 'is_critical' => false],
            ['setting_key' => 'fine_rate_per_day', 'setting_value' => '5.00', 'setting_type' => 'integer', 'allowed_role' => 'admin', 'is_critical' => true],
            ['setting_key' => 'max_renewals', 'setting_value' => '2', 'setting_type' => 'integer', 'allowed_role' => 'admin', 'is_critical' => false],
            ['setting_key' => 'reservation_validity_days', 'setting_value' => '3', 'setting_type' => 'integer', 'allowed_role' => 'admin', 'is_critical' => false],

            // Legal Documents - Terms & Conditions
            ['setting_key' => 'terms_content', 'setting_value' => $termsContent, 'setting_type' => 'html', 'allowed_role' => 'head_admin', 'is_critical' => true],
            ['setting_key' => 'terms_version', 'setting_value' => '1', 'setting_type' => 'integer', 'allowed_role' => 'head_admin', 'is_critical' => true],
            ['setting_key' => 'terms_updated_at', 'setting_value' => now()->toDateTimeString(), 'setting_type' => 'string', 'allowed_role' => 'head_admin', 'is_critical' => true],
            ['setting_key' => 'terms_require_acknowledgement', 'setting_value' => '1', 'setting_type' => 'boolean', 'allowed_role' => 'head_admin', 'is_critical' => true],

            // Legal Documents - Privacy Policy
            ['setting_key' => 'privacy_content', 'setting_value' => $privacyContent, 'setting_type' => 'html', 'allowed_role' => 'head_admin', 'is_critical' => true],
            ['setting_key' => 'privacy_version', 'setting_value' => '1', 'setting_type' => 'integer', 'allowed_role' => 'head_admin', 'is_critical' => true],
            ['setting_key' => 'privacy_updated_at', 'setting_value' => now()->toDateTimeString(), 'setting_type' => 'string', 'allowed_role' => 'head_admin', 'is_critical' => true],
            ['setting_key' => 'privacy_require_acknowledgement', 'setting_value' => '0', 'setting_type' => 'boolean', 'allowed_role' => 'head_admin', 'is_critical' => true],

            // Legal Documents - Cookie Policy
            ['setting_key' => 'cookie_content', 'setting_value' => $cookiesContent, 'setting_type' => 'html', 'allowed_role' => 'head_admin', 'is_critical' => true],
            ['setting_key' => 'cookie_version', 'setting_value' => '1', 'setting_type' => 'integer', 'allowed_role' => 'head_admin', 'is_critical' => true],
            ['setting_key' => 'cookie_updated_at', 'setting_value' => now()->toDateTimeString(), 'setting_type' => 'string', 'allowed_role' => 'head_admin', 'is_critical' => true],
            ['setting_key' => 'cookie_require_acknowledgement', 'setting_value' => '0', 'setting_type' => 'boolean', 'allowed_role' => 'head_admin', 'is_critical' => true],

            // Notifications
            ['setting_key' => 'notification_channels', 'setting_value' => json_encode(config('pgpc.notifications.channels', [])), 'setting_type' => 'json', 'allowed_role' => 'admin', 'is_critical' => false],
            ['setting_key' => 'notification_templates', 'setting_value' => json_encode(config('pgpc.notifications.templates', [])), 'setting_type' => 'json', 'allowed_role' => 'admin', 'is_critical' => false],
            ['setting_key' => 'daily_cron_time', 'setting_value' => '01:00', 'setting_type' => 'string', 'allowed_role' => 'admin', 'is_critical' => true],

            // AI & Integrations
            ['setting_key' => 'ai_recommendation_url', 'setting_value' => 'http://127.0.0.1', 'setting_type' => 'string', 'allowed_role' => 'head_admin', 'is_critical' => true],
            ['setting_key' => 'ai_recommendation_port', 'setting_value' => '5001', 'setting_type' => 'string', 'allowed_role' => 'head_admin', 'is_critical' => true],
            ['setting_key' => 'ai_confidence_threshold', 'setting_value' => '65', 'setting_type' => 'integer', 'allowed_role' => 'admin', 'is_critical' => false],
        ];

        // 3. Insert or Update Settings
        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['setting_key' => $setting['setting_key']],
                $setting
            );
        }
    }
}
