<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController,MarketplaceController};
use App\Http\Controllers\Tenant\{AccountingController,CooperationController,DashboardController as TenantDashboard,LookupController,PropertyController,QuickCreateController,RequirementController,ResourceController,SettingsController,StaffController,TransactionController};
use App\Http\Controllers\Platform\{AgencyController as PlatformAgencyController,DashboardController as PlatformDashboard,MarketplacePromotionController};
use App\Http\Controllers\Platform as Admin;
use App\Http\Controllers\Tenant\PromotionRequestController;
use App\Http\Controllers\Government\{DashboardController as GovernmentDashboard,RegistryController};
Route::get('/',[MarketplaceController::class,'home'])->name('home');
Route::get('/properties',[MarketplaceController::class,'properties'])->name('marketplace.properties');
Route::get('/properties/{property}/quick',[MarketplaceController::class,'quickProperty'])->name('marketplace.property.quick');
Route::get('/properties/{property}',[MarketplaceController::class,'property'])->name('marketplace.property');
Route::post('/properties/{property}/inquiry',[MarketplaceController::class,'inquiry'])->name('marketplace.inquiry');
Route::get('/rahnama/{agency:slug}',[MarketplaceController::class,'agency'])->name('marketplace.agency');
Route::get('/nearby-rahnamas',[MarketplaceController::class,'nearby'])->name('marketplace.nearby');
Route::get('/locations/districts/{province}',[MarketplaceController::class,'districts'])->name('marketplace.districts');
Route::get('/locations/areas/{district}',[MarketplaceController::class,'areas'])->name('marketplace.areas');
Route::get('/rahnamas',[MarketplaceController::class,'agencies'])->name('marketplace.agencies');
Route::get('/verify',[MarketplaceController::class,'verify'])->name('verify.form');
Route::get('/verify/{code}',[MarketplaceController::class,'verify'])->name('verify');
Route::get('/login',[AuthController::class,'showLogin'])->name('login');Route::post('/login',[AuthController::class,'login']);Route::post('/logout',[AuthController::class,'logout'])->middleware('auth')->name('logout');Route::get('/locale/{locale}',[AuthController::class,'locale'])->name('locale');
Route::middleware(['auth','tenant','tenant.access'])->prefix('workspace')->name('tenant.')->group(function(){
 Route::get('/',TenantDashboard::class)->name('dashboard');
 Route::get('lookup/{type}',LookupController::class)->whereIn('type',['owners','customers','properties','transactions','users'])->name('lookup');
 Route::resource('properties',PropertyController::class)->except('show');Route::get('properties/{property}/quick',[PropertyController::class,'quick'])->name('properties.quick');Route::get('properties/{property}/matches',[PropertyController::class,'matches'])->name('properties.matches');
 Route::get('requirements',[RequirementController::class,'index'])->name('requirements.index');Route::post('requirements',[RequirementController::class,'store'])->name('requirements.store');Route::get('requirements/{requirement}/matches',[RequirementController::class,'matches'])->name('requirements.matches');
 Route::resource('transactions',TransactionController::class)->only(['index','create','store','show']);Route::get('transactions/{transaction}/quick',[TransactionController::class,'quick'])->name('transactions.quick');
 Route::post('quick-create/owner',[QuickCreateController::class,'owner'])->name('quick.owner');
 Route::post('quick-create/customer',[QuickCreateController::class,'customer'])->name('quick.customer');
 Route::post('quick-create/property',[QuickCreateController::class,'property'])->name('quick.property');
 Route::get('accounting',[AccountingController::class,'index'])->name('accounting');Route::get('ledger',[AccountingController::class,'ledger'])->name('ledger');
 Route::get('staff',[StaffController::class,'index'])->name('staff.index');Route::post('staff',[StaffController::class,'store'])->name('staff.store');Route::post('staff/{user}/toggle',[StaffController::class,'toggle'])->name('staff.toggle');
 Route::get('cooperation',[CooperationController::class,'index'])->name('cooperation.index');Route::get('cooperation/lookup-properties',[CooperationController::class,'lookupProperties'])->name('cooperation.lookup-properties');Route::post('cooperation',[CooperationController::class,'store'])->name('cooperation.store');Route::post('cooperation/{id}/respond',[CooperationController::class,'respond'])->name('cooperation.respond');
 Route::get('promotions',[PromotionRequestController::class,'index'])->name('promotions.index');Route::post('promotions',[PromotionRequestController::class,'store'])->name('promotions.store');Route::delete('promotions/{promotionRequest}',[PromotionRequestController::class,'destroy'])->name('promotions.destroy');Route::get('promotions/{promotionRequest}/receipt',[PromotionRequestController::class,'receipt'])->name('promotions.receipt');
 Route::get('settings',[SettingsController::class,'edit'])->name('settings.edit');
 Route::post('settings/company',[SettingsController::class,'updateCompany'])->name('settings.company');
 Route::post('settings/marketplace',[SettingsController::class,'updateMarketplace'])->name('settings.marketplace');
 Route::post('settings/profile',[SettingsController::class,'updateProfile'])->name('settings.profile');
 Route::post('settings/password',[SettingsController::class,'updatePassword'])->name('settings.password');
 Route::get('{resource}/{id}/quick',[ResourceController::class,'quick'])->whereIn('resource',['owners','customers','leads','visits','payments','expenses'])->name('resource.quick');
 Route::get('{resource}',[ResourceController::class,'index'])->whereIn('resource',['owners','customers','leads','visits','payments','expenses'])->name('resource.index');
 Route::post('{resource}',[ResourceController::class,'store'])->whereIn('resource',['owners','customers','leads','visits','payments','expenses'])->name('resource.store');
 Route::delete('{resource}/{id}',[ResourceController::class,'destroy'])->whereIn('resource',['owners','customers','leads','visits','payments','expenses'])->name('resource.destroy');
});
Route::middleware(['auth','role:platform_admin'])->prefix('platform')->name('platform.')->group(function(){Route::get('/',PlatformDashboard::class)->name('dashboard');Route::get('/agencies',[PlatformAgencyController::class,'index'])->name('agencies');Route::post('/agencies',[PlatformAgencyController::class,'store'])->name('agencies.store');Route::post('/agencies/{agency}/toggle',[PlatformAgencyController::class,'toggle'])->name('agencies.toggle');Route::get('/marketplace',[MarketplacePromotionController::class,'index'])->name('marketplace');Route::post('/marketplace/promote',[MarketplacePromotionController::class,'store'])->name('marketplace.promote');Route::post('/marketplace/stop',[MarketplacePromotionController::class,'stop'])->name('marketplace.stop');Route::post('/marketplace/prices',[MarketplacePromotionController::class,'prices'])->name('marketplace.prices');Route::post('/marketplace/requests/{promotionRequest}/approve',[MarketplacePromotionController::class,'approve'])->whereNumber('promotionRequest')->name('marketplace.requests.approve');Route::post('/marketplace/requests/{promotionRequest}/reject',[MarketplacePromotionController::class,'reject'])->whereNumber('promotionRequest')->name('marketplace.requests.reject');Route::get('/marketplace/requests/{promotionRequest}/receipt',[MarketplacePromotionController::class,'receipt'])->whereNumber('promotionRequest')->name('marketplace.requests.receipt');});
// the rest of the administrator panel
Route::middleware(['auth','role:platform_admin'])->prefix('platform')->name('platform.')->group(function(){
 Route::get('/agencies/{agency}',[PlatformAgencyController::class,'show'])->name('agencies.show');
 Route::put('/agencies/{agency}',[PlatformAgencyController::class,'update'])->name('agencies.update');
 Route::post('/agencies/{agency}/verify',[PlatformAgencyController::class,'verify'])->name('agencies.verify');
 Route::delete('/agencies/{agency}',[PlatformAgencyController::class,'destroy'])->name('agencies.destroy');

 Route::get('/users',[Admin\UserController::class,'index'])->name('users');
 Route::post('/users',[Admin\UserController::class,'store'])->name('users.store');
 Route::put('/users/{user}',[Admin\UserController::class,'update'])->name('users.update');
 Route::post('/users/{user}/password',[Admin\UserController::class,'password'])->name('users.password');
 Route::post('/users/{user}/toggle',[Admin\UserController::class,'toggle'])->name('users.toggle');
 Route::post('/users/{user}/impersonate',[Admin\UserController::class,'impersonate'])->name('users.impersonate');
 Route::delete('/users/{user}',[Admin\UserController::class,'destroy'])->name('users.destroy');

 Route::get('/plans',[Admin\PlanController::class,'index'])->name('plans');
 Route::post('/plans',[Admin\PlanController::class,'store'])->name('plans.store');
 Route::put('/plans/{plan}',[Admin\PlanController::class,'update'])->name('plans.update');
 Route::delete('/plans/{plan}',[Admin\PlanController::class,'destroy'])->name('plans.destroy');

 Route::get('/subscriptions',[Admin\SubscriptionController::class,'index'])->name('subscriptions');
 Route::post('/subscriptions',[Admin\SubscriptionController::class,'store'])->name('subscriptions.store');
 Route::post('/subscriptions/{subscription}/paid',[Admin\SubscriptionController::class,'paid'])->whereNumber('subscription')->name('subscriptions.paid');
 Route::post('/subscriptions/{subscription}/renew',[Admin\SubscriptionController::class,'renew'])->whereNumber('subscription')->name('subscriptions.renew');
 Route::post('/subscriptions/{subscription}/cancel',[Admin\SubscriptionController::class,'cancel'])->whereNumber('subscription')->name('subscriptions.cancel');
 Route::delete('/subscriptions/{subscription}',[Admin\SubscriptionController::class,'destroy'])->whereNumber('subscription')->name('subscriptions.destroy');

 Route::get('/properties',[Admin\CatalogController::class,'properties'])->name('properties');
 Route::post('/properties/{property}/visibility',[Admin\CatalogController::class,'visibility'])->name('properties.visibility');
 Route::delete('/properties/{property}',[Admin\CatalogController::class,'destroyProperty'])->name('properties.destroy');
 Route::get('/transactions',[Admin\CatalogController::class,'transactions'])->name('transactions');

 Route::get('/finance',[Admin\FinanceController::class,'index'])->name('finance');
 Route::post('/finance',[Admin\FinanceController::class,'store'])->name('finance.store');
 Route::delete('/finance/{entry}',[Admin\FinanceController::class,'destroy'])->whereNumber('entry')->name('finance.destroy');
 Route::post('/finance/kinds',[Admin\FinanceController::class,'storeKind'])->name('finance.kinds.store');
 Route::put('/finance/kinds/{kind}',[Admin\FinanceController::class,'updateKind'])->name('finance.kinds.update');
 Route::delete('/finance/kinds/{kind}',[Admin\FinanceController::class,'destroyKind'])->name('finance.kinds.destroy');

 Route::get('/locations',[Admin\LocationController::class,'index'])->name('locations');
 Route::post('/locations',[Admin\LocationController::class,'store'])->name('locations.store');
 Route::put('/locations/{level}/{id}',[Admin\LocationController::class,'update'])->whereNumber('id')->name('locations.update');
 Route::delete('/locations/{level}/{id}',[Admin\LocationController::class,'destroy'])->whereNumber('id')->name('locations.destroy');

 Route::get('/audit',[Admin\SystemController::class,'audit'])->name('audit');
 Route::get('/settings',[Admin\SystemController::class,'settings'])->name('settings');
 Route::post('/settings',[Admin\SystemController::class,'saveSettings'])->name('settings.save');
 Route::get('/system',[Admin\SystemController::class,'system'])->name('system');
 Route::post('/system/cache',[Admin\SystemController::class,'clearCache'])->name('system.cache');
 Route::get('/account',[Admin\SystemController::class,'account'])->name('account');
 Route::post('/account',[Admin\SystemController::class,'saveAccount'])->name('account.save');
 Route::post('/account/password',[Admin\SystemController::class,'savePassword'])->name('account.password');
});
// the list of alerts of the top bar, fetched again every minute
Route::get('/notifications',fn()=>view('partials.notifications'))->middleware('auth')->name('notifications');
// back from "sign in as" to the administrator account
Route::post('/impersonate/stop',[Admin\UserController::class,'stopImpersonating'])->middleware('auth')->name('impersonate.stop');
Route::middleware(['auth','role:government_admin,inspector'])->prefix('government')->name('government.')->group(function(){Route::get('/',GovernmentDashboard::class)->name('dashboard');Route::get('/licenses',[RegistryController::class,'licenses'])->name('licenses');Route::post('/licenses',[RegistryController::class,'storeLicense'])->name('licenses.store');Route::get('/setta',[RegistryController::class,'setta'])->name('setta');Route::post('/setta',[RegistryController::class,'storeSettaBook'])->name('setta.store');Route::get('/inspections',[RegistryController::class,'inspections'])->name('inspections');Route::post('/inspections',[RegistryController::class,'storeInspection'])->name('inspections.store');Route::get('/violations',[RegistryController::class,'violations'])->name('violations');Route::post('/violations',[RegistryController::class,'storeViolation'])->name('violations.store');});
