<?php

namespace App\Livewire\Components\Widgets;

use App\Models\BorrowingTransaction;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Charts extends Component
{
    public string $type = 'line'; // 'line', 'bar', 'donut'
    public string $title = 'Circulation Analytics';
    public string $filter = '30_days'; // '7_days', '30_days', 'this_year'
    public int $height = 200;

    public function mount(string $type = 'line', string $title = 'Circulation Analytics', string $filter = '30_days', int $height = 200): void
    {
        $this->type = $type;
        $this->title = $title;
        $this->filter = $filter;
        $this->height = $height;
    }

    public function setFilter(string $newFilter): void
    {
        $this->filter = $newFilter;
    }

    protected function getLineData(): array
    {
        $now = Carbon::now();
        $days = $this->filter === '7_days' ? 7 : ($this->filter === 'this_year' ? 12 : 10);
        $labels = [];
        $borrowed = [];
        $returned = [];

        if ($this->filter === 'this_year') {
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            foreach ($months as $idx => $m) {
                $labels[] = $m;
                $borrowed[] = [140, 210, 310, 280, 190, 110, 130, 340, 480, 520, 460, 390][$idx];
                $returned[] = [120, 190, 280, 260, 180, 95, 120, 310, 440, 490, 430, 380][$idx];
            }
        } elseif ($this->filter === '7_days') {
            for ($i = 6; $i >= 0; $i--) {
                $dt = (clone $now)->subDays($i);
                $labels[] = $dt->format('D');
                $borrowed[] = [14, 22, 19, 31, 28, 12, 18][6 - $i];
                $returned[] = [11, 16, 18, 25, 24, 9, 15][6 - $i];
            }
        } else {
            for ($i = 9; $i >= 0; $i--) {
                $dt = (clone $now)->subDays($i * 3);
                $labels[] = $dt->format('M j');
                $borrowed[] = [34, 42, 38, 55, 48, 62, 58, 68, 74, 82][9 - $i];
                $returned[] = [28, 36, 35, 48, 44, 56, 52, 60, 68, 75][9 - $i];
            }
        }

        return [
            'labels' => $labels,
            'series' => [
                ['name' => 'Loans', 'data' => $borrowed, 'color' => '#102B70'],
                ['name' => 'Returns', 'data' => $returned, 'color' => '#3B82F6'],
            ],
        ];
    }

    protected function getBarData(): array
    {
        return [
            'labels' => ['BSCS', 'BS Crim', 'BSMA', 'BPA', 'SHS', 'Faculty'],
            'series' => [
                ['name' => 'Loans', 'data' => [480, 395, 345, 260, 210, 145], 'color' => '#102B70'],
                ['name' => 'In-Library', 'data' => [310, 280, 240, 190, 160, 95], 'color' => '#3B82F6'],
            ],
        ];
    }

    protected function getDonutData(): array
    {
        $series = [485, 392, 340, 298, 215];
        return [
            'labels' => ['Computer Sci', 'Tech & Applied', 'Social Sciences', 'Management', 'Literature'],
            'series' => $series,
            'total' => array_sum($series),
        ];
    }

    public function render()
    {
        return view('livewire.components.widgets.charts', [
            'lineData' => $this->getLineData(),
            'barData' => $this->getBarData(),
            'donutData' => $this->getDonutData(),
        ]);
    }
}
