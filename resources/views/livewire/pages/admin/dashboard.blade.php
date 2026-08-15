    <div class="min-h-full bg-[#F8FAFC]">
        <div class="mx-auto w-full max-w-[1600px] max-h-auto p-4 lg:p-6 space-y-5">
            <!-- Top Row: Hero and Attention -->
            <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,2.05fr)_minmax(320px,1fr)] gap-5">
                <!-- Greeting Banner -->
                <x-greeting-banner staffName="{{ Auth::user()->name ?? 'Admin' }}" />
                
                <!-- Requires Attention -->
                <div class="h-full">
                    <x-dashboard.requires-attention 
                        :overdueCount="$overdueItems" 
                        :pendingCount="$pendingReservations" 
                    />
                </div>
            </div>

            <!-- Middle Row: 4 Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
                <x-dashboard.stat-card title="Total Titles" :value="$totalTitles" subtext="Unique book records in catalog" />
                <x-dashboard.stat-card title="Total Copies" :value="$totalCopies" subtext="Physical copies across all titles" />
                <x-dashboard.stat-card title="Active Members" :value="$activeMembers" subtext="Currently registered students" />
                <x-dashboard.stat-card title="Borrowed Items" :value="$borrowedItems" subtext="Books currently on loan" />
            </div>

            <!-- Bottom Row: Current Borrowers and Most Borrowed -->
            <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,2.05fr)_minmax(320px,1fr)] gap-5 items-start">
                <!-- Current Borrowers -->
                <x-dashboard.current-borrowers :borrowers="$currentBorrowers" />

                <!-- Most Borrowed Titles -->
                <x-dashboard.most-borrowed-titles :titles="$mostBorrowedTitles" />
            </div>
        </div>
    </div>