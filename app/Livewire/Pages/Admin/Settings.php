<?php

namespace App\Livewire\Pages\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;

use App\Models\SystemSetting;

#[Layout('layouts.admin')]
class Settings extends Component
{
    public $activeTab = 'general'; // general, circulation, content, notifications, ai, backup
    
    // Track unsaved changes
    public $isDirty = false;
    
    // Hold settings values loaded from config/DB
    public $settings = [];

    public function mount()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        // Fetch all key-value pairs from database
        $dbSettings = SystemSetting::pluck('setting_value', 'setting_key')->toArray();

        // Load legal content securely from public storage
        $termsPath = $dbSettings['terms_and_conditions'] ?? 'settings/terms_and_conditions.txt';
        $privacyPath = $dbSettings['data_privacy_policy'] ?? 'settings/privacy_policy.txt';
        
        $termsContent = \Illuminate\Support\Facades\Storage::disk('public')->exists($termsPath) 
            ? \Illuminate\Support\Facades\Storage::disk('public')->get($termsPath) : '';
            
        $privacyContent = \Illuminate\Support\Facades\Storage::disk('public')->exists($privacyPath) 
            ? \Illuminate\Support\Facades\Storage::disk('public')->get($privacyPath) : '';

        // Load system logs from private storage
        $logsPath = 'private/system_logs.json';
        $systemLogs = \Illuminate\Support\Facades\Storage::disk('local')->exists($logsPath) 
            ? json_decode(\Illuminate\Support\Facades\Storage::disk('local')->get($logsPath), true) : [];

        // Map DB keys back to the nested array structure expected by the Blade files
        $this->settings = [
            'general' => [
                'library_name' => $dbSettings['library_name'] ?? 'Padre Garcia Polytechnic College Library',
                'email' => $dbSettings['system_email'] ?? 'library@pgpc.edu.ph',
                'phone' => $dbSettings['system_phone'] ?? '+63 912 111 6789',
                'operating_hours' => json_decode($dbSettings['operating_hours'] ?? '[]', true) ?: config('pgpc.general.operating_hours'),
                'closures' => json_decode($dbSettings['closures'] ?? '[]', true) ?: config('pgpc.general.closures'),
            ],
            
            'circulation' => [
                'borrowing_limits' => [
                    'Student' => $dbSettings['borrowing_limit_student'] ?? 3,
                    'Faculty' => $dbSettings['borrowing_limit_faculty'] ?? 5,
                ],
                'loan_durations' => [
                    'Textbooks' => $dbSettings['loan_duration_textbooks'] ?? 3,
                    'General Collection' => $dbSettings['loan_duration_general'] ?? 7,
                    'Reference Materials' => $dbSettings['loan_duration_reference'] ?? 1,
                ],
                'fine_rules' => [
                    'daily_fine' => $dbSettings['fine_rate_per_day'] ?? 5.00,
                ],
                'renewal_limits' => [
                    'max_consecutive' => $dbSettings['max_renewals'] ?? 2,
                ]
            ],
            
            'content_legal' => [
                'terms_and_conditions' => $termsContent,
                'data_privacy_policy' => $privacyContent,
                'announcements' => json_decode($dbSettings['announcements'] ?? '[]', true) ?: config('pgpc.content_legal.announcements'),
            ],
            
            'notifications' => [
                'channels' => json_decode($dbSettings['notification_channels'] ?? '[]', true) ?: config('pgpc.notifications.channels'),
                'templates' => json_decode($dbSettings['notification_templates'] ?? '[]', true) ?: config('pgpc.notifications.templates'),
                'daily_cron' => $dbSettings['daily_cron_time'] ?? '01:00',
            ],
            
            'ai_integrations' => [
                'recommendation_service' => [
                    'url' => $dbSettings['ai_recommendation_url'] ?? 'http://127.0.0.1',
                    'port' => $dbSettings['ai_recommendation_port'] ?? '5001',
                    'status' => 'Connected',
                ],
                'confidence_threshold' => $dbSettings['ai_confidence_threshold'] ?? 65,
            ],
            
            'backup' => [
                'last_backup' => '2026-08-14 23:30:00'
            ],

            'system_logs' => $systemLogs
        ];
    }

    public function setTab($tab)
    {
        // Only allow changing tab if not dirty, or just allow it anyway
        // For this frontend mockup, we'll just change the tab
        $this->activeTab = $tab;
    }
    
    public function markAsDirty()
    {
        $this->isDirty = true;
    }

    public function discardChanges()
    {
        $this->loadSettings();
        $this->isDirty = false;
        
        $this->dispatch('toast', message: 'Changes discarded.');
    }

    public function saveChanges()
    {
        // Flatten the nested array back to the DB key-value structure
        $updates = [
            'library_name' => $this->settings['general']['library_name'],
            'system_email' => $this->settings['general']['email'],
            'system_phone' => $this->settings['general']['phone'],
            'operating_hours' => json_encode($this->settings['general']['operating_hours']),
            'closures' => json_encode($this->settings['general']['closures']),
            
            'borrowing_limit_student' => $this->settings['circulation']['borrowing_limits']['Student'],
            'borrowing_limit_faculty' => $this->settings['circulation']['borrowing_limits']['Faculty'],
            'loan_duration_textbooks' => $this->settings['circulation']['loan_durations']['Textbooks'],
            'loan_duration_general' => $this->settings['circulation']['loan_durations']['General Collection'],
            'loan_duration_reference' => $this->settings['circulation']['loan_durations']['Reference Materials'],
            'fine_rate_per_day' => $this->settings['circulation']['fine_rules']['daily_fine'],
            'max_renewals' => $this->settings['circulation']['renewal_limits']['max_consecutive'],
            
            'announcements' => json_encode($this->settings['content_legal']['announcements']),
            
            'notification_channels' => json_encode($this->settings['notifications']['channels']),
            'notification_templates' => json_encode($this->settings['notifications']['templates']),
            'daily_cron_time' => $this->settings['notifications']['daily_cron'],
            
            'ai_recommendation_url' => $this->settings['ai_integrations']['recommendation_service']['url'],
            'ai_recommendation_port' => $this->settings['ai_integrations']['recommendation_service']['port'],
            'ai_confidence_threshold' => $this->settings['ai_integrations']['confidence_threshold'],
        ];

        // Loop and update standard database records
        foreach ($updates as $key => $value) {
            SystemSetting::where('setting_key', $key)->update(['setting_value' => $value]);
        }

        // Save physical files for Legal Content
        // We get the file path from the database so we know where to save the text in public storage
        $dbSettings = SystemSetting::pluck('setting_value', 'setting_key')->toArray();
        $termsPath = $dbSettings['terms_and_conditions'] ?? 'settings/terms_and_conditions.txt';
        $privacyPath = $dbSettings['data_privacy_policy'] ?? 'settings/privacy_policy.txt';

        \Illuminate\Support\Facades\Storage::disk('public')->put($termsPath, $this->settings['content_legal']['terms_and_conditions']);
        \Illuminate\Support\Facades\Storage::disk('public')->put($privacyPath, $this->settings['content_legal']['data_privacy_policy']);

        $this->isDirty = false;
        $this->dispatch('toast', message: 'Settings saved successfully.');
        $this->dispatch('settings-saved');
    }

    public function render()
    {
        return view('livewire.pages.admin.settings');
    }
}
