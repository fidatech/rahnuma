import 'package:flutter/material.dart';
import '../api.dart';
import '../l10n.dart';
import '../storage.dart';
import '../theme.dart';
import '../widgets.dart';

class PropertyEditorScreen extends StatefulWidget {
  final Api api;
  final LocalStore store;
  const PropertyEditorScreen({super.key, required this.api, required this.store});
  @override State<PropertyEditorScreen> createState() => _PropertyEditorScreenState();
}

class _PropertyEditorScreenState extends State<PropertyEditorScreen> {
  final title = TextEditingController(), price = TextEditingController(), address = TextEditingController();
  String type = 'apartment', transaction = 'rent', visibility = 'private', currency = 'AFN';
  bool busy = false;
  String? message;
  Map<String,dynamic> data() => {
    'title': title.text,
    'property_type': type,
    'transaction_type': transaction,
    'visibility': visibility,
    'status': 'available',
    'currency': currency,
    if (transaction == 'rent') 'monthly_rent': double.tryParse(price.text),
    if (transaction != 'rent') 'price': double.tryParse(price.text),
    'address': address.text,
  };
  Future<void> saveOnline() async { setState(() => busy = true); try { await widget.api.post('/workspace/properties', data()); if (mounted) Navigator.pop(context); } catch (e) { setState(() => message = '$e'); } finally { if (mounted) setState(() => busy = false); } }
  Future<void> saveDraft() async { await widget.store.addDraft({...data(), 'saved_at': DateTime.now().toIso8601String()}); if (mounted) setState(() => message = 'Draft saved offline. Financial and regulatory records are never queued offline.'.tr); }

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: Text('Register property'.tr)),
    body: ListView(padding: const EdgeInsets.fromLTRB(16, 18, 16, 32), children: [
      SectionLabel('Property record'.tr),
      const SizedBox(height: 5),
      Text('Add property inventory'.tr, style: const TextStyle(fontSize: 23, fontWeight: FontWeight.w800, color: RahnumaTheme.heading)),
      const SizedBox(height: 6),
      Text('Capture the essential commercial and location information now; complete advanced details from web when needed.'.tr, style: const TextStyle(fontSize: 12, color: RahnumaTheme.muted, height: 1.5)),
      const SizedBox(height: 18),
      SoftCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        SectionLabel('Core details'.tr), const SizedBox(height: 12),
        TextField(controller: title, decoration: InputDecoration(labelText: 'Listing title'.tr)),
        const SizedBox(height: 10),
        DropdownButtonFormField<String>(value: type, isExpanded: true, decoration: InputDecoration(labelText: 'Property type'.tr), items: ['house','apartment','shop','office','land','warehouse','building'].map((x) => DropdownMenuItem(value: x, child: Text(L.label(x)))).toList(), onChanged: (v) => setState(() => type = v!)),
        const SizedBox(height: 10),
        DropdownButtonFormField<String>(value: transaction, isExpanded: true, decoration: InputDecoration(labelText: 'Transaction'.tr), items: [DropdownMenuItem(value: 'rent', child: Text('Rent'.tr)), DropdownMenuItem(value: 'sale', child: Text('Sale'.tr)), DropdownMenuItem(value: 'bay_wafa', child: Text('Mortgage / Bay-e-Wafa'.tr))], onChanged: (v) => setState(() => transaction = v!)),
      ])),
      const SizedBox(height: 12),
      SoftCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        SectionLabel('Commercial terms'.tr), const SizedBox(height: 12),
        TextField(controller: price, keyboardType: TextInputType.number, textDirection: TextDirection.ltr, decoration: InputDecoration(labelText: transaction == 'rent' ? 'Monthly rent'.tr : 'Amount'.tr)),
        const SizedBox(height: 10),
        DropdownButtonFormField<String>(value: currency, isExpanded: true, decoration: InputDecoration(labelText: 'Currency'.tr), items: ['AFN','USD'].map((x) => DropdownMenuItem(value: x, child: Text(x))).toList(), onChanged: (v) => setState(() => currency = v!)),
        const SizedBox(height: 10),
        DropdownButtonFormField<String>(value: visibility, isExpanded: true, decoration: InputDecoration(labelText: 'Visibility'.tr), items: ['private','link','public','featured'].map((x) => DropdownMenuItem(value: x, child: Text(L.label(x)))).toList(), onChanged: (v) => setState(() => visibility = v!)),
      ])),
      const SizedBox(height: 12),
      SoftCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [SectionLabel('Location'.tr), const SizedBox(height: 12), TextField(controller: address, decoration: InputDecoration(labelText: 'Address'.tr), maxLines: 2)])),
      if (message != null) Padding(padding: const EdgeInsets.symmetric(vertical: 12), child: Text(message!, style: const TextStyle(color: RahnumaTheme.muted))),
      const SizedBox(height: 14),
      FilledButton(onPressed: busy ? null : saveOnline, child: Text(busy ? 'Saving...'.tr : 'Save property'.tr)),
      const SizedBox(height: 8),
      OutlinedButton(onPressed: saveDraft, child: Text('Save offline draft'.tr)),
    ]),
  );
}
