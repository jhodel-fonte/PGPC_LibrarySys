@props([
    'show' => true,
    'type' => 'danger', // 'danger'|'hard-delete', 'warning'|'disable'|'soft-delete', 'primary'|'navy', 'info', 'success'
    'title' => 'Confirm Action',
    'subtitle' => null,
    'icon' => null, // 'trash', 'ban', 'warning', 'info', 'check', 'none'
    'maxWidth' => 'max-w-md',
    
    // Target entity details
    'targetName' => null,
    'targetId' => null,
    'targetRole' => 'account',
    
    // Preset behaviors
    'isHardDelete' => false,
    'hasHistory' => false,
    'historyCount' => 0,
    'historyMessage' => null,
    
    // Custom content
    'message' => null,
    'bulletPoints' => null,
    'warningNotice' => null,
    
    // Actions & Buttons
    'confirmAction' => null,
    'closeAction' => 'closeModal',
    'cancelAction' => null,
    'confirmText' => null,
    'cancelText' => 'Cancel',
    'confirmButtonClass' => null,
    'confirmLoadingTarget' => null,
    'confirmDisabled' => false,
])

@php
    $show = $show ?? true;
    $type = $type ?? 'danger';
    $title = $title ?? 'Confirm Action';
    $subtitle = $subtitle ?? null;
    $icon = $icon ?? null;
    $maxWidth = $maxWidth ?? 'max-w-md';
    $targetName = $targetName ?? null;
    $targetId = $targetId ?? null;
    $targetRole = $targetRole ?? 'account';
    $isHardDelete = $isHardDelete ?? false;
    $hasHistory = $hasHistory ?? false;
    $historyCount = $historyCount ?? 0;
    $historyMessage = $historyMessage ?? null;
    $message = $message ?? null;
    $bulletPoints = $bulletPoints ?? null;
    $warningNotice = $warningNotice ?? null;
    $confirmAction = $confirmAction ?? null;
    $closeAction = $closeAction ?? 'closeModal';
    $cancelAction = $cancelAction ?? null;
    $confirmText = $confirmText ?? null;
    $cancelText = $cancelText ?? 'Cancel';
    $confirmButtonClass = $confirmButtonClass ?? null;
    $confirmLoadingTarget = $confirmLoadingTarget ?? null;
    $confirmDisabled = $confirmDisabled ?? false;

    $effectiveClose = $cancelAction ?? $closeAction ?? 'closeModal';
    $normalizedType = strtolower($type);

    // Map common aliases
    $isDisableType = in_array($normalizedType, ['disable', 'soft-delete', 'warning', 'amber']);
    $isDeleteType = in_array($normalizedType, ['delete', 'hard-delete', 'danger', 'red']);
    $isPrimaryType = in_array($normalizedType, ['primary', 'navy', 'blue']);
    $isSuccessType = in_array($normalizedType, ['success', 'green', 'emerald']);

    // Determine default icon if not explicitly set
    $effectiveIcon = $icon;
    if ($effectiveIcon === null) {
        if ($isDisableType) {
            $effectiveIcon = 'ban';
        } elseif ($isDeleteType) {
            $effectiveIcon = 'trash';
        } elseif ($isSuccessType) {
            $effectiveIcon = 'check';
        } elseif ($isPrimaryType) {
            $effectiveIcon = 'info';
        }
    }

    // Modal container border
    $modalBorder = 'border-[#CBD5E1]';
    if ($isDeleteType) {
        $modalBorder = $isHardDelete ? 'border-[#FECACA]' : 'border-[#E2E8F0]';
    } elseif ($isPrimaryType) {
        $modalBorder = 'border-[#E2E8F0]';
    }

    // Title color class
    $titleColorClass = 'text-[#0F172A]';
    if ($isDeleteType && $isHardDelete) {
        $titleColorClass = 'text-[#B91C1C]';
    } elseif ($isPrimaryType || ($isDeleteType && !$isHardDelete)) {
        $titleColorClass = 'text-[#102B70]';
    }

    // Default Subtitle
    $effectiveSubtitle = $subtitle;
    if ($effectiveSubtitle === null) {
        if ($isDisableType) {
            $effectiveSubtitle = 'Soft Delete / Account Deactivation';
        } elseif ($isDeleteType && $isHardDelete) {
            $effectiveSubtitle = 'Irreversible Hard Delete';
        }
    }

    // Confirm button defaults
    $defaultConfirmText = 'Confirm';
    $defaultConfirmClass = 'bg-[#102B70] hover:bg-[#0B225E]';
    if ($isDisableType) {
        $defaultConfirmText = 'Disable Account';
        $defaultConfirmClass = 'bg-amber-600 hover:bg-amber-700';
    } elseif ($isDeleteType) {
        $defaultConfirmText = $isHardDelete ? 'Permanently Delete' : 'Delete ' . ucfirst($targetRole);
        $defaultConfirmClass = 'bg-[#EF4444] hover:bg-[#DC2626]';
    } elseif ($isSuccessType) {
        $defaultConfirmText = 'Save Changes';
        $defaultConfirmClass = 'bg-emerald-600 hover:bg-emerald-700';
    }

    $effectiveConfirmText = $confirmText ?? $defaultConfirmText;
    $effectiveConfirmClass = $confirmButtonClass ?? $defaultConfirmClass;
    $effectiveLoadingTarget = $confirmLoadingTarget ?? $confirmAction;
@endphp

@if($show)
    <div 
        class="fixed inset-0 z-50 flex items-center justify-center p-3.5 sm:p-4 bg-black/60 backdrop-blur-xs overflow-y-auto" 
        role="dialog" 
        aria-modal="true"
        x-cloak
    >
        <div class="my-auto w-full {{ $maxWidth }} space-y-4 rounded-2xl border {{ $modalBorder }} bg-white p-5 sm:p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
            
            <!-- MODAL HEADER -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5 min-w-0">
                    @if($effectiveIcon && $effectiveIcon !== 'none')
                        @if($isDisableType)
                            <div class="h-9 w-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                            </div>
                        @elseif($isDeleteType && $isHardDelete)
                            <div class="h-9 w-9 rounded-xl bg-[#FEF2F2] text-[#EF4444] flex items-center justify-center shrink-0 border border-[#FECACA]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </div>
                        @elseif($isPrimaryType)
                            <div class="h-9 w-9 rounded-xl bg-[#EFF6FF] text-[#102B70] flex items-center justify-center shrink-0 border border-[#DBEAFE]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                </svg>
                            </div>
                        @endif
                    @endif

                    <div class="min-w-0">
                        <h3 class="text-base font-bold {{ $titleColorClass }} truncate">{{ $title }}</h3>
                        @if($effectiveSubtitle)
                            <p class="text-sm text-slate-500 font-medium">{{ $effectiveSubtitle }}</p>
                        @endif
                    </div>
                </div>

                <button 
                    type="button" 
                    wire:click="{{ $effectiveClose }}" 
                    class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 cursor-pointer transition shrink-0"
                    aria-label="Close modal"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- MODAL BODY -->
            <div class="space-y-4">
                @if(isset($slot) && !$slot->isEmpty())
                    {{ $slot }}
                @elseif($isDisableType)
                    {{-- DISABLE / SOFT DELETE PRESET --}}
                    <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-4 text-sm text-amber-900 space-y-2 leading-relaxed">
                        <p class="font-bold">
                            {{ $message ?? 'Are you sure you want to disable this ' . strtolower($targetRole) . ' account?' }}
                        </p>
                        @if($targetName)
                            <p>
                                {{ ucfirst($targetRole) }}: <strong class="text-[#0F172A]">{{ $targetName }}</strong>
                                @if($targetId)
                                    <span class="text-slate-600">(ID: {{ $targetId }})</span>
                                @endif
                            </p>
                        @endif
                        <ul class="list-disc list-inside space-y-1 text-amber-800">
                            @if(!empty($bulletPoints) && is_array($bulletPoints))
                                @foreach($bulletPoints as $point)
                                    <li>{!! $point !!}</li>
                                @endforeach
                            @else
                                <li>The account will be soft-deleted and marked as <strong>Inactive</strong>.</li>
                                <li>The {{ strtolower($targetRole) }} will no longer be able to log in or borrow books.</li>
                                <li>All circulation history, records, and fines are preserved.</li>
                                <li>You can re-enable this account at any time.</li>
                            @endif
                        </ul>
                    </div>
                @elseif($isDeleteType && $isHardDelete)
                    {{-- HARD DELETE PRESET --}}
                    @if($hasHistory)
                        <div class="rounded-xl border border-red-300 bg-red-50 p-4 text-sm text-red-900 space-y-2 leading-relaxed">
                            <div class="flex items-center gap-2 font-bold text-red-700">
                                <svg class="w-4 h-4 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>Circulation Transactions Exist</span>
                            </div>
                            <p>
                                @if($historyMessage)
                                    {!! $historyMessage !!}
                                @else
                                    This {{ strtolower($targetRole) }} has recorded <strong>{{ $historyCount }}</strong> library transaction(s).
                                @endif
                            </p>
                            <p class="font-semibold text-red-800">
                                To preserve institutional audit logs, database foreign key constraints will block permanent deletion. Please use <strong class="underline">Disable Account</strong> instead.
                            </p>
                        </div>
                    @else
                        <div class="rounded-xl border border-red-200 bg-[#FEF2F2] p-4 text-sm text-red-900 space-y-2 leading-relaxed">
                            <p class="font-bold text-[#B91C1C]">Warning: This action CANNOT be undone!</p>
                            <p>
                                You are about to permanently delete the {{ strtolower($targetRole) }} account for 
                                <strong class="text-slate-900">{{ $targetName ?? 'this user' }}</strong>
                                @if($targetId)
                                    (ID: {{ $targetId }})
                                @endif.
                            </p>
                            <p>
                                {{ $message ?? 'The account, login credentials, and profile will be permanently wiped from the database using a hard delete.' }}
                            </p>
                        </div>
                    @endif
                @elseif($isDeleteType)
                    {{-- SOFT DELETE (STUDENT / STANDARD DELETE) PRESET --}}
                    @if($message)
                        <p class="text-sm text-slate-600 leading-relaxed">
                            {!! $message !!}
                        </p>
                    @elseif($targetName)
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Are you sure you want to delete the {{ strtolower($targetRole) }} account for <strong class="text-slate-900">{{ $targetName }}</strong>
                            @if($targetId)
                                (ID: <span class="font-mono font-semibold">{{ $targetId }}</span>)
                            @endif?
                        </p>
                    @endif

                    @if($hasHistory)
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-3.5 text-sm text-amber-800 flex items-start gap-2">
                            <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>
                                @if($historyMessage)
                                    {!! $historyMessage !!}
                                @else
                                    This {{ strtolower($targetRole) }} currently has <strong>{{ $historyCount }} active borrowed book(s)</strong>. Active loans must be returned before deleting the account.
                                @endif
                            </span>
                        </div>
                    @elseif($warningNotice)
                        <div class="rounded-xl border border-red-100 bg-red-50/60 p-3.5 text-sm text-[#B91C1C] leading-relaxed">
                            {!! $warningNotice !!}
                        </div>
                    @else
                        <div class="rounded-xl border border-red-100 bg-red-50/60 p-3.5 text-sm text-[#B91C1C] leading-relaxed">
                            This will soft-delete the {{ strtolower($targetRole) }} record, deactivate their system credentials, and revoke library access.
                        </div>
                    @endif
                @else
                    {{-- GENERIC MESSAGE --}}
                    @if($message)
                        <p class="text-sm text-slate-700 leading-relaxed">
                            {!! $message !!}
                        </p>
                    @endif
                @endif

                <!-- MODAL FOOTER BUTTONS -->
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        wire:click="{{ $effectiveClose }}"
                        class="h-10 px-4 rounded-xl border border-slate-300 text-sm font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer"
                    >
                        {{ $cancelText }}
                    </button>

                    @if($confirmAction && (!$isHardDelete || !$hasHistory))
                        <button
                            type="button"
                            wire:click="{{ $confirmAction }}"
                            @if($effectiveLoadingTarget)
                                wire:loading.attr="disabled"
                                wire:target="{{ $effectiveLoadingTarget }}"
                            @endif
                            @if($confirmDisabled || ($hasHistory && $isDeleteType && !$isHardDelete))
                                disabled
                            @endif
                            class="h-10 px-5 rounded-xl {{ $effectiveConfirmClass }} text-sm font-bold text-white shadow-sm transition active:scale-[0.98] cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            @if($effectiveLoadingTarget)
                                <svg wire:loading wire:target="{{ $effectiveLoadingTarget }}" class="w-3.5 h-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            @endif
                            <span>{{ $effectiveConfirmText }}</span>
                        </button>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endif