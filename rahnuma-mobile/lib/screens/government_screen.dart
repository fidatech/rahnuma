import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import '../api.dart';
import '../config.dart';
import '../l10n.dart';
import '../models.dart';
import '../theme.dart';
import '../widgets.dart';

class GovernmentScreen extends StatefulWidget {
  final Api api;
  final SessionUser user;
  final VoidCallback onLogout;
  const GovernmentScreen({super.key, required this.api, required this.user, required this.onLogout});
  @override State<GovernmentScreen> createState() => _GovernmentScreenState();
}

class _GovernmentScreenState extends State<GovernmentScreen> {
  Map<String,dynamic> stats = {};
  final agency = TextEditingController(), summary = TextEditingController();
  String? msg;
  @override void initState() { super.initState(); load(); }
  Future<void> load() async { try { final d = await widget.api.get('/government/dashboard'); if (mounted) setState(() => stats = Map<String,dynamic>.from(d)); } catch (e) { if (mounted) setState(() => msg = '$e'); } }
  Future<void> inspect() async {
    try {
      LocationPermission p = await Geolocator.checkPermission();
      if (p == LocationPermission.denied) p = await Geolocator.requestPermission();
      final pos = await Geolocator.getCurrentPosition();
      await widget.api.post('/government/inspections', {'agency_id': int.parse(agency.text), 'latitude': pos.latitude, 'longitude': pos.longitude, 'status': 'completed', 'summary': summary.text});
      if (mounted) setState(() => msg = 'Inspection recorded with GPS.'.tr);
      load();
    } catch (e) { if (mounted) setState(() => msg = '$e'); }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: Text('Regulatory Workspace'.tr), actions: [const LanguageButton(), IconButton(onPressed: widget.onLogout, tooltip: 'Sign out'.tr, icon: const Icon(Icons.logout_rounded))]),
    body: ListView(padding: const EdgeInsets.fromLTRB(16, 18, 16, 32), children: [
      if (!AppConfig.governmentMode) ...[
        Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(color: RahnumaTheme.warning.withOpacity(.12), border: Border.all(color: RahnumaTheme.warning.withOpacity(.4)), borderRadius: BorderRadius.circular(12)),
          child: Row(children: [
            const Icon(Icons.warning_amber_rounded, color: Color(0xFFB76E00)),
            const SizedBox(width: 10),
            Expanded(child: Text('PRIVATE DEMONSTRATION MODE — this does not represent Ministry approval.'.tr, style: const TextStyle(color: Color(0xFF7F5411), fontSize: 11.5, fontWeight: FontWeight.w800))),
          ]),
        ),
        const SizedBox(height: 16),
      ],
      SectionLabel('Regulatory intelligence'.tr),
      const SizedBox(height: 5),
      Text('National oversight dashboard'.tr, style: const TextStyle(fontSize: 23, fontWeight: FontWeight.w800, color: RahnumaTheme.heading)),
      const SizedBox(height: 5),
      Text('License, Setta, transaction and field-inspection visibility.'.tr, style: const TextStyle(fontSize: 12, color: RahnumaTheme.muted)),
      const SizedBox(height: 16),
      GridView.count(
        crossAxisCount: 2,
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        mainAxisSpacing: 10,
        crossAxisSpacing: 10,
        childAspectRatio: 1.45,
        children: [
          StatCard(label: 'Agencies'.tr, value: stats['agencies'] ?? 0, primary: true, icon: Icons.business_rounded),
          StatCard(label: 'Setta available'.tr, value: stats['setta_available'] ?? 0, icon: Icons.menu_book_rounded, color: RahnumaTheme.purple),
          StatCard(label: 'Transactions'.tr, value: stats['transactions'] ?? 0, icon: Icons.handshake_rounded, color: RahnumaTheme.success),
          StatCard(label: 'Inspections'.tr, value: stats['inspections'] ?? stats['open_violations'] ?? 0, icon: Icons.fact_check_rounded, color: RahnumaTheme.warning),
        ],
      ),
      const SizedBox(height: 20),
      SoftCard(child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
        SectionLabel('Field supervision'.tr),
        const SizedBox(height: 6),
        Text('Record inspection'.tr, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 18, color: RahnumaTheme.heading)),
        const SizedBox(height: 5),
        Text('Capture the agency, findings and current GPS position.'.tr, style: const TextStyle(fontSize: 11.5, color: RahnumaTheme.muted)),
        const SizedBox(height: 14),
        TextField(controller: agency, keyboardType: TextInputType.number, textDirection: TextDirection.ltr, decoration: InputDecoration(labelText: 'Agency ID'.tr)),
        const SizedBox(height: 10),
        TextField(controller: summary, maxLines: 4, decoration: InputDecoration(labelText: 'Inspection summary'.tr)),
        const SizedBox(height: 12),
        FilledButton.icon(onPressed: inspect, icon: const Icon(Icons.location_on_outlined), label: Text('Save inspection with GPS'.tr)),
      ])),
      if (msg != null) Padding(padding: const EdgeInsets.all(12), child: Text(msg!, style: const TextStyle(color: RahnumaTheme.muted))),
    ]),
  );
}
