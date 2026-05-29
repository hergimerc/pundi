<?php

namespace App\Livewire\Reports;

use App\Enums\TransactionType;
use App\Services\ReportService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Reports')]
class Index extends Component
{
    #[Url(as: 'month')]
    public string $currentMonth;

    public function mount(): void
    {
        $this->currentMonth = now()->format('Y-m');
    }

    public function previousMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth.'-01')->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth.'-01')->addMonth()->format('Y-m');
    }

    public function render(ReportService $service)
    {
        $month = Carbon::parse($this->currentMonth.'-01');

        return view('livewire.reports.index', [
            'month' => $month,
            'summary' => $service->monthlySummary($month->month, $month->year),
            'expenseBreakdown' => $service->categoryBreakdown($month->month, $month->year, TransactionType::Expense),
            'incomeBreakdown' => $service->categoryBreakdown($month->month, $month->year, TransactionType::Income),
            'trend' => $service->annualTrend($month->year),
        ]);
    }
}
