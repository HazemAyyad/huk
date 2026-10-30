# Published translation sources

Snapshots were retrieved on 2026-10-30 from the owning services:

- [QuranEnc official API](https://quranenc.com/en/home/api), [live translation index](https://quranenc.com/api/v1/translations/list?localization=en).
- [HadeethEnc official API documentation](https://github.com/islamhouse-dev/hadith-api), [live language index](https://hadeethenc.com/api/v1/languages).

The snapshots contain **metadata**, not a claim that every language has a complete translation of every existing Haq item. The retrieved QuranEnc index contains 76 translation resources, and the HadeethEnc index contains 72 language records. `coverage-20261030.json` compares the target app locales with those exact responses. An empty resource list means no match in this captured response, not permanent absence from all of the provider's services. Arabic scripture continues to use Haq's existing verified Arabic dataset.

The proposed aliases `fil → tl`, `zh-Hans → zh`, and `ckb → ku` require a script/dialect/content review before enabling a language. A generic provider code does not establish the correct Chinese script or Kurdish variety. Kazakh script/region and Portuguese regional wording also need the planned locale review.

QuranEnc's [republishing conditions](https://quranenc.com/en/home) require keeping the translation content intact, retaining publisher/source and transcript information, displaying the version, following source corrections/updates, reporting notes to the publisher, and avoiding inappropriate advertisements beside translations. These requirements must travel with downloaded source packages. HadeethEnc's public API documentation alone does not establish permission to redistribute its entire database; confirm the applicable publication terms before bulk distribution. No third-party account or API key was created, and no messages were sent to publishers.

`php artisan haq-app:stage-quranenc english_rwwad --locale=en` stages current Quran-content references in private local storage, preserving exact source JSON, footnotes, source metadata, version and byte hashes. It does not update a database, turn on a language, overwrite Arabic content or publish a package. Source text is kept separate from editable interface translations.

The live English stage completed on 2026-10-30: Rowwad v1.0.20, 23 complete surah responses covering 43 existing Quran-content items, 2,744,831 bytes. SHA-256: `e5d0c4f23ba5ee5d6efc6cee173b9988a4167b0493b9a7fa463461fe9671f9af`. The review artifact is under `storage/app/private/haq-source-review/`, intentionally outside Git and public storage. It is marked unreviewed; mapping/approval and connection to the publishing workflow remain necessary. It does not contain the complete Quran or translations of the remaining hadith/adhkar/articles.

External calls are made only to fixed QuranEnc endpoints. Unknown source keys, mismatched languages, duplicate/missing verses and a version change during acquisition fail without committing a staged package. The Arabic response fields are preserved as source metadata and never replace the existing Haq Quran text.
