import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import '../api.dart';
import '../config.dart';
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
  Future<void> load() async { try { final d = await widget.api.get('/government/dashboard'); if (mounted) setState(() => stats = Map<String,dynamic>.from(d)); } catch (e) { setState(() => msg = '$e'); } }
  Future<void> inspect() async {
    try {
      LocationPermission p = await Geolocator.checkPermission();
      if (p == LocationPermission.denied) p = await Geolocator.requestPermission();
      final pos = await Geolocator.getCurrentPosition();
      await widget.api.post('/government/inspections', {'agency_id': int.parse(agency.text), 'latitude': pos.latitude, 'longitude': pos.longitude, 'status': 'completed', 'summary': summary.text});
      setState(() => msg = 'Inspection recorded with GPS.');
      load();
    } catch (e) { setState(() => msg = '$e'); }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: const Text('Regulatory Workspace'), actions: [IconButton(onPressed: widget.onLogout, icon: const Icon(Icons.logout_rounded))]),
    body: ListView(padding: const EdgeInsets.fromLTRB(16, 18, 16, 32), children: [
      if (!AppConfig.governmentMode) ...[
        Container(padding: const EdgeInsets.all(14), decoration: BoxDecoration(color: const Color(0xFFFFF7E8), border: Border.all(color: const Color(0xFFF2D29A)), borderRadius: BorderRadius.circular(14)), child: const Text('PRIVATE DEMONSTRATION MODE — this does not represent Ministry approval.', style: TextStyle(color: Color(0xFF7F5411), fontSize: 10.5, fontWeight: FontWeight.w900))),
        const SizedBox(height: 16),
      ],
      const SectionLabel('Regulatory intelligence'),
      const SizedBox(height: 5),
      const Text('National oversight dashboard', style: TextStyle(fontSize: 25, fontWeight: FontWeight.w900, letterSpacing: -.7, color: RahnumaTheme.ink)),
      const SizedBox(height: 5),
      const Text('License, Setta, transaction and field-inspection visibility.', style: TextStyle(fontSize: 11.5, color: RahnumaTheme.muted)),
      const SizedBox(height: 16),
      GridView.count(
        crossAxisCount: 2,
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        mainAxisSpacing: 10,
        crossAxisSpacing: 10,
        childAspectRatio: 1.45,
        children: [
          StatCard(label: 'Agencies', value: stats['agencies'] ?? 0, primary: true),
          StatCard(label: 'Setta available', value: stats['setta_available'] ?? 0),
          StatCard(label: 'Transactions', value: stats['transactions'] ?? 0),
          StatCard(label: 'Inspections', value: stats['inspections'] ?? stats['open_violations'] ?? 0),
        ],
      ),
      const SizedBox(height: 20),
      Card(child: Padding(padding: const EdgeInsets.all(17), child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
        const SectionLabel('Field supervision'),
        const SizedBox(height: 6),
        const Text('Record inspection', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 19, color: RahnumaTheme.ink)),
        const SizedBox(height: 5),
        const Text('Capture the agency, findings and current GPS position.', style: TextStyle(fontSize: 10.5, color: RahnumaTheme.muted)),
        const SizedBox(height: 14),
        TextField(controller: agency, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Agency ID')),
        const SizedBox(height: 10),
        TextField(controller: summary, maxLines: 4, decoration: const InputDecoration(labelText: 'Inspection summary')),
        const SizedBox(height: 12),
        FilledButton.icon(onPressed: inspect, icon: const Icon(Icons.location_on_outlined), label: const Text('Save inspection with GPS')),
      ]))),
      if (msg != null) Padding(padding: const EdgeInsets.all(12), child: Text(msg!, style: const TextStyle(color: RahnumaTheme.muted))),
    ]),
  );
}
