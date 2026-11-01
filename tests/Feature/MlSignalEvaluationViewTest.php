<?php

namespace Tests\Feature;

use Tests\TestCase;

class MlSignalEvaluationViewTest extends TestCase
{
    public function test_signal_metrics_and_missing_values_are_displayed(): void
    {
        $this->view('livewire.pages.dashboard.partials.ml-signal-evaluation', [
            'signals' => ['hybrid' => ['interactions' => 12, 'rmse' => 0.25, 'mae' => null], 'coverage' => ['candidate_books' => 42]],
            'formatCount' => fn ($value) => $value === null ? '—' : number_format((int) $value),
            'formatMetric' => fn ($value) => $value === null ? '—' : number_format((float) $value, 4),
        ])->assertSee('Combined model')->assertSee('0.2500')->assertSee('42')->assertSee('—')->assertDontSee('Content matching');
    }
}
