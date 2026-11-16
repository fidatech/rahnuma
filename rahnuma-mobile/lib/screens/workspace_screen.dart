import 'package:flutter/material.dart';
import '../api.dart';
import '../l10n.dart';
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
    appBar: AppBar(
      title: Row(children: [const BrandMark(size: 30), const SizedBox(width: 10), Expanded(child: Text(widget.user.agencyName ?? 'Rahnama'.tr, overflow: TextOverflow.ellipsis))]),
      actions: [const LanguageButton(), IconButton(onPressed: widget.onLogout, tooltip: 'Sign out'.tr, icon: const Icon(Icons.logout_rounded))],
    ),
    floatingActionButton: FloatingActionButton.extended(
      onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => PropertyEditorScreen(api: widget.api, store: widget.store))),
      icon: const Icon(Icons.add_home_work_rounded),
      label: Text('Add property'.tr, style: const TextStyle(fontWeight: FontWeight.w800)),
    ),
    body: RefreshIndicator(
      onRefresh: load,
      child: ListView(padding: const EdgeInsets.fromLTRB(16, 18, 16, 100), children: [
        SectionLabel('Operations overview'.tr),
        const SizedBox(height: 5),
        Text('Good day, :name'.trp({'name': widget.user.name}), style: const TextStyle(fontSize: 23, fontWeight: FontWeight.w800, color: RahnumaTheme.heading)),
        const SizedBox(height: 5),
        Text('Your property pipeline and office activity at a glance.'.tr, style: const TextStyle(color: RahnumaTheme.muted, fontSize: 12.5)),
        const SizedBox(height: 16),
        GridView.count(
          crossAxisCount: 2,
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          mainAxisSpacing: 10,
          crossAxisSpacing: 10,
          childAspectRatio: 1.45,
          children: [
            StatCard(label: 'Properties'.tr, value: stats['properties'] ?? 0, hint: 'Total inventory'.tr, primary: true, icon: Icons.apartment_rounded),
            StatCard(label: 'Customers'.tr, value: stats['customers'] ?? 0, hint: 'CRM records'.tr, icon: Icons.people_alt_rounded, color: RahnumaTheme.purple),
            StatCard(label: 'Active leads'.tr, value: stats['leads'] ?? stats['active_leads'] ?? 0, hint: 'Open opportunities'.tr, icon: Icons.filter_alt_rounded, color: RahnumaTheme.warning),
            StatCard(label: 'Transactions'.tr, value: stats['transactions'] ?? 0, hint: 'Deal register'.tr, icon: Icons.handshake_rounded, color: RahnumaTheme.success),
          ],
        ),
        const SizedBox(height: 22),
        SectionLabel('Workspace'.tr),
        const SizedBox(height: 10),
        ...[
          ['Properties','/workspace/properties',Icons.apartment_rounded,'Inventory, pricing and marketplace',RahnumaTheme.primary],
          ['Customers','/workspace/customers',Icons.people_alt_rounded,'Buyers, tenants and requirements',RahnumaTheme.purple],
          ['Owners / Landlords','/workspace/owners',Icons.person_pin_circle_rounded,'Property ownership records',RahnumaTheme.primary],
          ['Leads','/workspace/leads',Icons.filter_alt_rounded,'Sales pipeline and follow-ups',RahnumaTheme.warning],
          ['Visits','/workspace/visits',Icons.calendar_month_rounded,'Property viewings and outcomes',RahnumaTheme.primary],
          ['Transactions','/workspace/transactions',Icons.handshake_rounded,'Sale, rent and mortgage deals',RahnumaTheme.success],
          ['Payments','/workspace/payments',Icons.account_balance_wallet_rounded,'Receipts and financial movements',RahnumaTheme.success],
        ].map((x) => Padding(padding: const EdgeInsets.only(bottom: 9), child: WorkspaceAction(icon: x[2] as IconData, title: (x[0] as String).tr, subtitle: (x[3] as String).tr, color: x[4] as Color, onTap: () => open((x[0] as String).tr, x[1] as String)))),
      ]),
    ),
  );
}
