# Nusuk adhan recordings

The manifest `nusuk_adhans.json` contains 48 recording identities, each with a full adhan and a takbeer file. All 96 files were copied from the user's downloaded Nusuk 17.5.8 recordings on 2026-10-29. SHA-256 and byte length identify the exact audio; recordings were not re-encoded. Arabic display names are transcriptions of the English labels observed in the reference UI; `englishName` preserves those labels. The UI repeats Hussain Abdullah Hussain Rajab; the downloaded set contains ID 30 and no ID 31, so no recording for 31 was invented.

Media belongs in `storage/app/religious/adhans/` (ignored by Git). Keep the manifest in Git; do not commit the 249 MiB media library to the Flutter assets. Only `nusuk-1` (Ali Ahmed Mulla) and `nusuk-2` (Hamad Ibn Ahmad Al-Daghriri) are bundled in Flutter: four MP3 files, 8,015,214 bytes total. The remaining 46 voices download on demand from this backend.

Public endpoints:

- `GET /api/v1/religious/adhans/catalog`
- `GET /api/v1/religious/adhans/nusuk-{id}/full`
- `GET /api/v1/religious/adhans/nusuk-{id}/takbeer`

Audio responses support byte ranges. The server checks size/hash against the manifest; the app independently checks both variants before marking a voice ready. No database migration or external Nusuk API credential is required.

## Media delivery

The local delivery archive is `C:\Users\Hazem\Documents\Haq-Designs\Nusuk_Location_2026-10-29\nusuk-adhan-media-2026-10-29.tar`. It contains only the manifest and media at their project-relative paths. Deploy the backend code with the project's normal release procedure, extract the archive at the backend project root, and allow the PHP process to read `storage/app/religious/adhans/`. Do not extract into Flutter or a public upload folder. Verify the catalog and a ranged request after deployment. If config/routes are cached, refresh them through the normal release procedure. No remote deployment was performed for this change.

The app uses its existing `HAQ_API_BASE` setting (default `https://hak.dr-bike.shop/public/api`). Publish the backend and media before distributing the updated app; otherwise bundled voices work offline but the other downloads return 404.

## Reference peculiarities

All 96 staged files passed SHA-256 comparison with the copied recordings and MPEG Layer III frame parsing. Full adhan duration is at most ~251 seconds. Reference takbeer files 25, 45 and 49 contain long/full recordings (104–212 seconds). Android lets genuine takbeer recordings finish, with a 45-second upper bound; those long reference variants use the existing 25-second short-mode fallback. Full mode plays the complete recording. iOS notification audio retains its existing 25-second platform limit; full in-app preview remains available.

Evidence, source APK identity, original UI captures, per-file REA pull receipts, device hashes and `mp3-verification.json` are retained in the local investigation folder above. No source-service credential is included in the app, backend, manifest or archive.

Validation: `php artisan test --filter=AdhanApiTest` covers public catalog, both variants, range headers, invalid IDs, traversal, missing media and checksum mismatch. Flutter tests cover bundled offline installation, paired on-demand downloads, pinned checksum rejection, native preparation paths, compatibility and selection UI. Android source compilation and native checks run with `tool/check_reminders_android.ps1` in the Flutter checkout. iOS compilation/device playback and production download validation require their respective environments.
