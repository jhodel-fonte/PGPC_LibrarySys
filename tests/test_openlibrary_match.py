import csv
import gzip
import importlib.util
import json
import sys
import tempfile
import unittest
from unittest.mock import patch
from pathlib import Path

spec = importlib.util.spec_from_file_location('matcher', Path(__file__).parents[1] / 'scripts' / 'openlibrary_match.py')
matcher = importlib.util.module_from_spec(spec)
spec.loader.exec_module(matcher)
sys.path.insert(0, str(Path(__file__).parents[1] / 'scripts'))
import openlibrary_import


class OpenLibraryMatchTest(unittest.TestCase):
    def test_conflicting_descriptions_for_same_title_are_skipped(self):
        rows = [dict(book_data_id='1', catalog_title='Title', isbn='123', description='First'),
                dict(book_data_id='1', catalog_title='Title', isbn='456', description='Second'),
                dict(book_data_id='2', catalog_title='Other', isbn='789', description='Safe')]
        self.assertEqual(openlibrary_import.candidates(rows), [(('2', 'Other', '789'), 'Safe')])

    def test_matches_work_description_and_preserves_multiple_candidates(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            dump, catalog, output = root / 'dump.gz', root / 'catalog.csv', root / 'results.csv'
            with gzip.open(dump, 'wt', encoding='utf-8') as stream:
                for kind, key, data in [
                    ('/type/work', '/works/1', {'description': {'value': 'A summary'}}),
                    ('/type/edition', '/books/1', {'title': 'Example', 'isbn_13': ['9781234567890'], 'works': [{'key': '/works/1'}]}),
                    ('/type/edition', '/books/2', {'title': 'Another', 'isbn_13': ['9781234567890'], 'description': 'Another summary'}),
                ]:
                    stream.write(f'{kind}\t{key}\t1\tdate\t{json.dumps(data)}\n')
            catalog.write_text('book_data_id,title,isbn\n7,Example,978-1234567890\n', encoding='utf-8')
            cache = root / 'cache.json'
            matcher.match(dump, catalog, output, cache)
            with output.open(encoding='utf-8-sig', newline='') as stream:
                rows = list(csv.DictReader(stream))
            self.assertEqual([row['description'] for row in rows], ['A summary', 'Another summary'])
            self.assertEqual(rows[0]['book_data_id'], '7')
            with patch.object(matcher, 'records', side_effect=AssertionError('cache was not used')):
                matcher.match(dump, catalog, root / 'cached.csv', cache)
            with patch('builtins.input', return_value='y'), patch.object(matcher, 'records', wraps=matcher.records) as scan:
                matcher.match(dump, catalog, root / 'rescanned.csv', cache, ask_rescan=True)
                self.assertEqual(scan.call_count, 2)
            with self.assertRaises(FileExistsError):
                matcher.match(dump, catalog, output)


if __name__ == '__main__':
    unittest.main()
