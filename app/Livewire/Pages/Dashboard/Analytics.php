<?php

namespace App\Livewire\Pages\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.layouts.admin', ['title' => 'Analytics', 'subpage' => 'Operational & Circulation Statistics'])]
class Analytics extends Component
{
    // Interactive UI Filters
    public string $timeRange = '30_days'; // '7_days', '30_days', '90_days', 'this_semester', 'this_year'
    public string $departmentFilter = 'all'; // 'all', 'BSCS', 'BS Crim', 'BSMA', 'BPA'
    public string $circulationMetric = 'all'; // 'all', 'borrowed', 'returned', 'reservations'

    public function setTimeRange(string $range): void
    {
        $this->timeRange = $range;
    }

    public function setCirculationMetric(string $metric): void
    {
        $this->circulationMetric = $metric;
    }

    /**
     * Static multi-series data for the Circulation Dynamics Line Graph.
     */
    public function getCirculationTrendData(): array
    {
        // Static datasets keyed by time range
        $datasets = [
            '7_days' => [
                'labels' => ['Mon, Oct 19', 'Tue, Oct 20', 'Wed, Oct 21', 'Thu, Oct 22', 'Fri, Oct 23', 'Sat, Oct 24', 'Sun, Oct 25'],
                'borrowed' => [18, 26, 38, 31, 24, 12, 19],
                'returned' => [14, 20, 32, 28, 22, 9, 15],
                'reservations' => [8, 14, 19, 15, 11, 4, 9],
            ],
            '30_days' => [
                'labels' => ['Oct 1', 'Oct 4', 'Oct 7', 'Oct 10', 'Oct 13', 'Oct 16', 'Oct 19', 'Oct 22', 'Oct 25', 'Oct 28'],
                'borrowed' => [38, 44, 52, 60, 55, 68, 62, 74, 79, 85],
                'returned' => [32, 38, 45, 52, 49, 61, 56, 67, 72, 78],
                'reservations' => [15, 19, 22, 27, 24, 30, 27, 34, 36, 40],
            ],
            '90_days' => [
                'labels' => ['Wk 32', 'Wk 33', 'Wk 34', 'Wk 35', 'Wk 36', 'Wk 37', 'Wk 38', 'Wk 39', 'Wk 40', 'Wk 41', 'Wk 42', 'Wk 43'],
                'borrowed' => [95, 110, 104, 128, 142, 135, 118, 150, 165, 140, 155, 172],
                'returned' => [88, 98, 96, 115, 130, 122, 110, 138, 150, 132, 145, 158],
                'reservations' => [35, 42, 38, 48, 55, 50, 42, 58, 62, 54, 59, 68],
            ],
            'this_semester' => [
                'labels' => ['August', 'September', 'October', 'November', 'December'],
                'borrowed' => [340, 520, 580, 490, 410],
                'returned' => [295, 470, 530, 460, 420],
                'reservations' => [130, 210, 240, 190, 160],
            ],
            'this_year' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'borrowed' => [180, 240, 320, 290, 210, 110, 140, 360, 490, 540, 480, 390],
                'returned' => [160, 215, 295, 270, 205, 105, 130, 320, 450, 510, 460, 400],
                'reservations' => [65, 95, 120, 110, 80, 40, 55, 140, 190, 215, 185, 150],
            ],
        ];

        $current = $datasets[$this->timeRange] ?? $datasets['30_days'];

        // Assemble series according to active stream filter
        $series = [];
        if ($this->circulationMetric === 'borrowed') {
            $series[] = ['name' => 'Loans (Checkouts)', 'data' => $current['borrowed'], 'color' => '#102B70'];
        } elseif ($this->circulationMetric === 'returned') {
            $series[] = ['name' => 'Returned Items', 'data' => $current['returned'], 'color' => '#3B82F6'];
        } elseif ($this->circulationMetric === 'reservations') {
            $series[] = ['name' => 'Hold Reservations', 'data' => $current['reservations'], 'color' => '#FCC719'];
        } else {
            $series[] = ['name' => 'Loans (Checkouts)', 'data' => $current['borrowed'], 'color' => '#102B70'];
            $series[] = ['name' => 'Returned Items', 'data' => $current['returned'], 'color' => '#3B82F6'];
            $series[] = ['name' => 'Hold Reservations', 'data' => $current['reservations'], 'color' => '#FCC719'];
        }

        return [
            'labels' => $current['labels'],
            'series' => $series,
        ];
    }

    /**
     * Static data for Category Distribution Donut Chart.
     */
    public function getCategoryDistributionData(): array
    {
        $labels = [
            '000 - Computer Sci & IT',
            '600 - Applied Sciences & Tech',
            '300 - Social Sciences & Law',
            '650 - Management Accounting',
            '800 - Literature & Language',
            'Others / Generalities'
        ];
        $series = [485, 392, 340, 298, 215, 142];

        return [
            'labels' => $labels,
            'series' => $series,
            'total' => array_sum($series),
        ];
    }

    /**
     * Static data for Academic Department Utilization Grouped Bar Chart.
     */
    public function getDepartmentUtilizationData(): array
    {
        return [
            'labels' => [
                'BS Computer Sci',
                'BS Criminology',
                'BS Mgmt Acctg',
                'Pub Administration',
                'Senior High',
                'Faculty & Staff'
            ],
            'series' => [
                ['name' => 'Catalog Loans (Checkouts)', 'data' => [480, 395, 345, 260, 210, 145], 'color' => '#102B70'],
                ['name' => 'In-Library / Room Use', 'data' => [310, 280, 240, 190, 160, 95], 'color' => '#3B82F6'],
            ],
        ];
    }

    /**
     * Static data for Circulation Desk Rush Hours & Hourly Distribution.
     */
    public function getHourlyTrafficData(): array
    {
        return [
            ['hour' => '8:00 AM', 'count' => 18, 'pct' => 25, 'isPeak' => false],
            ['hour' => '9:00 AM', 'count' => 38, 'pct' => 52, 'isPeak' => false],
            ['hour' => '10:00 AM', 'count' => 64, 'pct' => 88, 'isPeak' => true, 'tag' => 'Morning Peak'],
            ['hour' => '11:00 AM', 'count' => 45, 'pct' => 62, 'isPeak' => false],
            ['hour' => '12:00 PM', 'count' => 28, 'pct' => 38, 'isPeak' => false],
            ['hour' => '1:00 PM', 'count' => 58, 'pct' => 80, 'isPeak' => false],
            ['hour' => '2:00 PM', 'count' => 72, 'pct' => 100, 'isPeak' => true, 'tag' => 'Afternoon Rush'],
            ['hour' => '3:00 PM', 'count' => 56, 'pct' => 78, 'isPeak' => false],
            ['hour' => '4:00 PM', 'count' => 42, 'pct' => 58, 'isPeak' => false],
            ['hour' => '5:00 PM', 'count' => 22, 'pct' => 30, 'isPeak' => false],
        ];
    }

    /**
     * Static Executive Summary KPI Metrics.
     */
    public function getKpiMetrics(): array
    {
        return [
            'totalCirculation' => [
                'value' => '2,845',
                'change' => '+14.2%',
                'isPositive' => true,
                'subtext' => 'vs. previous cycle period',
            ],
            'activeLoans' => [
                'value' => '418',
                'change' => '91.4%',
                'isPositive' => true,
                'subtext' => 'on-time return compliance',
            ],
            'activePatrons' => [
                'value' => '1,290',
                'change' => '+8.6%',
                'isPositive' => true,
                'subtext' => 'enrolled student adoption',
            ],
            'turnoverVelocity' => [
                'value' => '3.4x',
                'change' => '+0.6x',
                'isPositive' => true,
                'subtext' => 'annual catalog turnover rate',
            ],
            'overdueResolution' => [
                'value' => '18',
                'change' => '-22.5%',
                'isPositive' => true,
                'subtext' => 'delinquent loans resolved',
            ],
        ];
    }

    /**
     * Static Top 5 Circulated & High-Demand Book Titles.
     */
    public function getTopCirculatedTitles(): array
    {
        return [
            [
                'rank' => 1,
                'title' => 'Data Structures and Algorithms in Python',
                'author' => 'Michael T. Goodrich, Roberto Tamassia',
                'call_number' => 'QA 76.73 .P98 G66 2024',
                'borrows' => 142,
                'status' => 'High Demand',
                'trend' => '+24%',
            ],
            [
                'rank' => 2,
                'title' => 'Philippine Criminal Justice System: Principles and Procedures',
                'author' => 'Dean Rodolfo S. Bautista, Ph.D.',
                'call_number' => 'HV 8141 .B38 2023',
                'borrows' => 118,
                'status' => 'In Circulation',
                'trend' => '+16%',
            ],
            [
                'rank' => 3,
                'title' => 'Management Accounting: Strategic Decision Making',
                'author' => 'Prof. Carmela Hernandez, CPA',
                'call_number' => 'HF 5657.4 .H47 2024',
                'borrows' => 96,
                'status' => 'In Circulation',
                'trend' => '+12%',
            ],
            [
                'rank' => 4,
                'title' => 'Modern Database Management (13th Edition)',
                'author' => 'Jeffrey A. Hoffer, Ramesh Venkataraman',
                'call_number' => 'QA 76.9 .D3 H64 2022',
                'borrows' => 89,
                'status' => 'Available',
                'trend' => '+9%',
            ],
            [
                'rank' => 5,
                'title' => 'Public Administration in the Philippines: Governance & Reform',
                'author' => 'Dr. Emmanuel C. Morales',
                'call_number' => 'JQ 1411 .M67 2023',
                'borrows' => 74,
                'status' => 'Available',
                'trend' => '+5%',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.pages.dashboard.analytics', [
            'circulationTrend' => $this->getCirculationTrendData(),
            'categoryDonut' => $this->getCategoryDistributionData(),
            'departmentBars' => $this->getDepartmentUtilizationData(),
            'hourlyTraffic' => $this->getHourlyTrafficData(),
            'kpiMetrics' => $this->getKpiMetrics(),
            'topBooks' => $this->getTopCirculatedTitles(),
        ]);
    }
}
