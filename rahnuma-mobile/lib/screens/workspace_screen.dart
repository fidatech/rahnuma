import 'package:flutter/material.dart';
import '../api.dart';
import '../models.dart';
import '../storage.dart';
import '../theme.dart';
import '../widgets.dart';
import 'workspace_list_screen.dart';
import 'property_editor_screen.dart';

class WorkspaceScreen extends StatefulWidget {
  final Api api;
  final LocalStore store;
  final SessionUser user;
  final VoidCallback onLogout;
  const WorkspaceScreen({super.key, required this.api, required this.store, required this.user, required this.onLogout});
  @override State<WorkspaceScreen> createState() => _WorkspaceScreenState();
}

class _WorkspaceScreenState extends State<WorkspaceScreen> {
  Map<String,dynamic> stats = {};
  @override void initState() { super.initState(); load(); }
  Future<void> load() async { try { final d = await widget.api.get('/workspace/dashboard'); if (mounted) setState(() => stats = Map<String,dynamic>.from(d)); } catch (_) {} }
  void open(String title, String endpoint) { Navigator.push(context, MaterialPageRoute(builder: (_) => WorkspaceListScreen(api: widget.api, title: title, endpoint: endpoint))); }

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: Text(widget.user.agencyName ?? 'Rahnama'), actions: [IconButton(onPressed: widget.onLogout, icon: const Icon(Icons.logout_rounded))]),
    floatingActionButton: FloatingActionButton.extended(
      onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => PropertyEditorScreen(api: widget.api, store: widget.store))),
      icon: const Icon(Icons.add_home_work_rounded),
      label: const Text('Add property', style: TextStyle(fontWeight: FontWeight.w800)),
    ),
    body: RefreshIndicator(
      onRefresh: load,
      child: ListView(padding: const EdgeInsets.fromLTRB(16, 18, 16, 100), children: [
        const SectionLabel('Operations overview'),
        const SizedBox(height: 5),
        Text('Good day, ${widget.user.name}', style: const TextStyle(fontSize: 25, fontWeight: FontWeight.w900, letterSpacing: -.7, color: RahnumaTheme.ink)),
        const SizedBox(height: 5),
        const Text('Your property pipeline and office activity at a glance.', style: TextStyle(color: RahnumaTheme.muted, fontSize: 12)),
        const SizedBox(height: 16),
        GridView.count(
          crossAxisCount: 2,
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          mainAxisSpacing: 10,
          crossAxisSpacing: 10,
          childAspectRatio: 1.45,
          children: [
            StatCard(label: 'Properties', value: stats['properties'] ?? 0, hint: 'Total inventory', primary: true),
            StatCard(label: 'Customers', value: stats['customers'] ?? 0, hint: 'CRM records'),
            StatCard(label: 'Active leads', value: stats['leads'] ?? stats['active_leads'] ?? 0, hint: 'Open opportunities'),
            StatCard(label: 'Transactions', value: stats['transactions'] ?? 0, hint: 'Deal register'),
          ],
        ),
        const SizedBox(height: 22),
        const SectionLabel('Workspace'),
        const SizedBox(height: 10),
        ...[
          ['Properties','/workspace/properties',Icons.apartment_rounded,'Inventory, pricing and marketplace'],
          ['Customers','/workspace/customers',Icons.people_alt_rounded,'Buyers, tenants and requirements'],
          ['Owners / Landlords','/workspace/owners',Icons.person_pin_circle_rounded,'Property ownership records'],
          ['Leads','/workspace/leads',Icons.filter_alt_rounded,'Sales pipeline and follow-ups'],
          ['Visits','/workspace/visits',Icons.calendar_month_rounded,'Property viewings and outcomes'],
          ['Transactions','/workspace/transactions',Icons.handshake_rounded,'Sale, rent and mortgage deals'],
          ['Payments','/workspace/payments',Icons.account_balance_wallet_rounded,'Receipts and financial movements'],
        ].map((x) => Padding(padding: const EdgeInsets.only(bottom: 9), child: WorkspaceAction(icon: x[2] as IconData, title: x[0] as String, subtitle: x[3] as String, onTap: () => open(x[0] as String, x[1] as String)))),
      ]),
    ),
  );
}
