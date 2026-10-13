# Quran catalog, content and account API

Flutter's default API base is `https://hak.duosparktech.com/api`. The catalog remains file-backed and publicly accessible. The Quran dataset is unmodified Tanzil Uthmani 1.1 / Simple Clean 1.1 with metadata 1.0; copyright notices and source hashes remain embedded. Search operates on the separately licensed Simple Clean text; display uses Uthmani text.

## Public endpoints

All below are relative to `/api/v1/quran`:

| Endpoint | Purpose |
| --- | --- |
| `GET /manifest`, `/dataset` | Version, notices, SHA256, capabilities and complete canonical dataset |
| `GET /surahs`, `/juzs` | 114 chapters / 30 exact parts |
| `GET /verses`, `/search?q=...` | Canonical text; optional surah/juz/page; cursor offset, limit 1..100 |
| `GET /content/pages/{page}` | Original QCF V2 word and physical line metadata, page 1..604 |
| `GET /content/tafsirs/{source}/{surah}/{ayah}` | Saadi 91, Tabari 15, Ibn Kathir 14, Qurtubi 90; original HTML and verse range |
| `GET /content/meanings/{surah}/{ayah}` | QuranEnc Arabic Seraj original payload and footnotes |
| `GET /content/words/{surah}/{ayah}` | Quran.com word/audio references, including verses spanning pages |
| `GET /content/recitations` | Original MP3Quran Hafs catalog |

Provider responses are cached for one day with the `file` store (`config/quran.php`), independent of the application's DB cache. Only validated responses are cached. Source failures return 502, invalid references return 422; request parameters cannot select arbitrary external hosts. The client caches viewed content and uses official provider fallback if these new server endpoints are not deployed. Canonical text is always bundled locally.

## Religious accounts and sync

These accounts are separate from staff `User` records, admin permissions and attendance.

| Endpoint | Auth |
| --- | --- |
| `POST /api/v1/religious/account/register` | Guest; name, email, password (10..128 characters) |
| `POST /api/v1/religious/account/login` | Guest; email and password |
| `GET /api/v1/religious/account/profile` | Sanctum religious token |
| `POST /api/v1/religious/account/logout` | Revokes current token |
| `POST /api/v1/quran/sync` | Religious account token with `quran:sync` ability |

Registration/login are throttled. Passwords are hashed by Laravel, token plaintext is returned once and stored by Flutter in the platform vault. Tokens expire after 90 days. Staff tokens/session users are rejected by `ReligiousAccountOnly`.

The additive migration `2026_10_13_120000_create_religious_accounts_and_quran_sync.php` creates only:

- `religious_accounts`: own identity and monotonically increasing Quran revision.
- `quran_sync_entities`: per-owner unique key, value, revision and persistent deletion tombstone.
- `quran_sync_operations`: per-owner unique UUID and conflict result, allowing safe replay.

Sync request is `{"operations": [...]}` (maximum 250 per request; an empty list pulls current state). Each operation has `id` UUID, `key`, `base_revision`, `deleted` boolean and `value` (present even when null). Keys: `favorite:{surah}:{ayah}`, `bookmark:{surah}:{ayah}`, `surah:{id}`, `juz:{id}`, `reading`, `preferences`. Reading includes canonical `lastKey`, page and alignment; preferences include mode, fontSize and paper. Values and bounds are validated before writes.

The owner row is locked inside a transaction. An operation applies only when the supplied entity revision equals its current server revision (absent = 0). Server revisions, not device clocks, arbitrate edits. Replays do not increment revision. Response has `revision`, `entities` (including tombstones), `acknowledged` UUIDs and conflicted keys. A stale edit cannot resurrect a deleted favorite. A new deliberate edit based on the deletion revision can restore it.

Guest/account states and queues are separate. Login's visible merge option adds guest entries only if no server record or tombstone exists; existing server reading/preferences prevail. Pending changes are persisted offline and retried at resume/interval/manual sync. Logout restores unchanged guest state. Audio files and audio preferences are device-local and excluded from sync. Offline logout clears the local token; remote revocation requires connectivity (otherwise the server token retains its original expiry).

## Deployment

New endpoints on the supplied live domain returned 404 on 2026-10-13; source code alone does not deploy them. No production/local association DB migration was run by this implementation.

Deploy the Quran-owned PHP/config/routes/resources files and the new migration to the Laravel server. Confirm the existing Sanctum `personal_access_tokens` table/migration is present before the account migration. Review migration status and run only the new Quran migration:

```sh
php artisan migrate:status
php artisan migrate --path=database/migrations/2026_10_13_120000_create_religious_accounts_and_quran_sync.php --force
php artisan optimize:clear
php artisan route:list --path=api/v1/quran --except-vendor
```

If Sanctum's existing token migration has never been deployed, run its named migration separately after checking status; do not blindly migrate unrelated application changes. Set server `APP_URL=https://hak.duosparktech.com` so the manifest dataset link is public HTTPS. Ensure `storage/framework/cache` is writable. Test manifest, register/login, offline merge, two-device conflict/deletion and logout on the deployed server. This chat did not push or deploy the backend repository.

## Validation

```sh
php vendor/bin/phpunit tests/Unit/QuranCatalogTest.php tests/Feature/QuranApiTest.php tests/Feature/QuranContentTest.php tests/Feature/QuranSyncTest.php
```

Catalog/content tests use files and HTTP fakes. Sync tests explicitly use a dedicated SQLite `:memory:` connection and only the Quran and personal-token migrations; they never run all application migrations or access the real local database. PHP lint/Pint are limited to Quran-owned files. These tests do not prove live-domain deployment or native audio operation.
