# Open Library catalog enrichment

Open Library asks users to use its data dumps or coordinate with it for bulk
imports. A slow request rate does not change that guidance. See
https://openlibrary.org/developers/api before running this across a catalog.

From the Laravel directory, preview with your library contact email:

```bash
php artisan catalog:enrich-openlibrary library@example.org --dry-run
```

Run without `--dry-run` to save. Make a database backup first. The command
uses batches of up to 20 ISBNs, identifies the library, and waits at least
1.1 seconds between API requests. It also requests a work record when a
matching edition has no description. If a request fails, including a 429,
the command stops and prints the last completed ID. After resolving the
problem, resume using `--after=ID` and the same email. The ID is printed
after each processed record; do not run multiple copies simultaneously.

Only empty descriptions, subtitles, page counts, publication years and cover
URLs are filled. Records with missing, invalid or unmatched ISBNs and fields
not supplied by Open Library remain incomplete. The command does not import
authors, subjects, publishers, classifications, or school copy data. Check
the result for matching quality, particularly when several editions share a
book record. No cover images are downloaded. Refresh the ML catalog index
after saving changes so search and recommendations can use updated metadata.
