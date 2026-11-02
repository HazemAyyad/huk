"""Canonical, explicitly located publisher passages for additional Sunnah items.

HTML paragraphs retain their exact strings and linked note text. The two PDF
passages below are transcribed from rendered pages (raw extraction corrupts
letters/word spacing); both carry the edition hash, PDF page and publisher URL.
"""
import hashlib
import json
import pathlib
import re

from source_html import PublisherHTML

root = pathlib.Path(__file__).resolve().parents[1]
private = root / "storage/app/private/haq-en-tr-content"
originals = {row["id"]: row for row in json.loads((root / "resources/religious/catalog.json").read_text("utf8"))["items"]}
result = {"en": {}, "tr": {}}


def add(locale, ident, text, path, url, title, footnotes="", source_arabic="", page=None):
    source = {"provider": "IslamHouse.com", "title": title, "version": "snapshot-2026-11-02", "url": url,
        "artifact_sha256": hashlib.sha256(path.read_bytes()).hexdigest()}
    if page:
        source["page"] = page
    result[locale][ident] = {"title": title, "text": text, "footnotes": footnotes,
        "source_arabic": source_arabic, "alignment": "publisher_record_variant",
        "original_sha256": hashlib.sha256(originals[ident]["text"].encode()).hexdigest(), "source_url": url, "source": source}


for ident, page, number in [
    ("sunnah-perfume", 214, "1154/8"), ("sunnah-salawat", 214, "1158/12"),
    ("sunnah-answer", 214, "1156/10"), ("sunnah-after-friday", 207, "1126/1"),
    ("sunnah-mosque-walk", 193, "1057/5"),
]:
    path = private / f"riyadh-en-{page}.html"
    blocks = PublisherHTML(path.read_text("utf8")).blocks
    text = next(row["text"] for row in blocks if row["text"].lstrip().startswith(number + " -"))
    add("en", ident, text, path, f"https://riyadh.islamenc.com/en/page/{page}", "Riyadh Al-Salheen — " + number)


for ident, filename, selector, url, title in [
    ("sunnah-perfume", "tr-namaz", "Her kim cuma günü gusleder", "https://islamhouse.com/read/tr/t%C3%BCm-detaylar%C4%B1yla-namaz-791", "Tüm Detaylarıyla Namaz"),
    ("sunnah-after-friday", "tr-namaz", "dört rekât namaz kılsın.", "https://islamhouse.com/read/tr/t%C3%BCm-detaylar%C4%B1yla-namaz-791", "Tüm Detaylarıyla Namaz"),
    ("sunnah-answer", "tr-profitable", "Cuma gününde öyle bir saat vardır", "https://islamhouse.com/read/tr/k%C3%82rli-pazardan-salih-amelin-sevabina-dair-se%C3%87meler-2835242", "Kârlı Pazardan Salih Amelin Sevabına Dair Seçmeler"),
    ("sunnah-mosque-walk", "tr-profitable", "Namazda en büyük sevabı", "https://islamhouse.com/read/tr/k%C3%82rli-pazardan-salih-amelin-sevabina-dair-se%C3%87meler-2835242", "Kârlı Pazardan Salih Amelin Sevabına Dair Seçmeler"),
    ("sunnah-walk", "tr-steps", "attığı her adım için", "https://islamhouse.com/tr/articles/942", "Sayılı Adımlarla Elde Edilen Milyonlarca Sevaplar — Süleyman b. Abdulkerim el-Muferric"),
]:
    path = private / (filename + ".html")
    blocks = PublisherHTML(path.read_text("utf8")).blocks
    selected = [row for row in blocks if selector in " ".join(row["text"].split())]
    assert len(selected) == 1, (ident, len(selected))
    row = selected[0]
    notes = []
    if filename == 'tr-namaz':
        number = '515' if ident == 'sunnah-perfume' else '539'
        notes.append(next(p['text'] for p in blocks if re.match(r'^\s*' + number + r'\s', p['text'])))
    if filename == 'tr-profitable':
        position = blocks.index(row)
        # The very next paragraph is the explicit Bukhari/Muslim reference.
        notes.append(blocks[position + 1]['text'])
    for link in row["links"]:
        fragment = link.split("#")[-1]
        if fragment.startswith("note"):
            number = re.search(r"[0-9]+", fragment)[0]
            note = next((p["text"] for p in blocks if re.match(r"^\s*\[" + number + r"\]", p["text"])), None)
            if note:
                notes.append(note)
    add("tr", ident, row["text"], path, url + ("#" + row["id"] if row["id"] else ""), title, "\n\n".join(notes))

add("en", "sunnah-walk",
    "Aws ibn Aws reported the Messenger of Allaah ﷺ as saying, “If anyone washes and bathes on Friday, goes out early [94] walking, not riding, goes near to the Imaam and listens without interrupting, he will have the reward of a year's fasting and praying for every step he takes.” [95]",
    private / "en-friday.pdf", "https://d1.islamhouse.com/data/en/ih_books/single/en_Ettiquettes_of_a_Muslim_on_Friday.pdf#page=54",
    "Ettiquettes of a Muslim on Friday — publisher PDF page 54 (printed page 55)",
    footnotes="[94] The text has bakkara wa’btakara. Both words mean the same thing and are presumably both used to emphasise the idea of going out early.\n[95] Sunan Aboo Daawood: vol. 1; p. 91; no. 345. Saheeh Sunan Aboo Daawood: vol. 1; p. 70; no. 333.\nPublisher PDF page 56 (printed page 57).", page=54)
add("tr", "sunnah-salawat",
    "Günlerinizin en faziletlisi cuma günüdür. Âdem aleyhisselâm o gün yaratıldı, o gün vefat etti, Sûr’a o gün üflenir ve o gün sayha (bayılma/ölüm) olur. Bu yüzden o gün bana çokça salât edin, zira salâtlarınız Bana sunulur.",
    private / "tr-friday.pdf", "https://d1.islamhouse.com/data/aa/tr-cuma-hutbeler-turkish.pdf#page=46",
    "Cuma Hutbeleri — Mâcid b. Süleyman er-Rassî — page 46",
    footnotes="(75) İsrafil’in sûr’a ikinci üfleyişi; mahlûkatın kabirlerinden kalkışı.\n(76) Sûr’a ilk üflenişle mahlûkatın ölmesi; iki üfleme arası kırk yıldır.\n(77) Nesâî 1373, Ebû Dâvud 1047, İbn Mâce 1085, Ahmed b. Hanbel, el-Müsned 4/8. (Elbânî ve Müsned muhakkiklerince sahih görülmüştür).", page=46)

for locale, entries in result.items():
    assert len(entries) == 6
    (private / locale / "sunnah-supplement.json").write_text(json.dumps(entries, ensure_ascii=False, separators=(",", ":")), encoding="utf8")
    print(locale, "additional Sunnah publisher passages", len(entries))
