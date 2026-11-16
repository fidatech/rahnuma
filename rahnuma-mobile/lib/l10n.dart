import 'package:flutter/material.dart';

/// English, Dari and Pashto for the whole app, without generated code.
///
///   Text('Sign in'.tr)                         the text in the chosen language
///   'Good day, :name'.trp({'name': 'Ahmad'})   with a value put in
///   L.label('bay_wafa')                        a stored value such as a deal type or a status
///
/// English is the key. A text without a translation is shown in English.
class L {
  static const names = {'en': 'English', 'fa': 'دری', 'ps': 'پښتو'};

  /// The chosen language. The app rebuilds when it changes (see main.dart).
  static final ValueNotifier<String> locale = ValueNotifier<String>('fa');

  static String get code => locale.value;
  static bool get isRtl => code == 'fa' || code == 'ps';
  static TextDirection get direction => isRtl ? TextDirection.rtl : TextDirection.ltr;

  /// Called when the person picks a language, to keep the choice on the phone (set in main.dart).
  static void Function(String code)? saver;

  static void set(String value) {
    if (names.containsKey(value)) locale.value = value;
  }

  /// The person picked a language in the top bar.
  static void choose(String value) {
    if (!names.containsKey(value)) return;
    locale.value = value;
    saver?.call(value);
  }

  static String t(String key) {
    if (code == 'en') return key;
    final row = _words[key];
    if (row == null) return key;
    return row[code == 'fa' ? 0 : 1];
  }

  /// A stored value (rent, bay_wafa, apartment, available, …) in the chosen language.
  static String label(String? value) {
    if (value == null || value.isEmpty) return '';
    if (value == 'bay_wafa') return t('Mortgage');
    final words = value.replaceAll('_', ' ').replaceAll('-', ' ');
    return t(words[0].toUpperCase() + words.substring(1));
  }

  // key: [Dari, Pashto]
  static const Map<String, List<String>> _words = {
    'Rahnuma': ['رهنما', 'رهنما'],
    'Rahnama': ['رهنما', 'رهنما'],
    'Language': ['زبان', 'ژبه'],
    'Sign in': ['ورود', 'ننوتل'],
    'Sign out': ['خروج', 'وتل'],
    'Signing in...': ['در حال ورود...', 'ننوځي...'],
    'Secure access': ['ورود مطمئن', 'خوندي ننوتل'],
    'Rahnama workspace': ['محیط کاری رهنما', 'د رهنما کاري چاپېریال'],
    'Sign in to your operations workspace': ['به محیط کاری خود وارد شوید', 'خپل کاري چاپېریال ته ننوځئ'],
    'Property, customers, transactions, finance and regulatory tools from one account.': ['ملکیت، مشتریان، معاملات، مالی و ابزار نظارت با یک حساب.', 'ملکیت، مشتریان، معاملې، مالي چارې او د څارنې وسایل په یوه حساب.'],
    'Email address': ['ایمیل آدرس', 'برېښنالیک'],
    'Password': ['رمز عبور', 'پټنوم'],
    'Afghanistan property network': ['شبکهٔ املاک افغانستان', 'د افغانستان د املاکو شبکه'],
    'Find the right property through local Rahnamas.': ['ملکیت مناسب را از طریق رهنماهای محلی پیدا کنید.', 'مناسب ملکیت د ځایي رهنماوو له لارې ومومئ.'],
    'Browse live property inventory by deal type and category.': ['ملکیت‌های فعال را بر اساس نوع معامله و نوع ملکیت ببینید.', 'فعال ملکیتونه د معاملې او ملکیت د ډول له مخې وګورئ.'],
    'Rent / Sale / Mortgage': ['کرایه / فروش / گروی', 'کرایه / پلور / ګروي'],
    'Rent': ['کرایه', 'کرایه'],
    'Sale': ['فروش', 'پلور'],
    'Mortgage': ['گروی', 'ګروي'],
    'Mortgage / Bay-e-Wafa': ['گروی / بیع وفا', 'ګروي / بیع وفا'],
    'Property type': ['نوع ملکیت', 'د ملکیت ډول'],
    'Fresh inventory': ['ملکیت‌های تازه', 'تازه ملکیتونه'],
    'Available properties': ['ملکیت‌های موجود', 'موجود ملکیتونه'],
    ':count listings': [':count اعلان', ':count اعلانونه'],
    'No matching properties found.': ['ملکیت مطابقی یافت نشد.', 'برابر ملکیت ونه موندل شو.'],
    'Property details': ['جزئیات ملکیت', 'د ملکیت جزئیات'],
    'Loading...': ['در حال بارگیری...', 'بارېږي...'],
    'Bedrooms': ['اتاق‌های خواب', 'د خوب خونې'],
    'Bathrooms': ['تشناب‌ها', 'تشنابونه'],
    'Area': ['مساحت', 'مساحت'],
    'Description': ['توضیحات', 'تشریح'],
    'No description available.': ['توضیحاتی موجود نیست.', 'تشریح نشته.'],
    'Listing Rahnama': ['رهنمای اعلان‌کننده', 'اعلان کوونکی رهنما'],
    'Add property': ['افزودن ملکیت', 'ملکیت اضافه کول'],
    'Operations overview': ['نمای کلی کارها', 'د کارونو عمومي کتنه'],
    'Good day, :name': ['روز بخیر، :name', 'ورځ مو پخیر، :name'],
    'Your property pipeline and office activity at a glance.': ['جریان ملکیت‌ها و فعالیت دفتر شما در یک نگاه.', 'ستاسو د ملکیتونو بهیر او د دفتر فعالیت په یوه نظر کې.'],
    'Properties': ['ملکیت‌ها', 'ملکیتونه'],
    'Total inventory': ['مجموع ملکیت‌ها', 'ټول ملکیتونه'],
    'Customers': ['مشتریان', 'مشتریان'],
    'CRM records': ['ریکاردهای مشتریان', 'د مشتریانو ریکارډونه'],
    'Active leads': ['مشتریان احتمالی فعال', 'فعال احتمالي مشتریان'],
    'Open opportunities': ['فرصت‌های باز', 'خلاص فرصتونه'],
    'Transactions': ['معاملات', 'معاملې'],
    'Deal register': ['دفتر معاملات', 'د معاملو دفتر'],
    'Workspace': ['محیط کاری', 'کاري چاپېریال'],
    'Inventory, pricing and marketplace': ['ملکیت‌ها، قیمت و بازار', 'ملکیتونه، قیمت او بازار'],
    'Buyers, tenants and requirements': ['خریداران، کرایه‌نشینان و نیازمندی‌ها', 'اخیستونکي، کرایه‌داران او اړتیاوې'],
    'Owners / Landlords': ['مالکان / صاحبان ملک', 'مالکان / د ملک خاوندان'],
    'Property ownership records': ['ریکاردهای مالکیت ملکیت', 'د ملکیت د مالکیت ریکارډونه'],
    'Leads': ['مشتریان احتمالی', 'احتمالي مشتریان'],
    'Sales pipeline and follow-ups': ['جریان فروش و پیگیری‌ها', 'د پلور بهیر او تعقیبونه'],
    'Visits': ['بازدیدها', 'لیدنې'],
    'Property viewings and outcomes': ['بازدیدهای ملکیت و نتایج', 'د ملکیت لیدنې او پایلې'],
    'Sale, rent and mortgage deals': ['معاملات فروش، کرایه و گروی', 'د پلور، کرایې او ګروۍ معاملې'],
    'Payments': ['پرداخت‌ها', 'ورکړې'],
    'Receipts and financial movements': ['رسیدها و حرکت‌های مالی', 'رسیدونه او مالي حرکتونه'],
    'Workspace register': ['دفتر ثبت محیط کاری', 'د کاري چاپېریال د ثبت دفتر'],
    ':count records': [':count ریکارد', ':count ریکارډونه'],
    'No records': ['ریکاردی نیست', 'ریکارډ نشته'],
    'Register property': ['ثبت ملکیت', 'ملکیت ثبتول'],
    'Property record': ['ریکارد ملکیت', 'د ملکیت ریکارډ'],
    'Add property inventory': ['افزودن ملکیت', 'ملکیت اضافه کول'],
    'Capture the essential commercial and location information now; complete advanced details from web when needed.': ['معلومات اصلی مالی و موقعیت را حالا ثبت کنید؛ جزئیات بیشتر را در وقت ضرورت از وب تکمیل کنید.', 'اصلي مالي او د ځای معلومات اوس ثبت کړئ؛ نور جزئیات د اړتیا پر مهال له وېب څخه بشپړ کړئ.'],
    'Core details': ['مشخصات اصلی', 'اصلي مشخصات'],
    'Listing title': ['عنوان اعلان', 'د اعلان سرلیک'],
    'Transaction': ['معامله', 'معامله'],
    'Commercial terms': ['شرایط مالی', 'مالي شرایط'],
    'Monthly rent': ['کرایهٔ ماهانه', 'میاشتنۍ کرایه'],
    'Amount': ['مبلغ', 'مبلغ'],
    'Currency': ['واحد پولی', 'اسعار'],
    'Visibility': ['نمایش', 'ښودنه'],
    'Location': ['موقعیت', 'ځای'],
    'Address': ['آدرس', 'پته'],
    'Saving...': ['در حال ثبت...', 'ثبتېږي...'],
    'Save property': ['ثبت ملکیت', 'ملکیت ثبتول'],
    'Save offline draft': ['ثبت پیش‌نویس آفلاین', 'آفلاین مسوده ثبتول'],
    'Draft saved offline. Financial and regulatory records are never queued offline.': ['پیش‌نویس به‌صورت آفلاین ثبت شد. ریکاردهای مالی و نظارتی هیچ‌وقت آفلاین نگه‌داری نمی‌شود.', 'مسوده آفلاین ثبت شوه. مالي او څارنیز ریکارډونه هېڅکله آفلاین نه ساتل کېږي.'],
    'Regulatory Workspace': ['بخش نظارت', 'د څارنې برخه'],
    'PRIVATE DEMONSTRATION MODE — this does not represent Ministry approval.': ['حالت نمایشی خصوصی — این به معنای تأیید وزارت نیست.', 'خصوصي نمایشي حالت — دا د وزارت د تایید معنا نه لري.'],
    'Regulatory intelligence': ['معلومات نظارتی', 'څارنیز معلومات'],
    'National oversight dashboard': ['داشبورد ملی نظارت', 'د څارنې ملي ډشبورډ'],
    'License, Setta, transaction and field-inspection visibility.': ['دید جواز، سته، معاملات و تفتیش ساحوی.', 'د جواز، سټې، معاملو او ساحوي پلټنو لید.'],
    'Agencies': ['دفاتر', 'دفترونه'],
    'Setta available': ['ستهٔ موجود', 'موجوده سټه'],
    'Inspections': ['تفتیش‌ها', 'پلټنې'],
    'Field supervision': ['نظارت ساحوی', 'ساحوي څارنه'],
    'Record inspection': ['ثبت تفتیش', 'پلټنه ثبتول'],
    'Capture the agency, findings and current GPS position.': ['دفتر، یافته‌ها و موقعیت فعلی GPS را ثبت کنید.', 'دفتر، موندنې او د GPS اوسنی ځای ثبت کړئ.'],
    'Agency ID': ['شمارهٔ دفتر', 'د دفتر شمېره'],
    'Inspection summary': ['خلاصهٔ تفتیش', 'د پلټنې لنډیز'],
    'Save inspection with GPS': ['ثبت تفتیش با GPS', 'پلټنه له GPS سره ثبتول'],
    'Inspection recorded with GPS.': ['تفتیش با GPS ثبت شد.', 'پلټنه له GPS سره ثبت شوه.'],
    'Role :role uses the web administration portal.': ['نقش :role از پورتال مدیریت وب استفاده می‌کند.', 'د :role رول د وېب د ادارې پورټال کاروي.'],
    // stored values
    'House': ['خانه', 'کور'],
    'Apartment': ['اپارتمان', 'اپارتمان'],
    'Shop': ['دکان', 'دوکان'],
    'Office': ['دفتر', 'دفتر'],
    'Land': ['زمین', 'ځمکه'],
    'Warehouse': ['گدام', 'ګودام'],
    'Building': ['تعمیر', 'ودانۍ'],
    'Factory': ['فابریکه', 'فابریکه'],
    'Garden': ['باغ', 'باغ'],
    'Hotel': ['هوتل', 'هوټل'],
    'Restaurant': ['رستورانت', 'رستورانت'],
    'Other': ['دیگر', 'نور'],
    'Private': ['خصوصی', 'خصوصي'],
    'Link': ['لینک', 'لینک'],
    'Public': ['عمومی', 'عمومي'],
    'Featured': ['ویژه', 'ځانګړی'],
    'Available': ['موجود', 'موجود'],
    'Active': ['فعال', 'فعال'],
    'Rented': ['کرایه داده شده', 'په کرایه ورکړل شوی'],
    'Sold': ['فروخته شده', 'پلورل شوی'],
    'Mortgaged': ['گروی شده', 'ګرو شوی'],
    'Inactive': ['غیرفعال', 'غیر فعال'],
    'Completed': ['تکمیل شده', 'بشپړ شوی'],
    'Draft': ['پیش‌نویس', 'مسوده'],
    'Cancelled': ['لغو شده', 'لغوه شوی'],
    'Pending': ['در انتظار', 'په تمه'],
    'Scheduled': ['تعیین شده', 'ټاکل شوی'],
    'New': ['جدید', 'نوی'],
    'Contacted': ['تماس گرفته شده', 'اړیکه نیول شوې'],
    'Negotiating': ['در حال مذاکره', 'د خبرو اترو په حال کې'],
    'Won': ['موفق', 'ګټل شوی'],
    'Lost': ['از دست رفته', 'له لاسه وتلی'],
  };
}

extension Tr on String {
  /// This text in the chosen language.
  String get tr => L.t(this);

  /// This text in the chosen language, with values put in place of :name marks.
  String trp(Map<String, Object?> values) {
    var out = L.t(this);
    values.forEach((key, value) => out = out.replaceAll(':$key', '$value'));
    return out;
  }
}

/// The language list of the top bar, as in the web workspace.
class LanguageButton extends StatelessWidget {
  const LanguageButton({super.key});

  @override
  Widget build(BuildContext context) => PopupMenuButton<String>(
        tooltip: 'Language'.tr,
        onSelected: L.choose,
        itemBuilder: (_) => L.names.entries
            .map((e) => PopupMenuItem<String>(
                  value: e.key,
                  child: Row(children: [
                    Icon(e.key == L.code ? Icons.radio_button_checked : Icons.radio_button_off, size: 18, color: e.key == L.code ? const Color(0xFF3A7BF0) : const Color(0xFF8A92A3)),
                    const SizedBox(width: 10),
                    Text(e.value),
                  ]),
                ))
            .toList(),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 10),
          child: Row(mainAxisSize: MainAxisSize.min, children: [
            const Icon(Icons.language_rounded, size: 20),
            const SizedBox(width: 5),
            Text(L.code.toUpperCase(), style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800)),
          ]),
        ),
      );
}
