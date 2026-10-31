# Published translation sources

## Hadith acquisition and publication workflow — 2026-10-31

HadeethEnc CSV acquisition is now implemented for explicit local-to-provider reference mappings, with private bounded streaming, full raw record/metadata retention, independent review and publication permissions, signed release snapshots, and offline reader display. Details and deployment migration: [hadith source workflow](../../../docs/hadith-translation-sources.md). No heuristic candidate is approved automatically. The 15-column metadata row with 14 empty trailing cells in the live export is covered by the parser fixture.

A single current-English record was staged from the private v1.25.0 archive for `nawawi-01` / provider ID `66511`: 12,570 artifact bytes, SHA-256 `d64cda6744a906717bb050b5b2048f1bdfb24dce40b78a4ee2c03c0451780801`, unreviewed and unpublished. The full archive remains private. This is acquisition evidence, not approval of all current hadith mappings or language coverage.

## Publication terms verification update — 2026-10-30

The official [English CSV export](https://hadeethenc.com/en/browse/download/en/csv) was downloaded into private review storage on 2026-10-30. Its document header identifies **v1.25.0**, last update `2026-05-10 17:43:35` (no timezone specified by the source), and its update-check URL. The 10,958,600-byte archive has SHA-256 `a65e73317685f69cee26e56d2376516e375557b2e48a1d109a7eff5a6f07f48a` and 2,328 records, retaining Arabic, English and source metadata fields. The raw CSV remains outside Git/public storage. An initial strict text-comparison audit did not establish unique automatic mappings to the 46 current Haq hadiths; this does not establish that their narrations are absent from the provider. No approximate mapping or translation was approved or published.

### Chapter interface metadata

`quran-chapters-en-20261030.json` preserves the exact response from [Quran.com's chapter metadata API](https://api.quran.com/api/v4/chapters?language=en), SHA-256 `ff6ef5aadcc7710271179c0f9c1519c89ae613b11c8ba3265873c6c4fc6aed0b`. Its 114 unique IDs and verse counts match the bundled canonical dataset. English interface labels use `name_simple` (transliterated names); Arabic labels retain the exact local chapter names. This is chapter metadata, not a translation of Quranic verses. The snapshot and mapping have an isolated validation test. Quran text, Mushaf headings and personal identifiers are retained.

The official [HadeethEnc home page, Terms and Policies](https://hadeethenc.com/en/home) now explicitly confirms downloading and republishing translations under conditions: preserve the complete content and document version metadata, identify HadeethEnc.com and the publisher, display the version, follow source updates, report translation observations to the source and exclude inappropriate advertising. This resolves the earlier unverified redistribution note below; no publisher message was sent.

Current Haq hadith entries identify Sunnah.com references, not HadeethEnc IDs. Source acquisition still needs explicit reference mapping and preservation of the provider edition; equal-looking titles or matching hadith numbers from different collections do not establish identity. No hadith translation was generated or auto-published by this verification.

The six absent matches in the captured QuranEnc index are `bn`, `my`, `kk`, `ko`, `ms`, `ru`. Rechecking the language-filtered live list returned no resources for five; Bengali lookup was unavailable. This is a provider-availability observation, not a permanent claim that a language has no published Quran translation. The official [API documentation](https://quranenc.com/en/home/api) also documents tafsir resources and per-verse translated audio; availability must be checked per resource before exposing it.

Snapshots were retrieved on 2026-10-30 from the owning services:

- [QuranEnc official API](https://quranenc.com/en/home/api), [live translation index](https://quranenc.com/api/v1/translations/list?localization=en).
- [HadeethEnc official API documentation](https://github.com/islamhouse-dev/hadith-api), [live language index](https://hadeethenc.com/api/v1/languages).

The snapshots contain **metadata**, not a claim that every language has a complete translation of every existing Haq item. The retrieved QuranEnc index contains 76 translation resources, and the HadeethEnc index contains 72 language records. `coverage-20261030.json` compares the target app locales with those exact responses. An empty resource list means no match in this captured response, not permanent absence from all of the provider's services. Arabic scripture continues to use Haq's existing verified Arabic dataset.

The proposed aliases `fil → tl`, `zh-Hans → zh`, and `ckb → ku` require a script/dialect/content review before enabling a language. A generic provider code does not establish the correct Chinese script or Kurdish variety. Kazakh script/region and Portuguese regional wording also need the planned locale review.

QuranEnc's [republishing conditions](https://quranenc.com/en/home) require keeping the translation content intact, retaining publisher/source and transcript information, displaying the version, following source corrections/updates, reporting notes to the publisher, and avoiding inappropriate advertisements beside translations. These requirements must travel with downloaded source packages. HadeethEnc's public API documentation alone does not establish permission to redistribute its entire database; confirm the applicable publication terms before bulk distribution. No third-party account or API key was created, and no messages were sent to publishers.

`php artisan haq-app:stage-quranenc english_rwwad --locale=en` stages current Quran-content references in private local storage, preserving exact source JSON, footnotes, source metadata, version and byte hashes. It does not update a database, turn on a language, overwrite Arabic content or publish a package. Source text is kept separate from editable interface translations.

The live English stage completed on 2026-10-30: Rowwad v1.0.20, 23 complete surah responses covering 43 existing Quran-content items, 2,744,831 bytes. SHA-256: `e5d0c4f23ba5ee5d6efc6cee173b9988a4167b0493b9a7fa463461fe9671f9af`. The review artifact is under `storage/app/private/haq-source-review/`, intentionally outside Git and public storage. It is marked unreviewed; mapping/approval and connection to the publishing workflow remain necessary. It does not contain the complete Quran or translations of the remaining hadith/adhkar/articles.

External calls are made only to fixed QuranEnc endpoints. Unknown source keys, mismatched languages, duplicate/missing verses and a version change during acquisition fail without committing a staged package. The Arabic response fields are preserved as source metadata and never replace the existing Haq Quran text.

### Reciter display metadata — 2026-10-31

`mp3quran-reciters-en-20261031.json` records display metadata from the [official English MP3Quran API](https://www.mp3quran.net/api/v3/reciters?language=eng), documented by [MP3Quran](https://www.mp3quran.net/eng/api). All 221 existing Hafs recitations matched both numeric reciter/recitation IDs and the exact audio-server URL. The snapshot records the fetched response SHA-256 and original/English labels. Only display/search metadata enters the UI registry; audio URLs, availability, recording identities, Arabic catalog names and cache keys are retained. The four existing EveryAyah names use manually transliterated display labels based on the bundled catalog; they are not attributed to this MP3Quran snapshot. A translated name does not imply translated recitation audio.
