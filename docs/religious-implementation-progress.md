# تنفيذ خطة حق — الباك

النسخة المعتمدة: `C:\laragon\www\haq-foundation`، مع Flutter في `C:\flutter_projects\haq_app`. بداية التنفيذ: `13cde775857c1fd19ad69300e99f4310587cb55a`.

## P00 — استعادة قابلية الاختبار

يُستعاد vendor من `composer.lock` دون تحديث الإصدارات أو تشغيل سكربتات التثبيت. وجود اسم الحزمة في installed.json لا يضمن اكتمال ملفاتها: نسخة الجهاز كانت ناقصة ملفات Collision وPHPUnit.

امتداد zip متوفر كـDLL لكنه غير مفعّل في PHP CLI الحالي؛ يمكن تحميله للأمر وحده دون تعديل إعدادات Laragon العامة:

```powershell
$env:Path='C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64;' + $env:Path
php -d extension=zip C:\laragon\bin\composer\composer.phar install --no-interaction --prefer-dist --no-scripts --no-progress
```

عند نقص ملفات حزمة مثبتة، يُستخدم `composer reinstall` للإصدار المقفل للحزمة المتضررة؛ لا يُستخدم update لتجاوز المشكلة.

### الاختبارات المعزولة

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File tools/test-religious.ps1 -Php C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe
```

المشغّل يقتصر على Quran وReligiousContent وDeploymentRunner. التهيئة `tests/religious-bootstrap.php` تفرض SQLite في الذاكرة، وتمنع أن يعيد dotenv رابط قاعدة حقيقية، وتستخدم cache/session من نوع array. يُرفض التشغيل عند وجود إعداد Laravel مخزّن في cache. يستعيد مشغّل PowerShell بيئة العملية عند انتهائه.

الاختبارات العامة تقرأ ملفات، واختبارات المزامنة تنشئ جداولها المخصصة داخل الذاكرة فقط. لا تشغيل migrations عامة، ولا تغيير `.env`، ولا اتصال بقاعدة الجمعية، ولا نشر أو تشغيل سيرفر ضمن المرحلة.

أُعيد تثبيت الحزم الـ130 بإصدارات composer.lock نفسها، دون تغيير manifest أو lock أو تشغيل سكربتات Composer. استُكملت أرشيفات GitHub الكبيرة من المرجع المقفل وفُحصت CRC وهوية الحزمة قبل استخدامها في cache؛ نجح التثبيت النهائي من cache مع تعطيل الشبكة للعملية.

أُغلقت P00: نجح المشغّل بـ34 اختبارًا و241 تحققًا، دون أخطاء أو تحذيرات PHPUnit. فحص PHP للتهيئة وفحص Git للفروق نجحا. في Flutter نجح التحليل و73 اختبارًا وفحص 17 ملف منصة؛ كوميت Flutter هو `fad6ae8853144547e277aba13be9806dd63d8e78`.

لا يعد نجاح الاختبارات إثبات نشر API أو تشغيل جهاز فعلي. لم يُشغّل التطبيق أو يُبنَ APK، ولم تُعدّل قاعدة الجمعية أو `.env`.
