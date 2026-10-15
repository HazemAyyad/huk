# تشغيل أوامر السيرفر من المتصفح

ارفع الملفين `public/deploy_once.php` و`app/Support/DeploymentRunner.php` مع ملفات المشروع المحدّثة و`vendor` إلى السيرفر. لا يحتاج الرابط إضافة إلى `routes/web.php`؛ ملف PHP داخل public يُفتح مباشرة، حتى لو كان كاش الراوت قديمًا.

أضف إلى `.env` على السيرفر:

```dotenv
DEPLOY_ONCE_ENABLED=true
DEPLOY_ONCE_TOKEN=ضع_هنا_توكن_عشوائي_لا_يقل_عن_32_حرفا
```

اترك `APP_ENV=production` و`APP_DEBUG=false`. لا تحتاج الأداة تفعيل وضع debug. تستخدم إعدادات قاعدة البيانات الحالية من `.env`؛ لا تغيّر بيانات اتصالها.

افتح على دومين السيرفر:

```text
/deploy_once.php?token=قيمة_DEPLOY_ONCE_TOKEN
```

إذا كان document root هو مجلد public، فهذا هو المسار الكامل بعد الدومين. إذا كانت الاستضافة تعرض public كجزء من الرابط، يكون المسار `/public/deploy_once.php` وفق إعداد الاستضافة.

## إذا ظهرت صفحة 404 بتصميم الموقع

هذه الصفحة تأتي من Laravel، بينما ملف النشر عند تعطيله يعرض رسالة `Not Found` نصية فقط. ظهور تصميم الموقع يعني أن الطلب لم يصل إلى الملف المباشر. تحقق من رفع `deploy_once.php` إلى مجلد public الذي يخدم الدومين، ورفع `DeploymentRunner.php` إلى `app/Support` في نفس مشروع Laravel.

في استضافة `hak.duosparktech.com`، فحص 14 أكتوبر 2026 أعاد 404 من Laravel عند `/public/deploy_once.php`، و404 من الاستضافة عند `/deploy_once.php`. المسار المتوقع وفق موقع الملفات هو `/public/deploy_once.php` بعد رفع الملف. إذا كان المشروع داخل `public_html`، تكون الوجهتان `public_html/public/deploy_once.php` و`public_html/app/Support/DeploymentRunner.php`. لا تنشئ `public/public` عند فك حزمة الرفع.

اضبط متغيرات النشر داخل `.env` الموجود بجانب `artisan` و`composer.json`. لا يحتاج هذا المسار إضافة Laravel route أو تشغيل مايجريشن حتى يظهر نموذج الأوامر. إذا استمرت صفحة Laravel بعد وجود الملف في المسار الصحيح، راجع rewrite rules في `.htaccess` على الاستضافة؛ النسخة المحلية تستثني الملفات الموجودة من التحويل إلى index.php.

فتح الرابط يعرض لوحة فقط. اختر «النشر الكامل» ثم اضغط «تنفيذ العملية المختارة»، أو اختر المايجريشن فقط، مسح الكاش، بناء كاش الإعدادات أو الراوت، storage:link، أو queue:restart.

النشر الكامل ينفذ بالترتيب:

```text
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear
php artisan storage:link
php artisan migrate --force
php artisan cache:clear
php artisan config:cache
php artisan view:cache
php artisan queue:restart
```

توقف أول خطأ يمنع الأوامر التالية. لا تستخدم الأداة migrate:fresh أو rollback أو seed، ولا تقبل أوامر PHP/Shell حرة. queue:restart يطلب إعادة تشغيل عمال موجودين؛ تشغيل العامل نفسه يحتاج إعداد الاستضافة/Supervisor.

يمكن تكرار كل خيار بنفس التوكن حتى بعد نجاحه، ولا تحتاج إلى تغيير التوكن بين مرات النشر. لا تُقرأ علامات التنفيذ القديمة ولا تمنع إعادة التشغيل. عند الفشل يمكن إعادة المحاولة بعد إصلاح السبب. لا يوجد تراجع تلقائي للمايجريشن التي اكتملت قبل الفشل. يمنع قفل الملفات في `storage/framework/deploy-once` تشغيل عمليتين معًا.

بعد انتهاء العمل:

```dotenv
DEPLOY_ONCE_ENABLED=false
```

تعطيل الأداة يعمل فورًا رغم كاش إعدادات Laravel؛ الأداة تقرأ إعداداتها من `.env` مباشرة. إذا كانت الاستضافة تضع DEPLOY_ONCE_ENABLED/TOKEN كمتغيرات نظام PHP، فهي تتقدم على `.env` ويجب تحديثها هناك. للنشر التالي فعّل الأداة من جديد، ويمكن إبقاء التوكن نفسه. مجلد storage ومسار جلسات PHP يجب أن يكونا قابلين للكتابة.

التحقق المحلي: PHP lint واختبارات مستقلة عن Laravel/قاعدة البيانات لمسارات السماح والمنع ووقف الأوامر عند الفشل. لم تُنفذ أي مايجريشن أو أوامر Artisan للنشر محليًا؛ نتيجة التشغيل الفعلية تظهر عند تنفيذها على السيرفر.
