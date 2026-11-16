<?php
namespace App\Providers;
use App\Models\Setting;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider {
    public function register():void{ require_once app_path('Support/helpers.php'); }

    /** Settings saved by the administrator in the panel take the place of the values of .env. */
    public function boot():void{
        if ($this->app->runningInConsole() && ! $this->app->runningUnitTests()) return;

        $map = [
            'contact_phone' => 'rahnuma.contact.phone', 'contact_email' => 'rahnuma.contact.email',
            'contact_address' => 'rahnuma.contact.address', 'contact_hours' => 'rahnuma.contact.hours',
            'official_emblem' => 'rahnuma.official_emblem',
        ];
        $saved = Setting::map();
        foreach ($map as $key => $config) {
            if (array_key_exists($key, $saved) && $saved[$key] !== null && $saved[$key] !== '') config([$config => $saved[$key]]);
        }
        if (isset($saved['government_mode'])) config(['rahnuma.government_mode' => $saved['government_mode'] === '1']);
        if (isset($saved['sale_baywafa_rate']) && is_numeric($saved['sale_baywafa_rate'])) config(['rahnuma.sale_baywafa_rate' => (float) $saved['sale_baywafa_rate']]);
        if (isset($saved['rent_multiplier']) && is_numeric($saved['rent_multiplier'])) config(['rahnuma.rent_multiplier' => (float) $saved['rent_multiplier']]);
    }
}
