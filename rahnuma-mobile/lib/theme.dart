import 'package:flutter/material.dart';

/// Colours and shapes of the fida-admin template (the template of Fida Sarafi and of the
/// Rahnuma web workspace), so the phone app and the web look like one product.
class RahnumaTheme {
  // template tokens
  static const primary = Color(0xFF3A7BF0);
  static const primaryDark = Color(0xFF2A62D9);
  static const primarySoft = Color(0xFFEAF1FE);
  static const brandFrom = Color(0xFF4A8DF8);
  static const brandTo = Color(0xFF5B45E0);
  static const heading = Color(0xFF232A3A);
  static const text = Color(0xFF3B4254);
  static const background = Color(0xFFF3F5F9);
  static const border = Color(0xFFE9EDF3);
  static const muted = Color(0xFF8A92A3);
  static const success = Color(0xFF20B26B);
  static const warning = Color(0xFFF4A51C);
  static const danger = Color(0xFFEF4444);
  static const purple = Color(0xFF8B5CF6);

  // names used by the screens since the first version
  static const ink = heading;
  static const ink2 = text;
  static const cobalt = primary;
  static const cobaltDark = primaryDark;
  static const copper = warning;

  /// The gradient of the brand tile, the sign-in panel and the hero cards.
  static const brandGradient = LinearGradient(
    colors: [brandFrom, primaryDark, brandTo],
    stops: [0, .6, 1],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static ThemeData light() {
    final scheme = ColorScheme.fromSeed(
      seedColor: primary,
      brightness: Brightness.light,
      primary: primary,
      secondary: purple,
      surface: Colors.white,
      error: danger,
    );

    return ThemeData(
      useMaterial3: true,
      colorScheme: scheme,
      scaffoldBackgroundColor: background,
      textTheme: const TextTheme(
        headlineLarge: TextStyle(fontWeight: FontWeight.w800, color: heading),
        headlineMedium: TextStyle(fontWeight: FontWeight.w800, color: heading),
        headlineSmall: TextStyle(fontWeight: FontWeight.w800, color: heading),
        titleLarge: TextStyle(fontWeight: FontWeight.w800, color: heading),
        titleMedium: TextStyle(fontWeight: FontWeight.w700, color: heading),
        bodyLarge: TextStyle(color: text),
        bodyMedium: TextStyle(color: text),
      ),
      // the light top bar of the template
      appBarTheme: const AppBarTheme(
        backgroundColor: Colors.white,
        foregroundColor: heading,
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        scrolledUnderElevation: 1,
        shadowColor: Color(0x14121C2D),
        centerTitle: false,
        titleTextStyle: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: heading),
      ),
      cardTheme: CardThemeData(
        elevation: 0,
        margin: EdgeInsets.zero,
        color: Colors.white,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
          side: const BorderSide(color: border),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        labelStyle: const TextStyle(color: muted, fontWeight: FontWeight.w600),
        hintStyle: const TextStyle(color: muted),
        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(9),
          borderSide: const BorderSide(color: Color(0xFFDFE4EC)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(9),
          borderSide: const BorderSide(color: Color(0xFFDFE4EC)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(9),
          borderSide: const BorderSide(color: primary, width: 1.4),
        ),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: primary,
          foregroundColor: Colors.white,
          minimumSize: const Size(0, 48),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          textStyle: const TextStyle(fontWeight: FontWeight.w800),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: text,
          backgroundColor: Colors.white,
          minimumSize: const Size(0, 48),
          side: const BorderSide(color: Color(0xFFDFE4EC)),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          textStyle: const TextStyle(fontWeight: FontWeight.w700),
        ),
      ),
      floatingActionButtonTheme: const FloatingActionButtonThemeData(
        backgroundColor: primary,
        foregroundColor: Colors.white,
      ),
      popupMenuTheme: PopupMenuThemeData(
        color: Colors.white,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12), side: const BorderSide(color: border)),
      ),
      dividerColor: border,
    );
  }
}
