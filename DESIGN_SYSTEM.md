# Rahnuma Product Design System — V9 (fida-admin template)

Rahnuma now uses the fida-admin template of Fida Technologies, the same template as Fida Sarafi.
The web workspace, the public marketplace, the platform administration, the regulatory workspace
and the phone app share one look and work in English, Dari and Pashto.

## Core palette (variables of the template)

- Primary: `#3A7BF0` (`--primary`), pressed `#2A62D9`, soft `#EAF1FE`
- Brand gradient: `#4A8DF8` to `#5B45E0` (logo tile, sign-in panel, hero, deal value card)
- Background: `#F3F5F9`, cards `#FFFFFF`, border `#E9EDF3`
- Heading `#232A3A`, text `#3B4254`, muted `#8A92A3`
- Success `#20B26B`, warning `#F4A51C`, danger `#EF4444`, info `#0EA5E9`
- Dark mode: the same variables with dark values (`data-bs-theme="dark"`), switched from the top bar

## Web

- Shell: sidebar with icons, top bar (page search with Ctrl+K, language list, light / dark switch,
  user menu), footer, and on phones a tab bar with the sidebar as a drawer.
- Pages: `<x-page-header>`, `<x-stat>` figures, cards, tables that become cards on phones,
  soft badges, pop-ups for every "add" form and for the quick view, toasts for messages,
  a confirm box before a delete or a sign-out.
- Sources: `resources/assets/css` (Bootstrap 5.3, `fida.css`, `erp.css`, and `rahnuma.css` for
  the marketplace and the property card), `resources/assets/js/app.js`, Font Awesome icons.
  `php tools/build-assets.php` builds `public/css/app.css`, `public/css/app.rtl.css`,
  `public/js/app.js` and `public/icons.svg`, keeping only the classes and icons the views use.
  No Node is needed. Write class names in full, or add them to `resources/assets/safelist.txt`.

## Languages

- English, Dari (`fa`) and Pashto (`ps`); Dari and Pashto are right to left with their own
  style sheet. The language list is in the top bar and on the sign-in page; the choice is kept
  for the signed-in user.
- Every text in a view or a controller message is written as `__('English text')`.
  Translations are in `lang/fa.json` and `lang/ps.json`; validation and sign-in messages in
  `lang/fa/*.php` and `lang/ps/*.php`.
- Stored values (status, property type, role, deal type) are shown with `enum_label()` and
  `deal_label()`; names of provinces, districts and areas with `loc_name()` (`app/Support/helpers.php`).
- Font of Dari and Pashto text: Noto Kufi Arabic by default; `FONT_DARI` / `FONT_PASHTO` in `.env`
  can be `vazirmatn`, `noto-naskh-arabic` or `noto-kufi-arabic` (`config/fonts.php`).

## Mobile

Material 3 with the same colours, gradient, bordered cards, soft icons and badges
(`lib/theme.dart`, `lib/widgets.dart`). English, Dari and Pashto from `lib/l10n.dart`,
right-to-left layout for Dari and Pashto, language button in the top bar.

## Regulatory workspace

Same system. While `GOVERNMENT_MODE=false` every regulatory page carries the
"private demonstration mode" notice and nothing is presented as an official Ministry product.

## Public site: the formal ("Rasmi") look

The public marketplace and the sign-in page use a formal palette in the manner of a government
service site: navy `#0a1f3d`, deep blue `#0c3b7a` and a gold accent `#c9a227`. It is set in
`resources/assets/css/rahnuma.css` by re-pointing the colour variables of the template on
`.public-body` and `.auth-body`, so buttons, links and badges follow; the workspaces keep the
template colours. The layout is a navy utility bar (languages, light / dark), the header with a
gold rule, the page, and a navy footer.

Optional `.env` values, shown only when set: `CONTACT_PHONE`, `CONTACT_EMAIL`, `CONTACT_ADDRESS`,
`CONTACT_HOURS`, and `OFFICIAL_EMBLEM` (a path under `public/`). An official emblem may be used
only with the written permission of the authority it belongs to.
