<div wire:poll.60s>
        <div class="dashboard-kpis grid gap-4">
            {{-- KPI 1: Total Number of Students --}}
            <x-widget.data-card 
                title="Total Students" 
                :value="$metrics['totalStudents']['value']" 
                :change="$metrics['totalStudents']['change']" 
                :subtext="$metrics['totalStudents']['subtext']" 
            />

            {{-- KPI 2: Total Number of Books --}}
            <x-widget.data-card 
                title="Total Books" 
                :value="$metrics['totalBooks']['value']" 
                :change="$metrics['totalBooks']['change']" 
                :subtext="$metrics['totalBooks']['subtext']" 
            />

            {{-- KPI 3: Due Books Today --}}
            <x-widget.data-card 
                title="Due Books Today" 
                :value="$metrics['dueBooksToday']['value']" 
                :change="$metrics['dueBooksToday']['change']" 
                :subtext="$metrics['dueBooksToday']['subtext']" 
            />

            {{-- KPI 4: Book Reserved Today --}}
            <x-widget.data-card 
                title="Book Reserved Today" 
                :value="$metrics['bookReservedToday']['value']" 
                :change="$metrics['bookReservedToday']['change']" 
                :subtext="$metrics['bookReservedToday']['subtext']" 
            />
        </div>

</div>
