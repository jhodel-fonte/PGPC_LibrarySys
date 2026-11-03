"""Launch library maintenance actions on the VM or local computer."""

from pathlib import Path
import subprocess
import sys

ROOT = Path(__file__).resolve().parent


def main():
    while True:
        print('\n=== PGPC Library Menu ===')
        print('1. VM maintenance menu (update, services and backups)')
        print('2. Replace local database with VM snapshot')
        print('0. Exit')
        try:
            choice = input('Choose: ').strip()
        except (EOFError, KeyboardInterrupt):
            print('\nExiting.')
            return
        if choice == '0':
            return
        if choice == '1':
            if sys.platform != 'linux' or ROOT != Path('/opt/pgpc-lib'):
                print('Run this option on the Ubuntu VM from /opt/pgpc-lib.')
                continue
            command = ['sudo', 'bash', str(ROOT / 'deploy/manage.sh')]
        elif choice == '2':
            command = ['php', 'artisan', 'db:refresh-from-vm']
        else:
            print('Choose 0, 1 or 2.')
            continue
        try:
            result = subprocess.run(command, cwd=ROOT, check=False)
            if result.returncode:
                print(f'Command failed with exit code {result.returncode}.')
        except OSError as error:
            print(f'Could not run command: {error}')


if __name__ == '__main__':
    main()
