<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

/**
 * Arabic Translations
 *
 * @since 1.0.0
 */

return [
    // Plugin Meta
    'SmartLink Manager' => 'مدير الروابط الذكية',
    '{name} plugin loaded' => 'تم تحميل إضافة {name}',

    // Element Names
    'Smart Link' => 'رابط ذكي',
    'smart link' => 'رابط ذكي',
    'smart links' => 'روابط ذكية',
    'New smart link' => 'رابط ذكي جديد',

    // Permissions
    'View smart links' => 'عرض الروابط الذكية',
    'Create smart links' => 'إنشاء روابط ذكية',
    'Edit smart links' => 'تعديل الروابط الذكية',
    'Delete smart links' => 'حذف الروابط الذكية',
    'View analytics' => 'عرض التحليلات',
    'Manage settings' => 'إدارة الإعدادات',

    // Navigation
    'Analytics' => 'التحليلات',
    'Settings' => 'الإعدادات',
    'General' => 'عام',
    'QR Code' => 'رمز الاستجابة السريعة',
    'Redirect' => 'إعادة التوجيه',
    'Export' => 'تصدير',
    'Advanced' => 'متقدم',
    'Interface' => 'الواجهة',

    // General Settings
    'Plugin Name' => 'اسم الإضافة',
    'The name of the plugin as it appears in the Control Panel menu' => 'اسم الإضافة كما يظهر في قائمة لوحة التحكم',
    'Plugin Settings' => 'إعدادات الإضافة',

    // Site Settings
    'Site Settings' => 'إعدادات الموقع',
    'Enabled Sites' => 'المواقع المُفعلة',
    'Select which sites SmartLink Manager should be enabled for. Leave empty to enable for all sites.' => 'اختر المواقع التي يجب تفعيل مدير الروابط الذكية فيها. اتركها فارغة للتفعيل في جميع المواقع.',

    // URL Settings
    'URL Settings' => 'إعدادات عنوان URL',
    'Smart Link URL Prefix' => 'بادئة عنوان URL للرابط الذكي',
    'QR Code URL Prefix' => 'بادئة عنوان URL لرمز QR',
    'The URL prefix for smart links (e.g., \'go\' creates /go/your-link)' => 'بادئة عنوان URL للروابط الذكية (مثلاً، \'go\' تنشئ /go/your-link)',
    'The URL prefix for QR code pages (e.g., \'qr\' creates /qr/your-link)' => 'بادئة عنوان URL لصفحات رمز QR (مثلاً، \'qr\' تنشئ /qr/your-link)',
    'Only letters, numbers, hyphens, and underscores are allowed.' => 'يُسمح فقط بالحروف والأرقام والشُرط والشُرط السفلية.',
    'This is being overridden by the <code>slugPrefix</code> setting in <code>config/smartlink-manager.php</code>. Clear routes cache after changing this.' => 'يتم تجاوز هذا الإعداد بواسطة إعداد <code>slugPrefix</code> في <code>config/smartlink-manager.php</code>. امسح ذاكرة التخزين المؤقت للمسارات بعد تغيير هذا.',
    'This is being overridden by the <code>qrPrefix</code> setting in <code>config/smartlink-manager.php</code>. Clear routes cache after changing this.' => 'يتم تجاوز هذا الإعداد بواسطة إعداد <code>qrPrefix</code> في <code>config/smartlink-manager.php</code>. امسح ذاكرة التخزين المؤقت للمسارات بعد تغيير هذا.',
    'Clear routes cache after changing this (php craft clear-caches/compiled-templates).' => 'امسح ذاكرة التخزين المؤقت للمسارات بعد تغيير هذا (php craft clear-caches/compiled-templates).',

    // Smart Link Fields
    'Title' => 'العنوان',
    'The title of this smart link' => 'عنوان هذا الرابط الذكي',
    'Description' => 'الوصف',
    'A brief description of this smart link' => 'وصف موجز لهذا الرابط الذكي',
    'Icon' => 'الأيقونة',
    'Icon identifier or URL for this smart link' => 'معرف الأيقونة أو رابطها لهذا الرابط الذكي',

    // Image Settings
    'Image' => 'الصورة',
    'Select an image for this smart link' => 'اختر صورة لهذا الرابط الذكي',
    'Image Size' => 'حجم الصورة',
    'Select the size for the smart link image' => 'اختر حجم صورة الرابط الذكي',
    'Extra Large (2048px)' => 'كبير جداً (2048 بكسل)',
    'Large (1024px)' => 'كبير (1024 بكسل)',
    'Medium (512px)' => 'متوسط (512 بكسل)',
    'Small (256px)' => 'صغير (256 بكسل)',
    'Hide Title on Landing Pages' => 'إخفاء العنوان في صفحات الهبوط',
    'Hide the smart link title on both redirect and QR code landing pages' => 'إخفاء عنوان الرابط الذكي في صفحات إعادة التوجيه ورمز الاستجابة السريعة',

    // URL Fields
    'Destination URL' => 'رابط الوجهة',
    'Last Destination URL' => 'آخر رابط وجهة',
    'Fallback URL' => 'الرابط الاحتياطي',
    'The URL to redirect to when no platform-specific URL is available' => 'الرابط المستخدم لإعادة التوجيه عندما لا يتوفر رابط خاص بالمنصة',
    'iOS URL' => 'رابط iOS',
    'App Store URL for iOS devices' => 'رابط متجر التطبيقات لأجهزة iOS',
    'Android URL' => 'رابط أندرويد',
    'Google Play Store URL for Android devices' => 'رابط متجر جوجل بلاي لأجهزة أندرويد',
    'Huawei URL' => 'رابط هواوي',
    'AppGallery URL for Huawei devices' => 'رابط AppGallery لأجهزة هواوي',
    'Amazon URL' => 'رابط أمازون',
    'Amazon Appstore URL' => 'رابط متجر أمازون للتطبيقات',
    'Windows URL' => 'رابط ويندوز',
    'Microsoft Store URL for Windows devices' => 'رابط متجر مايكروسوفت لأجهزة ويندوز',
    'Mac URL' => 'رابط Mac',
    'Mac App Store URL' => 'رابط متجر تطبيقات Mac',
    'App Store URLs' => 'روابط متاجر التطبيقات',
    'Enter the store URLs for each platform. The system will automatically redirect users to the appropriate store based on their device.' => 'أدخل روابط المتاجر لكل منصة. سيقوم النظام تلقائياً بتوجيه المستخدمين إلى المتجر المناسب حسب أجهزتهم.',

    // Display Settings
    'Display Settings' => 'إعدادات العرض',

    // QR Code Settings
    'QR Code Settings' => 'إعدادات رمز الاستجابة السريعة',
    'Enable QR Code' => 'تفعيل رمز الاستجابة السريعة',
    'Default QR Code Size' => 'الحجم الافتراضي لرمز الاستجابة السريعة',
    'Default size in pixels for generated QR codes' => 'الحجم الافتراضي بالبكسل لرموز الاستجابة السريعة المُنشأة',
    'Default QR Code Color' => 'اللون الافتراضي لرمز الاستجابة السريعة',
    'Color' => 'اللون',
    'Default QR Background Color' => 'لون الخلفية الافتراضي',
    'Background' => 'الخلفية',
    'Background Color' => 'لون الخلفية',
    'Default QR Code Format' => 'الصيغة الافتراضية لرمز الاستجابة السريعة',
    'Default format for generated QR codes' => 'الصيغة الافتراضية لرموز الاستجابة السريعة المُنشأة',
    'Override the default QR code format' => 'تجاوز الصيغة الافتراضية لرمز الاستجابة السريعة',
    'Format' => 'الصيغة',
    'Use Default ({format|upper})' => 'استخدم الافتراضي ({format|upper})',
    'QR Code Cache Duration (seconds)' => 'مدة التخزين المؤقت لرمز الاستجابة السريعة (بالثواني)',
    'How long to cache generated QR codes (in seconds)' => 'مدة تخزين رموز الاستجابة السريعة المُنشأة (بالثواني)',
    'Cache duration in seconds' => 'مدة التخزين المؤقت بالثواني',
    'Caching' => 'التخزين المؤقت',

    // QR Code Technical Options
    'Technical Options' => 'خيارات تقنية',
    'Error Correction Level' => 'مستوى تصحيح الأخطاء',
    'Higher levels work better if QR code is damaged but create denser patterns' => 'المستويات الأعلى تعمل بشكل أفضل إذا تضرر الرمز لكنها تنشئ أنماط أكثر كثافة',
    'QR Code Margin' => 'هامش رمز الاستجابة السريعة',
    'Margin Size' => 'حجم الهامش',
    'White space around QR code (0-10 modules)' => 'المسافة البيضاء حول الرمز (0-10 وحدات)',
    'Module Style' => 'نمط الوحدة',
    'Shape of the QR code modules' => 'شكل وحدات رمز الاستجابة السريعة',
    'Eye Style' => 'نمط العين',
    'Shape of the position markers (corners)' => 'شكل علامات الموضع (الزوايا)',
    'Eye Color' => 'لون العين',
    'Color for position markers (leave empty to use main color)' => 'لون علامات الموضع (اتركه فارغاً لاستخدام اللون الرئيسي)',

    // QR Code Appearance
    'Appearance & Style' => 'المظهر والنمط',

    // QR Code Logo Settings
    'Logo Settings' => 'إعدادات الشعار',
    'Enable QR Code Logo' => 'تفعيل شعار رمز الاستجابة السريعة',
    'Enable Logo Overlay' => 'تفعيل طبقة الشعار',
    'Add a logo in the center of QR codes' => 'إضافة شعار في وسط رموز الاستجابة السريعة',
    'Logo Volume' => 'مجلد الشعارات',
    'Logo Asset Volume' => 'مجلد ملفات الشعارات',
    'Which asset volume contains QR code logos. Save settings after changing this to update the logo selection below.' => 'أي مجلد ملفات يحتوي على شعارات رموز الاستجابة السريعة. احفظ الإعدادات بعد تغيير هذا لتحديث اختيار الشعار أدناه.',
    'Default Logo' => 'الشعار الافتراضي',
    'Default logo to use for QR codes (can be overridden per smart link)' => 'الشعار الافتراضي لرموز الاستجابة السريعة (يمكن تجاوزه لكل رابط ذكي)',
    'Default logo is required when logo overlay is enabled.' => 'الشعار الافتراضي مطلوب عند تفعيل طبقة الشعار.',
    'Logo Size (%)' => 'حجم الشعار (%)',
    'Logo Size' => 'حجم الشعار',
    'Logo size as percentage of QR code (10-30%)' => 'حجم الشعار كنسبة مئوية من رمز الاستجابة السريعة (10-30%)',
    'Logo' => 'الشعار',
    'Logo overlay only works with PNG format. SVG format does not support logos.' => 'طبقة الشعار تعمل فقط مع صيغة PNG. صيغة SVG لا تدعم الشعارات.',
    'Logo requires PNG format' => 'الشعار يتطلب صيغة PNG',
    'Using default logo from settings (click to override)' => 'استخدام الشعار الافتراضي من الإعدادات (اضغط للتجاوز)',

    // QR Code Download Settings
    'Download Settings' => 'إعدادات التحميل',
    'Enable QR Code Downloads' => 'تفعيل تحميل رموز الاستجابة السريعة',
    'Allow users to download QR codes' => 'السماح للمستخدمين بتحميل رموز الاستجابة السريعة',
    'Download Filename Pattern' => 'نمط اسم ملف التحميل',
    'Available variables: {slug}, {size}, {format}' => 'المتغيرات المتاحة: {slug}, {size}, {format}',
    'Download QR Code' => 'تحميل رمز الاستجابة السريعة',

    // QR Code Actions
    'QR Code Actions' => 'إجراءات رمز الاستجابة السريعة',
    'Reset to Defaults' => 'إعادة تعيين للافتراضي',
    'Reset QR code settings to plugin defaults?' => 'إعادة تعيين إعدادات رمز الاستجابة السريعة إلى الافتراضية؟',
    'QR code settings reset to defaults' => 'تم إعادة تعيين إعدادات رمز الاستجابة السريعة إلى الافتراضية',
    'Live Preview' => 'معاينة مباشرة',
    'Preview' => 'معاينة',
    'Click to view QR code page' => 'اضغط لعرض صفحة رمز الاستجابة السريعة',
    'Toggle preview' => 'تبديل المعاينة',
    'Please save to apply the volume change' => 'يرجى الحفظ لتطبيق تغيير المجلد',
    'Size' => 'الحجم',
    'Custom Size...' => 'حجم مخصص...',
    'Enter custom size (100-4096 pixels):' => 'أدخل حجم مخصص (100-4096 بكسل):',
    'Please enter a valid size between 100 and 4096 pixels' => 'يرجى إدخال حجم صالح بين 100 و 4096 بكسل',

    // Asset Settings
    'Asset Settings' => 'إعدادات الملفات',
    'Image Volume' => 'مجلد الصور',
    'Smart Link Image Volume' => 'مجلد صور الروابط الذكية',
    'Which asset volume should be used for SmartLink Manager images' => 'أي مجلد ملفات يجب استخدامه لصور مدير الروابط الذكية',
    'All asset volumes' => 'جميع مجلدات الملفات',

    // Analytics Settings
    'Analytics Settings' => 'إعدادات التحليلات',
    'Enable Analytics' => 'تفعيل التحليلات',
    'Track Analytics' => 'تتبع التحليلات',
    'Track clicks and visitor data for smart links' => 'تتبع النقرات وبيانات الزوار للروابط الذكية',
    'When enabled, SmartLink Manager will track visitor interactions, device types, geographic data, and other analytics information.' => 'عند التفعيل، سيتتبع مدير الروابط الذكية تفاعلات الزوار وأنواع الأجهزة والبيانات الجغرافية ومعلومات تحليلية أخرى.',
    'Are you sure you want to disable analytics tracking for this smart link? This smart link will no longer collect visitor data and interactions.' => 'هل أنت متأكد من تعطيل تتبع التحليلات لهذا الرابط الذكي؟ لن يعود هذا الرابط يجمع بيانات الزوار والتفاعلات.',
    'Analytics Retention (days)' => 'الاحتفاظ بالتحليلات (بالأيام)',
    'Analytics Retention' => 'الاحتفاظ بالتحليلات',
    'How many days to keep analytics data (0 for unlimited, max 3650)' => 'عدد الأيام للاحتفاظ بالتحليلات (0 لغير محدود، أقصى 3650)',
    'Data Retention' => 'الاحتفاظ بالبيانات',
    'Analytics Cleanup' => 'تنظيف التحليلات',
    'Clean Up Now' => 'تنظيف الآن',
    'Are you sure you want to clean up old analytics data now?' => 'هل أنت متأكد من تنظيف بيانات التحليلات القديمة الآن؟',
    'Analytics cleanup job queued' => 'تمت جدولة مهمة تنظيف التحليلات',
    'Failed to queue cleanup job' => 'فشلت جدولة مهمة التنظيف',
    'Scheduled initial analytics cleanup job to run in 5 minutes' => 'تمت جدولة مهمة التنظيف الأولية للتشغيل خلال 5 دقائق',
    'Analytics cleanup job already scheduled, skipping' => 'مهمة تنظيف التحليلات مجدولة بالفعل، يتم التخطي',
    'Analytics cleanup settings updated' => 'تم تحديث إعدادات تنظيف التحليلات',
    'Unlimited Retention Warning' => 'تحذير الاحتفاظ غير المحدود',
    'Analytics data will be retained indefinitely. This could result in large database size, slower performance, and increased storage costs over time. Consider setting a retention period (recommended: 90-365 days) for production sites.' => 'سيتم الاحتفاظ بالتحليلات إلى أجل غير مسمى. قد يؤدي هذا إلى حجم قاعدة بيانات كبير وأداء أبطأ وزيادة تكاليف التخزين بمرور الوقت. فكر في تحديد فترة احتفاظ (مستحسن: 90-365 يوماً) لمواقع الإنتاج.',

    // Geographic Detection
    'Enable Geographic Detection' => 'تفعيل الكشف الجغرافي',
    'Detect user location for analytics' => 'كشف موقع المستخدم للتحليلات',
    'Geographic Detection' => 'الكشف الجغرافي',
    'Geographic Analytics' => 'التحليلات الجغرافية',
    'Geographic Distribution' => 'التوزيع الجغرافي',
    'View Geographic Details' => 'عرض التفاصيل الجغرافية',
    'Loading geographic data...' => 'جاري تحميل البيانات الجغرافية...',

    // Device Detection
    'Cache Device Detection' => 'تخزين كشف الجهاز مؤقتاً',
    'Cache device detection results for better performance' => 'تخزين نتائج كشف الجهاز مؤقتاً لأداء أفضل',
    'Device Detection Cache Duration (seconds)' => 'مدة تخزين كشف الجهاز المؤقت (بالثواني)',

    // Language Detection
    'Language Detection Method' => 'طريقة كشف اللغة',
    'How to detect user language preference' => 'كيفية كشف تفضيل لغة المستخدم',
    'Language Detection' => 'كشف اللغة',
    'Enable automatic language detection to redirect users based on their browser or location' => 'تفعيل الكشف التلقائي للغة لإعادة توجيه المستخدمين بناءً على المتصفح أو الموقع',

    // Analytics Export
    'Analytics Export Options' => 'خيارات تصدير التحليلات',
    'Export Settings' => 'إعدادات التصدير',
    'Include Disabled Links in Export' => 'تضمين الروابط المعطلة في التصدير',
    'Include Disabled SmartLinks in Export' => 'تضمين الروابط الذكية المعطلة في التصدير',
    'When enabled, analytics exports will include data from disabled smart links' => 'عند التفعيل، ستتضمن صادرات التحليلات بيانات من الروابط الذكية المعطلة',
    'Include Expired Links in Export' => 'تضمين الروابط المنتهية في التصدير',
    'Include Expired SmartLinks in Export' => 'تضمين الروابط الذكية المنتهية في التصدير',
    'When enabled, analytics exports will include data from expired smart links' => 'عند التفعيل، ستتضمن صادرات التحليلات بيانات من الروابط الذكية المنتهية',
    'Export as CSV' => 'تصدير كملف CSV',

    // Redirect Settings
    'Custom Redirect Template' => 'قالب إعادة توجيه مخصص',
    'Path to custom template in your templates/ folder (e.g., smartlink-manager/redirect)' => 'مسار القالب المخصص في مجلد templates/ (مثال: smartlink-manager/redirect)',
    'Custom QR Code Template' => 'قالب رمز الاستجابة السريعة المخصص',
    'Path to custom template in your templates/ folder (e.g., smartlink-manager/qr)' => 'مسار القالب المخصص في مجلد templates/ (مثال: smartlink-manager/qr)',
    'Redirect Settings' => 'إعدادات إعادة التوجيه',
    'Redirect Behavior' => 'سلوك إعادة التوجيه',
    '404 Redirect URL' => 'رابط إعادة توجيه 404',
    'Where to redirect when a smart link is not found or disabled' => 'إلى أين يتم التوجيه عندما لا يُعثر على الرابط الذكي أو يكون معطلاً',
    'Can be a relative path (/) or full URL (https://example.com)' => 'يمكن أن يكون مساراً نسبياً (/) أو رابط كامل (https://example.com)',

    // Interface Settings
    'Interface Settings' => 'إعدادات الواجهة',
    'Items Per Page' => 'العناصر في الصفحة',
    'Number of smart links to show per page' => 'عدد الروابط الذكية المعروضة في كل صفحة',
    'Allow Multiple' => 'السماح بالمتعدد',
    'Whether to allow multiple smart links to be selected' => 'ما إذا كان يسمح باختيار عدة روابط ذكية',

    // Advanced Settings
    'Advanced Settings' => 'إعدادات متقدمة',

    // Analytics Dashboard
    'SmartLink Manager Overview' => 'نظرة عامة على مدير الروابط الذكية',
    'View Analytics' => 'عرض التحليلات',
    'Traffic Overview' => 'نظرة عامة على الزيارات',
    'Total Links' => 'إجمالي الروابط',
    'Active Links' => 'الروابط النشطة',
    'Total Clicks' => 'إجمالي النقرات',
    'total clicks' => 'إجمالي النقرات',
    'Clicks' => 'النقرات',
    'Unique Visitors' => 'الزوار الفريدون',
    'Top SmartLinks' => 'أفضل الروابط الذكية',
    'Top Performing Links (Last 7 Days)' => 'الروابط الأكثر أداءً (آخر 7 أيام)',
    'Top Countries' => 'أفضل الدول',
    'Top Cities' => 'أفضل المدن',
    'Top Cities Worldwide' => 'أفضل المدن عالمياً',
    'Device Breakdown' => 'توزيع الأجهزة',
    'Device Types' => 'أنواع الأجهزة',
    'Device Brands' => 'ماركات الأجهزة',
    'Operating Systems' => 'أنظمة التشغيل',
    'Browser Usage' => 'استخدام المتصفح',
    'Daily Clicks' => 'النقرات اليومية',
    'Usage Patterns' => 'أنماط الاستخدام',
    'Peak Usage Hours' => 'ساعات الذروة',
    'Peak usage at {hour}' => 'ذروة الاستخدام عند {hour}',
    'Avg. Clicks/Day' => 'متوسط النقرات/يوم',
    'Engagement Rate' => 'معدل التفاعل',
    'No analytics data yet' => 'لا توجد بيانات تحليلية بعد',
    'Analytics will appear here once your smart link starts receiving clicks.' => 'ستظهر التحليلات هنا بمجرد أن يبدأ رابطك الذكي في تلقي النقرات.',
    'Failed to load analytics data' => 'فشل تحميل بيانات التحليلات',
    'Failed to load countries data' => 'فشل تحميل بيانات الدول',
    'No data for selected period' => 'لا توجد بيانات للفترة المحددة',
    'No country data available' => 'لا توجد بيانات دول متاحة',
    'No city data available' => 'لا توجد بيانات مدن متاحة',

    // Time Periods
    'Today' => 'اليوم',
    'Yesterday' => 'أمس',
    'Last 7 days' => 'آخر 7 أيام',
    'Last 30 days' => 'آخر 30 يوماً',
    'Last 90 days' => 'آخر 90 يوماً',
    'All time' => 'كل الوقت',

    // Analytics Data
    'Date' => 'التاريخ',
    'Device' => 'الجهاز',
    'Location' => 'الموقع',
    'Country' => 'البلد',
    'Countries' => 'الدول',
    'City' => 'المدينة',
    'Site' => 'الموقع',
    'Source' => 'المصدر',
    'Type' => 'النوع',
    'OS' => 'نظام التشغيل',
    'Operating System' => 'نظام التشغيل',
    'Device Analytics' => 'تحليلات الأجهزة',
    'Interactions' => 'التفاعلات',
    'Total Interactions' => 'إجمالي التفاعلات',
    'Latest Interactions' => 'آخر التفاعلات',
    'No interactions recorded yet' => 'لم يتم تسجيل تفاعلات بعد',
    'Last Interaction' => 'آخر تفاعل',
    'Last Interaction Type' => 'نوع آخر تفاعل',
    'Last Click' => 'آخر نقرة',
    'Device information not available' => 'معلومات الجهاز غير متاحة',
    'OS information not available' => 'معلومات نظام التشغيل غير متاحة',

    // Interaction Types
    'Direct' => 'مباشر',
    'Direct Visits' => 'زيارات مباشرة',
    'QR' => 'رمز استجابة سريعة',
    'QR Scans' => 'مسح رمز الاستجابة السريعة',
    'Button' => 'زر',
    'Landing' => 'صفحة هبوط',

    // Actions
    'Actions' => 'الإجراءات',
    'Save Settings' => 'حفظ الإعدادات',
    'Manage SmartLinks' => 'إدارة الروابط الذكية',

    // Messages
    'Loading...' => 'جاري التحميل...',
    'Error' => 'خطأ',
    'Name' => 'الاسم',
    'Percentage' => 'النسبة المئوية',

    // Dynamic Plugin Name Strings (with parameters) - Arabic translations
    'Integrate {pluginName} with third-party analytics and tracking services to push click events to Google Tag Manager, Google Analytics, and other platforms.' => 'دمج {pluginName} مع خدمات التحليلات والتتبع من طرف ثالث لإرسال أحداث النقرات إلى Google Tag Manager و Google Analytics ومنصات أخرى.',
    'Push {pluginName} events to SEOmatic\'s Google Tag Manager data layer for tracking in GTM and Google Analytics.' => 'إرسال أحداث {pluginName} إلى طبقة بيانات Google Tag Manager في SEOmatic للتتبع في GTM و Google Analytics.',
    'Scripts receiving {pluginName} events' => 'السكريبتات التي تستقبل أحداث {pluginName}',
    'Select which {pluginName} events to send to SEOmatic' => 'اختر أحداث {pluginName} التي سيتم إرسالها إلى SEOmatic',
    'Are you sure you want to clear all {pluginName} caches?' => 'هل أنت متأكد من مسح جميع ذاكرة التخزين المؤقت لـ {pluginName}؟',

    // Utilities
    'Monitor link performance, track analytics, and manage cache for your {singularName} redirects and QR codes.' => 'راقب أداء الروابط وتتبع التحليلات وإدارة ذاكرة التخزين المؤقت لإعادة توجيه {singularName} ورموز QR.',
    'Active {pluginName}' => '{pluginName} النشطة',

    // Smart Link Fields
    'The title of this {singularName}' => 'عنوان {singularName}',
    'A brief description of this {singularName}' => 'وصف موجز لـ {singularName}',
    'Select an image for this {singularName}' => 'اختر صورة لـ {singularName}',
    'Select the size for the {singularName} image' => 'اختر حجم صورة {singularName}',
    'Hide the {singularName} title on both redirect and QR code landing pages' => 'إخفاء عنوان {singularName} في صفحات إعادة التوجيه ورمز QR',
    'Icon identifier or URL for this {singularName}' => 'معرف الأيقونة أو رابطها لـ {singularName}',

    // Field Layout
    'Add custom fields to {singularName} elements. Any fields you add here will appear in the {singularName} edit screen.' => 'إضافة حقول مخصصة إلى عناصر {singularName}. أي حقول تضيفها هنا ستظهر في شاشة تحرير {singularName}.',

    // Analytics Settings
    'Track clicks and visitor data for {pluginName}' => 'تتبع النقرات وبيانات الزوار لـ {pluginName}',
    'When enabled, {pluginName} will track visitor interactions, device types, geographic data, and other analytics information.' => 'عند التفعيل، ستتتبع {pluginName} تفاعلات الزوار وأنواع الأجهزة والبيانات الجغرافية ومعلومات تحليلية أخرى.',

    // Export Settings
    'Include Disabled {pluginName} in Export' => 'تضمين {pluginName} المعطلة في التصدير',
    'When enabled, analytics exports will include data from disabled {pluginName}' => 'عند التفعيل، ستتضمن صادرات التحليلات بيانات من {pluginName} المعطلة',
    'Include Expired {pluginName} in Export' => 'تضمين {pluginName} المنتهية في التصدير',
    'When enabled, analytics exports will include data from expired {pluginName}' => 'عند التفعيل، ستتضمن صادرات التحليلات بيانات من {pluginName} المنتهية',

    // Redirect Settings
    'Where to redirect when a {singularName} is not found or disabled' => 'إلى أين يتم التوجيه عندما لا يُعثر على {singularName} أو يكون معطلاً',

    // General Settings
    '{singularName} URL Prefix' => 'بادئة عنوان URL لـ {singularName}',
    'The URL prefix for {pluginName} (e.g., \'go\' creates /go/your-link). Clear routes cache after changing (php craft clear-caches/compiled-templates).' => 'بادئة عنوان URL لـ {pluginName} (مثلاً، \'go\' تنشئ /go/your-link). امسح ذاكرة التخزين المؤقت للمسارات بعد التغيير (php craft clear-caches/compiled-templates).',
    'Select which sites {pluginName} should be enabled for. Leave empty to enable for all sites.' => 'اختر المواقع التي يجب تفعيل {pluginName} فيها. اتركها فارغة للتفعيل في جميع المواقع.',
    '{singularName} Image Volume' => 'مجلد صور {singularName}',
    'Which asset volume should be used for {singularName} images' => 'أي مجلد ملفات يجب استخدامه لصور {singularName}',

    // Interface Settings
    'Number of {pluginName} to show per page' => 'عدد {pluginName} المعروضة في كل صفحة',

    // Integration Settings
    '{pluginName} pushes events to GTM or GA4 dataLayer only' => '{pluginName} ترسل الأحداث فقط إلى طبقة بيانات GTM أو GA4',
    'Configure GTM triggers and tags to forward {pluginName} events to Facebook Pixel, LinkedIn, HubSpot, etc.' => 'قم بتكوين مشغلات وعلامات GTM لإعادة توجيه أحداث {pluginName} إلى Facebook Pixel و LinkedIn و HubSpot وما إلى ذلك.',
    'Fathom, Matomo, and Plausible are shown above but do not receive events directly from {pluginName}' => 'يتم عرض Fathom و Matomo و Plausible أعلاه ولكنها لا تستقبل الأحداث مباشرة من {pluginName}',

    // Config Override Warnings
    'This is being overridden by the <code>pluginName</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>pluginName</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableAnalytics</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>enableAnalytics</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>analyticsRetention</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>analyticsRetention</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>includeDisabledInExport</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>includeDisabledInExport</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>includeExpiredInExport</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>includeExpiredInExport</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrSize</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>defaultQrSize</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrColor</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>defaultQrColor</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrBgColor</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>defaultQrBgColor</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrFormat</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>defaultQrFormat</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrCodeCacheDuration</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>qrCodeCacheDuration</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrErrorCorrection</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>defaultQrErrorCorrection</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrMargin</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>defaultQrMargin</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrModuleStyle</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>qrModuleStyle</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrEyeStyle</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>qrEyeStyle</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrEyeColor</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>qrEyeColor</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableQrLogo</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>enableQrLogo</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrLogoVolumeUid</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>qrLogoVolumeUid</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>imageVolumeUid</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>imageVolumeUid</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrLogoSize</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>qrLogoSize</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableQrDownload</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>enableQrDownload</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrDownloadFilename</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>qrDownloadFilename</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>redirectTemplate</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>redirectTemplate</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrTemplate</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>qrTemplate</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableGeoDetection</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>enableGeoDetection</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>cacheDeviceDetection</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>cacheDeviceDetection</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>deviceDetectionCacheDuration</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>deviceDetectionCacheDuration</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>languageDetectionMethod</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>languageDetectionMethod</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>itemsPerPage</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>itemsPerPage</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>notFoundRedirectUrl</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>notFoundRedirectUrl</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enabledSites</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>enabledSites</code> في <code>config/smartlink-manager.php</code>.',
];
