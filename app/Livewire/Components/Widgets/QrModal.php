<?php

namespace App\Livewire\Components\Widgets;

use Livewire\Attributes\On;
use Livewire\Component;

class QrModal extends Component
{
    public bool $isOpen = false;
    public string $value = '';
    public string $title = 'PGPC Library QR Code';
    public string $subtitle = 'Digital Identification Pass';
    public ?string $name = null;
    public ?string $secondaryInfo = null;
    public string $label = 'ID Number:';
    public string $instruction = 'Present this QR code to the library circulation desk or scanner.';
    public ?string $qrCodeSvg = null;

    /**
     * Generate and return vector SVG directly for a specific QR payload.
     * This allows Alpine to request the SVG asynchronously without triggering
     * full component DOM re-renders or morphing conflicts.
     */
    public function getSvg(string $value): ?string
    {
        $value = trim($value);
        if (empty($value)) {
            return null;
        }

        try {
            return \App\Services\QrCodeServices::svg($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Fallback server-side open handler (if invoked directly from backend).
     */
    public function openQrModal(...$args): void
    {
        $payload = [];
        if (count($args) === 1 && is_array(reset($args))) {
            $payload = reset($args);
        } elseif (count($args) === 1 && is_string(reset($args))) {
            $payload = ['value' => reset($args)];
        } elseif (!empty($args)) {
            $payload = $args;
        }

        $this->value = (string) (
            $payload['value'] 
            ?? $payload['id'] 
            ?? $payload['student_id'] 
            ?? $payload['school_id_number'] 
            ?? $payload['accession_no'] 
            ?? $payload['accession_number'] 
            ?? $payload['code'] 
            ?? $payload['qr'] 
            ?? ''
        );

        $this->title = $payload['title'] ?? ($payload['heading'] ?? 'PGPC Library QR Code');
        $this->subtitle = $payload['subtitle'] ?? 'Digital Identification Pass';
        $this->name = $payload['name'] ?? null;
        $this->secondaryInfo = $payload['secondaryInfo'] ?? ($payload['secondary'] ?? ($payload['program'] ?? ($payload['author'] ?? null)));
        $this->label = $payload['label'] ?? 'ID Number:';
        $this->instruction = $payload['instruction'] ?? ($payload['note'] ?? ($payload['description'] ?? 'Present this QR code to the library circulation desk or scanner.'));

        $this->generateQrSvg();
        $this->isOpen = true;
    }

    #[On('close-qr-modal')]
    public function closeQrModal(): void
    {
        $this->isOpen = false;
        $this->value = '';
        $this->qrCodeSvg = null;
        $this->name = null;
        $this->secondaryInfo = null;
        $this->title = 'PGPC Library QR Code';
        $this->subtitle = 'Digital Identification Pass';
    }

    public function generateQrSvg(): void
    {
        if (empty($this->value)) {
            $this->qrCodeSvg = null;
            return;
        }

        try {
            $this->qrCodeSvg = \App\Services\QrCodeServices::svg($this->value);
        } catch (\Throwable $e) {
            $this->qrCodeSvg = null;
        }
    }

    public function render()
    {
        return view('livewire.components.widgets.qr-modal');
    }
}

