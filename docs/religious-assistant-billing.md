# P12 — عقد المساعد والاشتراكات

الحساب ReligiousAccount وSanctum quran:sync مستقلان عن الموظفين. لا استعمال لجداول جمعية حق أو ربط صلاحية من boolean في Flutter. المفاتيح والحسابات والمنتجات مؤجلة بقرار المستخدم حتى نهاية التطوير؛ flags كلهاfalse وأسرارها فارغة، ولا تعديل .env أو تفعيل أو بيع أو نموذج مدفوع أو قاعدة فعلية.

## العقد

الأساس /api/v1/religious، والرد داخل data. التقييد40/دقيقة للباقة و8 تحقق إيصال و6 أسئلة بمفاتيح مستقلة.

| الطلب | السلوك |
|---|---|
| GET assistant/capabilities | عام؛ schema1/schema_ready/enabled والحدود وتهيئة كل متجر وروابط السياسات دون أسرار |
| GET billing/profile | خاص؛ UUID متجر عشوائي للحساب واستحقاق محدث من المتجر ورصيد الأسئلة |
| POST billing/verify | platform=android/ios وproof؛ تحقق قبل المنح، لا منتج أو سعر يحدده العميل |
| POST assistant/requests | UUID operation_id وسؤال حتى600؛ إيصال موجود لا يستدعي نموذجًا ثانية |
| GET assistant/requests/{uuid} | حالة الإيصال الخاصة بالمالك دون توليد |
| GET assistant/history |20عنصرًا تنازليًا وصفحات cursor؛ خاص بالمالك |
| DELETE assistant/history/{id} | يمسح السؤال والجواب والمصادر من العرض والتخزين دون رد الاستهلاك أو حذف الإيصال |
| POST billing/webhooks/google | PubSub OIDC موقّع/audience/email صحيح ثم إعادة تحقق من Google |
| POST billing/webhooks/apple | signedPayload وسلسلة Apple وES256 والبيئة/التطبيق، ثم إعادة تحقق من App Store |

مصدر migration 2026_10_26_180000_create_religious_assistant_and_billing بعد حساب P10. خمسة جداول دينية مستقلة: billing_accounts،subscriptions،assistant_budgets،assistant_requests،billing_events. السؤال والجواب وإثبات الشراء مشفرة بـCrypt؛ لا تطبع في السجلات أو الردود الإدارية. حساب المتجر UUID عشوائي لا بريد أو رقم مستخدم. الإيصالات متفردة للحساب وoperation_id، وإثباتات المتجر مجزأة للبحث ومشفرة للاستعادة. حذف الحساب cascade للخاص، دون حذف الميزانية الشهرية أو إعادة تكلفة صُرفت بالفعل.

## المساعد والمال

gpt-6-luna عبر HTTPS في Responses، بلا retry أو أدوات مدفوعة أو سجل محادثة متراكم. أقصى3000 input يُعدّ رسميًا قبل التوليد و600 output تشمل reasoning؛ effort=none وservice_tier=default وstore=false. explicit prompt caching دون breakpoints يمنع cache writes حسب الوثائق الحالية. مصدر الجواب الكتالوج المنشور، أعلى3نصوص كاملة محدودة الطول؛ لا نص ديني جديد أو مرجع غير موجود. JSON مقيد، والتحقق من المعرفات والاستخدام والنموذج قبل عرض الشرح؛ الرفض أو النتيجة غير المكتملة لا تصبح نجاحًا مختلقًا.

الحدود25 سؤالًا لكل شهر UTC و3 لكل يوم UTC. الحجز ذري بعد قفل الحساب والميزانية وقبل الشبكة؛ لا تبقى معاملة مفتوحة خلال HTTP. وحدة المالmicro-USD، ومعدل المصدر100000 input و500000 output لكل مليون رمز:600micro عند الحد. سقف20مليونmicro(20$)، منه2مليون هامش، أي18$ للحجوزات. لا تخفيض للحجز بسبب تسعير cache غير مثبت. المعرف نفسه مع نص مختلف409، ومع النص نفسه يعيد الإيصال دون توليد، حتى لو انتهى الاشتراك أو حُذف النص لاحقًا.

فشل عدّ الإدخال قبل إرسال التوليد يحرر الحجز ولا يستهلك حصة. فشل الاتصال بعد بدء التوليد يبقي الحجز/الحصة في uncertain، ولا retries تلقائية؛ worker غير المكتمل ينتقل من reserved إلى uncertain عند قراءة إيصال أقدم من دقيقتين. نجاح الاستخدام يحاسب التكلفة الفعلية مرة واحدة؛ فشل صيغة الجواب أو مصدره بعد توليد يُحاسب ولا يُعرض. اختلاف الاستخدام/الموديل يوقف الشهر مع إبقاء الحجز. التعطل أو تجاوز السقف يمنع بيع منتج جديد في capabilities أيضًا. هذه حدود مصدرية وفق السعر المعروف، وليست ضمانًا ضد تغيير تسعير المزود؛ التحقق من سعر وفاتورة حقيقية خطوة تفعيل.

## Google وApple

Google: OAuth لحساب خدمة بنطاق androidpublisher، ثم subscriptionsv2.get للحزمة الثابتة. المنتج يجب أن يطابق الإعدادات وobfuscatedExternalAccountId يساوي UUID الحساب. pending/on-hold/expired لا تمنح صلاحية؛ canceled يبقى سارياً فقط إلى انتهاء مدفوع موثق، وgrace بحسب استجابة المتجر. testPurchase مرفوض في الإنتاج افتراضيًا. acknowledgment خادمي بعد التحقق؛ linkedPurchaseToken يصبحREPLACED ولا يمكن إحياؤه بطلب قديم. إثبات مستعمل بحساب آخر403.

Apple: JWT ES256 ببيانات issuer/key/bundle خادمية، واستعلام App Store Server API للبيئة الصريحة. التحقق من bundle/environment/appAppleId في الإنتاج، ثم سلسلة x5c من3شهادات متصلة بجذر Apple المضمن، صلاحية السلسلة وOIDs الخاصة بالتوقيع ومنحنىP-256 وتوقيع ES256 وsignedDate. المنتج وappAccountToken وانتهاء الاشتراك/revocation/status كلها مطابقة. الجذر من Apple PKI؛ certificate DER SHA256=63343abfb89a6a03ebb57e9b3f5fa7be7c4f5c756f3017b3a8c488c3653e9179. التحقق الحالي offline للسلسلة في الوقت الحالي، ولا يدّعي OCSP حيًا؛ السلسلة المنتهية تُرفض حتى لو كان إيصالها تاريخيًا. يلزم فحص استعادة فعلية وتجديد الشهادات قبل تفعيلiOS.

webhooks لا تثق بنوع الحدث لمنح حق؛ بعد التوقيع تعيد قراءة الحالة من المتجر، وتتذكر معرّف الحدث بعد النجاح. فشل الخدمة يبقي الحدث قابلًا لإعادة التسليم. كذلك كل استخدام للمساعد يعيد فحص الاستحقاق؛ لا مدة cache تسمح لراية محلية قديمة بمنح استدعاء مدفوع. callbacks المتجر في Flutter تُختبر منفصلة عن webhook الخادمي.

## التحقق والتهيئة المؤجلة

tests/Feature/ReligiousAssistantTest.php يضيف17اختبارًا إلى المشغّل SQLite :memory: المعزول. تحقق RSA لـPubSub وES256/سلسلة ذات OIDs يجري فعليًا بمفاتيح اختبار وشبكة محاكية، ولا bypass توقيع يقبل JWT مفكوكًا. ملفات tests/Fixtures/apple-test-chain مفاتيح وشهادات اصطناعية للاختبار فقط؛ جذرها لا يُقبل في إعداد الإنتاج. التحقق من Apple/Google status والمراجعة والإلغاء/الانتهاء/الاسترداد والإيصال المتكرر والحصة والحجز والرد المفقود والملكية والسجل/حذفه/حذف الحساب كلها مغطاة. لا يثبت ذلك MySQL المتزامن أو شراء/مفتاح حقيقي أو استجابة نموذج فعلية.

متغيرات النهاية: RELIGIOUS_ASSISTANT_API_KEY وRELIGIOUS_ASSISTANT_ENABLED؛ RELIGIOUS_BILLING_TERMS_URL/PRIVACY_URL؛ ANDROID_ENABLED/ANDROID_PRODUCT وGOOGLE_CREDENTIALS/GOOGLE_NOTIFICATION_AUDIENCE/GOOGLE_NOTIFICATION_EMAIL بعد بادئةRELIGIOUS_BILLING_؛ IOS_ENABLED/IOS_PRODUCT وAPPLE_ISSUER/APPLE_KEY_ID/APPLE_KEY_PATH/APPLE_APP_ID/APPLE_ENVIRONMENT بالبادئة نفسها. ALLOW_SANDBOX خاص بـStaging فقط. الأسرار خارجpublic وGit وFlutter. ملفات JSON/P8 لا تُرسل في المحادثة. إعداد المنتجات شهريًا بسعر الأساس5$ والسعر النهائي من المتجر؛ لا إنشاء منتج أو فاتورة هنا.

يلزم قبل الإتاحة: Staging ونسخ بيانات وmigration المحدد، إعداد المتجر/المدفوعات والسياسات، اختبار SDK وSandbox والإلغاء/الاسترداد والاستعادة وربط حسابين وwebhooks، ومراجعة سعر الموديل وtoken counting وتقييم العربية مع مصادر حقيقية وميزانية تجربة صغيرة. Google OAuth والبريد ووسائط المجتمع وإعدادات المنصات الأخرى تُجمع في قائمة التفعيل النهائية، دون تعطيل التطوير الحالي.
