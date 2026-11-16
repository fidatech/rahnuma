import 'package:flutter/material.dart';
import '../api.dart';
import '../l10n.dart';
import '../models.dart';
import '../storage.dart';
import '../theme.dart';
import '../widgets.dart';
import 'property_detail_screen.dart';

class MarketplaceScreen extends StatefulWidget {
  final Api api;
  final LocalStore store;
  final VoidCallback onLogin;
  const MarketplaceScreen({super.key, required this.api, required this.store, required this.onLogin});
  @override State<MarketplaceScreen> createState() => _MarketplaceScreenState();
}

class _MarketplaceScreenState extends State<MarketplaceScreen> {
  List<PropertyItem> items = [];
  bool loading = true;
  String transaction = '';
  String type = '';

  @override void initState() { super.initState(); load(); }

  Future<void> load() async {
    setState(() => loading = true);
    try {
      final q = ['/properties?', if (transaction.isNotEmpty) 'transaction_type=$transaction&', if (type.isNotEmpty) 'property_type=$type&'].join();
      final d = await widget.api.get(q);
      final raw = List<dynamic>.from(d['data'] ?? []);
      await widget.store.cacheProperties(raw);
      if (mounted) setState(() => items = raw.map((e) => PropertyItem.fromJson(Map<String, dynamic>.from(e))).toList());
    } catch (_) {
      final raw = await widget.store.cachedProperties();
      if (mounted) setState(() => items = raw.map((e) => PropertyItem.fromJson(Map<String, dynamic>.from(e))).toList());
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(
      title: Row(children: [
        const BrandMark(size: 32),
        const SizedBox(width: 10),
        Text('Rahnuma'.tr),
      ]),
      actions: [
        const LanguageButton(),
        IconButton(onPressed: widget.onLogin, tooltip: 'Sign in'.tr, icon: const Icon(Icons.person_outline_rounded)),
      ],
    ),
    body: RefreshIndicator(
      onRefresh: load,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 18, 16, 28),
        children: [
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(borderRadius: BorderRadius.circular(18), gradient: RahnumaTheme.brandGradient),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
                decoration: BoxDecoration(color: Colors.white.withOpacity(.16), borderRadius: BorderRadius.circular(99)),
                child: Text('Afghanistan property network'.tr, style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700)),
              ),
              const SizedBox(height: 12),
              Text('Find the right property through local Rahnamas.'.tr, style: const TextStyle(color: Colors.white, fontSize: 23, height: 1.3, fontWeight: FontWeight.w800)),
              const SizedBox(height: 8),
              Text('Browse live property inventory by deal type and category.'.tr, style: TextStyle(color: Colors.white.withOpacity(.88), fontSize: 12.5)),
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(14)),
                child: Column(children: [
                  DropdownButtonFormField<String>(
                    value: transaction.isEmpty ? null : transaction,
                    isExpanded: true,
                    hint: Text('Rent / Sale / Mortgage'.tr),
                    items: [
                      DropdownMenuItem(value: 'rent', child: Text('Rent'.tr)),
                      DropdownMenuItem(value: 'sale', child: Text('Sale'.tr)),
                      DropdownMenuItem(value: 'bay_wafa', child: Text('Mortgage / Bay-e-Wafa'.tr)),
                    ],
                    onChanged: (v) { transaction = v ?? ''; load(); },
                  ),
                  const SizedBox(height: 9),
                  DropdownButtonFormField<String>(
                    value: type.isEmpty ? null : type,
                    isExpanded: true,
                    hint: Text('Property type'.tr),
                    items: ['house','apartment','shop','office','land','warehouse','building','factory','garden'].map((x) => DropdownMenuItem(value: x, child: Text(L.label(x)))).toList(),
                    onChanged: (v) { type = v ?? ''; load(); },
                  ),
                ]),
              ),
            ]),
          ),
          const SizedBox(height: 22),
          Row(children: [
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              SectionLabel('Fresh inventory'.tr),
              const SizedBox(height: 4),
              Text('Available properties'.tr, style: const TextStyle(fontSize: 19, fontWeight: FontWeight.w800, color: RahnumaTheme.heading)),
            ])),
            Text(':count listings'.trp({'count': items.length}), style: const TextStyle(fontSize: 11, color: RahnumaTheme.muted)),
          ]),
          const SizedBox(height: 12),
          if (loading)
            const Center(child: Padding(padding: EdgeInsets.all(40), child: CircularProgressIndicator()))
          else if (items.isEmpty)
            SoftCard(padding: const EdgeInsets.all(32), child: Center(child: Text('No matching properties found.'.tr, style: const TextStyle(color: RahnumaTheme.muted))))
          else
            GridView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 2, crossAxisSpacing: 10, mainAxisSpacing: 10, childAspectRatio: .66),
              itemCount: items.length,
              itemBuilder: (_, i) => PropertyTile(item: items[i], onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => PropertyDetailScreen(api: widget.api, id: items[i].id)))),
            ),
        ],
      ),
    ),
  );
}
