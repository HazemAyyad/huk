"""Acquire publisher text verbatim and build the app's offline EN/TR sources.

No database access, generated religious prose, credentials, or automatic fuzzy
matches. Raw HTTP responses stay in private storage; the distributable retains
the publisher metadata and source text. Run with Python 3.13 or later.
"""
from __future__ import annotations

import concurrent.futures
import csv
import datetime as dt
import gzip
import hashlib
import json
import pathlib
import re
import sqlite3
import time
import unicodedata
import urllib.request
import urllib.parse
from source_html import PublisherHTML

ROOT = pathlib.Path(__file__).resolve().parents[1]
PRIVATE = ROOT / "storage/app/private/haq-en-tr-content"
OUT = ROOT / "resources/haq-app/content"


def digest(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def fetch(url: str, *, limit: int = 40 * 1024 * 1024) -> bytes:
    # Source URLs are constructed by this build tool, never supplied by clients.
    if urllib.parse.urlparse(url).scheme != "https":
        raise ValueError("HTTPS required")
    PRIVATE.mkdir(parents=True, exist_ok=True)
    cache = PRIVATE / (digest(url.encode()) + ".response")
    if cache.exists():
        return cache.read_bytes()
    for attempt in range(4):
        try:
            request = urllib.request.Request(url, headers={"User-Agent": "HaqSourceAcquisition/1.0", "Accept": "application/json,*/*"})
            with urllib.request.urlopen(request, timeout=40) as response:
                data = response.read(limit + 1)
                if len(data) > limit:
                    raise ValueError("Source response exceeds limit")
            cache.write_bytes(data)
            return data
        except Exception:
            if attempt == 3:
                raise
            time.sleep(attempt + 1)
    raise AssertionError("unreachable")


def get(url: str):
    # Hisn's published JSON contains literal control whitespace in a few strings.
    # Python's permissive string decoder preserves those characters verbatim.
    raw = fetch(url).decode("utf-8-sig")
    if url == "https://www.hisnmuslim.com/api/en/126.json":
        # The publisher omitted the closing quote of the top-level chapter key.
        # Decode the intact record array without changing any record characters.
        return {"What to say when you feel frightened": json.loads(raw[raw.index("["):raw.rindex("]") + 1], strict=False)}
    return json.loads(raw, strict=not url.startswith("https://www.hisnmuslim.com/"))


def parallel(tasks, worker):
    with concurrent.futures.ThreadPoolExecutor(max_workers=4) as pool:
        return list(pool.map(worker, tasks))


def arabic_identity(text: str) -> str:
    text = unicodedata.normalize("NFKD", text)
    text = text.translate(str.maketrans("أإآٱى", "ااااي"))
    return "".join(c for c in text if "\u0621" <= c <= "\u064a" and c != "\u0640" and unicodedata.category(c).startswith("L"))


def quran(locale: str, catalog: dict) -> dict:
    key = {"en": "english_rwwad", "tr": "turkish_rwwad"}[locale]
    source = next(row for row in catalog["translations"] if row["key"] == key)
    assert source["language_iso_code"] == locale
    raw = fetch("https://quranenc.com/downloads/sqlite/" + key + ".sqlite")
    path = PRIVATE / (key + "-publisher.sqlite")
    path.write_bytes(raw)
    with sqlite3.connect(path.as_uri() + "?mode=ro", uri=True) as connection:
        records = connection.execute("select sura,aya,translation,footnotes from translations order by sura,aya").fetchall()
    verses = {f"{s}:{a}": {"text": text, "footnotes": notes or ""} for s, a, text, notes in records}
    assert len(verses) == 6236 and all(row["text"] for row in verses.values())
    local = json.loads((ROOT / "resources/quran/quran.json").read_text("utf-8")) if (ROOT / "resources/quran/quran.json").exists() else None
    if local and "verses" in local:
        assert set(verses) == {row["key"] for row in local["verses"]}
    print(f"{locale} Quran: {len(verses)} verses", flush=True)
    return {"source": {**source, "provider": "QuranEnc.com", "url": "https://quranenc.com/" + locale + "/browse/" + key,
                       "policy_url": "https://quranenc.com/en/home", "artifact_sha256": digest(raw)}, "verses": verses}


def tafsir(locale: str, catalog: dict) -> dict:
    if locale == "tr":
        source = next(row for row in catalog["tafsirs"] if row["key"] == "turkish_saadi")
        def chapter(s):
            return s, get(f"https://quranenc.com/api/v1/tafsir/sura/turkish_saadi/{s}")["result"]
        chapters = parallel(range(1, 115), chapter)
        blocks, keys = {}, {}
        for s, rows in chapters:
            for row in rows:
                assert int(row["sura"]) == s and isinstance(row["text"], str)
                identifier = str(row["id"])
                assert identifier not in blocks
                blocks[identifier] = row
                start, end = int(row["from_aya"]), int(row["to_aya"])
                assert 0 <= start <= end <= 286
                if start:
                    for a in range(start, end + 1):
                        keys.setdefault(f"{s}:{a}", []).append(identifier)
        source = {**source, "provider": "QuranEnc.com", "url": "https://saadi.islamenc.com/tr", "policy_url": "https://quranenc.com/en/home"}
    else:
        index = get("https://api.quran.com/api/v4/resources/tafsirs?language=en")
        source = next(row for row in index["tafsirs"] if row["id"] == 169)
        assert source["language_name"] == "english"
        def chapter(s):
            page, rows = 1, []
            while True:
                data = get(f"https://api.quran.com/api/v4/tafsirs/169/by_chapter/{s}?per_page=50&page={page}")
                rows.extend(data["tafsirs"])
                following = data.get("pagination", {}).get("next_page")
                if not following:
                    break
                assert following > page and following < 300
                page = following
            return s, rows
        chapters = parallel(range(1, 115), chapter)
        blocks, keys = {}, {}
        for s, rows in chapters:
            for row in rows:
                assert row["resource_id"] == 169 and int(row["verse_key"].split(":")[0]) == s
                identifier = digest(row["text"].encode())
                blocks.setdefault(identifier, {"text": row["text"]})
                keys[row["verse_key"]] = [identifier]
        source = {**source, "provider": "Quran.com", "version": "snapshot-" + dt.date.today().isoformat(),
                  "url": "https://quran.com/en/tafsirs/en-tafisr-ibn-kathir", "policy_url": "https://quran.com/about-us"}
    print(f"{locale} Tafsir: {len(keys)} verse references, {len(blocks)} complete blocks", flush=True)
    assert len(keys) == 6236
    return {"source": source, "blocks": blocks, "verses": keys}


def resolve_tafsir_groups(data: dict) -> dict:
    """Quran.com's chapter export has blank continuation rows. Resolve every
    continuation using the publisher's explicit verse-group API, never by
    assuming that a preceding paragraph covers a range of verses.
    """
    if data.get("groups_verified"):
        return data
    def chapter(surah):
        assigned, blocks = {}, {}
        for key, ids in data["verses"].items():
            if int(key.split(":")[0]) != surah or key in assigned:
                continue
            if all(data["blocks"][ident]["text"] for ident in ids):
                continue
            source = get(f"https://api.quran.com/api/v4/tafsirs/169/by_ayah/{key}")["tafsir"]
            assert source["resource_id"] == 169 and source["text"].strip(), f"Empty publisher tafsir response for {key}"
            identifier = digest(source["text"].encode())
            blocks[identifier] = {"text": source["text"], "verse_keys": list(source["verses"])}
            # Some publisher group lists omit the requested verse (20:32,
            # for example). Bind that response ONLY through its explicit aya
            # endpoint; retain the original group list without inventing ranges.
            if key not in source["verses"]:
                blocks[identifier]["requested_key"] = key
                blocks[identifier]["group_metadata_omits_requested_key"] = True
                blocks[identifier]["response_url"] = f"https://api.quran.com/api/v4/tafsirs/169/by_ayah/{key}"
            assigned[key] = [identifier]
            valid_group = key in source["verses"] and all(member in data["verses"] and int(member.split(":")[0]) == surah for member in source["verses"])
            if not valid_group:
                blocks[identifier]["response_url"] = f"https://api.quran.com/api/v4/tafsirs/169/by_ayah/{key}"
                blocks[identifier]["requested_key"] = key
                blocks[identifier]["group_metadata_invalid"] = True
                continue
            for member in source["verses"]:
                existing = data["verses"][member]
                if all(data["blocks"][ident]["text"] for ident in existing):
                    # The chapter export can contain a different overlapping
                    # block. Preserve its exact text and existing binding.
                    continue
                assigned[member] = [identifier]
        if surah % 10 == 0:
            print(f"English tafsir verse groups checked through surah {surah}", flush=True)
        return assigned, blocks
    for assigned, blocks in parallel(range(1, 115), chapter):
        data["verses"].update(assigned)
        data["blocks"].update(blocks)
    assert all(all(data["blocks"][ident]["text"] for ident in ids) for ids in data["verses"].values())
    used = {ident for ids in data["verses"].values() for ident in ids}
    data["blocks"] = {ident: row for ident, row in data["blocks"].items() if ident in used}
    data["groups_verified"] = True
    return data


def english_dhikr() -> dict:
    index = get("https://www.hisnmuslim.com/api/en/husn_en.json")
    chapters = next(iter(index.values()))
    def chapter(row):
        url = f"https://www.hisnmuslim.com/api/en/{row['ID']}.json"
        try:
            data = get(url)
            return row, next(iter(data.values()))
        except (ValueError, urllib.error.HTTPError) as error:
            print(f"Hisn chapter {row['ID']}: unavailable publisher JSON ({type(error).__name__})", flush=True)
            return row, []
    entries = {}
    local = json.loads((ROOT / "resources/religious/adhkar.json").read_text("utf-8"))
    items = {int(row["source_id"]): row for row in local["items"] if row.get("source_id")}
    unmatched = []
    for section, rows in parallel(chapters, chapter):
        for row in rows:
            identifier = int(row["ID"])
            if identifier not in items:
                continue
            original = items[identifier]
            arabic = row.get("ARABIC_TEXT") or row.get("Text")
            if not arabic:
                unmatched.append(original["id"])
                continue
            exact = arabic_identity(original["text"]) == arabic_identity(arabic)
            if not exact:
                unmatched.append(original["id"])
            translated = row.get("TRANSLATED_TEXT", "")
            if identifier == 133:
                # In this one publisher record the English instruction lives
                # in the transliteration field; retain its exact field value.
                translated = row["LANGUAGE_ARABIC_TRANSLATED_TEXT"]
            if identifier == 185:
                with (PRIVATE / "hadeethenc-en.csv").open(encoding="utf-8-sig", newline="") as handle:
                    document = next(csv.reader(handle))[0]
                    publisher = next(record for record in csv.DictReader(handle) if record["id"] == "58114")
                entries[original["id"]] = {"title": publisher["title"], "text": publisher["hadith_text"],
                    "source_arabic": publisher["hadith_text_ar"], "fields": publisher,
                    "original_sha256": digest(original["text"].encode()), "source_id": 185,
                    "alignment": "publisher_record_variant", "source_url": publisher["link"],
                    "source": {"provider": "HadeethEnc.com", "title": "Translated Prophetic Hadiths", "version": "1.25.0",
                        "document": document, "url": publisher["link"]}}
                continue
            if not translated.strip():
                continue
            entries[original["id"]] = {"title": section["TITLE"], "text": translated,
                "transliteration": row.get("LANGUAGE_ARABIC_TRANSLATED_TEXT", ""),
                "original_sha256": digest(original["text"].encode()), "source_arabic": arabic,
                "alignment": "arabic_exact" if exact else "publisher_record_variant",
                "source_id": identifier, "source_url": f"https://www.hisnmuslim.com/api/en/{section['ID']}.json"}
    print(f"en Dhikr: {len(entries)} publisher records; {len(unmatched)} Arabic variants", flush=True)
    return {"source": {"provider": "HisnMuslim.com", "title": "Fortress of the Muslim — Sa'id ibn Ali ibn Wahf al-Qahtani",
            "url": "https://www.hisnmuslim.com", "version": "snapshot-" + dt.date.today().isoformat()},
            "entries": entries, "unmatched": unmatched}


def turkish_dhikr() -> dict:
    # IslamHouse's published HTML retains numbered paragraphs and all footnotes.
    # Keep the exact record boundary: a numbered translated paragraph follows
    # its Arabic paragraph(s), with reference notes resolved by explicit IDs.
    raw = (PRIVATE / "tr-hisn-page.html").read_bytes()
    blocks = [row for row in PublisherHTML(raw.decode("utf-8")).blocks if row["id"] and re.fullmatch(r"p[0-9]+", row["id"])]
    notes = {}
    for row in blocks:
        match = re.match(r"^\[([0-9]+)\]", row["text"].strip())
        if match:
            notes[match[1]] = row["text"]
    local = json.loads((ROOT / "resources/religious/adhkar.json").read_text("utf-8"))
    items = {int(row["source_id"]): row for row in local["items"] if row.get("source_id")}
    entries, unmatched = {}, []
    markers = [(i, re.match(r"^\s*([0-9]{1,3})(?:[.\-]|\s+\")", row["text"])) for i, row in enumerate(blocks) if "ltr" in row["class"]]
    markers = [(i, match) for i, match in markers if match and int(match[1]) in items]
    assert len({int(m[1]) for _, m in markers}) == len(markers)
    def start(index):
        while index > 0 and "rtl" in blocks[index - 1]["class"]:
            index -= 1
        return index
    for marker_index, (i, match) in enumerate(markers):
        row = blocks[i]
        if not match or int(match[1]) not in items:
            continue
        original = items[int(match[1])]
        end = start(markers[marker_index + 1][0]) if marker_index + 1 < len(markers) else len(blocks)
        section = blocks[start(i):end]
        arabic = [p["text"] for p in section if "rtl" in p["class"]]
        source_arabic = "\n".join(arabic)
        exact = arabic_identity(original["text"]) == arabic_identity(source_arabic)
        if not exact:
            unmatched.append(original["id"])
        paragraphs = [p for p in section if p["class"] == "ltr justify" and not re.match(r"^\s*\[[0-9]+\]", p["text"])]
        footnotes = []
        for paragraph in paragraphs:
            for link in paragraph["links"]:
                number = re.search(r"#note_([0-9]+)$", link)
                if number and number[1] not in footnotes:
                    footnotes.append(number[1])
        assert all(number in notes for number in footnotes), f"Missing Turkish footnote {original['id']}"
        entries[original["id"]] = {"title": "Hısnul-Müslim", "text": "\n\n".join(p["text"] for p in paragraphs),
            "footnotes": "\n\n".join(notes[n] for n in footnotes), "source_arabic": source_arabic,
            "original_sha256": digest(original["text"].encode()), "source_id": int(match[1]),
            "alignment": "arabic_exact" if exact else "publisher_record_variant",
            "source_url": "https://islamhouse.com/tr/books/861/#" + row["id"]}
    print(f"tr Dhikr: {len(entries)} publisher records; {len(unmatched)} Arabic variants", flush=True)
    return {"source": {"provider": "IslamHouse.com", "title": "Hısnul-Müslim — Said b. Ali el-Kahtani",
        "translator": "İsmail Yaşa", "reviewer": "Muhammed Şahin", "version": "1436 / HTML snapshot",
        "url": "https://islamhouse.com/tr/books/861/", "artifact_sha256": digest(raw)}, "entries": entries, "unmatched": unmatched}


def names(locale: str) -> dict:
    local = json.loads((ROOT / "resources/religious/catalog.json").read_text("utf-8"))
    items = {arabic_identity(row["text"]): row for row in local["items"] if row["type"] == "name"}
    entries = {}
    if locale == "en":
        raw = (PRIVATE / "aladhan-names.html").read_bytes()
        rows = json.loads(raw)["data"]
        for row in rows:
            original = items.get(arabic_identity(row["name"]))
            if original:
                entries[original["id"]] = {"title": row["transliteration"], "text": row["en"]["meaning"],
                    "source_arabic": row["name"], "original_sha256": digest(original["text"].encode())}
        source = {"provider": "AlAdhan.com", "title": "Asma Al Husna", "version": "snapshot-2026-11-02",
                  "url": "https://aladhan.com/asma-al-husna", "artifact_sha256": digest(raw)}
    else:
        raw = (PRIVATE / "tr-names-diyanet.html").read_bytes()
        for row in PublisherHTML(raw.decode("utf-8")).blocks:
            match = re.match(r"^\s*(.+?)\s*\(([\u0600-\u06ff ]+)\)\s*:\s*(.+?)\s*$", row["text"], re.S)
            if not match:
                continue
            original = items.get(arabic_identity(match[2]))
            if original:
                assert original["id"] not in entries
                entries[original["id"]] = {"title": match[1].strip(), "text": match[3], "source_arabic": match[2],
                    "original_sha256": digest(original["text"].encode())}
        source = {"provider": "Diyanet İşleri Başkanlığı — Din İşleri Yüksek Kurulu", "title": "Allah’ın 99 ismi hakkında bilgi verir misiniz?",
                  "version": "snapshot-2026-11-02", "url": "https://kurul.diyanet.gov.tr/tr/kurul/karar/allahin-99-ismi-hakkinda-bilgi-verir-misiniz/56aa0e7b-6fe0-45ce-0892-08dd1c135351", "artifact_sha256": digest(raw)}
        # This edition lists Allah instead of Al-Ahad. The latter has its own
        # explicit published entry; do not shift numeric IDs between lists.
        extra_raw = (PRIVATE / "tr-names-page.html").read_bytes()
        extra = PublisherHTML(extra_raw.decode("utf-8")).blocks
        index = next(i for i, row in enumerate(extra) if row["text"].strip() == "el-Ehad")
        original = next(row for row in items.values() if row["id"] == "name-67")
        entries[original["id"]] = {"title": extra[index]["text"], "text": extra[index + 1]["text"],
            "source_arabic": original["text"], "original_sha256": digest(original["text"].encode()),
            "source": {"provider": "IslamHouse.com", "title": "ALLAH’IN GÜZEL İSİMLERİ — Muhammed Şahin", "version": "2009 / 1430",
                "url": "https://islamhouse.com/tr/articles/208784/#" + extra[index]["id"], "artifact_sha256": digest(extra_raw)}}
    print(f"{locale} Names: {len(entries)} exact Arabic names", flush=True)
    return {"source": source, "entries": entries, "unmatched": [row["id"] for row in items.values() if row["id"] not in entries]}


def hadith(locale: str) -> dict:
    path = PRIVATE / ("hadeethenc-" + locale + ".csv")
    with path.open(encoding="utf-8-sig", newline="") as handle:
        header = next(csv.reader(handle))[0]
        records = list(csv.DictReader(handle))
    source_version = re.search(r"\(v([0-9.]+)\)", header)[1]
    assert f"# Source: https://hadeethenc.com/{locale}" in header
    rows = {row["id"]: row for row in records}
    assert len(rows) == len(records)
    mapping = json.loads((ROOT / "resources/haq-app/sources/content-reference-map.json").read_text("utf8"))["hadith"]
    originals = {row["id"]: row for row in json.loads((ROOT / "resources/religious/catalog.json").read_text("utf8"))["items"]}
    entries, unmatched = {}, []
    for ident, source_id in mapping.items():
        record = rows.get(source_id)
        if not record:
            unmatched.append(ident)
            continue
        assert record["lang"] == locale and record["link"] == f"https://hadeethenc.com/{locale}/browse/hadith/{source_id}"
        assert record["hadith_text"].strip() and record["hadith_text_ar"].strip()
        original = originals[ident]
        entries[ident] = {"title": record["title"], "text": record["hadith_text"], "source_arabic": record["hadith_text_ar"],
            "original_sha256": digest(original["text"].encode()), "alignment": "publisher_record_variant",
            "source_id": source_id, "source_url": record["link"], "fields": record}
    print(f"{locale} Hadith: {len(entries)} canonical references; {len(unmatched)} unavailable", flush=True)
    return {"source": {"provider": "HadeethEnc.com", "title": "Translated Prophetic Hadiths", "version": source_version,
        "document": header, "url": f"https://hadeethenc.com/{locale}/home", "policy_url": "https://hadeethenc.com/en/home",
        "artifact_sha256": digest(path.read_bytes())}, "entries": entries, "unmatched": unmatched}


def export():
    catalog = json.loads((ROOT / "resources/religious/catalog.json").read_text("utf-8"))
    original_dhikr = json.loads((ROOT / "resources/religious/adhkar.json").read_text("utf-8"))
    originals = {row["id"]: row for row in original_dhikr["items"]}
    kinds = {"quran": "quran", "dua": "dhikr", "name": "names", "hadith": "hadith", "sunnah": "hadith"}
    manifest = {"schema": 1, "version": "2026-11-02.1", "locales": {},
        "content_kinds": {row["id"]: kinds[row["type"]] for row in catalog["items"] if row["type"] in kinds}}
    for locale in ("en", "tr"):
        manifest["locales"][locale] = {}
        for kind in ("quran", "tafsir", "dhikr", "names", "hadith"):
            path = PRIVATE / locale / (kind + ".json")
            data = json.loads(path.read_text("utf-8"))
            if kind == "quran":
                data["references"] = {row["id"]: row["verseKeys"] for row in catalog["items"] if row["type"] == "quran"}
                data["reference_hashes"] = {row["id"]: digest(row["text"].encode()) for row in catalog["items"] if row["type"] == "quran"}
            if kind == "hadith":
                supplement = PRIVATE / locale / "sunnah-supplement.json"
                if supplement.exists():
                    additions = json.loads(supplement.read_text("utf8"))
                    assert not set(additions).intersection(data["entries"])
                    data["entries"].update(additions)
            if kind == "dhikr":
                chapters = {}
                for ident in data["entries"]:
                    local = originals[ident]
                    if local.get("haq_content_id"):
                        chapters.setdefault(local["haq_content_id"], []).append(ident)
                data["chapters"] = {ident: {"entries": ids, "original_sha256": digest(next(row["text"] for row in catalog["items"] if row["id"] == ident).encode())} for ident, ids in chapters.items()}
                data["scripture"] = {row["id"]: [key for verse in row.get("scripture", []) for key in verse["keys"]] for row in original_dhikr["items"] if row.get("scripture")}
                data["unavailable"] = [ident for ident in originals if ident not in data["entries"]]
                data["aliases"] = {}
                supplement_path = PRIVATE / locale / "additional.json"
                data["additional_content"] = json.loads(supplement_path.read_text("utf8")) if supplement_path.exists() else {}
                for row in catalog["items"]:
                    if row["type"] != "dua" or row["id"] in chapters:
                        continue
                    matches = [ident for ident in data["entries"] if arabic_identity(originals[ident]["text"]) == arabic_identity(row["text"])]
                    if len(matches) == 1:
                        data["aliases"][row["id"]] = {"entry": matches[0], "original_sha256": digest(row["text"].encode())}
            if kind == "tafsir":
                assert set(data["verses"]) == set(json.loads((PRIVATE / locale / "quran.json").read_text("utf8"))["verses"])
                data["without_distinct_commentary"] = []
                if locale == "tr":
                    data["without_distinct_commentary"] = [key for key, ids in data["verses"].items() if not any(data["blocks"][ident]["text"].strip() and data["blocks"][ident]["type"] != "المقطع" for ident in ids)]
                # Empty provider blocks remain in the immutable block corpus;
                # they are not presented as an independent verse commentary.
                data["verses"] = {key: [ident for ident in ids if data["blocks"][ident]["text"].strip()] for key, ids in data["verses"].items()}
                assert all(data["verses"].values())
            data = {"schema": 1, "format": "haq.bundled-source", "locale": locale, "kind": kind, **data}
            if kind == "tafsir" and locale == "tr":
                data["source"]["display_title"] = "Tefsîru’s-Sa‘dî (Türkçe)"
            raw = json.dumps(data, ensure_ascii=False, separators=(",", ":")).encode()
            compressed = gzip.compress(raw, compresslevel=9, mtime=0)
            name = f"{locale}-{kind}.json.gz"
            (OUT / name).write_bytes(compressed)
            count = len(data.get("verses", data.get("entries", {})))
            meta = {"file": name, "sha256": digest(compressed), "json_sha256": digest(raw), "bytes": len(compressed),
                "json_bytes": len(raw), "records": count, "source": data["source"]}
            if kind == "dhikr":
                meta.update({"exact": sum(row["alignment"] == "arabic_exact" for row in data["entries"].values()),
                             "variants": sum(row["alignment"] != "arabic_exact" for row in data["entries"].values()), "unavailable": data["unavailable"]})
            manifest["locales"][locale][kind] = meta
            print(f"Export {name}: {count} records, {len(compressed)} bytes", flush=True)
    (OUT / "index.json").write_text(json.dumps(manifest, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")


def main():
    OUT.mkdir(parents=True, exist_ok=True)
    quran_catalog = get("https://quranenc.com/api/v1/translations/list?localization=en")
    tafsir_catalog = get("https://quranenc.com/api/v1/tafsirs/list?localization=en")
    for locale in ("en", "tr"):
        folder = PRIVATE / locale
        folder.mkdir(exist_ok=True)
        for kind, builder in (("quran", lambda: quran(locale, quran_catalog)), ("tafsir", lambda: tafsir(locale, tafsir_catalog))):
            path = folder / (kind + ".json")
            if not path.exists():
                path.write_text(json.dumps(builder(), ensure_ascii=False, separators=(",", ":")), encoding="utf-8")
            if locale == "en" and kind == "tafsir":
                data = resolve_tafsir_groups(json.loads(path.read_text("utf-8")))
                path.write_text(json.dumps(data, ensure_ascii=False, separators=(",", ":")), encoding="utf-8")
        for kind, builder in (("dhikr", english_dhikr if locale == "en" else turkish_dhikr), ("names", lambda: names(locale)), ("hadith", lambda: hadith(locale))):
            (folder / (kind + ".json")).write_text(json.dumps(builder(), ensure_ascii=False, separators=(",", ":")), encoding="utf-8")
    export()


if __name__ == "__main__":
    main()
