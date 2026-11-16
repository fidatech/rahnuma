import 'package:flutter/material.dart';
import '../api.dart';
import '../l10n.dart';
import '../theme.dart';
import '../widgets.dart';

class PropertyDetailScreen extends StatefulWidget {
  final Api api;
  final int id;
  const PropertyDetailScreen({super.key, required this.api, required this.id});
  @override State<PropertyDetailScreen> createState() => _PropertyDetailScreenState();
}

class _PropertyDetailScreenState extends State<PropertyDetailScreen> {
  Map<String,dynamic>? data;
  String? err;
  @override void initState() { super.initState(); widget.api.get('/properties/${widget.id}').then((v) { if (mounted) setState(() => data = Map<String,dynamic>.from(v)); }).catchError((e) { if (mounted) setState(() => err = '$e'); }); }

  String get amount {
    if (data == null) return '';
    final value = data!['transaction_type'] == 'rent' ? data!['monthly_rent'] : data!['price'];
    return '$value ${data!['currency'] ?? ''}';
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: Text('Property details'.tr)),
    body: data == null
      ? Center(child: Text(err ?? 'Loading...'.tr))
      : ListView(padding: const EdgeInsets.fromLTRB(16, 18, 16, 30), children: [
          Container(
            height: 220,
            decoration: BoxDecoration(borderRadius: BorderRadius.circular(16), gradient: const LinearGradient(colors: [RahnumaTheme.primarySoft, Color(0xFFF6F8FC)], begin: Alignment.topLeft, end: Alignment.bottomRight)),
            child: const Center(child: Icon(Icons.apartment_rounded, size: 62, color: RahnumaTheme.primary)),
          ),
          const SizedBox(height: 16),
          Row(children: [
            SoftBadge(data!['transaction_type'] == 'bay_wafa' ? 'Mortgage / Bay-e-Wafa'.tr : L.label('${data!['transaction_type'] ?? ''}')),
            const Spacer(),
            Text(L.label('${data!['property_type'] ?? ''}'), style: const TextStyle(color: RahnumaTheme.muted, fontSize: 12)),
          ]),
          const SizedBox(height: 11),
          Text(data!['title'] ?? '', style: const TextStyle(fontSize: 24, height: 1.3, fontWeight: FontWeight.w800, color: RahnumaTheme.heading)),
          const SizedBox(height: 7),
          Align(alignment: AlignmentDirectional.centerStart, child: Directionality(textDirection: TextDirection.ltr, child: Text(amount, style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800, color: RahnumaTheme.heading)))),
          const SizedBox(height: 5),
          Text(data!['address'] ?? '', style: const TextStyle(color: RahnumaTheme.muted)),
          const SizedBox(height: 18),
          Row(children: [
            Expanded(child: _Fact(label: 'Bedrooms'.tr, value: '${data!['bedrooms'] ?? '—'}')),
            const SizedBox(width: 8),
            Expanded(child: _Fact(label: 'Bathrooms'.tr, value: '${data!['bathrooms'] ?? '—'}')),
            const SizedBox(width: 8),
            Expanded(child: _Fact(label: 'Area'.tr, value: '${data!['total_area'] ?? '—'}')),
          ]),
          const SizedBox(height: 16),
          SoftCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [SectionLabel('Description'.tr), const SizedBox(height: 8), Text(data!['description'] ?? 'No description available.'.tr, style: const TextStyle(height: 1.6, color: RahnumaTheme.text))])),
          const SizedBox(height: 12),
          SoftCard(child: Row(children: [
            const SoftIcon(Icons.business_center_rounded),
            const SizedBox(width: 12),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              SectionLabel('Listing Rahnama'.tr),
              const SizedBox(height: 4),
              Text(data!['agency']?['name'] ?? 'Rahnama'.tr, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: RahnumaTheme.heading)),
              const SizedBox(height: 2),
              Directionality(textDirection: TextDirection.ltr, child: Text(data!['agency']?['phone'] ?? '', style: const TextStyle(color: RahnumaTheme.muted))),
            ])),
          ])),
        ]),
  );
}

class _Fact extends StatelessWidget {
  final String label, value;
  const _Fact({required this.label, required this.value});
  @override Widget build(BuildContext context) => Container(padding: const EdgeInsets.all(12), decoration: BoxDecoration(color: const Color(0xFFF6F8FC), borderRadius: BorderRadius.circular(10)), child: Column(children: [Text(label, style: const TextStyle(fontSize: 11, color: RahnumaTheme.muted)), const SizedBox(height: 3), Text(value, style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: RahnumaTheme.heading))]));
}
