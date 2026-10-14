<?php

namespace App\Services;

class UserService
{
    /**
     * Determine if given role/type corresponds to a librarian.
     */
    public static function isLibrarianRole(?string $role): bool
    {
        if (empty($role)) {
            return false;
        }

        $normalized = strtolower(trim($role));
        return in_array($normalized, [
            'librarian',
            'head librarian',
            'head_librarian',
            'librarian_head',
            'staff',
            'library_staff',
            'admin',
        ], true);
    }

    /**
     * Get the resolved username configuration based on role.
     *
     * @param string|null $role
     * @return array<string, mixed>|string
     */
    protected static function getUsernameConfig(?string $role = null)
    {
        if (self::isLibrarianRole($role)) {
            return config('settings.default_librarian_account.username')
                ?? config('settings.default_user_account.librarian.username')
                ?? config('settings.default_user_account.username', 'school_id');
        }

        return config('settings.default_user_account.student.username')
            ?? config('settings.default_user_account.username', 'school_id');
    }

    /**
     * Get the resolved password configuration based on role.
     *
     * @param string|null $role
     * @return array<string, mixed>|string
     */
    protected static function getPasswordConfig(?string $role = null)
    {
        if (self::isLibrarianRole($role)) {
            return config('settings.default_librarian_account.password')
                ?? config('settings.default_user_account.librarian.password')
                ?? config('settings.default_user_account.password', '{lastname}{id}');
        }

        return config('settings.default_user_account.student.password')
            ?? config('settings.default_user_account.password', '{lastname}{id}');
    }

    /**
     * Generate default username based on config/settings.php rules.
     *
     * @param array<string, mixed> $data
     * @param string|null $role
     * @return string
     */
    public static function generateDefaultUsername(array $data, ?string $role = null): string
    {
        $role = $role ?? ($data['role'] ?? ($data['type'] ?? ($data['user_type'] ?? null)));
        $config = self::getUsernameConfig($role);

        $strategy = is_array($config) ? ($config['strategy'] ?? 'school_id') : (string) $config;
        $case = is_array($config) ? ($config['case'] ?? 'lowercase') : 'lowercase';
        $fallback = is_array($config) ? ($config['fallback'] ?? 'email') : 'email';

        $schoolId = trim((string) ($data['school_id_number'] ?? $data['school_id'] ?? $data['employee_id'] ?? $data['id'] ?? $data['roll'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));

        $username = match ($strategy) {
            'email' => $email ?: $schoolId,
            'id', 'school_id', 'school_id_number', 'employee_id' => $schoolId ?: $email,
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
     * @param string|null $role
     * @return string
     */
    public static function generateDefaultPassword(array $data, ?string $role = null): string
    {
        $role = $role ?? ($data['role'] ?? ($data['type'] ?? ($data['user_type'] ?? null)));
        $config = self::getPasswordConfig($role);

        $pattern = is_array($config) ? ($config['pattern'] ?? '{lastname}{id}') : (string) $config;
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
     * @param string|null $role
     * @return array{username: string, password: string, force_change: bool}
     */
    public static function generateCredentials(array $data, ?string $role = null): array
    {
        $role = $role ?? ($data['role'] ?? ($data['type'] ?? ($data['user_type'] ?? null)));
        $passwordConfig = self::getPasswordConfig($role);
        $forceChange = is_array($passwordConfig) ? ($passwordConfig['force_change_on_first_login'] ?? true) : true;

        return [
            'username' => self::generateDefaultUsername($data, $role),
            'password' => self::generateDefaultPassword($data, $role),
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
        $schoolId = trim((string) ($data['school_id_number'] ?? $data['school_id'] ?? $data['employee_id'] ?? $data['id'] ?? $data['roll'] ?? ''));
        $lastName = trim((string) ($data['last_name'] ?? $data['lastname'] ?? ''));
        $firstName = trim((string) ($data['first_name'] ?? $data['firstname'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $role = trim((string) ($data['role'] ?? $data['type'] ?? ''));
        $year = date('Y');

        $replacements = [
            '{id}' => $schoolId,
            '{school_id}' => $schoolId,
            '{school_id_number}' => $schoolId,
            '{employee_id}' => $schoolId,
            '{lastname}' => $lastName,
            '{last_name}' => $lastName,
            '{firstname}' => $firstName,
            '{first_name}' => $firstName,
            '{email}' => $email,
            '{role}' => $role,
            '{year}' => $year,
        ];

        return str_ireplace(array_keys($replacements), array_values($replacements), $template);
    }
}
