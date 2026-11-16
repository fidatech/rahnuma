# Rahnuma Flutter app

Public marketplace + Rahnama ERP mobile workspace + government/inspector role-aware workspace.

```bash
chmod +x bootstrap_mobile.sh
./bootstrap_mobile.sh
flutter run --dart-define=API_BASE_URL=https://rahnuma.af/api/v1
```

The script generates the standard Android/iOS runner folders with your installed Flutter SDK while preserving this Dart source.

Offline behavior: public listings are cached and property drafts can be saved locally. Financial transactions, Setta usage and regulatory writes require a confirmed server request and are deliberately not queued offline.
