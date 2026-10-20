<?php

namespace App\Livewire\Pages\Dashboard;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.layouts.admin', ['title' => 'System Settings'])]
class Settings extends Component
{
    public string $activeTab = 'general'; // general, circulation, content, notifications, ai, backup, logs

    // In-memory settings state for UI design & planning (zero backend DB dependency)
    public array $settings = [];
    public array $originalSettings = [];

    public function mount(): void
    {
        $this->loadDefaultSettings();
    }

    public function loadDefaultSettings(): void
    {
        $this->settings = [
            'general' => [
                'library_name' => config('settings.college.name', 'Padre Garcia Polytechnic College') . ' - Library',
                'email' => 'library@pgpc.edu.ph',
                'phone' => '(043) 515-8123 / +63 912 345 6789',
                'address' => config('settings.library_config.location', config('settings.college.address', 'Main Building, 2nd Floor Library')),
                'timezone' => 'Asia/Manila (GMT+8)',
                'operating_hours' => [
                    'monday' => ['status' => 'Open', 'opens' => '08:00', 'closes' => '17:00'],
                    'tuesday' => ['status' => 'Open', 'opens' => '08:00', 'closes' => '17:00'],
                    'wednesday' => ['status' => 'Open', 'opens' => '08:00', 'closes' => '17:00'],
                    'thursday' => ['status' => 'Open', 'opens' => '08:00', 'closes' => '17:00'],
                    'friday' => ['status' => 'Open', 'opens' => '08:00', 'closes' => '17:00'],
                    'saturday' => ['status' => 'Open', 'opens' => '08:00', 'closes' => '12:00'],
                    'sunday' => ['status' => 'Closed', 'opens' => '08:00', 'closes' => '12:00'],
                ],
                'closures' => [
                    ['date' => '2026-11-01', 'reason' => 'All Saints Day (Special Non-Working Holiday)', 'description' => 'All Saints Day'],
                    ['date' => '2026-11-30', 'reason' => 'Bonifacio Day (Regular Holiday)', 'description' => 'Bonifacio Day'],
                    ['date' => '2026-12-25', 'reason' => 'Christmas Day', 'description' => 'Christmas Day'],
                    ['date' => '2026-12-30', 'reason' => 'Rizal Day', 'description' => 'Rizal Day'],
                ],
            ],

            'circulation' => [
                'borrowing_limits' => [
                    'Student' => config('settings.library_config.max_loan_limit', 3),
                    'Faculty' => 5,
                    'Staff' => 4,
                ],
                'loan_durations' => [
                    'Textbooks' => 3,
                    'General Collection' => config('settings.library_config.loan_period', 5),
                    'Reference Materials' => 1,
                    'Filipiniana' => 3,
                ],
                'fine_rules' => [
                    'daily_fine' => (float) config('settings.library_config.penalty_fee', 50.00),
                    'grace_period_days' => 1,
                    'max_fine_per_book' => 300.00,
                ],
                'renewal_limits' => [
                    'max_consecutive' => 2,
                    'allow_online_renewal' => true,
                    'block_on_overdue' => true,
                ]
            ],

            'content_legal' => [
                'terms' => [
                    'content' => "Padre Garcia Polytechnic College Library Terms & Conditions\n\n1. Library ID Policy: Every registered student and faculty member must present a valid digital or RFID library ID.\n2. Borrowing Responsibility: Borrowers are strictly responsible for loaned books and equipment.\n3. Overdue & Lost Materials: Fines accumulate per calendar day. Lost or unreturned items require replacement or reimbursement.",
                    'version' => 2,
                    'updated_at' => '2026-08-15 10:30:00',
                    'require_acknowledgement' => true,
                ],
                'privacy' => [
                    'content' => "Padre Garcia Polytechnic College Library Privacy Notice\n\nIn compliance with the Data Privacy Act of 2012 (RA 10173), student and faculty circulation history is kept strictly confidential and used solely for library services and accreditation metrics.",
                    'version' => 1,
                    'updated_at' => '2026-06-01 09:00:00',
                    'require_acknowledgement' => false,
                ],
                'cookie' => [
                    'content' => "This web portal uses essential session cookies to manage authenticated sessions and remember circulation preferences.",
                    'version' => 1,
                    'updated_at' => '2026-05-10 14:15:00',
                    'require_acknowledgement' => false,
                ]
            ],

            'notifications' => [
                'channels' => [
                    'email' => true,
                    'in_app' => true,
                    'sms' => false,
                ],
                'templates' => [
                    ['id' => 'due_reminder', 'name' => 'Due Date Reminder (24h Before)', 'channel' => 'Email & In-App', 'status' => 'Active'],
                    ['id' => 'overdue_notice', 'name' => 'Overdue Notice & Fine Alert', 'channel' => 'Email & In-App', 'status' => 'Active'],
                    ['id' => 'reservation_ready', 'name' => 'Reserved Copy Available for Pickup', 'channel' => 'Email', 'status' => 'Active'],
                    ['id' => 'loan_receipt', 'name' => 'Circulation Checkout / Return Receipt', 'channel' => 'Email', 'status' => 'Active'],
                ],
                'daily_cron' => '08:00',
            ],

            'ai_integrations' => [
                'recommendation_service' => [
                    'url' => 'http://127.0.0.1',
                    'port' => '5001',
                    'status' => 'Connected',
                ],
                'confidence_threshold' => 70,
                'enable_smart_search' => true,
            ],

            'backup' => [
                'last_backup' => '2026-10-19 23:30:00',
                'auto_backup_enabled' => true,
                'backup_frequency' => 'Daily',
                'retention_days' => 30,
            ],

            'system_logs' => [
                ['timestamp' => '2026-10-20 11:45:22', 'level' => 'INFO', 'user' => 'admin@pgpc.edu.ph', 'message' => 'Accession record BK-2026-0042 added to Main Collection'],
                ['timestamp' => '2026-10-20 10:12:05', 'level' => 'SUCCESS', 'user' => 'librarian@pgpc.edu.ph', 'message' => 'MARC 21 batch catalog record extracted (24 titles)'],
                ['timestamp' => '2026-10-19 16:30:18', 'level' => 'WARNING', 'user' => 'system', 'message' => 'Overdue notification dispatched for 12 borrowed items'],
                ['timestamp' => '2026-10-19 14:02:44', 'level' => 'INFO', 'user' => 'admin@pgpc.edu.ph', 'message' => 'System settings draft saved'],
            ],
        ];

        $this->originalSettings = $this->settings;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function discardChanges(): void
    {
        $this->settings = $this->originalSettings;
        $this->dispatch('toast', message: 'Draft changes discarded.', type: 'info');
    }

    public function saveChanges(): void
    {
        $this->originalSettings = $this->settings;
        $this->dispatch('toast', message: 'Settings saved successfully (Design Preview Mode).', type: 'success');
        $this->dispatch('settings-saved');
    }

    public function updatePolicy(string $policyType, bool $isImmediate = true, bool $requireAcknowledgement = false, ?string $scheduledDate = null): void
    {
        $this->originalSettings = $this->settings;
        $this->dispatch('toast', message: ucfirst($policyType) . ' policy updated (Design Preview Mode).', type: 'success');
    }

    #[Computed]
    public function dirtyState(): array
    {
        $state = [
            'is_dirty' => false,
            'categories' => [],
            'sections' => [],
        ];

        foreach ($this->settings as $category => $categoryData) {
            if (in_array($category, ['system_logs', 'backup'], true)) {
                $state['categories'][$category] = false;
                continue;
            }

            $categoryDirty = false;
            if (is_array($categoryData)) {
                foreach ($categoryData as $section => $value) {
                    $originalValue = $this->originalSettings[$category][$section] ?? null;
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

    public function render()
    {
        return view('livewire.pages.dashboard.settings');
    }
}
