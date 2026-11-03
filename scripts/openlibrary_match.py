"""Find description candidates in an Open Library all-types dump; never updates a database."""

import argparse
import csv
import gzip
import json
import re
import sys
import time
from pathlib import Path


def isbn(value):
    return re.sub(r'[^0-9X]', '', value.upper())


def description(value):
    if isinstance(value, dict):
        value = value.get('value')
    return value.strip() if isinstance(value, str) else ''


def records(path, kind, status=None):
    size = Path(path).stat().st_size
    last_update = 0
    with gzip.open(path, 'rt', encoding='utf-8') as stream:
        for line in stream:
            now = time.monotonic()
            if now - last_update >= 2:
                percent = min(100, stream.buffer.fileobj.tell() * 100 / size) if size else 0
                filled = int(percent / 5)
                detail = f' | {status()}' if status else ''
                print(f'\r{kind}: [{"#" * filled}{"-" * (20 - filled)}] {percent:5.1f}%{detail}\033[K', end='', flush=True)
                last_update = now
            columns = line.rstrip('\n').split('\t', 4)
            if len(columns) != 5:
                raise ValueError('Malformed dump record; download may be incomplete')
            if columns[0] == kind:
                yield columns[1], json.loads(columns[4])
    print(f'\r{kind}: [{"#" * 20}] 100.0%')


def match(dump, catalog, output, cache=None, ask_rescan=False):
    with open(catalog, newline='', encoding='utf-8-sig') as source:
        rows = list(csv.DictReader(source))
    if not rows or not {'book_data_id', 'title', 'isbn'} <= rows[0].keys():
        raise ValueError('Catalog CSV must contain book_data_id, title and isbn columns')

    wanted = {isbn(row['isbn']) for row in rows if len(isbn(row['isbn'])) in (10, 13)}
    stat = Path(dump).stat()
    identity = {'path': str(Path(dump).resolve()), 'size': stat.st_size, 'mtime_ns': stat.st_mtime_ns, 'isbns': sorted(wanted)}
    saved = None
    if cache and Path(cache).is_file():
        try:
            with open(cache, encoding='utf-8') as stream:
                saved = json.load(stream)
        except (OSError, ValueError):
            print('Could not read the scan cache; scanning the dump again.')
    use_cache = bool(saved and saved.get('identity') == identity)
    if use_cache and ask_rescan:
        use_cache = input('Saved scan found. Scan the full dump again? [y/N]: ').strip().lower() != 'y'
    if use_cache:
        print('Using saved results for this dump and ISBN list.')
        editions, works = saved['editions'], saved['works']
    else:
        editions = {}
        work_ids = set()
        latest = ''
        matched_count = 0
        print(f'Scanning editions for {len(wanted)} ISBNs...', flush=True)
        for key, record in records(dump, '/type/edition', lambda: f'{matched_count} editions found | latest: {latest}'):
            matches = wanted.intersection(isbn(str(i)) for i in record.get('isbn_10', []) + record.get('isbn_13', []))
            if matches:
                matched_count += 1
                latest = str(record.get('title') or key).replace('\n', ' ').replace('\r', ' ')[:80]
                keys = [w.get('key') for w in record.get('works', []) if isinstance(w, dict) and w.get('key')]
                work_ids.update(keys)
                for number in matches:
                    editions.setdefault(number, []).append((key, record.get('title', ''), description(record.get('description')), keys))

        works = {}
        print(f'Scanning for {len(work_ids)} related works...', flush=True)
        if work_ids:
            for key, record in records(dump, '/type/work'):
                if key in work_ids:
                    works[key] = description(record.get('description'))
        if cache:
            temporary = Path(str(cache) + '.tmp')
            with temporary.open('w', encoding='utf-8') as stream:
                json.dump({'identity': identity, 'editions': editions, 'works': works}, stream, ensure_ascii=False)
            temporary.replace(cache)

    fields = ['book_data_id', 'catalog_title', 'isbn', 'open_library_title', 'edition_key', 'work_key', 'description']
    count = 0
    matched_ids = set()
    with open(output, 'x', newline='', encoding='utf-8-sig') as target:
        writer = csv.DictWriter(target, fieldnames=fields)
        writer.writeheader()
        for row in rows:
            for key, title, edition_description, keys in editions.get(isbn(row['isbn']), []):
                candidates = [(key, edition_description)] if edition_description else [(work, works.get(work, '')) for work in keys]
                for source_key, text in candidates:
                    if text:
                        writer.writerow(dict(book_data_id=row['book_data_id'], catalog_title=row['title'], isbn=row['isbn'], open_library_title=title, edition_key=key, work_key=source_key if source_key != key else '', description=text))
                        count += 1
                        matched_ids.add(row['book_data_id'])
    print(f'Wrote {count} description candidates to {output}. Review before applying; no database changes were made.')
    missing = [row for row in rows if row['book_data_id'] not in matched_ids]
    print(f'{len(missing)} catalog records had no description match:')
    for row in missing:
        print(f"  {row['book_data_id']} | {row['isbn']} | {row['title']}")


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('dump', help='Open Library all-types .txt.gz dump')
    parser.add_argument('catalog', help='CSV exported from the local database')
    parser.add_argument('output', help='New CSV file for review')
    args = parser.parse_args()
    try:
        match(args.dump, args.catalog, args.output)
    except (OSError, ValueError, EOFError) as exc:
        sys.exit(f'Error: {exc}')
