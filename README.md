# PGPC Library System

The Padre Garcia Polytechnic College (PGPC) Library System is a modern, web-based application designed to manage library operations, circulation rules, and member resources efficiently. 

Built on a robust Laravel foundation, this system provides administrators with granular control over library policies, legal content, notifications, and AI-driven integrations.

## Technology Stack

- Backend: Laravel 11 (PHP)
- Frontend Framework: Livewire 3
- UI Styling: Tailwind CSS
- Interactivity: Alpine.js
- Database: Postgresql

## Installation and Setup

Follow these steps to set up the PGPC Library System in your local development environment.

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js and npm
- MySQL or MariaDB database

### Setup Instructions

1. Clone the repository and navigate to the project directory:
   ```bash
   git clone <repository-url>
   cd pgpc-lib
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install Node.js dependencies and build the frontend assets:
   ```bash
   npm install
   npm run build
   ```

4. Create a copy of the environment file:
   ```bash
   cp .env.example .env
   ```

5. Generate an application key:
   ```bash
   php artisan key:generate
   ```

6. Configure your database connection in the `.env` file:
   ```ini
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=pgpc_lib
   DB_USERNAME=root
   DB_PASSWORD=
   ```

7. Run database migrations and seeders:
   ```bash
   php artisan migrate:fresh --seed
   ```

8. Link the storage directory (required for public legal documents and logs):
   ```bash
   php artisan storage:link
   ```

9. Start the local development server:
   ```bash
   php artisan serve
   ```
   The application will be accessible at `http://localhost:8000`.

## Start the application on the Ubuntu VM

For the native Ubuntu deployment, place the repository at `/opt/pgpc-lib` and
copy the existing production `.env` there from a secure backup. Do not commit
that file. Use Ubuntu 24.04, set `APP_ENV=production` and `APP_DEBUG=false`,
and configure the PostgreSQL connection and ML API credentials. Laravel should
use `ML_RECOMMENDER_URL=http://127.0.0.1:8005` and the same `ML_API_KEY` as the
ML service's `API_KEY`.

The installer builds the frontend, installs Composer dependencies, configures
Nginx/PHP-FPM, and registers the queue and scheduler services:

```bash
cd /opt/pgpc-lib
sudo bash deploy/install-ubuntu.sh
```

The installer requires PHP 8.4 packages to be available in APT (on Ubuntu 24.04,
configure a PHP 8.4 package repository first), plus Node.js 20.19+ or 22.12+
and npm for the Vite build. It
starts and enables the application stack. To start it later with one command:

```bash
sudo systemctl start pgpc-lib.target
```

The target starts Nginx, PHP-FPM, the Laravel queue worker, and the Laravel
scheduler. Check them with `sudo systemctl status pgpc-lib.target` and view
application logs with `sudo journalctl -u pgpc-lib-queue -n 100 --no-pager`.
The Nginx configuration serves `library.pgpc.online` and `pgpc.online` on HTTP
port 80; configure a TLS certificate before serving production traffic over
the public internet. Open only the web ports needed in the Azure Network
Security Group, and keep database port 5432 private.

### Ubuntu maintenance menu

After this version is present in the native Ubuntu checkout, run:

```bash
sudo bash /opt/pgpc-lib/deploy/manage.sh
```

The menu checks service and database status, starts or stops the library stack,
updates a clean Git checkout, edits `.env`, runs pending migrations, creates
local backups, manages the queue and scheduler, sends confirmed test messages,
and shows recent logs. It is designed for the native `/opt/pgpc-lib` deployment,
not Docker Compose. PostgreSQL client tools (`pg_dump` and `pg_restore`) are
required for backups; no automatic restore is provided.

Stopping the library puts Laravel in maintenance mode and stops its queue and
scheduler; shared Nginx and PHP-FPM remain running. Queue jobs can take up to
30 minutes to finish. Updating requires a confirmed restorable backup, a clean
checkout with an upstream branch, working Git credentials, and writable
dependencies for the checkout owner. If an update fails after maintenance
begins, inspect the failed step and recover before bringing the site back up;
database migrations are not automatically rolled back. Installed systemd and
Nginx configuration files are not overwritten by an application update.

Environment edits are made in a protected copy, checked for a changed or
missing `APP_KEY`, and applied only after confirmation. Protected copies and
local archives are stored in `/var/backups/pgpc-lib`; they contain secrets and
student data. Backups include the configured PostgreSQL database, `storage/app`,
`.env`, and available PGPC server config files. Database and file copies occur
at different times, so stop the library first for a consistent backup. The menu
checks archive and dump structure but does not test a restore or create an
off-server copy. TLS keys and external services are not included.

## Directory Structure Highlights

- `app/Livewire/Pages/Admin/Settings.php`: Core logic for the unified system settings module, featuring deep-comparison unsaved changes tracking.
- `resources/views/livewire/pages/admin/`: Contains the main blade templates for the admin dashboard.
- `resources/views/components/settings/`: Houses the modular blade components for each settings category (e.g., circulation, notifications, general).
- `storage/app/public/settings/`: Publicly accessible storage for dynamic content like Terms and Conditions and Privacy Policies.
- `storage/app/private/`: Secure storage for system logs and backups.

## License

The PGPC Library System is proprietary software designed specifically for Padre Garcia Polytechnic College. All rights reserved.

### Advance loan reminders

Outstanding loans are reminded once per due date, starting three calendar days before the due date. Change `notifications.due_reminder_days` in `config/settings.php` to adjust the lead time. Reminders use in-app notifications, email when an address is available, and SMS when a student phone number is available. Existing mail and SMS settings apply.

Run `php artisan migrate` after updating. The Laravel scheduler must run continuously (`php artisan schedule:work`), or run `php artisan schedule:run` every minute via cron. Docker Compose includes a scheduler service. A queue worker (`php artisan queue:work`) is also required for asynchronous delivery. To run the reminder check manually, use `php artisan loans:send-due-reminders`.

Catalog and student imports use a dedicated database queue. Run both `php artisan queue:work database --timeout=1800` and `php artisan queue:work database --queue=imports --timeout=1800` continuously; Docker Compose includes both workers. The first worker must consume the configured `DB_QUEUE` used by notifications, and `QUEUE_CONNECTION` must match their worker connection. Canceling an import stops after the current row; catalog records already created remain in place. The database queue retry interval must exceed the worker timeout.

For an existing systemd deployment, install `deploy/pgpc-lib-import-queue.service` and the updated `deploy/pgpc-lib.target` under `/etc/systemd/system`, run `sudo systemctl daemon-reload`, and start `pgpc-lib-import-queue.service` before restarting the existing queue worker with the updated application code. The management update command does not overwrite installed service definitions. Check both workers with `systemctl status pgpc-lib-queue pgpc-lib-import-queue`. Import jobs already waiting on the old queue will finish there; their new continuation jobs use `imports`.

### Reservation waitlist

Students can join the waitlist from the reservation catalog when all copies of a title are borrowed or reserved. Copies are assigned in queue order when released, and the student receives a notification with the pickup deadline. Students can leave their waitlist from the same page. Accounts at the three-reservation limit or with reservations blocked are skipped until eligible; their waitlist entry remains active. Staff can block or restore reservation access on the student's account tab without disabling login or returns.

Run `php artisan migrate` to add the waitlist and reservation access flag. The scheduler and queue worker described above must be running for allocation retries and notifications. `php artisan reservations:allocate-waitlist` runs an allocation check manually.

### Machine-learning identity

Run `php artisan migrate` after updating to add a unique, random ML token to
each account. Recommendation requests send this token with the student's
program/year and borrowing history; the numeric account ID and legacy
`encrypter_id` are not sent. Rebuild the ML interaction snapshot and model
after this migration before switching traffic to the updated services; see
the ML repository README for the order of operations.
