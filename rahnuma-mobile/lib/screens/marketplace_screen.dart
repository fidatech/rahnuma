import 'package:flutter/material.dart';
import '../api.dart';
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
      title: const Row(children: [
        CircleAvatar(radius: 16, backgroundColor: RahnumaTheme.cobalt, child: Text('R', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900))),
        SizedBox(width: 10),
        Text('Rahnuma'),
      ]),
      actions: [IconButton(onPressed: widget.onLogin, icon: const Icon(Icons.person_outline_rounded))],
    ),
    body: RefreshIndicator(
      onRefresh: load,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 18, 16, 28),
        children: [
          Container(
            padding: const EdgeInsets.all(22),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(24),
              gradient: const LinearGradient(colors: [RahnumaTheme.ink, Color(0xFF263769)], begin: Alignment.topLeft, end: Alignment.bottomRight),
            ),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              const Text('AFGHANISTAN PROPERTY NETWORK', style: TextStyle(color: Color(0xFFAFC0FF), fontSize: 9.5, fontWeight: FontWeight.w900, letterSpacing: 1.1)),
              const SizedBox(height: 8),
              const Text('Find the right property through local Rahnamas.', style: TextStyle(color: Colors.white, fontSize: 26, height: 1.08, fontWeight: FontWeight.w900, letterSpacing: -.7)),
              const SizedBox(height: 9),
              const Text('Browse live property inventory by deal type and category.', style: TextStyle(color: Color(0xFFD0D5DD), fontSize: 12.5)),
              const SizedBox(height: 18),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16)),
                child: Column(children: [
                  DropdownButtonFormField<String>(
                    value: transaction.isEmpty ? null : transaction,
                    hint: const Text('Rent / Sale / Mortgage'),
                    items: const [
                      DropdownMenuItem(value: 'rent', child: Text('Rent')),
                      DropdownMenuItem(value: 'sale', child: Text('Sale')),
                      DropdownMenuItem(value: 'bay_wafa', child: Text('Mortgage / بیع وفا')),
                    ],
                    onChanged: (v) { transaction = v ?? ''; load(); },
                  ),
                  const SizedBox(height: 9),
                  DropdownButtonFormField<String>(
                    value: type.isEmpty ? null : type,
                    hint: const Text('Property type'),
                    items: ['house','apartment','shop','office','land','warehouse','building','factory','garden'].map((x) => DropdownMenuItem(value: x, child: Text(x[0].toUpperCase()+x.substring(1)))).toList(),
                    onChanged: (v) { type = v ?? ''; load(); },
                  ),
                ]),
              ),
            ]),
          ),
          const SizedBox(height: 22),
          Row(children: [const Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [SectionLabel('Fresh inventory'), SizedBox(height: 4), Text('Available properties', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900, color: RahnumaTheme.ink))])), Text('${items.length} listings', style: const TextStyle(fontSize: 10.5, color: RahnumaTheme.muted))]),
          const SizedBox(height: 12),
          if (loading)
            const Center(child: Padding(padding: EdgeInsets.all(40), child: CircularProgressIndicator()))
          else if (items.isEmpty)
            Container(padding: const EdgeInsets.all(32), decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(18), border: Border.all(color: RahnumaTheme.border)), child: const Center(child: Text('No matching properties found.', style: TextStyle(color: RahnumaTheme.muted))))
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
