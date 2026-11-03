@php
    $guideSections = [
        'getting-started' => ['Getting Started', [
            'Sign in through the student login page. Complete any account setup or verification requested before entering the portal.',
            'Use Dashboard to see your library summary, current loans, and student QR code. Present the QR code at the circulation desk when needed.',
            'Open this Student Guide from the sidebar whenever you need help.',
        ]],
        'search-reserve' => ['Search & Reserve', [
            'Open the OPAC to search the library catalog, or choose Reservations in the sidebar to browse materials available for reservation.',
            'Search by title, author, or ISBN and use the available filters to narrow the results. Open a material to see its details.',
            'Choose a material and follow the reservation form. If no copy is available, the system may offer a waitlist instead. Review the details before confirming.',
        ]],
        'reservations' => ['My Reservations', [
            'Open your profile from the profile menu at the top of the page and find your reservations.',
            'Check Active Reservations for the current status and available actions. Reservation History shows past requests.',
            'Follow the instructions shown for your reservation and contact the circulation desk if you need help.',
        ]],
        'borrowing' => ['Borrowed Books & Fines', [
            'Choose Borrow in the sidebar to view your current loans and their due dates.',
            'Use Borrowing History to review past transactions and Fines & Penalties to check recorded charges.',
            'Return materials to the circulation desk by the due date. Ask library staff about settling any outstanding fines.',
        ]],
        'profile' => ['Profile & Notifications', [
            'Open the profile menu at the top of the page to access your profile and available account options.',
            'Select the notification icon in the top bar to read library alerts.',
            'Contact library staff if your information needs attention or you have questions about your account.',
        ]],
    ];
@endphp

@include('livewire.components.navigation-bar.guide', ['guideTitle' => 'Student Guide'])
