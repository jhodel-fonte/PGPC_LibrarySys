<div class="mx-auto max-w-5xl space-y-6 p-4 sm:p-6 lg:p-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">System settings</h1>
        <p class="mt-1 text-sm text-slate-600">Manage library rules. Reset a setting to use its configured default.</p>
    </div>

    <form wire:submit="save" class="space-y-6">
        @foreach([
            'Circulation' => [
                'loan_limit' => ['Maximum active loans per student', 'books', 1, 50, 'Applies when checking out books.'],
                'loan_period' => ['Default loan period', 'days', 1, 365, 'Applies to new loans; existing due dates stay unchanged. Some periodicals have their own loan period.'],
            ],
            'Reservations' => [
                'pickup_days' => ['Maximum pickup period', 'days', 1, 365, 'Applies to new pickup deadlines; existing deadlines stay unchanged.'],
                'approval_delay' => ['Automatic approval delay', 'minutes', 0, 10080, 'Eligible pending reservations can be approved after this delay.'],
            ],
            'Notifications' => [
                'reminder_days' => ['Due reminder lead time', 'days', 1, 365, 'A reminder is sent once per due date when a loan enters this window.'],
            ],
        ] as $section => $fields)
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="text-lg font-semibold text-slate-900">{{ $section }}</h2>
                <div class="mt-4 divide-y divide-slate-100">
                    @foreach($fields as $field => [$label, $unit, $min, $max, $help])
                        <div class="flex flex-col gap-3 py-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <label for="{{ $field }}" class="text-sm font-semibold text-slate-900">{{ $label }}</label>
                                <p class="mt-1 text-sm text-slate-600">{{ $help }}</p>
                                <p class="mt-1 text-xs text-slate-500">Default: {{ config('settings.'.['loan_limit' => 'library_config.max_loan_limit', 'loan_period' => 'library_config.loan_period', 'pickup_days' => 'reservation.max_pickup_days', 'approval_delay' => 'reservation.auto_approve_delay_minutes', 'reminder_days' => 'notifications.due_reminder_days'][$field]) }} {{ $unit }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <input id="{{ $field }}" type="number" min="{{ $min }}" max="{{ $max }}" wire:model="values.{{ $field }}" class="w-24 rounded-lg border-slate-300 text-sm focus:border-[#102B70] focus:ring-[#102B70]" aria-describedby="{{ $field }}-error">
                                <span class="text-sm text-slate-600">{{ $unit }}</span>
                                <button type="button" wire:click="resetSetting('{{ $field }}')" class="rounded-lg px-2 py-2 text-sm font-medium text-[#102B70] hover:bg-blue-50 focus-visible:ring-2 focus-visible:ring-[#102B70]">Reset</button>
                            </div>
                            @error('values.'.$field) <p id="{{ $field }}-error" class="text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                    @if($section === 'Reservations')
                        <div class="py-4">
                            <label class="flex items-center gap-3 text-sm font-semibold text-slate-900">
                                <input type="checkbox" wire:model="values.auto_approve" class="rounded border-slate-300 text-[#102B70] focus:ring-[#102B70]">
                                Automatically approve eligible reservations
                            </label>
                            <p class="mt-2 text-sm text-slate-600">Enabling this can approve requests already pending once their delay has passed.</p>
                            <button type="button" wire:click="resetSetting('auto_approve')" class="mt-2 text-sm font-medium text-[#102B70] underline">Reset automatic approval</button>
                            @error('values.auto_approve') <p class="text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>
            </section>
        @endforeach
        <div class="flex justify-end"><button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-[#102B70] px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-900 focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2 disabled:opacity-50">Save settings</button></div>
    </form>
</div>
