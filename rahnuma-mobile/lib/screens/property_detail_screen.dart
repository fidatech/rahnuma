import 'package:flutter/material.dart';
import '../api.dart';
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
    appBar: AppBar(title: const Text('Property details')),
    body: data == null
      ? Center(child: Text(err ?? 'Loading...'))
      : ListView(padding: const EdgeInsets.fromLTRB(16, 18, 16, 30), children: [
          Container(
            height: 230,
            decoration: BoxDecoration(borderRadius: BorderRadius.circular(22), gradient: const LinearGradient(colors: [Color(0xFFE7EBF6), Color(0xFFF8F9FC)])),
            child: Center(child: Icon(Icons.apartment_rounded, size: 62, color: RahnumaTheme.cobalt.withOpacity(.45))),
          ),
          const SizedBox(height: 18),
          Row(children: [
            Container(padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5), decoration: BoxDecoration(color: const Color(0xFFEEF2FF), borderRadius: BorderRadius.circular(99)), child: Text(data!['transaction_type'] == 'bay_wafa' ? 'MORTGAGE / بیع وفا' : '${data!['transaction_type']}'.toUpperCase(), style: const TextStyle(fontSize: 9, color: RahnumaTheme.cobaltDark, fontWeight: FontWeight.w900))),
            const Spacer(),
            Text('${data!['property_type'] ?? ''}', style: const TextStyle(color: RahnumaTheme.muted, fontSize: 11)),
          ]),
          const SizedBox(height: 11),
          Text(data!['title'] ?? '', style: const TextStyle(fontSize: 27, fontWeight: FontWeight.w900, letterSpacing: -.8, color: RahnumaTheme.ink)),
          const SizedBox(height: 7),
          Text(amount, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900, color: RahnumaTheme.ink)),
          const SizedBox(height: 5),
          Text(data!['address'] ?? '', style: const TextStyle(color: RahnumaTheme.muted)),
          const SizedBox(height: 18),
          Row(children: [
            Expanded(child: _Fact(label: 'Bedrooms', value: '${data!['bedrooms'] ?? '—'}')),
            const SizedBox(width: 8),
            Expanded(child: _Fact(label: 'Bathrooms', value: '${data!['bathrooms'] ?? '—'}')),
            const SizedBox(width: 8),
            Expanded(child: _Fact(label: 'Area', value: '${data!['total_area'] ?? '—'}')),
          ]),
          const SizedBox(height: 18),
          Card(child: Padding(padding: const EdgeInsets.all(17), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [const SectionLabel('Description'), const SizedBox(height: 8), Text(data!['description'] ?? 'No description available.', style: const TextStyle(height: 1.55, color: RahnumaTheme.ink2))]))),
          const SizedBox(height: 12),
          Card(child: Padding(padding: const EdgeInsets.all(17), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [const SectionLabel('Listing Rahnama'), const SizedBox(height: 9), Text(data!['agency']?['name'] ?? 'Rahnama', style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w900, color: RahnumaTheme.ink)), const SizedBox(height: 4), Text(data!['agency']?['phone'] ?? '', style: const TextStyle(color: RahnumaTheme.muted))]))),
        ]),
  );
}

class _Fact extends StatelessWidget {
  final String label, value;
  const _Fact({required this.label, required this.value});
  @override Widget build(BuildContext context) => Container(padding: const EdgeInsets.all(12), decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(14), border: Border.all(color: RahnumaTheme.border)), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(value, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: RahnumaTheme.ink)), const SizedBox(height: 3), Text(label, style: const TextStyle(fontSize: 9.5, color: RahnumaTheme.muted))]));
}
