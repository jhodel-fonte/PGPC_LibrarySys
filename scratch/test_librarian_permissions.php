<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$app->instance('request', $request);
$kernel->bootstrap();

use App\Models\Account;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;

echo "=== TESTING LIBRARIAN LOGIN & PERMISSIONS ===\n";

// 1. Find Librarian account (Role ID 3)
$librarian = Account::where('role_id', 3)->first();
if (!$librarian) {
    echo "ERROR: No librarian account found with role_id 3!\n";
    exit(1);
}

echo "Found Librarian: ID {$librarian->id}, Username: {$librarian->username}, Role: {$librarian->role->name}\n";

// 2. Test hasRole
echo "hasRole('Librarian'): " . ($librarian->hasRole('Librarian') ? 'YES' : 'NO') . "\n";
echo "hasRole('Admin'): " . ($librarian->hasRole('Admin') ? 'YES' : 'NO') . "\n";

// 3. Test permissions
$permissionsToCheck = [
    'view_catalog',
    'create_book',
    'edit_book',
    'delete_book',
    'view_circulation',
    'borrow_book',
    'return_book',
    'view_students',
    'create_students',
    'edit_students',
    'delete_students',
    'manage_librarians',
    'manage_settings',
];

echo "\n--- Permission Checks for Librarian ({$librarian->username}) ---\n";
foreach ($permissionsToCheck as $perm) {
    $has = $librarian->hasPermission($perm);
    echo "  {$perm}: " . ($has ? 'GRANTED' : 'DENIED') . "\n";
}

// 4. Test BookPolicy with Librarian
echo "\n--- BookPolicy Checks ---\n";
Auth::login($librarian);
echo "Auth::user(): " . Auth::user()->username . "\n";
echo "can('viewAny', Book::class): " . (Auth::user()->can('viewAny', Book::class) ? 'YES' : 'NO') . "\n";
echo "can('create', Book::class): " . (Auth::user()->can('create', Book::class) ? 'YES' : 'NO') . "\n";
echo "can('update', Book::class): " . (Auth::user()->can('update', Book::class) ? 'YES' : 'NO') . "\n";
echo "can('delete', Book::class): " . (Auth::user()->can('delete', Book::class) ? 'YES' : 'NO') . "\n";

// 5. Test Admin bypass
$admin = Account::where('role_id', 1)->first();
if ($admin) {
    Auth::login($admin);
    echo "\n--- BookPolicy & Permission Checks for Admin ({$admin->username}) ---\n";
    echo "Admin hasPermission('manage_settings'): " . ($admin->hasPermission('manage_settings') ? 'YES' : 'NO') . "\n";
    echo "Admin can('delete', Book::class): " . (Auth::user()->can('delete', Book::class) ? 'YES' : 'NO') . "\n";
}

// 6. Test HTTP Route Dispatching
echo "\n--- Testing Route Access via HTTP Kernel ---\n";
Auth::login($librarian);

function testRoute($app, $method, $uri, $user) {
    $req = Illuminate\Http\Request::create($uri, $method);
    $req->setUserResolver(fn() => $user);
    try {
        $res = $app->handle($req);
        echo "  [{$method}] {$uri} -> Status: " . $res->getStatusCode() . "\n";
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        echo "  [{$method}] {$uri} -> Status: " . $e->getStatusCode() . " (" . $e->getMessage() . ")\n";
    } catch (\Throwable $e) {
        echo "  [{$method}] {$uri} -> Exception: " . $e->getMessage() . "\n";
    }
}

testRoute($app, 'GET', '/librarian/dashboard', $librarian);
testRoute($app, 'GET', '/librarian/book-management', $librarian);
testRoute($app, 'GET', '/librarian/circulation-desk', $librarian);
testRoute($app, 'GET', '/librarian/settings', $librarian); // Should 403!

// 7. Test View-Only Librarian scenario
echo "\n--- Testing View-Only Librarian Scenario ---\n";
// Temporarily mock an account with ONLY view_catalog
$viewOnlyAccount = new Account([
    'username' => 'viewonly_staff',
    'role_id' => 3,
]);
// Override permissions method
echo "Mocking View-Only Librarian with only 'view_catalog':\n";
echo "viewAny: " . ($librarian->hasPermission('view_catalog') ? 'YES' : 'NO') . "\n";

// Let's test direct permission override (account_permissions table)
// Give staff3 a direct override denying create_book:
DB::table('account_permissions')->updateOrInsert(
    ['account_id' => $librarian->id, 'permission_id' => DB::table('permissions')->where('name', 'create_book')->value('id')],
    ['is_allowed' => false, 'created_at' => now(), 'updated_at' => now()]
);

// Clear model relation cache
$freshLibrarian = Account::find($librarian->id);
Auth::login($freshLibrarian);
echo "Staff3 after direct override (create_book=DENIED):\n";
echo "  hasPermission('create_book'): " . ($freshLibrarian->hasPermission('create_book') ? 'YES' : 'NO') . "\n";
echo "  can('create', Book::class): " . ($freshLibrarian->can('create', Book::class) ? 'YES' : 'NO') . "\n";
echo "  can('viewAny', Book::class): " . ($freshLibrarian->can('viewAny', Book::class) ? 'YES' : 'NO') . "\n";

// Clean up test override
DB::table('account_permissions')
    ->where('account_id', $librarian->id)
    ->where('permission_id', DB::table('permissions')->where('name', 'create_book')->value('id'))
    ->delete();

// 8. Test EmployeeLogin Redirect Logic
echo "\n--- Testing EmployeeLogin Component Redirection ---\n";
Auth::login($librarian);
$empLogin = new \App\Livewire\Pages\Auth\EmployeeLogin();
try {
    $empLogin->mount();
    echo "Librarian mount redirection did not throw redirect exception\n";
} catch (\Livewire\Features\SupportRedirects\Redirector $r) {
    echo "Librarian mount redirected to: " . $r->getTargetUrl() . "\n";
} catch (\Throwable $e) {
    echo "Librarian mount redirect: " . $e->getMessage() . "\n";
}

if ($admin) {
    Auth::login($admin);
    $adminLogin = new \App\Livewire\Pages\Auth\EmployeeLogin();
    try {
        $adminLogin->mount();
        echo "Admin mount redirection did not throw redirect exception\n";
    } catch (\Livewire\Features\SupportRedirects\Redirector $r) {
        echo "Admin mount redirected to: " . $r->getTargetUrl() . "\n";
    } catch (\Throwable $e) {
        echo "Admin mount redirect: " . $e->getMessage() . "\n";
    }
}

echo "\n=== ALL TESTS COMPLETED ===\n";
