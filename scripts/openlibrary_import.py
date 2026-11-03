"""Match Open Library descriptions and optionally import reviewed matches into PostgreSQL."""

import csv
import getpass
import tempfile
from collections import defaultdict
from pathlib import Path

import openlibrary_match

RESULTS = Path(__file__).resolve().with_name('matches.csv')
CACHE = Path(__file__).resolve().with_name('openlibrary_cache.json')


def candidates(rows):
    grouped = defaultdict(list)
    for row in rows:
        text = row['description'].strip()
        if text and len(text) <= 3000:
            grouped[row['book_data_id']].append(((row['book_data_id'], row['catalog_title'], openlibrary_match.isbn(row['isbn'])), text))
    # shortcut: conflicting descriptions need manual review, add selection UI if needed.
    return [entries[0] for entries in grouped.values() if len({text for _, text in entries}) == 1]


def main():
    try:
        import psycopg
    except ImportError:
        print('Install the PostgreSQL driver first: python -m pip install "psycopg[binary]"')
        return

    mode = input('Type 1 to scan the dump, or 2 to import a reviewed CSV: ').strip()
    if mode not in ('1', '2'):
        raise ValueError('Choose 1 or 2')
    host = input('Database host [127.0.0.1]: ').strip() or '127.0.0.1'
    port = input('Database port [5432]: ').strip() or '5432'
    database = input('Database name: ').strip()
    user = input('Database user: ').strip()
    password = getpass.getpass('Database password (hidden): ')
    with psycopg.connect(host=host, port=port, dbname=database, user=user, password=password) as connection:
        with connection.cursor() as cursor:
            cursor.execute('SELECT current_database(), current_user, inet_server_addr(), inet_server_port()')
            print('Connected to database, user, server, port:', cursor.fetchone())
            if mode == '1':
                dump = input('Full path to the downloaded .gz dump: ').strip().strip('"')
                if RESULTS.exists():
                    raise ValueError(f'{RESULTS} already exists; move or rename it before scanning again')
                cursor.execute("""SELECT bd.id, bd.book_title, d.isbn FROM book_details d
                    JOIN book_datas bd ON bd.id = d.book_data_id
                    WHERE d.deleted_at IS NULL AND bd.deleted_at IS NULL AND d.isbn IS NOT NULL
                    AND trim(d.isbn) <> '' AND (bd.description IS NULL OR trim(bd.description) = '')""")
                rows = cursor.fetchall()
                if not rows:
                    print('No catalog records with ISBN and missing description found.')
                    return
                with tempfile.TemporaryDirectory() as folder:
                    catalog = Path(folder) / 'catalog.csv'
                    with catalog.open('w', newline='', encoding='utf-8') as stream:
                        writer = csv.writer(stream)
                        writer.writerow(['book_data_id', 'title', 'isbn'])
                        writer.writerows(rows)
                    openlibrary_match.match(dump, catalog, RESULTS, CACHE, ask_rescan=True)
            else:
                with RESULTS.open(newline='', encoding='utf-8-sig') as stream:
                    rows = list(csv.DictReader(stream))
                eligible = candidates(rows)
                print(f'{len(rows)} rows; {len(eligible)} unambiguous descriptions eligible for checking against the database.')
                print('Only empty descriptions will be filled. School-assigned accession numbers will not be changed.')
                if input('Backup completed and ready to update this database? Type YES: ').strip() != 'YES':
                    print('Cancelled. No database changes made.')
                    return
                updated = 0
                for (record_id, title, number), text in eligible:
                    cursor.execute("""UPDATE book_datas bd SET description = %s, updated_at = CURRENT_TIMESTAMP
                        WHERE bd.id = %s AND bd.book_title = %s AND bd.deleted_at IS NULL
                        AND (bd.description IS NULL OR trim(bd.description) = '')
                        AND EXISTS (SELECT 1 FROM book_details d WHERE d.book_data_id = bd.id
                            AND d.deleted_at IS NULL AND upper(regexp_replace(d.isbn, '[^0-9Xx]', '', 'g')) = %s)""",
                        (text, record_id, title, number))
                    updated += cursor.rowcount
                print(f'Updated {updated} missing descriptions. Existing or changed records were skipped.')


if __name__ == '__main__':
    try:
        main()
    except Exception as error:
        print(f'Error: {error}')
        raise SystemExit(1)
