#!/usr/bin/env bash
set -e
cd "$(dirname "$0")"
if [ ! -d android ] || [ ! -d ios ]; then
  tmp=$(mktemp -d)
  cp -R lib "$tmp/lib"
  cp pubspec.yaml "$tmp/pubspec.yaml"
  cp analysis_options.yaml "$tmp/analysis_options.yaml" 2>/dev/null || true
  flutter create --platforms=android,ios --org af.rahnuma --project-name rahnuma_mobile .
  rm -rf lib
  cp -R "$tmp/lib" lib
  cp "$tmp/pubspec.yaml" pubspec.yaml
  cp "$tmp/analysis_options.yaml" analysis_options.yaml 2>/dev/null || true
fi
flutter pub get
echo "Mobile runners are ready. Configure API_BASE_URL with --dart-define."
