import 'package:flutter/material.dart';
import '../api.dart';
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
  Future<void> saveDraft() async { await widget.store.addDraft({...data(), 'saved_at': DateTime.now().toIso8601String()}); if (mounted) setState(() => message = 'Draft saved offline. Financial and regulatory records are never queued offline.'); }

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: const Text('Register property')),
    body: ListView(padding: const EdgeInsets.fromLTRB(16, 18, 16, 32), children: [
      const SectionLabel('Property record'),
      const SizedBox(height: 5),
      const Text('Add property inventory', style: TextStyle(fontSize: 25, fontWeight: FontWeight.w900, letterSpacing: -.6, color: RahnumaTheme.ink)),
      const SizedBox(height: 6),
      const Text('Capture the essential commercial and location information now; complete advanced details from web when needed.', style: TextStyle(fontSize: 11.5, color: RahnumaTheme.muted, height: 1.5)),
      const SizedBox(height: 20),
      Card(child: Padding(padding: const EdgeInsets.all(16), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        const SectionLabel('Core details'), const SizedBox(height: 12),
        TextField(controller: title, decoration: const InputDecoration(labelText: 'Listing title')),
        const SizedBox(height: 10),
        DropdownButtonFormField<String>(value: type, decoration: const InputDecoration(labelText: 'Property type'), items: ['house','apartment','shop','office','land','warehouse','building'].map((x) => DropdownMenuItem(value: x, child: Text(x[0].toUpperCase()+x.substring(1)))).toList(), onChanged: (v) => setState(() => type = v!)),
        const SizedBox(height: 10),
        DropdownButtonFormField<String>(value: transaction, decoration: const InputDecoration(labelText: 'Transaction'), items: const [DropdownMenuItem(value:'rent',child:Text('Rent')),DropdownMenuItem(value:'sale',child:Text('Sale')),DropdownMenuItem(value:'bay_wafa',child:Text('Mortgage / بیع وفا'))], onChanged: (v) => setState(() => transaction = v!)),
      ]))),
      const SizedBox(height: 12),
      Card(child: Padding(padding: const EdgeInsets.all(16), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        const SectionLabel('Commercial terms'), const SizedBox(height: 12),
        TextField(controller: price, keyboardType: TextInputType.number, decoration: InputDecoration(labelText: transaction == 'rent' ? 'Monthly rent' : 'Amount')),
        const SizedBox(height: 10),
        DropdownButtonFormField<String>(value: currency, decoration: const InputDecoration(labelText: 'Currency'), items: ['AFN','USD','EUR','PKR','AED'].map((x) => DropdownMenuItem(value: x, child: Text(x))).toList(), onChanged: (v) => setState(() => currency = v!)),
        const SizedBox(height: 10),
        DropdownButtonFormField<String>(value: visibility, decoration: const InputDecoration(labelText: 'Visibility'), items: ['private','link','public','featured'].map((x) => DropdownMenuItem(value: x, child: Text(x[0].toUpperCase()+x.substring(1)))).toList(), onChanged: (v) => setState(() => visibility = v!)),
      ]))),
      const SizedBox(height: 12),
      Card(child: Padding(padding: const EdgeInsets.all(16), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [const SectionLabel('Location'), const SizedBox(height: 12), TextField(controller: address, decoration: const InputDecoration(labelText: 'Address'), maxLines: 2)]))),
      if (message != null) Padding(padding: const EdgeInsets.symmetric(vertical: 12), child: Text(message!, style: const TextStyle(color: RahnumaTheme.muted))),
      const SizedBox(height: 14),
      FilledButton(onPressed: busy ? null : saveOnline, child: Text(busy ? 'Saving...' : 'Save property')),
      const SizedBox(height: 8),
      OutlinedButton(onPressed: saveDraft, child: const Text('Save offline draft')),
    ]),
  );
}
