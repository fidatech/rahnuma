import 'package:flutter/material.dart';
import '../api.dart';
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
    appBar: AppBar(title: const Text('Secure access')),
    body: ListView(padding: const EdgeInsets.all(20), children: [
      const SizedBox(height: 30),
      Container(width: 54, height: 54, alignment: Alignment.center, decoration: BoxDecoration(color: RahnumaTheme.cobalt, borderRadius: BorderRadius.circular(17)), child: const Text('R', style: TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.w900))),
      const SizedBox(height: 24),
      const SectionLabel('Rahnama workspace'),
      const SizedBox(height: 7),
      const Text('Sign in to your operations workspace', style: TextStyle(fontSize: 27, fontWeight: FontWeight.w900, letterSpacing: -.8, color: RahnumaTheme.ink)),
      const SizedBox(height: 8),
      const Text('Property, customers, transactions, finance and regulatory tools from one account.', style: TextStyle(color: RahnumaTheme.muted, height: 1.5)),
      const SizedBox(height: 26),
      TextField(controller: email, keyboardType: TextInputType.emailAddress, decoration: const InputDecoration(labelText: 'Email address')),
      const SizedBox(height: 12),
      TextField(controller: pass, obscureText: true, decoration: const InputDecoration(labelText: 'Password')),
      if (error != null) Padding(padding: const EdgeInsets.only(top: 12), child: Text(error!, style: const TextStyle(color: Color(0xFFC2414B)))),
      const SizedBox(height: 18),
      FilledButton(onPressed: busy ? null : login, child: Text(busy ? 'Signing in...' : 'Sign in')),
    ]),
  );
}
