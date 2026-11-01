@php
    $guideSections = [
        'getting-started' => ['Getting Started', [
            'Sign in through the student login page. Complete any account setup or verification requested before entering the portal.',
            'Use Dashboard to see your library summary, current loans, and student QR code. Present the QR code at the circulation desk when needed.',
            'Open this Student Guide from the sidebar whenever you need help.',
        ]],
        'search-reserve' => ['Search & Reserve', [
            'Open the OPAC to search the library catalog, or choose Reservations in the sidebar to browse materials available for reservation.',
            'Search by title, author, or ISBN and use the available filters to narrow the results. Open a material to see its details.',
            'Choose a material and follow the reservation form. If no copy is available, the system may offer a waitlist instead. Review the details before confirming.',
        ]],
        'reservations' => ['My Reservations', [
            'Open your profile from the profile menu at the top of the page and find your reservations.',
            'Check Active Reservations for the current status and available actions. Reservation History shows past requests.',
            'Follow the instructions shown for your reservation and contact the circulation desk if you need help.',
        ]],
        'borrowing' => ['Borrowed Books & Fines', [
            'Choose Borrow in the sidebar to view your current loans and their due dates.',
            'Use Borrowing History to review past transactions and Fines & Penalties to check recorded charges.',
            'Return materials to the circulation desk by the due date. Ask library staff about settling any outstanding fines.',
        ]],
        'profile' => ['Profile & Notifications', [
            'Open the profile menu at the top of the page to access your profile and available account options.',
            'Select the notification icon in the top bar to read library alerts.',
            'Contact library staff if your information needs attention or you have questions about your account.',
        ]],
    ];
@endphp

<div x-data="{ guideOpen: false, section: 'getting-started' }">
    <button type="button" @click="guideOpen = true" aria-label="Open Student Guide" class="group flex w-full items-center rounded-xl px-3 py-2.5 text-base font-medium text-white transition-colors hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white">
        <span class="flex w-full items-center overflow-hidden" :class="sidebarMinimized ? 'justify-center' : 'gap-3'">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M9.75 9a2.25 2.25 0 114.5 0c0 1.5-2.25 1.5-2.25 3.75M12 21a9 9 0 110-18 9 9 0 010 18z" /></svg>
            <span class="whitespace-nowrap" x-show="!sidebarMinimized">Student Guide</span>
        </span>
    </button>

    <template x-teleport="body">
        <div x-cloak x-show="guideOpen" x-trap.inert.noscroll="guideOpen" @keydown.escape.window="guideOpen = false" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/60 p-3 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="student-guide-title" aria-describedby="student-guide-description">
            <div @click.outside="guideOpen = false" class="flex max-h-[90dvh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
                <header class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-7">
                    <div>
                        <h2 id="student-guide-title" class="text-xl font-bold text-[#102B70]">Student Guide</h2>
                        <p id="student-guide-description" class="mt-1 text-sm text-slate-600">Choose a topic to learn how to use your library portal.</p>
                    </div>
                    <button type="button" @click="guideOpen = false" aria-label="Close Student Guide" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </header>
                <div class="flex min-h-0 flex-1 flex-col overflow-y-auto sm:flex-row">
                    <nav aria-label="Guide topics" class="flex shrink-0 gap-2 overflow-x-auto border-b border-slate-200 bg-slate-50 p-4 sm:w-56 sm:flex-col sm:overflow-visible sm:border-b-0 sm:border-r">
                        @foreach($guideSections as $key => [$label, $steps])
                            <button type="button" @click="section = '{{ $key }}'" :aria-pressed="section === '{{ $key }}'" :class="section === '{{ $key }}' ? 'bg-[#102B70] text-white' : 'text-slate-700 hover:bg-slate-200'" class="min-h-11 shrink-0 rounded-lg px-3 py-2 text-left text-sm font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2">{{ $label }}</button>
                        @endforeach
                    </nav>
                    <div class="min-w-0 flex-1 px-5 py-6 sm:px-8 sm:py-7" aria-live="polite">
                        @foreach($guideSections as $key => [$label, $steps])
                            <section x-show="section === '{{ $key }}'" x-cloak>
                                <h3 class="text-lg font-bold text-[#102B70]">{{ $label }}</h3>
                                <ol class="mt-5 list-decimal space-y-4 pl-5 text-sm leading-6 text-slate-700 marker:font-bold marker:text-[#102B70]">
                                    @foreach($steps as $step)
                                        <li class="pl-1">{{ $step }}</li>
                                    @endforeach
                                </ol>
                            </section>
                        @endforeach
                    </div>
                </div>
                <footer class="flex justify-end border-t border-slate-200 px-5 py-4 sm:px-7">
                    <button type="button" @click="guideOpen = false" class="min-h-11 rounded-lg bg-[#102B70] px-5 py-2 text-sm font-semibold text-white hover:bg-[#0b225e] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2">Done</button>
                </footer>
            </div>
        </div>
    </template>
</div>
