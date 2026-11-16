<?php
return [
 'government_mode'=>(bool)env('GOVERNMENT_MODE',false),
 'sale_baywafa_rate'=>(float)env('SALE_BAYWAFA_COMMISSION_RATE',0.015),
 'rent_multiplier'=>(float)env('RENT_COMMISSION_MULTIPLIER',0.5),
 'base_currency'=>env('PLATFORM_CURRENCY','AFN'),
 'currencies'=>['AFN','USD','EUR','PKR','IRR','AED','CNY'],
 'property_types'=>['house','apartment','shop','office','land','warehouse','building','factory','garden','hotel','restaurant','other'],
 'transaction_types'=>['sale','rent','bay_wafa'],
];
