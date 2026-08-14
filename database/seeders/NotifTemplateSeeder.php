<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotifTemplate;

class NotifTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'type' => 'reservation_confirmed',
                'email_subject' => 'Book Reservation Confirmed',
                'message' => 'Your reservation for the book has been confirmed. Please pick it up within 24 hours.',
            ],
            [
                'type' => 'overdue_notice',
                'email_subject' => 'Overdue Book Notice',
                'message' => 'The book you borrowed is now overdue. Please return it as soon as possible to avoid further fines.',
            ],
            [
                'type' => 'due_reminder',
                'email_subject' => 'Book Due Reminder',
                'message' => 'This is a friendly reminder that your borrowed book is due tomorrow.',
            ],
            [
                'type' => 'system_announcement',
                'email_subject' => 'System Announcement',
                'message' => 'Welcome to the PGPC Library System! Explore our vast collection of resources.',
            ]
        ];

        foreach ($templates as $template) {
            NotifTemplate::create($template);
        }
    }
}
