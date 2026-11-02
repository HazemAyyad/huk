"""Extract explicit publisher PDF passages, keeping original wording and page.

Requires pypdf, and the two publisher files named below in private staging.
The PDF and extracted pages remain private; excerpts keep links and hashes.
"""
import hashlib
import json
import pathlib
import sys

root = pathlib.Path(__file__).resolve().parents[1]
private = root / "storage/app/private/haq-en-tr-content"
sys.path.insert(0, str(private / "python-tools"))
from pypdf import PdfReader

original = next(row for row in json.loads((root / "resources/religious/catalog.json").read_text("utf8"))["items"] if row["id"] == "ramadan-qadr-dua")
for locale, page, begin, end, url, title in [
    ("en", 47, "O Allah, You are Pardoning,", "so pardon me", "https://d1.islamhouse.com/data/en/ih_books/single3/en-etiquettes-of-walking-to-prayer.pdf", "Etiquettes of Walking to Prayer — Muhammad ibn Abdul-Wahhab"),
    ("tr", 77, "Allah’ım! Sen çok affedicisin", "Beni de affet.", "https://d1.islamhouse.com/data/tr/ih_books/single/tr-allahu-tealya-duayi-ve-senayi-dogru-bir-sekilde-yapmak.pdf", "Allahu Tealâ’ya, Duayı ve Senayı Doğru Bir Şekilde Yapmak"),
]:
    path = private / ("qadr-" + locale + ".pdf")
    reader = PdfReader(path)
    full = reader.pages[page - 1].extract_text()
    start = full.index(begin)
    stop = full.index(end, start) + len(end)
    text = full[start:stop]
    (private / ("qadr-" + locale + "-page.txt")).write_text(full, encoding="utf8")
    entry = {"title": title, "text": text, "alignment": "publisher_record_variant",
        "original_sha256": hashlib.sha256(original["text"].encode()).hexdigest(), "source_url": url + "#page=" + str(page),
        "source": {"provider": "IslamHouse.com", "title": title, "version": "PDF snapshot 2026-11-02", "url": url + "#page=" + str(page),
            "page": page, "artifact_sha256": hashlib.sha256(path.read_bytes()).hexdigest(), "pdf_metadata": {str(k): str(v) for k, v in (reader.metadata or {}).items()}}}
    (private / locale / "additional.json").write_text(json.dumps({original["id"]: entry}, ensure_ascii=False, separators=(",", ":")), encoding="utf8")
    print(locale, "Qadr source page", page, "characters", len(text))
