# P10 — عقد هوية الحساب الديني

الحساب الحالي ReligiousAccount وSanctum quran:sync مستقلان عن المستخدم الإداري. لا يُعاد استخدام staff provider أو جدول استرداده. register/login/profile/logout القديمة تبقى متوافقة؛ account/profile يزيد username وgoogle_linked وpassword_enabled، دون password أو google_subject.

المسارات تحت /api/v1/religious/account:

| الطلب | البيانات والنتيجة |
|---|---|
| GET capabilities | google وrecovery؛ كلاهما غير مفعّل دون إعداد |
| POST google | id_token، وusername عند إنشاء حساب جديد؛ تحقق خادمي قبل إصدار الجلسة |
| PATCH profile | مصادق؛ username مطلوب، name اختياري؛ لا تغيير بريد/أدوار |
| POST google/link | مصادق؛ id_token ببريد الحساب نفسه؛ لا ربط تلقائي عند تطابق البريد |
| POST forgot-password | email؛ رد عام، broker ديني مستقل، تقييد طلبات |
| POST reset-password | email، token، password، password_confirmation؛30دقيقة واستعمال واحد وإلغاء جلسات الحساب |
| DELETE المسار الأساسي | مصادق؛ confirmation=DELETE وكلمة المرور الحالية أو ID token حديث لحساب Google المرتبط |

اسم المستخدم يُطبّع lowercase، يبدأ بحرف إنجليزي،7–30 حرفًا/رقمًا/شرطة سفلية، ومتفرد بقاعدة البيانات. الحسابات القديمة لا تُحجب بانتظار إكماله. التحقق عبر google/auth1.55.2 وphpseclib3.0.57، لا فك JWT يدوي أو قبول user ID. رفض توقيع مزور أو aud/iss/exp غير صالح أو بريد غير موثق؛ شِباك HTTPS للشهادات محدد المهلة. إنشاء Google يحمل كلمة مرور عشوائية مع password_enabled=false. إضافة بريد مطابق لحساب قائم تتطلب دخوله وربطه أولًا.

migration المصدر الجديد يضيف username/google_subject/password_enabled وجدول religious_password_reset_tokens؛ لا تعديل للجداول الدينية الحالية أو تشغيل على قاعدة فعلية. حذف حساب يزيل بياناته بعلاقات cascade والرموز بواسطة علاقة morph، ويبقي الحسابات الأخرى. استعادة كلمة المرور تلغي الرموز الدينية فقط. تبقى personal/activity sync معطلة افتراضيًا وفق religious_modules؛ لا تفعيل تلقائي.

الإعدادات المصدرية: RELIGIOUS_GOOGLE_WEB_CLIENT_ID فارغ، وRELIGIOUS_ACCOUNT_RECOVERY_ENABLED=false. لا .env معدل، لا OAuth IDs مفترضة ولا أسرار. المستخدم أجّل الإعدادات الفعلية. لا Google حي أو بريد حقيقي أو نشر مثبت. تهيئة Flutter والمنصات وصيغة النسخ وعزلها وتراجع الاستعادة موثقة في docs/account-and-backup-implementation.md في C:/flutter_projects/haq_app.

المشغّل tools/test-religious.ps1 يضم ReligiousAccountIdentityTest: قاعدة SQLite :memory: فقط مع5 migrations محددة داخل الاختبار؛ لا migrate عام. اختبارات RSA تستخدم مفتاحًا مولدًا للاختبار وشهادات HTTP محاكية والتحقق الحقيقي للمكتبة، وليست claims مقبولة بلا توقيع. اختبارات الرسائل محاكية ولا تثبت SMTP. Composer أضاف6 حزم دون تغيير نسخة أي حزمة قديمة؛ ZIP فُعّل لأمر Composer وحده عبر -d extension=zip دون تعديل php.ini. فحص Composer audit وجد تنبيهات في16 حزمة قديمة، وليس في الحزم الست الجديدة؛ تحديثها عمل منفصل وبوابة تحقق قبل النشر.
