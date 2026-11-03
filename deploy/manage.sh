#!/usr/bin/env bash
set -uo pipefail

APP_DIR=/opt/pgpc-lib
BACKUP_DIR=/var/backups/pgpc-lib
PHP=/usr/bin/php8.4
QUEUE=pgpc-lib-queue.service
IMPORT_QUEUE=pgpc-lib-import-queue.service
SCHEDULER=pgpc-lib-scheduler.service
TARGET=pgpc-lib.target
FPM=php8.4-fpm.service
STAGING_DIR=
repo_owner=

confirm() {
    local answer
    read -r -p "$1 [y/N] " answer || return 1
    [[ "$answer" == y || "$answer" == Y || "$answer" == yes || "$answer" == YES ]]
}

require_app() {
    if [[ ! -f "$APP_DIR/artisan" || ! -f "$APP_DIR/.env" || ! -x "$PHP" ]]; then
        echo "Laravel, .env, or PHP 8.4 is missing. Check the installation first." >&2
        return 1
    fi
}

artisan() { (cd "$APP_DIR" && runuser -u www-data -- "$PHP" artisan "$@"); }
helper() { (cd "$APP_DIR" && runuser -u www-data -- "$PHP" deploy/ops.php "$@"); }
root_helper() { (cd "$APP_DIR" && "$PHP" deploy/ops.php "$@"); }

repo_command() {
    if [[ "$repo_owner" == root ]]; then
        (cd "$APP_DIR" && "$@")
    else
        (cd "$APP_DIR" && runuser -u "$repo_owner" -- "$@")
    fi
}

git_cmd() { repo_command git -c "safe.directory=$APP_DIR" "$@"; }

show_status() {
    local unit
    echo
    for unit in "$TARGET" "$QUEUE" "$IMPORT_QUEUE" "$SCHEDULER" "$FPM" nginx.service; do
        printf '%-30s active=%-12s boot=%s\n' "$unit" "$(systemctl is-active "$unit" 2>/dev/null || true)" "$(systemctl is-enabled "$unit" 2>/dev/null || true)"
    done
    echo
    df -h "$APP_DIR" "$BACKUP_DIR" 2>/dev/null || df -h "$APP_DIR" || true
    if require_app; then
        helper health || true
        echo 'HTTP /up on localhost:'
        if command -v curl >/dev/null && curl -fsS --max-time 5 -o /dev/null http://127.0.0.1/up; then
            echo '  reachable (local HTTP check only)'
        else
            echo '  not reachable; the site may require a different host or HTTPS'
        fi
        echo 'Recent failed jobs:'
        artisan queue:failed --no-ansi || echo 'Could not inspect failed jobs.'
    fi
}

stop_stack() {
    require_app || return 1
    echo 'Putting PGPC Library into maintenance mode; shared Nginx and PHP-FPM remain running.'
    artisan down --no-ansi || return 1
    echo 'Stopping scheduler and queue. The current queue job may take up to 30 minutes to finish.'
    systemctl stop "$SCHEDULER" "$QUEUE" "$IMPORT_QUEUE" "$TARGET" || return 1
    echo 'PGPC Library is in maintenance mode and its workers are stopped.'
}

start_stack() {
    require_app || return 1
    systemctl start "$TARGET" || return 1
    systemctl start "$QUEUE" "$IMPORT_QUEUE" "$SCHEDULER" || return 1
    artisan up --no-ansi || return 1
    echo 'PGPC Library stack started and maintenance mode cleared.'
}

server_menu() {
    local choice
    while true; do
        echo; echo 'Server controls: 1 Start  2 Stop  3 Restart  4 Status  5 Enable at boot  6 Disable at boot  0 Back'
        read -r -p 'Choose: ' choice || return
        case "$choice" in
            1) confirm 'Start the stack and bring the site online?' && { start_stack || echo 'Start failed; check the status and logs.'; } ;;
            2) echo 'This puts the site in maintenance mode and waits for the queue worker. Nginx and PHP-FPM stay running.'
               confirm 'Stop PGPC Library?' && { stop_stack || echo 'Stop failed; the site may remain in maintenance mode.'; } ;;
            3) echo 'Restart puts the site in maintenance mode and may wait up to 30 minutes for a queue job.'
               if confirm 'Restart PGPC Library?'; then
                   if stop_stack && start_stack; then
                       echo 'PHP-FPM is shared and reloading it may affect other applications.'
                       confirm 'Also reload PHP-FPM?' && systemctl reload "$FPM"
                   else echo 'Restart failed; the site may remain in maintenance mode.'; fi
               fi ;;
            4) show_status ;;
            5) echo 'This changes boot behavior; it does not start the application now.'; confirm 'Enable pgpc-lib.target at boot?' && systemctl enable "$TARGET" ;;
            6) echo 'This changes boot behavior, not current state. Individually enabled services may still start at boot.'; confirm 'Disable pgpc-lib.target at boot?' && systemctl disable "$TARGET" ;;
            0) return ;;
            *) echo 'Invalid choice.' ;;
        esac
    done
}

update_application() {
    local upstream target head branch changes before_down path queue_was_active=0 import_queue_was_active=0 scheduler_was_active=0 fpm_was_active=0
    require_app || return 1
    [[ -d "$APP_DIR/.git" ]] || { echo 'This update requires a normal Git checkout in /opt/pgpc-lib.'; return 1; }
    repo_owner=$(stat -c '%U' "$APP_DIR/.git") || return 1
    for path in "$APP_DIR" "$APP_DIR/vendor" "$APP_DIR/node_modules" "$APP_DIR/public/build"; do
        if [[ -e "$path" ]] && ! repo_command test -w "$path"; then
            echo "Repository owner $repo_owner cannot write $path. Fix ownership before updating."
            return 1
        fi
    done
    command -v git >/dev/null && command -v composer >/dev/null && command -v node >/dev/null && command -v npm >/dev/null || { echo 'Git, Composer, Node.js, and npm are required.'; return 1; }
    node -e 'const [major,minor]=process.versions.node.split(".").map(Number);process.exit((major===20&&minor>=19)||(major>=22&&(major>22||minor>=12))?0:1)' || { echo 'Node.js 20.19+ or 22.12+ is required.'; return 1; }
    changes=$(git_cmd status --porcelain --untracked-files=normal) || return 1
    [[ -z "$changes" ]] || { echo 'Local changes or untracked files exist. Resolve them before updating:'; printf '%s\n' "$changes"; return 1; }
    head=$(git_cmd rev-parse HEAD) || return 1
    branch=$(git_cmd symbolic-ref --quiet --short HEAD) || { echo 'The checkout is detached; choose a branch before updating.'; return 1; }
    upstream=$(git_cmd rev-parse --abbrev-ref --symbolic-full-name '@{upstream}' 2>/dev/null) || { echo 'No upstream branch is configured.'; return 1; }
    echo "Checking upstream $upstream..."
    git_cmd fetch --quiet 2>/dev/null || { echo 'Git fetch failed; check the server repository credentials.'; return 1; }
    target=$(git_cmd rev-parse '@{upstream}') || return 1
    git_cmd merge-base --is-ancestor HEAD "$target" || { echo 'The local branch has diverged. Resolve it manually; no changes were made.'; return 1; }
    [[ "$head" != "$target" ]] || echo 'Already up to date; continuing with dependency installation and deployment.'
    echo 'Incoming commits:'
    git_cmd log --oneline --max-count=20 "HEAD..$target" || return 1
    echo 'Migration files changed by this update (not necessarily pending):'
    git_cmd diff --name-only HEAD "$target" -- database/migrations || return 1
    echo 'Changed deployment definitions (installed system files will not be overwritten):'
    git_cmd diff --name-only HEAD "$target" -- deploy || return 1
    echo 'Current migration status:'
    artisan migrate:status --no-ansi || { echo 'Cannot inspect the current database; update cancelled.'; return 1; }
    echo 'The site enters maintenance mode. Queue work may take 30 minutes to finish.'
    echo 'PHP-FPM will be reloaded; that shared service may also serve other applications.'
    confirm 'Do you have a recent, restorable database and uploaded-files backup?' || { echo 'Create and verify a backup before updating.'; return 1; }
    confirm 'Apply this update, including pending migrations and PHP-FPM reload?' || return 1
    changes=$(git_cmd status --porcelain --untracked-files=normal) || return 1
    [[ -z "$changes" ]] || { echo 'The checkout changed during review; update cancelled.'; return 1; }
    [[ "$(git_cmd rev-parse HEAD)" == "$head" && "$(git_cmd symbolic-ref --quiet --short HEAD)" == "$branch" ]] || { echo 'The branch changed during review; update cancelled.'; return 1; }
    before_down=$(helper maintenance) || { echo 'Cannot determine maintenance state.'; return 1; }
    [[ "$before_down" == up || "$before_down" == down ]] || { echo 'Unexpected maintenance state.'; return 1; }
    systemctl is-active --quiet "$QUEUE" && queue_was_active=1
    systemctl is-active --quiet "$IMPORT_QUEUE" && import_queue_was_active=1
    systemctl is-active --quiet "$SCHEDULER" && scheduler_was_active=1
    systemctl is-active --quiet "$FPM" && fpm_was_active=1
    if [[ "$before_down" == up ]]; then artisan down --no-ansi || return 1; fi
    echo 'Waiting for active queue work to finish...'
    if [[ $scheduler_was_active == 1 ]]; then systemctl stop "$SCHEDULER" || return 1; fi
    if [[ $queue_was_active == 1 ]]; then systemctl stop "$QUEUE" || return 1; fi
    if [[ $import_queue_was_active == 1 ]]; then systemctl stop "$IMPORT_QUEUE" || return 1; fi
    git_cmd merge --ff-only "$target" || return 1
    repo_command env COMPOSER_ALLOW_SUPERUSER=1 "$PHP" "$(command -v composer)" install --no-interaction --no-dev --optimize-autoloader --no-scripts || return 1
    runuser -u www-data -- rm -f -- "$APP_DIR/bootstrap/cache/config.php" "$APP_DIR/bootstrap/cache/services.php" "$APP_DIR/bootstrap/cache/packages.php" || return 1
    artisan package:discover --no-ansi || return 1
    repo_command npm ci || return 1
    repo_command npm run build || return 1
    artisan config:clear --no-ansi || return 1
    echo 'Migration status for the updated code:'
    artisan migrate:status --no-ansi || return 1
    echo 'Declining leaves the updated files in place and the site in maintenance mode.'
    confirm 'Run the pending migrations now?' || { echo 'Update paused before migrations.'; return 1; }
    artisan migrate --force --no-ansi || return 1
    artisan config:cache --no-ansi || return 1
    artisan view:cache --no-ansi || return 1
    if [[ $fpm_was_active == 1 ]]; then systemctl reload "$FPM" || return 1; fi
    if [[ $queue_was_active == 1 ]]; then systemctl start "$QUEUE" || return 1; fi
    if [[ $import_queue_was_active == 1 ]]; then systemctl start "$IMPORT_QUEUE" || return 1; fi
    if [[ $scheduler_was_active == 1 ]]; then systemctl start "$SCHEDULER" || return 1; fi
    if [[ "$before_down" == up ]]; then artisan up --no-ansi || return 1; fi
    systemctl status "$FPM" "$QUEUE" "$SCHEDULER" --no-pager || true
    curl --noproxy '*' -I --resolve library.pgpc.online:443:127.0.0.1 https://library.pgpc.online/ || echo 'HTTPS check failed; inspect the site and certificate.'
    echo 'Update completed. Check system health and application logs. Review any changed deploy files against the installed system configuration.'
}

edit_environment() {
    local snapshot candidate cached_config= uid gid mode cache_uid= cache_gid= cache_mode= editor
    require_app || return 1
    [[ ! -L "$APP_DIR/.env" && -f "$APP_DIR/.env" ]] || { echo 'The environment file must be a regular file, not a symlink. Edit it manually.'; return 1; }
    mkdir -p "$BACKUP_DIR" && chmod 700 "$BACKUP_DIR" || return 1
    snapshot=$(mktemp "$BACKUP_DIR/env-before.XXXXXXXX") || return 1
    candidate=$(mktemp "$BACKUP_DIR/env-edit.XXXXXXXX") || return 1
    chmod 600 "$snapshot" "$candidate" && cp -- "$APP_DIR/.env" "$snapshot" && cp -- "$snapshot" "$candidate" || return 1
    uid=$(stat -c '%u' "$APP_DIR/.env") && gid=$(stat -c '%g' "$APP_DIR/.env") && mode=$(stat -c '%a' "$APP_DIR/.env") || return 1
    if command -v nano >/dev/null; then editor=nano; elif command -v vi >/dev/null; then editor=vi; else echo 'Install nano or vi first.'; return 1; fi
    echo "A protected copy was saved at $snapshot. Do not change APP_KEY."
    "$editor" "$candidate" || { echo "Editor failed. The active .env was not changed; edited copy: $candidate"; return 1; }
    root_helper env-check "$snapshot" "$candidate" || { echo "The active .env was not changed; edited copy: $candidate"; return 1; }
    confirm 'Apply these environment changes and refresh the configuration cache?' || { echo "The active .env was not changed; edited copy: $candidate"; return 0; }
    if [[ -f "$APP_DIR/bootstrap/cache/config.php" ]]; then
        [[ ! -L "$APP_DIR/bootstrap/cache/config.php" ]] || { echo 'The configuration cache is a symlink; apply the environment edit manually.'; return 1; }
        cache_uid=$(stat -c '%u' "$APP_DIR/bootstrap/cache/config.php") && cache_gid=$(stat -c '%g' "$APP_DIR/bootstrap/cache/config.php") && cache_mode=$(stat -c '%a' "$APP_DIR/bootstrap/cache/config.php") || return 1
        cached_config=$(mktemp "$BACKUP_DIR/config-before.XXXXXXXX") || return 1
        cp -- "$APP_DIR/bootstrap/cache/config.php" "$cached_config" && chmod 600 "$cached_config" || return 1
    fi
    replace_environment "$candidate" "$uid" "$gid" "$mode" || return 1
    if ! artisan config:cache --no-ansi >/dev/null 2>&1; then
        echo 'Configuration cache failed. Restoring the previous environment and cache.'
        replace_environment "$snapshot" "$uid" "$gid" "$mode" || { echo "Restore failed. Protected original: $snapshot"; return 1; }
        if [[ -n "$cached_config" ]]; then
            cp -- "$cached_config" "$APP_DIR/bootstrap/cache/config.php" && chown "$cache_uid:$cache_gid" "$APP_DIR/bootstrap/cache/config.php" && chmod "$cache_mode" "$APP_DIR/bootstrap/cache/config.php" || { echo "Cache restore failed. Protected copy: $cached_config"; return 1; }
        else
            rm -f -- "$APP_DIR/bootstrap/cache/config.php"
        fi
        return 1
    fi
    rm -f -- "$candidate"
    if [[ -n "$cached_config" && "$cached_config" == "$BACKUP_DIR"/config-before.* ]]; then rm -f -- "$cached_config"; fi
    echo 'Environment and configuration cache updated.'
    echo 'Applying settings to long-running workers may wait up to 30 minutes. PHP-FPM reload may affect other sites.'
    if confirm 'Restart active PGPC workers and reload PHP-FPM now?'; then
        if systemctl is-active --quiet "$QUEUE"; then systemctl restart "$QUEUE" || return 1; fi
        if systemctl is-active --quiet "$IMPORT_QUEUE"; then systemctl restart "$IMPORT_QUEUE" || return 1; fi
        if systemctl is-active --quiet "$SCHEDULER"; then systemctl restart "$SCHEDULER" || return 1; fi
        systemctl reload "$FPM" || return 1
    else
        echo 'Existing workers and PHP-FPM may retain their previous settings until restarted.'
    fi
}

replace_environment() {
    local source=$1 uid=$2 gid=$3 mode=$4 temp
    temp=$(mktemp "$APP_DIR/.env.tmp.XXXXXXXX") || return 1
    if ! cat -- "$source" > "$temp" || ! chown "$uid:$gid" "$temp" || ! chmod "$mode" "$temp" || ! mv -f -- "$temp" "$APP_DIR/.env"; then
        rm -f -- "$temp"
        return 1
    fi
}

cleanup_staging() {
    if [[ -n "$STAGING_DIR" && "$STAGING_DIR" == "$BACKUP_DIR"/.staging.* && -d "$STAGING_DIR" ]]; then
        rm -rf -- "$STAGING_DIR"
    fi
}

create_backup() {
    local output path
    require_app || return 1
    command -v pg_restore >/dev/null && command -v tar >/dev/null || { echo 'PostgreSQL client tools and tar are required.'; return 1; }
    echo 'Backups include the configured PostgreSQL database, storage/app, .env, and available PGPC server unit/config files.'
    echo 'They contain secrets and student data. TLS keys, external services, and off-server copies are not included.'
    echo 'The database and files are copied at different times; stop the application for a consistent backup.'
    confirm 'Create a protected local backup?' || return 1
    umask 077
    mkdir -p "$BACKUP_DIR" && chmod 700 "$BACKUP_DIR" || return 1
    STAGING_DIR=$(mktemp -d "$BACKUP_DIR/.staging.XXXXXXXX") || return 1
    output="$BACKUP_DIR/pgpc-$(date +%Y%m%d-%H%M%S)-$$.tar.gz"
    root_helper db-dump "$STAGING_DIR/database.dump" || { cleanup_staging; STAGING_DIR=; return 1; }
    pg_restore --list "$STAGING_DIR/database.dump" >/dev/null || { echo 'Database dump verification failed.'; cleanup_staging; STAGING_DIR=; return 1; }
    cp -p -- "$APP_DIR/.env" "$STAGING_DIR/.env" || { cleanup_staging; STAGING_DIR=; return 1; }
    mkdir -p "$STAGING_DIR/storage" || { cleanup_staging; STAGING_DIR=; return 1; }
    cp -a -- "$APP_DIR/storage/app" "$STAGING_DIR/storage/" || { echo 'Could not copy uploaded files.'; cleanup_staging; STAGING_DIR=; return 1; }
    for path in /etc/nginx/sites-available/pgpc-lib /etc/systemd/system/pgpc-lib.target /etc/systemd/system/pgpc-lib-queue.service /etc/systemd/system/pgpc-lib-import-queue.service /etc/systemd/system/pgpc-lib-scheduler.service /etc/php/8.4/fpm/conf.d/99-pgpc-lib.ini; do
        if [[ -f "$path" ]]; then mkdir -p "$STAGING_DIR/server$(dirname "$path")" && cp -p -- "$path" "$STAGING_DIR/server$path" || { cleanup_staging; STAGING_DIR=; return 1; }; fi
    done
    if ! tar -czf "$STAGING_DIR/backup.tar.gz" --exclude='./backup.tar.gz' -C "$STAGING_DIR" . || ! tar -tzf "$STAGING_DIR/backup.tar.gz" >/dev/null; then
        echo 'Backup archive verification failed.'; cleanup_staging; STAGING_DIR=; return 1
    fi
    mv -- "$STAGING_DIR/backup.tar.gz" "$output" && chmod 600 "$output" || { cleanup_staging; STAGING_DIR=; return 1; }
    cleanup_staging; STAGING_DIR=
    echo "Backup created: $output"
    sha256sum "$output"
    echo 'Archive and dump structure were checked; restore testing and an off-server copy are still required.'
}

database_menu() {
    local choice
    while true; do
        echo; echo 'Database: 1 Connection check  2 Migration status  3 Run pending migrations  4 Create backup  5 List backups  0 Back'
        read -r -p 'Choose: ' choice || return
        case "$choice" in
            1) require_app && helper health ;;
            2) require_app && artisan migrate:status --no-ansi ;;
            3) require_app || continue
               artisan migrate:status --no-ansi || continue
               echo 'Only pending migrations will run. Confirm a restorable backup exists first.'
               confirm 'Do you have a recent, restorable database and uploaded-files backup?' || continue
               confirm 'Run pending migrations?' && artisan migrate --force --no-ansi ;;
            4) create_backup || echo 'Backup not created.' ;;
            5) if [[ -d "$BACKUP_DIR" ]]; then find "$BACKUP_DIR" -maxdepth 1 -type f -name 'pgpc-*.tar.gz' -printf '%f  %s bytes\n'; else echo 'No backup directory.'; fi ;;
            0) return ;;
            *) echo 'Invalid choice.' ;;
        esac
    done
}

queue_menu() {
    local choice job
    while true; do
        echo; echo 'Queue and scheduler: 1 Status  2 Start queue  3 Stop queue  4 Restart queue  5 Start scheduler  6 Stop scheduler  7 Restart scheduler  8 Failed jobs  9 Retry one job  0 Back'
        read -r -p 'Choose: ' choice || return
        case "$choice" in
            1) systemctl --no-pager --full status "$QUEUE" "$IMPORT_QUEUE" "$SCHEDULER" || true ;;
            2) confirm 'Start both queue workers?' && systemctl start "$QUEUE" "$IMPORT_QUEUE" ;;
            3) echo 'A running job may take up to 30 minutes to finish.'; confirm 'Stop both queue workers?' && systemctl stop "$QUEUE" "$IMPORT_QUEUE" ;;
            4) echo 'A running job may take up to 30 minutes to finish.'; confirm 'Restart both queue workers?' && systemctl restart "$QUEUE" "$IMPORT_QUEUE" ;;
            5) confirm 'Start the scheduler?' && systemctl start "$SCHEDULER" ;;
            6) echo 'Stopping the scheduler can interrupt scheduled work.'; confirm 'Stop the scheduler?' && systemctl stop "$SCHEDULER" ;;
            7) echo 'Restarting the scheduler can interrupt scheduled work.'; confirm 'Restart the scheduler?' && systemctl restart "$SCHEDULER" ;;
            8) require_app && artisan queue:failed --no-ansi ;;
            9) require_app || continue
               artisan queue:failed --no-ansi || continue
               read -r -p 'Job ID to retry: ' job || continue
               if [[ ! "$job" =~ ^[[:alnum:]-]+$ ]]; then echo 'Invalid job ID.'; continue; fi
               echo 'Retrying a job can repeat its external effects.'
               confirm "Retry job $job?" && artisan queue:retry --no-ansi -- "$job" ;;
            0) return ;;
            *) echo 'Invalid choice.' ;;
        esac
    done
}

messaging_menu() {
    local choice recipient
    require_app || return 1
    while true; do
        echo; echo 'Email and SMS: 1 Settings status  2 Send test email  3 Send test SMS  0 Back'
        read -r -p 'Choose: ' choice || return
        case "$choice" in
            1) helper settings ;;
            2) read -r -p 'Recipient email: ' recipient || continue
               confirm "Send one test email to $recipient?" && helper email-test "$recipient" ;;
            3) read -r -p 'Recipient Philippine mobile number: ' recipient || continue
               confirm "Send one test SMS to $recipient?" && helper sms-test "$recipient" ;;
            0) return ;;
            *) echo 'Invalid choice.' ;;
        esac
    done
}

logs_menu() {
    local choice
    while true; do
        echo; echo 'Recent logs: 1 Laravel  2 Queue  3 Scheduler  4 Nginx  5 PHP-FPM  0 Back'
        read -r -p 'Choose: ' choice || return
        case "$choice" in
            1) if [[ -f "$APP_DIR/storage/logs/laravel.log" ]]; then tail -n 100 "$APP_DIR/storage/logs/laravel.log"; else echo 'No laravel.log found; check for daily log files.'; fi ;;
            2) journalctl --no-pager -u "$QUEUE" -u "$IMPORT_QUEUE" -n 100 ;;
            3) journalctl --no-pager -u "$SCHEDULER" -n 100 ;;
            4) journalctl --no-pager -u nginx.service -n 100 ;;
            5) journalctl --no-pager -u "$FPM" -n 100 ;;
            0) return ;;
            *) echo 'Invalid choice.' ;;
        esac
    done
}

main() {
    local choice
    if [[ $EUID -ne 0 ]]; then echo 'Run with: sudo bash /opt/pgpc-lib/deploy/manage.sh' >&2; return 1; fi
    command -v flock >/dev/null || { echo 'flock is required.' >&2; return 1; }
    exec 9>/run/lock/pgpc-lib-maintenance.lock
    flock -n 9 || { echo 'Another PGPC maintenance menu is already running.' >&2; return 1; }
    trap cleanup_staging EXIT
    while true; do
        echo; echo 'PGPC Library Maintenance'
        echo '1. System status and health'
        echo '2. Start, stop, or restart'
        echo '3. Update application'
        echo '4. Environment settings'
        echo '5. Database and backups'
        echo '6. Queue and scheduler'
        echo '7. Email and SMS tests'
        echo '8. Logs and troubleshooting'
        echo '9. First-time installation'
        echo '0. Exit'
        read -r -p 'Choose: ' choice || return
        case "$choice" in
            1) show_status ;;
            2) server_menu ;;
            3) update_application || echo 'Update did not finish. The site may be in maintenance mode; review the failed step and restore or recover before starting it.' ;;
            4) edit_environment || echo 'Environment changes were not fully applied.' ;;
            5) database_menu ;;
            6) queue_menu ;;
            7) messaging_menu ;;
            8) logs_menu ;;
            9) echo 'The installer changes system packages, Nginx configuration, and service files.'
               confirm 'Run the existing Ubuntu installer?' && bash "$APP_DIR/deploy/install-ubuntu.sh" ;;
            0) return ;;
            *) echo 'Invalid choice.' ;;
        esac
    done
}

if [[ "${BASH_SOURCE[0]}" == "$0" ]]; then
    if [[ -z "${PGPC_MANAGE_COPY:-}" ]]; then
        if [[ $EUID -ne 0 ]]; then echo 'Run with: sudo bash /opt/pgpc-lib/deploy/manage.sh' >&2; exit 1; fi
        temporary_script=$(mktemp /run/pgpc-manage.XXXXXXXX) || exit 1
        trap 'rm -f -- "$temporary_script"' EXIT
        cp -- "$0" "$temporary_script" || exit 1
        PGPC_MANAGE_COPY=1 bash "$temporary_script" "$@"
        exit $?
    fi
    main "$@"
fi
