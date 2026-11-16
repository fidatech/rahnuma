class SessionUser {
  final int id; final String name; final String email; final String role; final int? agencyId; final String? agencyName;
  SessionUser({required this.id,required this.name,required this.email,required this.role,this.agencyId,this.agencyName});
  factory SessionUser.fromJson(Map<String,dynamic> j)=>SessionUser(id:j['id']??0,name:j['name']??'',email:j['email']??'',role:j['role']??'agent',agencyId:j['agency_id'],agencyName:j['agency']?['name']);
  bool get isGovernment => role=='government_admin'||role=='inspector';
  bool get isPlatform => role=='platform_admin';
}
class PropertyItem {
  final int id; final String title; final String type; final String transaction; final String status; final String visibility; final String currency; final double price; final double monthlyRent; final String? address; final String? agencyName;
  PropertyItem({required this.id,required this.title,required this.type,required this.transaction,required this.status,required this.visibility,required this.currency,required this.price,required this.monthlyRent,this.address,this.agencyName});
  factory PropertyItem.fromJson(Map<String,dynamic> j)=>PropertyItem(id:j['id']??0,title:j['title']??'',type:j['property_type']??'',transaction:j['transaction_type']??'',status:j['status']??'',visibility:j['visibility']??'',currency:j['currency']??'AFN',price:double.tryParse('${j['price']??0}')??0,monthlyRent:double.tryParse('${j['monthly_rent']??0}')??0,address:j['address'],agencyName:j['agency']?['name']);
  double get displayPrice => transaction=='rent'?monthlyRent:price;
}
