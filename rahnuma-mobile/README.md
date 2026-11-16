# Rahnuma Flutter app

Public marketplace + Rahnama ERP mobile workspace + government/inspector role-aware workspace.

```bash
chmod +x bootstrap_mobile.sh
./bootstrap_mobile.sh
flutter run --dart-define=API_BASE_URL=https://rahnuma.af/api/v1
```

The script generates the standard Android/iOS runner folders with your installed Flutter SDK while preserving this Dart source.

## Look

The app uses the fida-admin template of Fida Technologies, the same one as Fida Sarafi and the Rahnuma web
workspace: the blue primary colour, the blue-to-violet brand gradient, white cards with a thin border, soft
coloured icon squares and rounded badges. Colours and shapes are in `lib/theme.dart`; the shared pieces
(`BrandMark`, `SoftCard`, `SoftIcon`, `SoftBadge`, `StatCard`) are in `lib/widgets.dart`.

## Languages

English, Dari and Pashto, chosen from the globe button in the top bar. Dari is the first language of a new
install, and the choice is kept on the phone. Dari and Pashto are laid out right to left.

All texts are in `lib/l10n.dart`: English is the key, with the Dari and the Pashto text beside it.
In a screen write `'Sign in'.tr`, or `'Good day, :name'.trp({'name': name})` when a value goes in.
A text without a translation is shown in English. No generated code and no extra package is needed.

## Offline

Public listings are cached and property drafts can be saved locally. Financial transactions, Setta usage and regulatory writes require a confirmed server request and are deliberately not queued offline.
