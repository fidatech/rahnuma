<?php
namespace App\Support;
class TenantContext { protected static ?int $agencyId=null; public static function set(?int $id):void{self::$agencyId=$id;} public static function id():?int{return self::$agencyId;} public static function clear():void{self::$agencyId=null;} }
