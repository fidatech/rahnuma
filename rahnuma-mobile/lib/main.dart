import 'package:flutter/material.dart';
import 'api.dart';
import 'l10n.dart';
import 'models.dart';
import 'storage.dart';
import 'theme.dart';
import 'screens/marketplace_screen.dart';
import 'screens/login_screen.dart';
import 'screens/workspace_screen.dart';
import 'screens/government_screen.dart';

void main() => runApp(const RahnumaApp());

class RahnumaApp extends StatefulWidget {
  const RahnumaApp({super.key});
  @override
  State<RahnumaApp> createState() => _RahnumaAppState();
}

class _RahnumaAppState extends State<RahnumaApp> {
  final store = LocalStore();
  late final Api api = Api(store);
  SessionUser? user;
  bool booting = true;

  @override
  void initState() {
    super.initState();
    L.saver = (code) => store.saveLocale(code);
    boot();
  }

  Future<void> boot() async {
    // the language chosen last time: Dari until the person picks another
    final saved = await store.locale();
    if (saved != null) L.set(saved);
    final t = await store.token();
    if (t != null) {
      try {
        final d = await api.get('/me');
        user = SessionUser.fromJson(Map<String, dynamic>.from(d));
      } catch (_) {
        await store.saveToken(null);
      }
    }
    if (mounted) setState(() => booting = false);
  }

  void logged(SessionUser u) {
    setState(() => user = u);
  }

  Future<void> logout() async {
    await api.logout();
    if (mounted) setState(() => user = null);
  }

  void showLogin(BuildContext context) {
    Navigator.push(context, MaterialPageRoute(builder: (_) => LoginScreen(api: api, onLoggedIn: (u) {
      logged(u);
      Navigator.pop(context);
    })));
  }

  Widget home() {
    if (booting) return const Scaffold(body: Center(child: CircularProgressIndicator()));
    if (user == null) return Builder(builder: (c) => MarketplaceScreen(api: api, store: store, onLogin: () => showLogin(c)));
    if (user!.isGovernment) return GovernmentScreen(api: api, user: user!, onLogout: logout);
    if (user!.agencyId != null) return WorkspaceScreen(api: api, store: store, user: user!, onLogout: logout);
    return Scaffold(
      appBar: AppBar(title: Text('Rahnuma'.tr), actions: [const LanguageButton(), IconButton(onPressed: logout, icon: const Icon(Icons.logout_rounded))]),
      body: Center(child: Padding(padding: const EdgeInsets.all(24), child: Text('Role :role uses the web administration portal.'.trp({'role': user!.role}), textAlign: TextAlign.center))),
    );
  }

  @override
  Widget build(BuildContext context) => ValueListenableBuilder<String>(
        valueListenable: L.locale,
        // the key makes every screen build again in the new language
        builder: (_, code, __) => MaterialApp(
          key: ValueKey(code),
          debugShowCheckedModeBanner: false,
          title: 'Rahnuma',
          theme: RahnumaTheme.light(),
          // Dari and Pashto are written right to left
          builder: (context, child) => Directionality(textDirection: L.direction, child: child ?? const SizedBox.shrink()),
          home: home(),
        ),
      );
}
