import 'package:flutter/material.dart';
import '../api.dart';
import '../l10n.dart';
import '../theme.dart';
import '../widgets.dart';

class WorkspaceListScreen extends StatefulWidget {
  final Api api;
  final String title;
  final String endpoint;
  const WorkspaceListScreen({super.key, required this.api, required this.title, required this.endpoint});
  @override State<WorkspaceListScreen> createState() => _WorkspaceListScreenState();
}

class _WorkspaceListScreenState extends State<WorkspaceListScreen> {
  List<dynamic> items = [];
  String? error;
  @override void initState() { super.initState(); load(); }
  Future<void> load() async { try { final d = await widget.api.get(widget.endpoint); if (mounted) setState(() => items = List<dynamic>.from(d['data'] ?? d)); } catch (e) { if (mounted) setState(() => error = '$e'); } }
  String label(Map<String,dynamic>x) => '${x['title'] ?? x['name'] ?? x['reference_no'] ?? x['category'] ?? '#${x['id']}'}';
  String sub(Map<String,dynamic>x) { if (x['phone'] != null) return '${x['phone']}'; if (x['status'] != null) return L.label('${x['status']}'); if (x['amount'] != null) return '${x['amount']} ${x['currency'] ?? ''}'; return ''; }

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: Text(widget.title)),
    body: RefreshIndicator(
      onRefresh: load,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 18, 16, 30),
        children: [
          SectionLabel('Workspace register'.tr),
          const SizedBox(height: 5),
          Text(widget.title, style: const TextStyle(fontSize: 23, fontWeight: FontWeight.w800, color: RahnumaTheme.heading)),
          const SizedBox(height: 5),
          Text(':count records'.trp({'count': items.length}), style: const TextStyle(fontSize: 11.5, color: RahnumaTheme.muted)),
          const SizedBox(height: 16),
          if (items.isEmpty)
            SoftCard(padding: const EdgeInsets.all(32), child: Center(child: Text(error ?? 'No records'.tr, style: const TextStyle(color: RahnumaTheme.muted))))
          else
            ...items.map((raw) {
              final x = Map<String,dynamic>.from(raw);
              return Padding(
                padding: const EdgeInsets.only(bottom: 9),
                child: SoftCard(
                  padding: const EdgeInsets.all(13),
                  child: Row(children: [
                    const SoftIcon(Icons.description_outlined, size: 40),
                    const SizedBox(width: 12),
                    Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Text(label(x), style: const TextStyle(fontWeight: FontWeight.w800, color: RahnumaTheme.heading)),
                      if (sub(x).isNotEmpty) ...[const SizedBox(height: 3), Text(sub(x), style: const TextStyle(fontSize: 11, color: RahnumaTheme.muted))],
                    ])),
                    Text('#${x['id']}', style: const TextStyle(fontSize: 10.5, color: RahnumaTheme.muted)),
                  ]),
                ),
              );
            }),
        ],
      ),
    ),
  );
}
