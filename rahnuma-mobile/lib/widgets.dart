import 'package:flutter/material.dart';
import 'l10n.dart';
import 'models.dart';
import 'theme.dart';

/// The Rahnuma mark: a house on the gradient tile of the template.
class BrandMark extends StatelessWidget {
  final double size;
  const BrandMark({super.key, this.size = 34});
  @override
  Widget build(BuildContext context) => Container(
        width: size,
        height: size,
        decoration: BoxDecoration(gradient: RahnumaTheme.brandGradient, borderRadius: BorderRadius.circular(size * .28)),
        child: Icon(Icons.home_rounded, color: Colors.white, size: size * .62),
      );
}

/// Small coloured heading above a title.
class SectionLabel extends StatelessWidget {
  final String text;
  const SectionLabel(this.text, {super.key});
  @override
  Widget build(BuildContext context) => Text(
        L.isRtl ? text : text.toUpperCase(),
        style: TextStyle(
          color: RahnumaTheme.primary,
          fontSize: L.isRtl ? 12 : 10.5,
          fontWeight: FontWeight.w800,
          letterSpacing: L.isRtl ? 0 : .9,
        ),
      );
}

/// A white card of the template: border, 12px corners, no shadow.
class SoftCard extends StatelessWidget {
  final Widget child;
  final EdgeInsetsGeometry padding;
  const SoftCard({super.key, required this.child, this.padding = const EdgeInsets.all(16)});
  @override
  Widget build(BuildContext context) => Container(
        padding: padding,
        decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: RahnumaTheme.border)),
        child: child,
      );
}

/// A soft coloured square with an icon (the "icon-soft" of the template).
class SoftIcon extends StatelessWidget {
  final IconData icon;
  final Color color;
  final double size;
  const SoftIcon(this.icon, {super.key, this.color = RahnumaTheme.primary, this.size = 42});
  @override
  Widget build(BuildContext context) => Container(
        width: size,
        height: size,
        decoration: BoxDecoration(color: color.withOpacity(.12), borderRadius: BorderRadius.circular(12)),
        child: Icon(icon, color: color, size: size * .5),
      );
}

/// One figure of a dashboard: icon, number, label and a short note.
class StatCard extends StatelessWidget {
  final String label;
  final dynamic value;
  final String? hint;
  final bool primary;
  final IconData icon;
  final Color color;
  const StatCard({super.key, required this.label, required this.value, this.hint, this.primary = false, this.icon = Icons.insights_rounded, this.color = RahnumaTheme.primary});

  @override
  Widget build(BuildContext context) => Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(14),
          gradient: primary ? RahnumaTheme.brandGradient : null,
          color: primary ? null : Colors.white,
          border: primary ? null : Border.all(color: RahnumaTheme.border),
        ),
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Row(children: [
              Expanded(child: Text(label, maxLines: 1, overflow: TextOverflow.ellipsis, style: TextStyle(color: primary ? Colors.white.withOpacity(.9) : RahnumaTheme.muted, fontSize: 12, fontWeight: FontWeight.w700))),
              Icon(icon, size: 18, color: primary ? Colors.white : color),
            ]),
            Text('$value', style: TextStyle(fontSize: 25, fontWeight: FontWeight.w800, color: primary ? Colors.white : RahnumaTheme.heading)),
            if (hint != null) Text(hint!, maxLines: 1, overflow: TextOverflow.ellipsis, style: TextStyle(fontSize: 10.5, color: primary ? Colors.white.withOpacity(.8) : RahnumaTheme.muted)),
          ],
        ),
      );
}

/// A small rounded label (the "badge-soft" of the template).
class SoftBadge extends StatelessWidget {
  final String text;
  final Color color;
  const SoftBadge(this.text, {super.key, this.color = RahnumaTheme.primary});
  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
        decoration: BoxDecoration(color: color.withOpacity(.12), borderRadius: BorderRadius.circular(99)),
        child: Text(text, style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: color)),
      );
}

class PropertyTile extends StatelessWidget {
  final PropertyItem item;
  final VoidCallback? onTap;
  const PropertyTile({super.key, required this.item, this.onTap});

  @override
  Widget build(BuildContext context) => InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Container(
          decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: RahnumaTheme.border)),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                height: 104,
                decoration: const BoxDecoration(
                  borderRadius: BorderRadius.vertical(top: Radius.circular(11)),
                  gradient: LinearGradient(colors: [RahnumaTheme.primarySoft, Color(0xFFF6F8FC)], begin: Alignment.topLeft, end: Alignment.bottomRight),
                ),
                child: const Center(child: Icon(Icons.apartment_rounded, size: 36, color: RahnumaTheme.primary)),
              ),
              Padding(
                padding: const EdgeInsets.all(12),
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Row(children: [
                    SoftBadge(L.label(item.transaction)),
                    const Spacer(),
                    Flexible(child: Text(L.label(item.type), maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 10.5, color: RahnumaTheme.muted))),
                  ]),
                  const SizedBox(height: 8),
                  Text(item.title, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 14.5, fontWeight: FontWeight.w800, color: RahnumaTheme.heading)),
                  const SizedBox(height: 3),
                  Text(item.agencyName ?? 'Rahnama'.tr, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 10.5, color: RahnumaTheme.muted)),
                  const SizedBox(height: 9),
                  Directionality(
                    textDirection: TextDirection.ltr,
                    child: Text('${item.displayPrice.toStringAsFixed(0)} ${item.currency}', style: const TextStyle(fontSize: 16.5, fontWeight: FontWeight.w800, color: RahnumaTheme.heading)),
                  ),
                ]),
              ),
            ],
          ),
        ),
      );
}

/// One row of the workspace menu: icon, title, note and an arrow that follows the language.
class WorkspaceAction extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  final VoidCallback onTap;
  final Color color;
  const WorkspaceAction({super.key, required this.icon, required this.title, required this.subtitle, required this.onTap, this.color = RahnumaTheme.primary});
  @override
  Widget build(BuildContext context) => InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Container(
          padding: const EdgeInsets.all(13),
          decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: RahnumaTheme.border)),
          child: Row(children: [
            SoftIcon(icon, color: color),
            const SizedBox(width: 12),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(title, style: const TextStyle(fontWeight: FontWeight.w800, color: RahnumaTheme.heading)), const SizedBox(height: 2), Text(subtitle, style: const TextStyle(fontSize: 11, color: RahnumaTheme.muted))])),
            Icon(L.isRtl ? Icons.chevron_left_rounded : Icons.chevron_right_rounded, color: RahnumaTheme.muted),
          ]),
        ),
      );
}
