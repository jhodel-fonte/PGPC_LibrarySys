<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Linkxtr\QrCode\Enums\ErrorCorrectionLevel;
use Linkxtr\QrCode\Enums\EyeStyle;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Enums\Style;
use Linkxtr\QrCode\Facades\QrCode;
use Throwable;

class QrCodeServices
{
    protected int $size = 260;
    protected int $margin = 1;
    protected string $format = 'svg'; // 'svg' or 'png'
    protected string $errorCorrection = 'H'; // 'L', 'M', 'Q', 'H'
    
    /** @var array{r: int, g: int, b: int} */
    protected array $color = ['r' => 16, 'g' => 43, 'b' => 112]; // PGPC Primary Navy (#102B70)
    
    /** @var array{r: int, g: int, b: int} */
    protected array $backgroundColor = ['r' => 255, 'g' => 255, 'b' => 255];
    
    protected ?string $style = 'square'; // 'square', 'round', 'dot'
    protected ?string $eyeStyle = 'square'; // 'square', 'circle', 'pointy'
    
    protected bool $embedLogo = false;
    protected ?string $logoPath = null;
    protected float $logoPercentage = 0.20;

    /**
     * Create a new QrCodeServices instance.
     * Automatically loads settings from config/settings.php (qrcode_customization).
     *
     * @param array<string, mixed> $overrides
     */
    public function __construct(array $overrides = [])
    {
        $this->loadConfigDefaults();

        if (!empty($overrides)) {
            $this->applyOptions($overrides);
        }
    }

    /**
     * Static factory method for fluent chaining.
     *
     * @param array<string, mixed> $options
     */
    public static function make(array $options = []): self
    {
        return new self($options);
    }

    /**
     * Load default styling, colors, and branding from config/settings.php.
     */
    public function loadConfigDefaults(): void
    {
        $config = config('settings.qrcode_customization', config('settings.qrcode', []));

        // 1. Theme Colors
        $theme = $config['theme'] ?? $config;
        if (!empty($theme['primary_color'])) {
            $this->color = $this->parseColor($theme['primary_color']);
        }
        if (!empty($theme['background_color'])) {
            $this->backgroundColor = $this->parseColor($theme['background_color']);
        }

        // 2. Display Settings
        $display = $config['display'] ?? $config;
        if (isset($display['size'])) {
            $this->size = (int) $display['size'];
        }
        if (isset($display['margin'])) {
            $this->margin = (int) $display['margin'];
        }
        if (!empty($display['error_correction_level'])) {
            $this->errorCorrection = strtoupper((string) $display['error_correction_level']);
        }
        if (!empty($display['format'])) {
            $this->format = strtolower((string) $display['format']);
        }
        if (!empty($display['style'])) {
            $this->style = strtolower((string) $display['style']);
        }
        if (!empty($display['eye_style'])) {
            $this->eyeStyle = strtolower((string) $display['eye_style']);
        }

        // 3. Branding & Logo Customization
        $branding = $config['branding'] ?? $config;
        $this->embedLogo = (bool) ($branding['embed_logo'] ?? ($config['embed_logo'] ?? false));
        
        $rawLogoPath = $branding['logo_path'] ?? ($config['logo_path'] ?? 'images/logo.webp');
        $this->logoPath = $this->resolveLogoPath($rawLogoPath);

        $percentValue = $branding['logo_size_percent'] ?? ($config['logo_size_percent'] ?? ($branding['logo_percentage'] ?? null));
        if ($percentValue !== null) {
            $percent = (float) $percentValue;
            // Normalize percentage (e.g. 20 -> 0.20)
            $this->logoPercentage = $percent > 1 ? ($percent / 100) : $percent;
        }

        // Ensure high error correction when embedding a logo
        if ($this->embedLogo) {
            $this->errorCorrection = 'H';
        }
    }

    /**
     * Apply runtime configuration options.
     *
     * @param array<string, mixed> $options
     */
    public function applyOptions(array $options): static
    {
        if (isset($options['size'])) {
            $this->size((int) $options['size']);
        }
        if (isset($options['margin'])) {
            $this->margin((int) $options['margin']);
        }
        if (isset($options['format'])) {
            $this->format((string) $options['format']);
        }
        if (isset($options['error_correction'])) {
            $this->errorCorrection((string) $options['error_correction']);
        }
        if (isset($options['color'])) {
            $this->color($options['color']);
        }
        if (isset($options['background_color']) || isset($options['bgColor'])) {
            $this->backgroundColor($options['background_color'] ?? $options['bgColor']);
        }
        if (isset($options['style'])) {
            $this->style((string) $options['style']);
        }
        if (isset($options['eye_style']) || isset($options['eyeStyle'])) {
            $this->eyeStyle((string) ($options['eye_style'] ?? $options['eyeStyle']));
        }
        if (isset($options['logo']) || isset($options['embed_logo'])) {
            $logoOption = $options['logo'] ?? $options['embed_logo'];
            if (is_bool($logoOption)) {
                $this->logo($logoOption);
            } elseif (is_string($logoOption)) {
                $this->withLogo($logoOption, $options['logo_percentage'] ?? null);
            } elseif (is_array($logoOption)) {
                $this->logo(
                    $logoOption['enabled'] ?? true,
                    $logoOption['path'] ?? null,
                    $logoOption['percentage'] ?? null
                );
            }
        }

        return $this;
    }

    /**
     * Set the QR code pixel dimension.
     */
    public function size(int $size): static
    {
        $this->size = max(50, $size);
        return $this;
    }

    /**
     * Set the outer quiet zone margin modules.
     */
    public function margin(int $margin): static
    {
        $this->margin = max(0, $margin);
        return $this;
    }

    /**
     * Set the output format: 'svg' or 'png'.
     */
    public function format(string $format): static
    {
        $this->format = strtolower($format) === 'png' ? 'png' : 'svg';
        return $this;
    }

    /**
     * Set error correction level: 'L', 'M', 'Q', 'H'.
     */
    public function errorCorrection(string $level): static
    {
        $level = strtoupper($level);
        if (in_array($level, ['L', 'M', 'Q', 'H'], true)) {
            $this->errorCorrection = $level;
        }
        return $this;
    }

    /**
     * Set the foreground color (HEX string or RGB integers).
     *
     * Examples:
     *   $service->color('#102B70');
     *   $service->color(16, 43, 112);
     *   $service->color(['r' => 16, 'g' => 43, 'b' => 112]);
     */
    public function color(int|string|array $r, ?int $g = null, ?int $b = null): static
    {
        if (is_string($r) || is_array($r)) {
            $this->color = $this->parseColor($r);
        } elseif ($g !== null && $b !== null) {
            $this->color = ['r' => $r, 'g' => $g, 'b' => $b];
        }
        return $this;
    }

    /**
     * Set the background color (HEX string or RGB integers).
     */
    public function backgroundColor(int|string|array $r, ?int $g = null, ?int $b = null): static
    {
        if (is_string($r) || is_array($r)) {
            $this->backgroundColor = $this->parseColor($r);
        } elseif ($g !== null && $b !== null) {
            $this->backgroundColor = ['r' => $r, 'g' => $g, 'b' => $b];
        }
        return $this;
    }

    /**
     * Set the module style: 'square', 'round', or 'dot'.
     */
    public function style(string $style): static
    {
        $style = strtolower($style);
        if (in_array($style, ['square', 'round', 'dot'], true)) {
            $this->style = $style;
        }
        return $this;
    }

    /**
     * Set the eye corner shape: 'square', 'circle', or 'pointy'.
     */
    public function eyeStyle(string $style): static
    {
        $style = strtolower($style);
        if (in_array($style, ['square', 'circle', 'pointy'], true)) {
            $this->eyeStyle = $style;
        }
        return $this;
    }

    /**
     * Enable logo embedding with an optional custom path and size percentage.
     */
    public function withLogo(?string $path = null, ?float $percentage = null): static
    {
        $this->embedLogo = true;
        $this->errorCorrection = 'H'; // Required for scanning reliability

        if ($path !== null) {
            $this->logoPath = $this->resolveLogoPath($path);
        }
        if ($percentage !== null) {
            $this->logoPercentage = $percentage > 1 ? ($percentage / 100) : $percentage;
        }

        return $this;
    }

    /**
     * Disable logo embedding.
     */
    public function withoutLogo(): static
    {
        $this->embedLogo = false;
        return $this;
    }

    /**
     * Configure logo embedding state.
     */
    public function logo(bool $enabled = true, ?string $path = null, ?float $percentage = null): static
    {
        if ($enabled) {
            return $this->withLogo($path, $percentage);
        }
        return $this->withoutLogo();
    }

    /**
     * Alias for withLogo to match generator semantics.
     */
    public function merge(string $path, float $percentage = 0.20): static
    {
        return $this->withLogo($path, $percentage);
    }

    /**
     * Generate the QR code as a string (SVG XML or PNG binary depending on format).
     *
     * @param string $payload
     * @param array<string, mixed> $options
     * @return string
     */
    public function generate(string $payload, array $options = []): string
    {
        if (!empty($options)) {
            $clone = clone $this;
            $clone->applyOptions($options);
            return $clone->build($payload);
        }

        return $this->build($payload);
    }

    /**
     * Generate specifically as a vector SVG string.
     *
     * @param string $payload
     * @param array<string, mixed> $options
     * @return string
     */
    public function generateSvg(string $payload, array $options = []): string
    {
        $options['format'] = 'svg';
        return $this->generate($payload, $options);
    }

    /**
     * Generate specifically as PNG binary data.
     *
     * @param string $payload
     * @param array<string, mixed> $options
     * @return string
     */
    public function generatePng(string $payload, array $options = []): string
    {
        $options['format'] = 'png';
        return $this->generate($payload, $options);
    }

    /**
     * Generate as a Base64 Data URI for immediate use in HTML/Blade: <img src="{{ $uri }}">
     *
     * @param string $payload
     * @param array<string, mixed> $options
     * @return string
     */
    public function generateDataUri(string $payload, array $options = []): string
    {
        $output = $this->generate($payload, $options);

        if ($this->format === 'svg') {
            return 'data:image/svg+xml;base64,' . base64_encode($output);
        }

        return 'data:image/png;base64,' . base64_encode($output);
    }

    /**
     * Generate and save the QR code directly to a file path.
     *
     * @param string $payload
     * @param string $filePath
     * @param array<string, mixed> $options
     * @return bool
     */
    public function save(string $payload, string $filePath, array $options = []): bool
    {
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }

        // Infer format from file extension if not explicitly set
        if (!isset($options['format'])) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'svg'], true)) {
                $options['format'] = $ext;
            }
        }

        $content = $this->generate($payload, $options);
        return (bool) @file_put_contents($filePath, $content);
    }

    /**
     * Internal builder that constructs the configured generator and produces the output.
     */
    protected function build(string $payload): string
    {
        if (trim($payload) === '') {
            $payload = 'PGPC-EMPTY';
        }

        // Always enforce Error Correction Level 'H' (30% redundancy) if logo embedding is enabled
        if ($this->embedLogo) {
            $this->errorCorrection = 'H';
        }

        try {
            $generator = $this->createConfiguredGenerator();
            $output = (string) $generator->generate($payload);

            // If output is SVG and logo embedding is active, cleanly embed the logo badge overlay
            if ($this->format === 'svg' && $this->embedLogo && !empty($this->logoPath) && file_exists($this->logoPath)) {
                $output = $this->embedLogoIntoSvg($output, $this->logoPath, $this->logoPercentage);
            }

            return $output;
        } catch (Throwable $e) {
            Log::warning("QrCodeServices primary generation failed: {$e->getMessage()}. Retrying with clean SVG fallback.");

            // Resilient fallback: generate vector SVG with brand colors and error correction 'H'
            try {
                $baseGenerator = QrCode::size($this->size)
                    ->margin($this->margin)
                    ->format(Format::SVG)
                    ->errorCorrection('H')
                    ->color($this->color['r'], $this->color['g'], $this->color['b'])
                    ->backgroundColor($this->backgroundColor['r'], $this->backgroundColor['g'], $this->backgroundColor['b']);

                $rawSvg = (string) $baseGenerator->generate($payload);

                if ($this->embedLogo && !empty($this->logoPath) && file_exists($this->logoPath)) {
                    return $this->embedLogoIntoSvg($rawSvg, $this->logoPath, $this->logoPercentage);
                }

                return $rawSvg;
            } catch (Throwable $fallbackError) {
                Log::error("QrCodeServices fallback generation also failed: {$fallbackError->getMessage()}");
            }

            // Ultimate bare-minimum fallback: simple vector SVG with brand navy
            return (string) QrCode::size($this->size)
                ->margin($this->margin)
                ->color(16, 43, 112)
                ->generate($payload);
        }
    }

    /**
     * Build the Linkxtr QrCode Generator instance with current properties.
     */
    protected function createConfiguredGenerator(): mixed
    {
        $generator = QrCode::size($this->size)
            ->margin($this->margin);

        // Format
        if ($this->format === 'png') {
            $generator = $generator->format(Format::PNG);
        } else {
            $generator = $generator->format(Format::SVG);
        }

        // Error Correction Level (Level 'H' provides 30% recovery capacity, vital when embedding logos)
        $generator = $generator->errorCorrection($this->errorCorrection);

        // Colors
        $generator = $generator->color($this->color['r'], $this->color['g'], $this->color['b']);
        $generator = $generator->backgroundColor(
            $this->backgroundColor['r'],
            $this->backgroundColor['g'],
            $this->backgroundColor['b']
        );

        // Shape / Module Style
        if ($this->style && in_array($this->style, ['square', 'round', 'dot'], true)) {
            $generator = $generator->style($this->style);
        }

        // Eye Corner Shape
        if ($this->eyeStyle && in_array($this->eyeStyle, ['square', 'circle', 'pointy'], true)) {
            $generator = $generator->eye($this->eyeStyle);
        }

        // Logo Embedding for PNG format only (SVG is handled by embedLogoIntoSvg to preserve vector fidelity & add contrast badge)
        if ($this->format === 'png' && $this->embedLogo && !empty($this->logoPath) && file_exists($this->logoPath)) {
            $ext = strtolower(pathinfo($this->logoPath, PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'webp', 'jpg', 'jpeg'], true)) {
                $generator = $generator->merge($this->logoPath, $this->logoPercentage);
            }
        }

        return $generator;
    }

    /**
     * Embed institutional logo overlay into an SVG QR code with a crisp white contrast badge.
     *
     * @param string $svgXml Raw vector SVG XML
     * @param string $logoPath Resolved filesystem path to the logo image
     * @param float $percentage Size percentage relative to QR code dimensions (e.g. 0.20 for 20%)
     * @return string Enhanced SVG with embedded logo badge
     */
    public function embedLogoIntoSvg(string $svgXml, string $logoPath, float $percentage = 0.20): string
    {
        if (!file_exists($logoPath) || !is_readable($logoPath)) {
            return $svgXml;
        }

        $logoBytes = @file_get_contents($logoPath);
        if ($logoBytes === false || empty($logoBytes)) {
            return $svgXml;
        }

        // Determine dimensions from SVG viewBox or width/height attributes
        $svgWidth = (float) $this->size;
        $svgHeight = (float) $this->size;

        if (preg_match('/viewBox\s*=\s*["\']([-\d.]+)\s+([-\d.]+)\s+([\d.]+)\s+([\d.]+)["\']/i', $svgXml, $vbMatches)) {
            $svgWidth = (float) $vbMatches[3];
            $svgHeight = (float) $vbMatches[4];
        } elseif (preg_match('/width\s*=\s*["\']([\d.]+)["\']/i', $svgXml, $wMatches) && preg_match('/height\s*=\s*["\']([\d.]+)["\']/i', $svgXml, $hMatches)) {
            $svgWidth = (float) $wMatches[1];
            $svgHeight = (float) $hMatches[1];
        }

        if ($svgWidth <= 0 || $svgHeight <= 0) {
            $svgWidth = (float) $this->size;
            $svgHeight = (float) $this->size;
        }

        // Clamp percentage between 14% and 26% to ensure optimal scanning readability
        $percentage = max(0.14, min(0.26, $percentage > 1 ? ($percentage / 100) : $percentage));

        // Calculate white shield background badge dimensions
        $badgeSize = round($svgWidth * $percentage, 2);
        $badgeX = round(($svgWidth - $badgeSize) / 2, 2);
        $badgeY = round(($svgHeight - $badgeSize) / 2, 2);
        $badgeRadius = round($badgeSize * 0.20, 2);

        // Calculate inner logo dimensions with a neat inset padding
        $padding = round($badgeSize * 0.08, 2);
        $logoSize = round($badgeSize - (2 * $padding), 2);
        $logoX = round($badgeX + $padding, 2);
        $logoY = round($badgeY + $padding, 2);

        // Determine MIME type
        $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            default => 'image/png',
        };

        $base64Data = base64_encode($logoBytes);
        $imageUri = "data:{$mime};base64,{$base64Data}";

        // Ensure xlink namespace is present on root <svg>
        if (!str_contains($svgXml, 'xmlns:xlink=')) {
            $svgXml = preg_replace('/<svg\b([^>]*)>/i', '<svg$1 xmlns:xlink="http://www.w3.org/1999/xlink">', $svgXml, 1) ?? $svgXml;
        }

        // SVG overlay group with white shield badge and centered institution logo
        $overlayGroup = sprintf(
            '<!-- PGPC Library Brand Overlay Badge -->' . "\n" .
            '<g id="qr-logo-badge" class="pgpc-qr-badge">' .
                '<rect x="%.2f" y="%.2f" width="%.2f" height="%.2f" rx="%.2f" ry="%.2f" fill="#FFFFFF" stroke="#E2E8F0" stroke-width="1.5" />' .
                '<image x="%.2f" y="%.2f" width="%.2f" height="%.2f" href="%s" xlink:href="%s" preserveAspectRatio="xMidYMid meet" />' .
            '</g>' . "\n",
            $badgeX,
            $badgeY,
            $badgeSize,
            $badgeSize,
            $badgeRadius,
            $badgeRadius,
            $logoX,
            $logoY,
            $logoSize,
            $logoSize,
            $imageUri,
            $imageUri
        );

        // Insert before closing </svg> tag
        $pos = strrpos($svgXml, '</svg>');
        if ($pos !== false) {
            return substr($svgXml, 0, $pos) . $overlayGroup . substr($svgXml, $pos);
        }

        return $svgXml . $overlayGroup;
    }

    /**
     * Resolve and validate a given logo file path from public, images, storage, or base directories.
     */
    protected function resolveLogoPath(?string $path): ?string
    {
        if (empty($path)) {
            $path = 'images/logo.webp';
        }

        $cleanPath = ltrim($path, '/\\');
        $baseName = basename($cleanPath);

        $candidates = [
            $path,
            public_path($cleanPath),
            public_path('images/' . $cleanPath),
            public_path($baseName),
            public_path('images/' . $baseName),
            resource_path('images/' . $cleanPath),
            resource_path('images/' . $baseName),
            storage_path('app/public/' . $cleanPath),
            // High-resolution institutional branding fallbacks
            public_path('images/logo.webp'),
            public_path('logo.webp'),
            public_path('logo.svg'),
            public_path('logo.ico'),
        ];

        foreach ($candidates as $candidate) {
            if (!empty($candidate) && file_exists($candidate) && is_file($candidate)) {
                return $candidate;
            }
        }

        return $this->ensurePngLogoExists();
    }

    /**
     * Ensure a PNG version of the logo exists for raster tools that require PNG.
     */
    protected function ensurePngLogoExists(): ?string
    {
        $pngPath = public_path('images/logo.png');
        if (file_exists($pngPath) && filesize($pngPath) > 0) {
            return $pngPath;
        }

        $webpPath = public_path('images/logo.webp');
        if (file_exists($webpPath) && function_exists('imagecreatefromwebp') && function_exists('imagepng')) {
            try {
                $im = @imagecreatefromwebp($webpPath);
                if ($im !== false) {
                    imagepalettetotruecolor($im);
                    imagealphablending($im, true);
                    imagesavealpha($im, true);
                    @imagepng($im, $pngPath, 6);
                    imagedestroy($im);
                    if (file_exists($pngPath) && filesize($pngPath) > 0) {
                        return $pngPath;
                    }
                }
            } catch (Throwable $e) {
                // Silently continue
            }
        }

        return null;
    }

    /**
     * Parse hex color or color array into an RGB array.
     *
     * @param string|array<string, int> $color
     * @return array{r: int, g: int, b: int}
     */
    protected function parseColor(string|array $color): array
    {
        if (is_array($color)) {
            return [
                'r' => (int) ($color['r'] ?? ($color[0] ?? 16)),
                'g' => (int) ($color['g'] ?? ($color[1] ?? 43)),
                'b' => (int) ($color['b'] ?? ($color[2] ?? 112)),
            ];
        }

        $hex = ltrim($color, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (strlen($hex) === 6 && ctype_xdigit($hex)) {
            return [
                'r' => (int) hexdec(substr($hex, 0, 2)),
                'g' => (int) hexdec(substr($hex, 2, 2)),
                'b' => (int) hexdec(substr($hex, 4, 2)),
            ];
        }

        // Default to PGPC brand navy
        return ['r' => 16, 'g' => 43, 'b' => 112];
    }

    // ==========================================
    // Static Convenience Shortcuts
    // ==========================================

    /**
     * Generate SVG string statically using current settings.
     *
     * @param string $payload
     * @param array<string, mixed> $options
     * @return string
     */
    public static function svg(string $payload, array $options = []): string
    {
        return self::make($options)->generateSvg($payload);
    }

    /**
     * Generate PNG binary statically using current settings.
     *
     * @param string $payload
     * @param array<string, mixed> $options
     * @return string
     */
    public static function png(string $payload, array $options = []): string
    {
        return self::make($options)->generatePng($payload);
    }

    /**
     * Generate Base64 Data URI statically using current settings.
     *
     * @param string $payload
     * @param array<string, mixed> $options
     * @return string
     */
    public static function dataUri(string $payload, array $options = []): string
    {
        return self::make($options)->generateDataUri($payload);
    }

    /**
     * Generate QR code using current settings.
     *
     * @param string $payload
     * @param array<string, mixed> $options
     * @return string
     */
    public static function render(string $payload, array $options = []): string
    {
        return self::make($options)->generate($payload);
    }
}