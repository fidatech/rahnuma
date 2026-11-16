import 'dart:convert'; import 'package:http/http.dart' as http; import 'config.dart'; import 'storage.dart';
class ApiException implements Exception {final String message;ApiException(this.message);@override String toString()=>message;}
class Api {
  final LocalStore store; Api(this.store);
  Future<Map<String,String>> _headers() async {final t=await store.token();return {'Accept':'application/json','Content-Type':'application/json',if(t!=null)'Authorization':'Bearer $t'};}
  Future<dynamic> get(String path) async {final r=await http.get(Uri.parse('${AppConfig.apiBase}$path'),headers:await _headers());return _decode(r);}
  Future<dynamic> post(String path,Map<String,dynamic> body) async {final r=await http.post(Uri.parse('${AppConfig.apiBase}$path'),headers:await _headers(),body:jsonEncode(body));return _decode(r);}
  dynamic _decode(http.Response r){dynamic d;try{d=jsonDecode(r.body);}catch(_){d={'message':r.body};}if(r.statusCode<200||r.statusCode>=300)throw ApiException(d is Map?(d['message']??'Request failed'):'Request failed');return d;}
  Future<Map<String,dynamic>> login(String email,String password) async {final d=Map<String,dynamic>.from(await post('/login',{'email':email,'password':password}));await store.saveToken(d['token']);return d;}
  Future<void> logout() async {try{await post('/logout',{});}catch(_){}await store.saveToken(null);}
}
