<?php

namespace App\Services;

class UserService
{
    /**
     * Generate default username based on config/settings.php rules.
     *
     * @param array<string, mixed> $data
     * @return string
     */
    public static function generateDefaultUsername(array $data): string
    {
        $config = config('settings.default_user_account.username', 'school_id');
        $strategy = is_array($config) ? ($config['strategy'] ?? 'school_id') : (string) $config;
        $case = is_array($config) ? ($config['case'] ?? 'lowercase') : 'lowercase';
        $fallback = is_array($config) ? ($config['fallback'] ?? 'email') : 'email';

        $schoolId = trim((string) ($data['school_id_number'] ?? $data['school_id'] ?? $data['roll'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));

        $username = match ($strategy) {
            'email' => $email ?: $schoolId,
            'school_id', 'school_id_number' => $schoolId ?: $email,
            default => self::replacePlaceholders($strategy, $data) ?: ($schoolId ?: $email),
        };

        if (empty($username) && $fallback === 'email') {
            $username = $email;
        } elseif (empty($username)) {
            $username = $schoolId;
        }

        return match ($case) {
            'lowercase' => strtolower($username),
            'uppercase' => strtoupper($username),
            default => $username,
        };
    }

    /**
     * Generate default password based on config/settings.php rules.
     *
     * @param array<string, mixed> $data
     * @return string
     */
    public static function generateDefaultPassword(array $data): string
    {
        $config = config('settings.default_user_account.password', '{lastname}{school_id}');
        $pattern = is_array($config) ? ($config['pattern'] ?? '{lastname}{school_id}') : (string) $config;
        $case = is_array($config) ? ($config['case'] ?? 'lowercase') : 'lowercase';
        $stripSpaces = is_array($config) ? ($config['strip_spaces'] ?? true) : true;
        $prefix = is_array($config) ? ($config['prefix'] ?? '') : '';
        $suffix = is_array($config) ? ($config['suffix'] ?? '') : '';
        $fallback = is_array($config) ? ($config['fallback'] ?? 'Pgpc12345!') : 'Pgpc12345!';

        $generated = self::replacePlaceholders($pattern, $data);

        if (empty(trim($generated))) {
            return $fallback;
        }

        if ($stripSpaces) {
            $generated = str_replace(' ', '', $generated);
        }

        $generated = match ($case) {
            'lowercase' => strtolower($generated),
            'uppercase' => strtoupper($generated),
            'capitalize' => ucfirst($generated),
            default => $generated,
        };

        return $prefix . $generated . $suffix;
    }

    /**
     * Generate both username and plain default password for a new user record.
     *
     * @param array<string, mixed> $data
     * @return array{username: string, password: string, force_change: bool}
     */
    public static function generateCredentials(array $data): array
    {
        $passwordConfig = config('settings.default_user_account.password', []);
        $forceChange = is_array($passwordConfig) ? ($passwordConfig['force_change_on_first_login'] ?? true) : true;

        return [
            'username' => self::generateDefaultUsername($data),
            'password' => self::generateDefaultPassword($data),
            'force_change' => (bool) $forceChange,
        ];
    }

    /**
     * Replace dynamic placeholders with user data.
     *
     * @param string $template
     * @param array<string, mixed> $data
     * @return string
     */
    protected static function replacePlaceholders(string $template, array $data): string
    {
        $schoolId = trim((string) ($data['school_id_number'] ?? $data['school_id'] ?? $data['roll'] ?? ''));
        $lastName = trim((string) ($data['last_name'] ?? $data['lastname'] ?? ''));
        $firstName = trim((string) ($data['first_name'] ?? $data['firstname'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $year = date('Y');

        $replacements = [
            '{school_id}' => $schoolId,
            '{school_id_number}' => $schoolId,
            '{lastname}' => $lastName,
            '{last_name}' => $lastName,
            '{firstname}' => $firstName,
            '{first_name}' => $firstName,
            '{email}' => $email,
            '{year}' => $year,
        ];

        return str_ireplace(array_keys($replacements), array_values($replacements), $template);
    }
}
