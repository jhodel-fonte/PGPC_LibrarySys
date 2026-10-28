<?php

namespace App\Livewire\Pages\Dashboard;

use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.layouts.admin', [
    'title' => 'Admin Dashboard',
    'subpage' => 'Operational Overview',
])]
class TestDashboard extends Component
{
    public string $trendsFilter = '7_days';

    public function setTrendsFilter(string $filter): void
    {
        $this->trendsFilter = $filter;
    }

    public function render()
    {
        // 1. Four Main Summary Cards (Top of Dashboard)
        $summary = [
            'totalBooks' => [
                'count' => '15,420',
                'label' => 'Total Books',
                'subtext' => 'Total cataloged physical copies across all stacks',
                'badge' => 'Cataloged Items',
            ],
            'activeLoans' => [
                'count' => '142',
                'label' => 'Active Loans',
                'subtext' => 'Items currently borrowed and in circulation',
                'badge' => 'In Circulation',
            ],
            'overdueItems' => [
                'count' => '18',
                'label' => 'Overdue Items',
                'subtext' => 'Items past due date awaiting staff follow-up',
                'badge' => 'Requires Follow-up',
                'link' => url('/analytics/circulation?tab=overdue'),
            ],
            'pendingReservations' => [
                'count' => '7',
                'label' => 'Pending Reservations',
                'subtext' => 'Hold requests awaiting librarian preparation',
                'badge' => 'Awaiting Action',
                'link' => url('/admin/circulation-desk'),
            ],
        ];

        // 2. Needs Attention Queue
        $needsAttention = [
            [
                'count' => 7,
                'title' => 'Pending Reservations',
                'subtext' => 'Hold requests waiting for staff preparation & pickup staging',
                'detail' => '3 requests submitted today • 4 pending from yesterday',
                'actionLabel' => 'Process Queue',
                'link' => url('/admin/circulation-desk'),
                'type' => 'reservation',
                'color' => 'amber',
            ],
            [
                'count' => 18,
                'title' => 'Overdue Items',
                'subtext' => 'Unreturned books past their assigned borrowing due dates',
                'detail' => '12 items 1-3 days late • 6 items requiring patron follow-up',
                'actionLabel' => 'View Overdue List',
                'link' => url('/analytics/circulation?tab=overdue'),
                'type' => 'overdue',
                'color' => 'red',
            ],
            [
                'count' => 14,
                'title' => 'Books Due Today',
                'subtext' => 'Items scheduled for check-in before desk closing at 5:00 PM',
                'detail' => '8 checked in on time • 6 pending student returns',
                'actionLabel' => 'Today\'s Schedule',
                'link' => url('/analytics?section=operations'),
                'type' => 'due_today',
                'color' => 'blue',
            ],
        ];

        // 3. Compact 7-Day Borrowing & Returning Velocity
        $trendsLabels = ['Mon, Oct 20', 'Tue, Oct 21', 'Wed, Oct 22', 'Thu, Oct 23', 'Fri, Oct 24', 'Sat, Oct 25', 'Sun, Oct 26'];
        $trendsSeries = [
            ['name' => 'Books Borrowed', 'data' => [24, 32, 28, 35, 41, 18, 12], 'color' => '#102B70'],
            ['name' => 'Books Returned', 'data' => [19, 28, 25, 30, 36, 15, 8], 'color' => '#3B82F6'],
        ];

        // 4. Most Borrowed Titles (Top 5 this month)
        $popularBooks = [
            [
                'rank' => 1,
                'title' => 'Introduction to Algorithms (4th Edition)',
                'author' => 'Thomas H. Cormen, Charles E. Leiserson',
                'category' => 'Computer Science & Software',
                'borrows' => 48,
                'available' => 1,
                'total' => 6,
            ],
            [
                'rank' => 2,
                'title' => 'Philippine Criminological Jurisprudence & Penology',
                'author' => 'Dean R. Santos, LLB',
                'category' => 'Criminal Justice & Law',
                'borrows' => 42,
                'available' => 0,
                'total' => 5,
            ],
            [
                'rank' => 3,
                'title' => 'Database System Concepts (7th Edition)',
                'author' => 'Abraham Silberschatz, Henry F. Korth',
                'category' => 'Information Technology',
                'borrows' => 36,
                'available' => 3,
                'total' => 8,
            ],
            [
                'rank' => 4,
                'title' => 'Principles of Managerial Finance & Accounting',
                'author' => 'Lawrence J. Gitman, Chad J. Zutter',
                'category' => 'Business Administration',
                'borrows' => 29,
                'available' => 1,
                'total' => 4,
            ],
            [
                'rank' => 5,
                'title' => 'Tourism and Hospitality Marketing Strategies',
                'author' => 'Philip Kotler, John T. Bowen',
                'category' => 'Hospitality & Tourism',
                'borrows' => 24,
                'available' => 2,
                'total' => 5,
            ],
        ];

        // 5. Recent Circulation Activity (Latest 6-8 records)
        $recentActivity = [
            [
                'student' => 'Maria Santos',
                'student_id' => '2023-00142',
                'program' => 'BSIT - Year 3',
                'initials' => 'MS',
                'book' => 'Introduction to Algorithms (4th Edition)',
                'accession' => 'ACC-0042',
                'type' => 'borrow', // 'borrow' or 'return'
                'date' => 'Today, 2:15 PM',
                'due_date' => 'Nov 02, 2026',
                'status' => 'On Loan',
                'status_color' => 'blue',
            ],
            [
                'student' => 'Juan Dela Cruz',
                'student_id' => '2022-00512',
                'program' => 'BSCrim - Year 4',
                'initials' => 'JD',
                'book' => 'Philippine Criminological Jurisprudence',
                'accession' => 'ACC-0108',
                'type' => 'return',
                'date' => 'Today, 1:40 PM',
                'due_date' => 'Oct 26, 2026',
                'status' => 'Returned On-Time',
                'status_color' => 'emerald',
            ],
            [
                'student' => 'Angelica Reyes',
                'student_id' => '2024-01201',
                'program' => 'BSBA - Year 2',
                'initials' => 'AR',
                'book' => 'Principles of Managerial Finance & Accounting',
                'accession' => 'ACC-0214',
                'type' => 'borrow',
                'date' => 'Today, 11:20 AM',
                'due_date' => 'Nov 02, 2026',
                'status' => 'On Loan',
                'status_color' => 'blue',
            ],
            [
                'student' => 'Carlo Gonzales',
                'student_id' => '2021-00890',
                'program' => 'BSCS - Year 4',
                'initials' => 'CG',
                'book' => 'Operating System Concepts (10th Edition)',
                'accession' => 'ACC-0077',
                'type' => 'borrow',
                'date' => 'Oct 25, 3:30 PM',
                'due_date' => 'Nov 01, 2026',
                'status' => 'On Loan',
                'status_color' => 'blue',
            ],
            [
                'student' => 'Patricia Lim',
                'student_id' => '2023-00331',
                'program' => 'BSHM - Year 3',
                'initials' => 'PL',
                'book' => 'Food & Beverage Service Operations Manual',
                'accession' => 'ACC-0312',
                'type' => 'return',
                'date' => 'Oct 25, 10:15 AM',
                'due_date' => 'Oct 24, 2026',
                'status' => 'Returned (1d late)',
                'status_color' => 'slate',
            ],
            [
                'student' => 'Joshua Mendoza',
                'student_id' => '2022-00674',
                'program' => 'BPA - Year 3',
                'initials' => 'JM',
                'book' => 'Public Administration in the Philippines',
                'accession' => 'ACC-0189',
                'type' => 'borrow',
                'date' => 'Oct 18, 4:05 PM',
                'due_date' => 'Oct 25, 2026',
                'status' => 'Overdue',
                'status_color' => 'red',
            ],
        ];

        return view('livewire.pages.dashboard.test-dashboard', [
            'summary' => $summary,
            'needsAttention' => $needsAttention,
            'trendsLabels' => $trendsLabels,
            'trendsSeries' => $trendsSeries,
            'popularBooks' => $popularBooks,
            'recentActivity' => $recentActivity,
        ]);
    }
}
