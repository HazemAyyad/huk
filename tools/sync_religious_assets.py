"""Copy prepared source artifacts and UI registry into a Flutter checkout."""
import json
import pathlib
import shutil
import sys

server = pathlib.Path(__file__).resolve().parents[1]
flutter = pathlib.Path(sys.argv[1]).resolve()
assert (flutter / "pubspec.yaml").is_file()
target = flutter / "assets/localization/content"
target.mkdir(parents=True, exist_ok=True)
for path in (server / "resources/haq-app/content").iterdir():
    if path.is_file():
        shutil.copyfile(path, target / path.name)
manifest = json.loads((target / "index.json").read_text("utf-8"))

def dart_json(name, value):
    return "const " + name + " = r'''" + json.dumps(value, ensure_ascii=False, separators=(",", ":")) + "''';\n"

(flutter / "lib/core/localization/bundled_content_catalog.dart").write_text(
    "// Generated from the verified publisher snapshots.\n" + dart_json("bundledContentCatalogJson", manifest), encoding="utf-8")

labels = {
    "source.tafsir": ("تفسير منشور باللغة المختارة", "Published tafsir in the selected language"),
    "source.nameMeaning": ("معنى الاسم", "Meaning of the name"),
    "source.publisherTranslation": ("ترجمة نص المصدر", "Translation of the publisher's text"),
    "source.localUnavailable": ("تعذّر فتح الترجمة المحفوظة؛ الأصل العربي متاح.", "The saved translation could not be opened. The Arabic original is available."),
    "source.narrationNotice": ("هذه ترجمة نص المصدر؛ قد تختلف ألفاظه عن النص العربي المعروض. راجع نص المصدر للمقارنة.", "This translation follows the publisher's text, whose wording may differ from the Arabic text shown. See the publisher's record to compare."),
    "source.publisherArabic": ("رواية المصدر بالعربية", "Publisher's Arabic narration"),
    "source.openRecord": ("فتح النص عند المصدر", "Open this publisher record"),
    "source.openPublisher": ("الناشر ومعلومات المصدر", "Publisher and source information"),
    "source.documentMetadata": ("بيانات الوثيقة والإصدار", "Document and edition metadata"),
    "source.contentCoverage": ("تغطية المحتوى المتاح", "Available content coverage"),
    "source.notSeparateTafsir": ("لا يقدّم المصدر شرحًا مستقلًا لهذه الآية. يمكنك قراءة تفسير السورة كاملًا للاطلاع على سياق الشرح.", "The publisher does not provide a separate commentary block for this ayah. Read the full chapter's tafsir for the surrounding explanation."),
    "source.readChapterTafsir": ("قراءة تفسير السورة من المصدر", "Read the publisher's full chapter tafsir"),
    "source.fullChapter": ("تفسير السورة كاملًا", "Full chapter tafsir"),
    "source.coverage": ("معاني القرآن: {quran} آية • التفسير: {tafsir} آية • الأذكار: {dhikr} • الأسماء: {names} • روايات الحديث: {hadith}", "Quran meanings: {quran} verses • Tafsir: {tafsir} verses • Adhkar: {dhikr} • Names: {names} • Hadith narrations: {hadith}"),
}
registry = json.loads((server / "resources/haq-app/ui.json").read_text("utf-8"))
for key, (ar, en) in labels.items():
    registry[key] = {"ar": ar, "en": en}
for path in (server / "resources/haq-app/ui.json", flutter / "assets/localization/ui.json"):
    path.write_text(json.dumps(registry, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
languages = json.loads((flutter / "assets/localization/languages.json").read_text("utf-8"))
(flutter / "lib/core/localization/bundled_localization.dart").write_text(
    "// Generated from assets/localization.\n" + dart_json("bundledUiJson", registry) + dart_json("bundledLanguagesJson", languages), encoding="utf-8")
print("Synced", len(registry), "UI keys and", len(manifest["locales"]), "content locales")
