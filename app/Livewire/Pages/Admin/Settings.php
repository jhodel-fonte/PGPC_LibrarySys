<?php

namespace App\Livewire\Pages\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;

use App\Models\SystemSetting;

#[Layout('layouts.admin')]
class Settings extends Component
{
    public $activeTab = 'general'; // general, circulation, content, notifications, ai, backup
    
    // Hold settings values loaded from config/DB
    public $settings = [];
    public $originalSettings = [];

    public function mount()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        // Fetch all key-value pairs from database
        $dbSettings = SystemSetting::pluck('setting_value', 'setting_key')->toArray();

        // Load system logs from private storage (local disk root is already storage/app/private in Laravel 11)
        $logsPath = 'system_logs.json';
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
                'terms' => [
                    'content' => $dbSettings['terms_content'] ?? '',
                    'version' => $dbSettings['terms_version'] ?? 1,
                    'updated_at' => $dbSettings['terms_updated_at'] ?? now()->toDateTimeString(),
                    'require_acknowledgement' => filter_var($dbSettings['terms_require_acknowledgement'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ],
                'privacy' => [
                    'content' => $dbSettings['privacy_content'] ?? '',
                    'version' => $dbSettings['privacy_version'] ?? 1,
                    'updated_at' => $dbSettings['privacy_updated_at'] ?? now()->toDateTimeString(),
                    'require_acknowledgement' => filter_var($dbSettings['privacy_require_acknowledgement'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ],
                'cookie' => [
                    'content' => $dbSettings['cookie_content'] ?? '',
                    'version' => $dbSettings['cookie_version'] ?? 1,
                    'updated_at' => $dbSettings['cookie_updated_at'] ?? now()->toDateTimeString(),
                    'require_acknowledgement' => filter_var($dbSettings['cookie_require_acknowledgement'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ]
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

        // Store snapshot for deep comparison dirty state tracking
        $this->originalSettings = $this->settings;
    }

    public function setTab($tab)
    {
        // Only allow changing tab if not dirty, or just allow it anyway
        // For this frontend mockup, we'll just change the tab
        $this->activeTab = $tab;
    }
    
    public function discardChanges()
    {
        $this->settings = $this->originalSettings; // Revert immediately to snapshot
        
        $this->dispatch('toast', message: 'Changes discarded.');
    }

    #[\Livewire\Attributes\Computed]
    public function dirtyState()
    {
        $state = [
            'is_dirty' => false,
            'categories' => [],
            'sections' => []
        ];

        foreach ($this->settings as $category => $categoryData) {
            // Do not track dirty state for logs or backup components
            if (in_array($category, ['system_logs', 'backup'])) {
                $state['categories'][$category] = false;
                continue;
            }

            $categoryDirty = false;
            
            if (is_array($categoryData)) {
                foreach ($categoryData as $section => $value) {
                    $originalValue = $this->originalSettings[$category][$section] ?? null;
                    
                    // Deep comparison
                    $isSectionDirty = json_encode($value) !== json_encode($originalValue);
                    
                    $state['sections']["{$category}.{$section}"] = $isSectionDirty;
                    
                    if ($isSectionDirty) {
                        $categoryDirty = true;
                    }
                }
            } else {
                $categoryDirty = json_encode($categoryData) !== json_encode($this->originalSettings[$category] ?? null);
            }

            $state['categories'][$category] = $categoryDirty;
            
            if ($categoryDirty) {
                $state['is_dirty'] = true;
            }
        }

        return $state;
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

        // Update the snapshot so dirty state is instantly reset
        $this->originalSettings = $this->settings;

        $this->dispatch('toast', message: 'Settings saved successfully.');
        $this->dispatch('settings-saved');
    }

    public function updatePolicy($policyType, $isImmediate, $requireAcknowledgement, $scheduledDate = null)
    {
        $validTypes = ['terms', 'privacy', 'cookie'];
        if (!in_array($policyType, $validTypes)) {
            return;
        }

        $newContent = $this->settings['content_legal'][$policyType]['content'];
        $currentVersion = (int)SystemSetting::where('setting_key', "{$policyType}_version")->value('setting_value');
        $newVersion = $currentVersion + 1;

        if ($isImmediate) {
            SystemSetting::updateOrCreate(['setting_key' => "{$policyType}_content"], ['setting_value' => $newContent, 'setting_type' => 'html']);
            SystemSetting::updateOrCreate(['setting_key' => "{$policyType}_version"], ['setting_value' => $newVersion, 'setting_type' => 'integer']);
            SystemSetting::updateOrCreate(['setting_key' => "{$policyType}_updated_at"], ['setting_value' => now()->toDateTimeString(), 'setting_type' => 'string']);
            SystemSetting::updateOrCreate(['setting_key' => "{$policyType}_require_acknowledgement"], ['setting_value' => $requireAcknowledgement, 'setting_type' => 'boolean']);
            
            // Clean up any pending updates
            SystemSetting::whereIn('setting_key', [
                "pending_{$policyType}_content",
                "pending_{$policyType}_version",
                "pending_{$policyType}_effective_at",
                "pending_{$policyType}_require_acknowledgement",
            ])->delete();

            $this->dispatch('toast', message: ucfirst($policyType) . ' Policy updated successfully.');
        } else {
            // Schedule it
            SystemSetting::updateOrCreate(['setting_key' => "pending_{$policyType}_content"], ['setting_value' => $newContent, 'setting_type' => 'html']);
            SystemSetting::updateOrCreate(['setting_key' => "pending_{$policyType}_version"], ['setting_value' => $newVersion, 'setting_type' => 'integer']);
            SystemSetting::updateOrCreate(['setting_key' => "pending_{$policyType}_effective_at"], ['setting_value' => $scheduledDate, 'setting_type' => 'string']);
            SystemSetting::updateOrCreate(['setting_key' => "pending_{$policyType}_require_acknowledgement"], ['setting_value' => $requireAcknowledgement, 'setting_type' => 'boolean']);

            $this->dispatch('toast', message: ucfirst($policyType) . ' Policy update scheduled.');
        }

        $this->loadSettings(); // Reload to refresh state and clear dirty flag
    }

    public function render()
    {
        return view('livewire.pages.admin.settings');
    }
}
