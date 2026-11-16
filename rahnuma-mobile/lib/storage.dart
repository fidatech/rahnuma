import 'dart:convert'; import 'package:shared_preferences/shared_preferences.dart';
class LocalStore {
  static const _token='auth_token', _drafts='property_drafts', _cache='public_property_cache';
  Future<String?> token() async => (await SharedPreferences.getInstance()).getString(_token);
  Future<void> saveToken(String? value) async {final p=await SharedPreferences.getInstance();if(value==null){await p.remove(_token);}else{await p.setString(_token,value);}}
  Future<List<Map<String,dynamic>>> drafts() async {final p=await SharedPreferences.getInstance();final raw=p.getString(_drafts);if(raw==null)return [];return List<Map<String,dynamic>>.from((jsonDecode(raw) as List).map((e)=>Map<String,dynamic>.from(e)));}
  Future<void> addDraft(Map<String,dynamic> d) async {final all=await drafts();all.add(d);final p=await SharedPreferences.getInstance();await p.setString(_drafts,jsonEncode(all));}
  Future<void> removeDraft(int index) async {final all=await drafts();all.removeAt(index);final p=await SharedPreferences.getInstance();await p.setString(_drafts,jsonEncode(all));}
  Future<void> cacheProperties(List<dynamic> list) async {(await SharedPreferences.getInstance()).setString(_cache,jsonEncode(list));}
  Future<List<dynamic>> cachedProperties() async {final raw=(await SharedPreferences.getInstance()).getString(_cache);return raw==null?[]:jsonDecode(raw) as List<dynamic>;}
}
