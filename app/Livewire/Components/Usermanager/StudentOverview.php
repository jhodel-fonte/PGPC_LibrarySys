<?php

namespace App\Livewire\Components\Usermanager;

use Livewire\Component;

class StudentOverview extends Component
{
    public $student;
    public $activeLoans;
    public int $activeLoansCount = 0;
    public int $overdueLoansCount = 0;
    public float $unpaidFinesTotal = 0.0;

    public function mount(
        $student = null,
        $activeLoans = null,
        int $activeLoansCount = 0,
        int $overdueLoansCount = 0,
        float $unpaidFinesTotal = 0.0
    ) {
        $this->student = $student;
        $this->activeLoans = $activeLoans ?? collect();
        $this->activeLoansCount = $activeLoansCount;
        $this->overdueLoansCount = $overdueLoansCount;
        $this->unpaidFinesTotal = $unpaidFinesTotal;
    }

    public function render()
    {
        return view('livewire.components.usermanager.student-overview', [
            'student' => $this->student,
            'activeLoans' => $this->activeLoans ?? collect(),
            'activeLoansCount' => $this->activeLoansCount,
            'overdueLoansCount' => $this->overdueLoansCount,
            'unpaidFinesTotal' => $this->unpaidFinesTotal,
        ]);
    }
}
