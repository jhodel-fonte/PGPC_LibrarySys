<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\SystemSetting;
use App\Services\AuditTrail;
use App\Services\EmployeeRoleService;
use App\Services\LibrarySettings;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('livewire.layouts.admin', ['title' => 'System Settings'])]
class Settings extends Component
{
    public array $values = [];

    #[Locked]
    public array $originalValues = [];

    private const KEYS = [
        'loan_limit' => 'library_config.max_loan_limit',
        'loan_period' => 'library_config.loan_period',
        'pickup_days' => 'reservation.max_pickup_days',
        'auto_approve' => 'reservation.auto_approve',
        'approval_delay' => 'reservation.auto_approve_delay_minutes',
        'reminder_days' => 'notifications.due_reminder_days',
    ];

    public function mount(): void
    {
        abort_unless(EmployeeRoleService::canManageSettings(), 403);
        $this->loadValues();
    }

    private function loadValues(): void
    {
        foreach (self::KEYS as $field => $key) {
            $this->values[$field] = LibrarySettings::get($key);
        }
        $this->originalValues = $this->values;
    }

    public function save(): void
    {
        abort_unless(EmployeeRoleService::canManageSettings(), 403);
        $validated = $this->validate([
            'values.loan_limit' => 'required|integer|min:1|max:50',
            'values.loan_period' => 'required|integer|min:1|max:365',
            'values.pickup_days' => 'required|integer|min:1|max:365',
            'values.auto_approve' => 'required|boolean',
            'values.approval_delay' => 'required|integer|min:0|max:10080',
            'values.reminder_days' => 'required|integer|min:1|max:365',
        ]);

        AuditTrail::transaction('System settings changed', function () use ($validated) {
            foreach (self::KEYS as $field => $key) {
                $value = $validated['values'][$field];
                if ((string) $value === (string) $this->originalValues[$field]) {
                    continue;
                }
                $serialized = $field === 'auto_approve' ? ($value ? '1' : '0') : (string) $value;
                SystemSetting::updateOrCreate(['setting_key' => $key], ['setting_value' => $serialized, 'setting_type' => $field === 'auto_approve' ? 'boolean' : 'integer', 'is_critical' => false]);
            }
        });
        $this->loadValues();
        $this->dispatch('toast', message: 'Settings saved.', type: 'success');
    }

    public function resetSetting(string $field): void
    {
        abort_unless(EmployeeRoleService::canManageSettings(), 403);
        abort_unless(isset(self::KEYS[$field]), 404);
        AuditTrail::transaction('System setting reset', fn () => SystemSetting::where('setting_key', self::KEYS[$field])->get()->each->delete());
        $this->values[$field] = config('settings.'.self::KEYS[$field]);
        $this->originalValues[$field] = $this->values[$field];
        $this->resetErrorBag('values.'.$field);
        $this->dispatch('toast', message: 'Setting reset to its default.', type: 'success');
    }

    public function render()
    {
        return view('livewire.pages.dashboard.settings');
    }
}
