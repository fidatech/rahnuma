<?php
namespace App\Services;
class CommissionService { public function calculate(string $type,float $amount):array { if($type==='rent'){ $total=round($amount*(float)config('rahnuma.rent_multiplier'),2); } else { $total=round($amount*(float)config('rahnuma.sale_baywafa_rate'),2);} return ['total'=>$total,'side_a'=>round($total/2,2),'side_b'=>round($total/2,2)]; } }
