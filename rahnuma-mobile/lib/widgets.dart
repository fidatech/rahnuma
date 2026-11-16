import 'package:flutter/material.dart';
import 'models.dart';
import 'theme.dart';

class SectionLabel extends StatelessWidget {
  final String text;
  const SectionLabel(this.text, {super.key});
  @override
  Widget build(BuildContext context) => Text(
        text.toUpperCase(),
        style: const TextStyle(
          color: RahnumaTheme.cobalt,
          fontSize: 10,
          fontWeight: FontWeight.w900,
          letterSpacing: 1.2,
        ),
      );
}

class StatCard extends StatelessWidget {
  final String label;
  final dynamic value;
  final String? hint;
  final bool primary;
  const StatCard({super.key, required this.label, required this.value, this.hint, this.primary = false});

  @override
  Widget build(BuildContext context) => Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(18),
          color: primary ? RahnumaTheme.cobalt : Colors.white,
          border: primary ? null : Border.all(color: RahnumaTheme.border),
        ),
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(label, style: TextStyle(color: primary ? const Color(0xFFDCE5FF) : RahnumaTheme.muted, fontSize: 11, fontWeight: FontWeight.w700)),
            const SizedBox(height: 10),
            Text('$value', style: TextStyle(fontSize: 25, fontWeight: FontWeight.w900, letterSpacing: -.6, color: primary ? Colors.white : RahnumaTheme.ink)),
            if (hint != null) ...[
              const SizedBox(height: 7),
              Text(hint!, style: TextStyle(fontSize: 9.5, color: primary ? const Color(0xFFC7D3FF) : RahnumaTheme.muted)),
            ],
          ],
        ),
      );
}

class PropertyTile extends StatelessWidget {
  final PropertyItem item;
  final VoidCallback? onTap;
  const PropertyTile({super.key, required this.item, this.onTap});

  @override
  Widget build(BuildContext context) => InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(18),
        child: Container(
          decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(18), border: Border.all(color: RahnumaTheme.border)),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                height: 118,
                decoration: const BoxDecoration(
                  borderRadius: BorderRadius.vertical(top: Radius.circular(17)),
                  gradient: LinearGradient(colors: [Color(0xFFE9EDF7), Color(0xFFF7F8FB)], begin: Alignment.topLeft, end: Alignment.bottomRight),
                ),
                child: Center(
                  child: Icon(Icons.apartment_rounded, size: 38, color: RahnumaTheme.cobalt.withOpacity(.55)),
                ),
              ),
              Padding(
                padding: const EdgeInsets.all(14),
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Row(children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(color: const Color(0xFFEEF2FF), borderRadius: BorderRadius.circular(99)),
                      child: Text(item.transaction == 'bay_wafa' ? 'Mortgage' : item.transaction.toUpperCase(), style: const TextStyle(fontSize: 9, fontWeight: FontWeight.w900, color: RahnumaTheme.cobaltDark)),
                    ),
                    const Spacer(),
                    Text(item.type, style: const TextStyle(fontSize: 10, color: RahnumaTheme.muted)),
                  ]),
                  const SizedBox(height: 9),
                  Text(item.title, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: RahnumaTheme.ink)),
                  const SizedBox(height: 4),
                  Text(item.agencyName ?? 'Rahnama', maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 10.5, color: RahnumaTheme.muted)),
                  const SizedBox(height: 11),
                  Text('${item.displayPrice.toStringAsFixed(0)} ${item.currency}', style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w900, color: RahnumaTheme.ink)),
                ]),
              ),
            ],
          ),
        ),
      );
}

class WorkspaceAction extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  final VoidCallback onTap;
  const WorkspaceAction({super.key, required this.icon, required this.title, required this.subtitle, required this.onTap});
  @override
  Widget build(BuildContext context) => InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), border: Border.all(color: RahnumaTheme.border)),
          child: Row(children: [
            Container(width: 42, height: 42, decoration: BoxDecoration(color: const Color(0xFFEEF2FF), borderRadius: BorderRadius.circular(12)), child: Icon(icon, color: RahnumaTheme.cobalt)),
            const SizedBox(width: 12),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(title, style: const TextStyle(fontWeight: FontWeight.w800, color: RahnumaTheme.ink)), const SizedBox(height: 2), Text(subtitle, style: const TextStyle(fontSize: 10.5, color: RahnumaTheme.muted))])),
            const Icon(Icons.chevron_right_rounded, color: Color(0xFF98A2B3)),
          ]),
        ),
      );
}
