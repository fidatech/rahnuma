class AppConfig {
  static const apiBase = String.fromEnvironment('API_BASE_URL', defaultValue: 'http://127.0.0.1:8000/api/v1');
  static const governmentMode = bool.fromEnvironment('GOVERNMENT_MODE', defaultValue: false);
}
