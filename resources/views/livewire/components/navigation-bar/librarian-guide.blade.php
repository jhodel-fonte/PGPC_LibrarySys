@php
    $guideSections = [
        'getting-started' => ['Getting Started', [
            'Use the Dashboard to see your library overview and open this Librarian Guide from the sidebar whenever you need help.',
            'The sections and actions available to you depend on your assigned permissions. Contact an administrator if you need access.',
        ]],
    ];
    $permissions = \App\Services\EmployeeRoleService::class;

    if ($signedInUser->canManageCirculation()) {
        $steps = ['Open Circulation Desk to find borrowing records and reservations.'];
        if ($signedInUser->canBorrow()) $steps[] = 'Choose Borrow to check out materials to a patron and confirm the transaction details.';
        if ($signedInUser->canReturn()) $steps[] = 'Choose Return to check in materials and review the return details.';
        if ($permissions::canManageFines($signedInUser)) $steps[] = 'Open fine payments to review and record payments.';
        $guideSections['circulation'] = ['Circulation', $steps];
    }

    if ($signedInUser->canManageCatalog()) {
        $steps = ['Open Cataloging to search and review library materials and their copies.'];
        if ($permissions::canCreateBook($signedInUser)) $steps[] = 'Use Add or Import to add materials to the catalog.';
        if ($permissions::canEditBook($signedInUser)) $steps[] = 'Open a material and choose Edit to update its information.';
        $guideSections['catalog'] = ['Cataloging', $steps];
    }

    if ($signedInUser->hasAnyPermission(['manage_users', 'view_students', 'manage_librarian'])) {
        $steps = ['Open User Management to find the student or librarian records available to you.'];
        if ($signedInUser->canCreateStudent()) $steps[] = 'Use the available add or import options to register students.';
        if ($signedInUser->canEditStudent()) $steps[] = 'Open a student record to update its details or review account approvals.';
        if ($signedInUser->hasAnyPermission(['manage_users', 'manage_librarian'])) $steps[] = 'Open Librarians to manage staff accounts and their available permissions.';
        $guideSections['users'] = ['User Management', $steps];
    }

    if ($signedInUser->canViewReports()) {
        $guideSections['analytics'] = ['Analytics', ['Open Analytics to review the available overview and AI analytics reports.']];
    }
    if ($signedInUser->canManageSettings()) {
        $guideSections['settings'] = ['Settings', ['Open Settings to review and update the library system options available to you.']];
    }
    if ($signedInUser->isAdmin()) {
        $guideSections['logs'] = ['Logs', ['Open Logs to review the system audit trail.']];
    }
@endphp

@include('livewire.components.navigation-bar.guide', ['guideTitle' => 'Librarian Guide'])
