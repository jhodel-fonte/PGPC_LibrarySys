#!/usr/bin/env bash
set -uo pipefail

ROOT=$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd -P)
source "$ROOT/deploy/manage.sh"

fail() { echo "FAIL: $1" >&2; exit 1; }

printf 'yes\n' | confirm 'Continue?' >/dev/null || fail 'yes confirmation'
if printf 'no\n' | confirm 'Continue?' >/dev/null; then fail 'no confirmation'; fi

calls=()
require_app() { return 0; }
artisan() { calls+=("artisan $*"); }
systemctl() { calls+=("systemctl $*"); }

stop_stack >/dev/null || fail 'stop operation'
[[ "${calls[0]}" == 'artisan down --no-ansi' ]] || fail 'maintenance mode must precede stopping workers'
[[ "${calls[1]}" == "systemctl stop $SCHEDULER $QUEUE $TARGET" ]] || fail 'stop only PGPC units'
calls=()
start_stack >/dev/null || fail 'start operation'
[[ "${calls[0]}" == "systemctl start $TARGET" ]] || fail 'start target'
[[ "${calls[1]}" == "systemctl start $QUEUE $SCHEDULER" ]] || fail 'start both workers'
[[ "${calls[2]}" == 'artisan up --no-ansi' ]] || fail 'bring the site up after workers'

calls=()
artisan() { calls+=("artisan $*"); return 1; }
if stop_stack >/dev/null; then fail 'stop should fail when maintenance mode fails'; fi
[[ ${#calls[@]} == 1 ]] || fail 'must not stop services if maintenance mode fails'

calls=()
artisan() { calls+=("artisan $*"); }
systemctl() { calls+=("systemctl $*"); return 1; }
if start_stack >/dev/null; then fail 'start should fail when target fails'; fi
[[ ${#calls[@]} == 1 ]] || fail 'must not take the site out of maintenance after failed start'

temp_dir=$(mktemp -d "${TMPDIR:-/tmp}/pgpc-menu-test.XXXXXXXX") || fail 'temporary directory'
APP_DIR=$temp_dir
mkdir "$temp_dir/.git" || fail 'test checkout'
repo_command() { if [[ "$1" == test ]]; then return 0; fi; "$@"; }
git_cmd() { if [[ "$1" == status ]]; then printf ' M existing-file\n'; else fail 'dirty checkout proceeded to another Git action'; fi; }
composer() { :; }
node() { :; }
npm() { :; }
if update_application >/dev/null; then fail 'dirty checkout should be refused'; fi
rmdir "$temp_dir/.git" "$temp_dir" || fail 'remove empty test directories'

echo 'Maintenance menu safety checks passed.'
