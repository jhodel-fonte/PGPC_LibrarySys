@props([
    'title' => 'Frequently Asked Questions',
    'subtitle' => 'Find quick answers to common questions about using the PGPC Library.',
])

@php
    $faqs = [];

    try {
        $jsonPath = storage_path('app/public/faq.json');

        if (file_exists($jsonPath)) {
            $content = file_get_contents($jsonPath);

            if ($content !== false) {
                $decoded = json_decode($content, true);
                $faqs = $decoded['faqs'] ?? (is_array($decoded) ? $decoded : []);
            }
        }
    } catch (\Throwable $e) {
        $faqs = [];
    }

    $totalFaqs = count($faqs);
    $half = (int) ceil($totalFaqs / 2);
    $col1 = array_slice($faqs, 0, $half);
    $col2 = array_slice($faqs, $half);
@endphp

@if (!empty($faqs))
    <!-- FAQ Section (Soft Blue Rhythm: #F3F6FA) -->
    <section id="faq" class="relative w-full bg-[#F3F6FA] py-10 lg:py-15 select-none border-t border-[#DCE5F0]/60">
        <div class="mx-auto max-w-[1100px] px-4 sm:px-6 lg:px-8">

            <!-- Centered Section Header -->
            <div class="text-center max-w-xl mx-auto mb-10 sm:mb-12">
                <!-- Main Heading -->
                <h2 class="text-5xl sm:text-3xl font-extrabold tracking-tight text-[#0B1B3A]">
                    {{ $title }}
                </h2>

                <!-- Subtitle -->
                <p class="mt-2 text-[14.5px] sm:text-[15px] text-[#61738F] leading-relaxed">
                    {{ $subtitle }}
                </p>
            </div>

            <!-- Two-Column Desktop FAQ Accordion -->
            <div
                x-data="{
                    openItems: [],
                    toggle(id) {
                        if (this.openItems.includes(id)) {
                            this.openItems = this.openItems.filter(item => item !== id);
                        } else {
                            this.openItems.push(id);
                        }
                    },
                    isOpen(id) {
                        return this.openItems.includes(id);
                    }
                }"
                class="grid grid-cols-1 md:grid-cols-2 gap-3.5 sm:gap-4 lg:gap-5 items-start"
            >
                <!-- Left Column Stack -->
                <div class="space-y-3.5 sm:space-y-4">
                    @foreach ($col1 as $index => $faq)
                        @php $faqId = $faq['id'] ?? ('col1_' . $index); @endphp
                        <div
                            class="rounded-2xl border border-[#DCE5F0] bg-white transition-all duration-200 overflow-hidden shadow-xs hover:border-[#163B7A]/30"
                            :class="{ 'border-[#163B7A]/40 ring-1 ring-[#163B7A]/15 shadow-sm': isOpen('{{ $faqId }}') }"
                        >
                            <!-- Question Button (68px–76px) -->
                            <button
                                type="button"
                                @click="toggle('{{ $faqId }}')"
                                class="flex w-full items-center justify-between min-h-[68px] sm:min-h-[74px] px-5 py-3.5 text-left transition-colors select-none focus:outline-none focus-visible:ring-2 focus-visible:ring-[#163B7A]/25"
                                :aria-expanded="isOpen('{{ $faqId }}') ? 'true' : 'false'"
                            >
                                <div class="flex items-center gap-3 pr-3">
                                    @if (!empty($faq['id']))
                                        <span class="text-[12.5px] font-bold text-[#5D769D] tabular-nums shrink-0">
                                            {{ $faq['id'] }}
                                        </span>
                                    @endif
                                    <span class="text-[14.5px] sm:text-[15.5px] font-semibold text-[#0B1B3A] leading-snug">
                                        {{ $faq['question'] ?? '' }}
                                    </span>
                                </div>

                                <!-- Right-Aligned Plus/Minus Toggle Icon -->
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#F7F9FC] border border-[#DCE5F0]/80 transition-all duration-200"
                                    :class="{ 'rotate-45 bg-[#163B7A]/10 border-[#163B7A]/20': isOpen('{{ $faqId }}') }"
                                >
                                    <svg class="h-4 w-4 text-[#163B7A]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                            </button>

                            <!-- Collapsible Answer Area -->
                            <div
                                x-show="isOpen('{{ $faqId }}')"
                                x-collapse
                                x-cloak
                            >
                                <div class="border-t border-[#DCE5F0] px-5 pt-3.5 pb-5 text-[14px] leading-relaxed text-[#61738F] font-normal">
                                    {{ $faq['answer'] ?? '' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Right Column Stack -->
                <div class="space-y-3.5 sm:space-y-4">
                    @foreach ($col2 as $index => $faq)
                        @php $faqId = $faq['id'] ?? ('col2_' . $index); @endphp
                        <div
                            class="rounded-2xl border border-[#DCE5F0] bg-white transition-all duration-200 overflow-hidden shadow-xs hover:border-[#163B7A]/30"
                            :class="{ 'border-[#163B7A]/40 ring-1 ring-[#163B7A]/15 shadow-sm': isOpen('{{ $faqId }}') }"
                        >
                            <!-- Question Button (68px–76px) -->
                            <button
                                type="button"
                                @click="toggle('{{ $faqId }}')"
                                class="flex w-full items-center justify-between min-h-[68px] sm:min-h-[74px] px-5 py-3.5 text-left transition-colors select-none focus:outline-none focus-visible:ring-2 focus-visible:ring-[#163B7A]/25"
                                :aria-expanded="isOpen('{{ $faqId }}') ? 'true' : 'false'"
                            >
                                <div class="flex items-center gap-3 pr-3">
                                    @if (!empty($faq['id']))
                                        <span class="text-[12.5px] font-bold text-[#5D769D] tabular-nums shrink-0">
                                            {{ $faq['id'] }}
                                        </span>
                                    @endif
                                    <span class="text-[14.5px] sm:text-[15.5px] font-semibold text-[#0B1B3A] leading-snug">
                                        {{ $faq['question'] ?? '' }}
                                    </span>
                                </div>

                                <!-- Right-Aligned Plus/Minus Toggle Icon -->
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#F7F9FC] border border-[#DCE5F0]/80 transition-all duration-200"
                                    :class="{ 'rotate-45 bg-[#163B7A]/10 border-[#163B7A]/20': isOpen('{{ $faqId }}') }"
                                >
                                    <svg class="h-4 w-4 text-[#163B7A]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                            </button>

                            <!-- Collapsible Answer Area -->
                            <div
                                x-show="isOpen('{{ $faqId }}')"
                                x-collapse
                                x-cloak
                            >
                                <div class="border-t border-[#DCE5F0] px-5 pt-3.5 pb-4.5 text-[14px] leading-relaxed text-[#61738F] font-normal">
                                    {{ $faq['answer'] ?? '' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </section>
@endif
