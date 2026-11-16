import 'package:flutter/material.dart';
import '../api.dart';
import '../l10n.dart';
import '../models.dart';
import '../theme.dart';
import '../widgets.dart';

class LoginScreen extends StatefulWidget {
  final Api api;
  final ValueChanged<SessionUser> onLoggedIn;
  const LoginScreen({super.key, required this.api, required this.onLoggedIn});
  @override State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final email = TextEditingController(), pass = TextEditingController();
  bool busy = false;
  bool hidden = true;
  String? error;

  Future<void> login() async {
    setState(() { busy = true; error = null; });
    try {
      final d = await widget.api.login(email.text.trim(), pass.text);
      widget.onLoggedIn(SessionUser.fromJson(Map<String, dynamic>.from(d['user'])));
    } catch (e) { setState(() => error = '$e'); }
    finally { if (mounted) setState(() => busy = false); }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: Text('Secure access'.tr), actions: const [LanguageButton()]),
    body: ListView(padding: const EdgeInsets.all(20), children: [
      // the gradient panel of the sign-in page of the template
      Container(
        padding: const EdgeInsets.all(22),
        decoration: BoxDecoration(gradient: RahnumaTheme.brandGradient, borderRadius: BorderRadius.circular(18)),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Container(width: 46, height: 46, decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(13)), child: const Icon(Icons.home_rounded, color: RahnumaTheme.primary, size: 28)),
          const SizedBox(height: 16),
          Text('Rahnama workspace'.tr, style: TextStyle(color: Colors.white.withOpacity(.85), fontSize: 12, fontWeight: FontWeight.w700)),
          const SizedBox(height: 6),
          Text('Sign in to your operations workspace'.tr, style: const TextStyle(fontSize: 23, height: 1.3, fontWeight: FontWeight.w800, color: Colors.white)),
          const SizedBox(height: 8),
          Text('Property, customers, transactions, finance and regulatory tools from one account.'.tr, style: TextStyle(color: Colors.white.withOpacity(.88), height: 1.5)),
        ]),
      ),
      const SizedBox(height: 22),
      // an email address and a password are always typed left to right
      Directionality(textDirection: TextDirection.ltr, child: TextField(controller: email, keyboardType: TextInputType.emailAddress, autocorrect: false, decoration: InputDecoration(labelText: 'Email address'.tr, prefixIcon: const Icon(Icons.mail_outline_rounded)))),
      const SizedBox(height: 12),
      Directionality(textDirection: TextDirection.ltr, child: TextField(controller: pass, obscureText: hidden, onSubmitted: (_) { if (!busy) login(); }, decoration: InputDecoration(labelText: 'Password'.tr, prefixIcon: const Icon(Icons.lock_outline_rounded), suffixIcon: IconButton(onPressed: () => setState(() => hidden = !hidden), icon: Icon(hidden ? Icons.visibility_outlined : Icons.visibility_off_outlined))))),
      if (error != null) Padding(padding: const EdgeInsets.only(top: 12), child: Text(error!, style: const TextStyle(color: RahnumaTheme.danger))),
      const SizedBox(height: 18),
      FilledButton(onPressed: busy ? null : login, child: Text(busy ? 'Signing in...'.tr : 'Sign in'.tr)),
      const SizedBox(height: 18),
      const Center(child: BrandMark(size: 30)),
    ]),
  );
}
