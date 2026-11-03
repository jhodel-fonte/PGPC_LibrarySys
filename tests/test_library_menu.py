import importlib.util
from pathlib import Path
import unittest
from unittest.mock import patch


spec = importlib.util.spec_from_file_location('library_menu', Path(__file__).parents[1] / 'library_menu.py')
menu = importlib.util.module_from_spec(spec)
spec.loader.exec_module(menu)


class MenuTest(unittest.TestCase):
    @patch.object(menu.subprocess, 'run')
    @patch('builtins.input', side_effect=['2', '0'])
    def test_local_refresh_calls_existing_guarded_command(self, _input, run):
        run.return_value.returncode = 0
        menu.main()
        self.assertEqual(run.call_args.args[0], ['php', 'artisan', 'db:refresh-from-vm'])
        self.assertEqual(run.call_args.kwargs['cwd'], menu.ROOT)


if __name__ == '__main__':
    unittest.main()
