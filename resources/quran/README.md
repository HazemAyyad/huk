# Quran catalog API

The Flutter Quran module and this public file-backed API share the same Tanzil dataset. No database migration is required. Existing staff accounts, attendance, and admin routes are not part of this module.

| Endpoint | Response |
| --- | --- |
| `GET /api/v1/quran/manifest` | Version, source, copyright notice, dataset URL, SHA256, supported capabilities |
| `GET /api/v1/quran/dataset` | Complete canonical text and navigation metadata, including embedded notices |
| `GET /api/v1/quran/surahs` | 114 chapter records |
| `GET /api/v1/quran/juzs` | 30 exact start and end boundaries |
| `GET /api/v1/quran/verses` | Verses filtered by optional `surah`, `juz`, or `page` |
| `GET /api/v1/quran/search?q=...` | Local orthographic text search, optional `surah` or `juz` filter |

Verse and search responses use `cursor` as a zero-based offset and `limit` between 1 and 100 (default 50), returning `meta.total` and `meta.next_cursor`. Invalid chapter, juz, page, cursor, and limit values produce HTTP 422. The empty normalized search produces no results. The Quran text returned to readers is unmodified Uthmani text; search operates on the separately licensed Tanzil Simple Clean text.

Dataset source: [Tanzil Quran Text](https://tanzil.net/download/) 1.1 and [Tanzil metadata](https://tanzil.net/docs/Quran_Metadata) 1.0. Notices and hashes of all three source XML files are embedded in `quran.json`. Source XML and the deterministic builder live in the Flutter repository at `tool/quran_sources/` and `tool/build_quran_data.py`. Preserve copyright notices when updating or redistributing the file.

There is no active tafsir, word-meaning, audio, fixed-page-layout, or account-sync service in this first implementation. The manifest reports these capabilities as false instead of returning sample content.

Verification uses file-backed unit and API tests, without database setup or fixtures:

```powershell
php vendor/phpunit/phpunit/phpunit tests/Unit/QuranCatalogTest.php tests/Feature/QuranApiTest.php
php artisan route:list --path=api/v1/quran --except-vendor
```
