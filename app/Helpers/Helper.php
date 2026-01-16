<?php

use App\Models\User;
use App\Models\Branch;
use App\Models\Option;
use App\Models\Business;
use App\Models\Currency;
use App\Models\UserCurrency;
use App\Models\PlanSubscribe;
use App\Models\ProductSetting;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Notifications\SendNotification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;

function cache_remember(string $key, callable $callback, int $ttl = 5000): mixed
{
    return cache()->remember($key, env('CACHE_LIFETIME', $ttl), $callback);
}

function get_option($key)
{
    return cache_remember($key, function () use ($key) {
        return Option::where('key', $key)->first()->value ?? [];
    });
}

function invoice_setting()
{
    return get_option('invoice_setting_' . auth()->user()->business_id);
}

function product_setting()
{
    $businessId = auth()->user()->business_id;
    $cacheKey = 'product_setting_' . $businessId;

    return cache()->remember($cacheKey, 60, function () use ($businessId) {
        $productSetting = ProductSetting::where('business_id', $businessId)->first();

        if ($productSetting) {
            $productSetting->modules = $productSetting->modules ?? [];
        }

        return $productSetting;
    });
}

function is_module_enabled(?array $modules, string $key): bool
{
    // Keys that should default to true if not set
    $defaultTrueKeys = [
        'show_product_type_single',
        'show_product_category',
        'show_alert_qty',
        'show_product_unit',
        'show_exclusive_price',
        'show_inclusive_price',
        'show_profit_percent',
        'show_product_sale_price',
        'show_product_price',
        'show_product_stock',
    ];

    if (in_array($key, $defaultTrueKeys)) {
        return !isset($modules[$key]) || (bool)$modules[$key];
    }

    // All other keys: show only if explicitly set to true
    return isset($modules[$key]) && (bool)$modules[$key];
}

function formatted_date(?string $date = null, string $format = 'd M, Y'): ?string
{
    return !empty($date) ? Date::parse($date)->format($format) : null;
}

function formatted_time(?string $time = null, string $format = 'h:i A'): ?string
{
    return !empty($time) ? Date::parse($time)->format($format) : null;
}

function sendNotification($id, $url, $message, $user = null)
{
    $notify = [
        'id' => $id,
        'url' => $url,
        'user' => $user,
        'message' => $message,
    ];

    $notify_user = User::where('role', 'superadmin')->first();
    Notification::send($notify_user, new SendNotification($notify));
}

function sendNotifyToUser($id, $url, $message, $user)
{
    $notify = [
        'id' => $id,
        'url' => $url,
        'user' => $user,
        'message' => $message,
    ];

    $notify_user = User::where('business_id', $user)->first();
    Notification::send($notify_user, new SendNotification($notify));
}

function currency_format($amount, $type = "icon", $decimals = 2, $currency = null, $abbreviate = false, $apply_rounding = false)
{
    $currency = $currency ?? default_currency();

    if ($apply_rounding) {
        $amount = sale_rounding((float)$amount);
    }

    if ($abbreviate) {
        $amount = format_number($amount, $decimals);
    } else {
        $has_fraction = $amount != floor($amount);
        $amount = $has_fraction ? number_format($amount, $decimals) : number_format($amount, 0);
    }

    if ($type == "icon" || $type == "symbol") {
        if ($currency->position == "right") {
            return $amount . $currency->symbol;
        } else {
            return $currency->symbol . $amount;
        }
    } else {
        if ($currency->position == "right") {
            return $amount . ' ' . $currency->code;
        } else {
            return $currency->code . ' ' . $amount;
        }
    }
}

function format_number(float|int $number, int $decimals = 2): string
{
    if ($number >= 1e9) {
        return remove_trailing_zeros($number / 1e9, $decimals) . "B";
    } elseif ($number >= 1e6) {
        return remove_trailing_zeros($number / 1e6, $decimals) . "M";
    } elseif ($number >= 1e3) {
        return remove_trailing_zeros($number / 1e3, $decimals) . "K";
    } else {
        return remove_trailing_zeros($number, $decimals);
    }
}

function remove_trailing_zeros(float|int $number, int $decimals = 2): string
{
    return rtrim(rtrim(number_format($number, $decimals, '.', ''), '0'), '.');
}


function amountInWords(float $amount, int $decimals = 2): string
{
    if (!extension_loaded('intl')) {
        return '';
    }

    $has_fraction = fmod($amount, 1) != 0;
    $amount = $has_fraction ? round($amount, $decimals) : round($amount);

    $formatter = new \NumberFormatter('en_US', \NumberFormatter::SPELLOUT);
    $words = $formatter->format($amount);

    return $words . ' ' . (business_currency()->name ?? '');
}



function convert_money($amount, $currency)
{
    if ($currency->code == default_currency('code') || $amount == 0) {
        return round($amount, 2);
    } else {
        return $amount * $currency->rate / default_currency()->rate;
    }
}

function default_currency($key = null, Currency $currency = null): object|int|string
{
    $currency = $currency ?? cache_remember('default_currency', function () {
        $currency = Currency::whereIsDefault(1)->first();

        if (!$currency) {
            $currency = (object)['name' => 'US Dollar', 'code' => 'USD', 'rate' => 1, 'symbol' => '$', 'position' => 'left', 'status' => true, 'is_default' => true,];
        }

        return $currency;
    });

    return $key ? $currency->$key : $currency;
}

function dueCollectMessage($data, $party, $business_name, $invoiceNumber)
{
    if ($invoiceNumber) {
        $message = "Dear " . $party->name . "
We have received a payment of: " . $data->payDueAmount . "
Your Total Previous Due: " . $party->due . "
Thanks, " . $business_name;
    } else {
        $message = "Dear " . $party->name . "
Your Invoice : " . $data->invoiceNumber . "
We have received a payment of: " . $data->payDueAmount . "
Your Total Previous Due: " . $party->due . "
Thanks, " . $business_name;
    }

    return $message;
}

function saleMessage($sale, $party, $business_name)
{
    $message = "Dear " . $party->name . "
Your Invoice No: " . $sale->invoiceNumber . "
Total Bill: " . $sale->totalAmount . "
Paid: " . $sale->paidAmount . "
Due: " . $sale->dueAmount . "
Total Previous Due: " . $party->due . "
Thanks, " . $business_name;

    return $message;
}

function dueMessage($party, $business_name)
{
    $message = "Dear " . $party->name . "
You have pending payment of: " . $party->due . "
Kindly pay it as soon as possible.
Thanks, " . $business_name;

    return $message;
}

function sendMessage($numbers, $message)
{
    $settings = get_option('sms-settings');
    $response = Http::withHeaders([
        'Authorization' => "Bearer " . $settings['api_token'],
        'Content-Type' => "application/json",
        'Accept' => "application/json",
    ])->post($settings['api_url'], [
        'recipient' => $numbers,
        'sender_id' => $settings['sender_id'],
        'type' => $settings['type'],
        'message' => $message,
    ]);

    return $response;
}

function restorePublicImages()
{
    if (!env('DEMO_MODE')) {
        return true;
    }

    DB::table('sales')->where('business_id', 1)->delete();
    DB::table('sale_returns')->where('business_id', 1)->delete();
    DB::table('purchases')->where('business_id', 1)->delete();
    DB::table('purchase_returns')->where('business_id', 1)->delete();
    DB::table('due_collects')->where('business_id', 1)->delete();
    DB::table('parties')->where('business_id', 1)->delete();
    DB::table('expense_categories')->where('business_id', 1)->delete();
    DB::table('income_categories')->where('business_id', 1)->delete();

    Artisan::call('db:seed', ['--class' => 'DemoSeeder']);
}

if (!function_exists('formatTimeToWords')) {
    function formatTimeToWords(string|null $time): string
    {
        if (empty($time)) {
            return '0';
        }

        if (!preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $time)) {
            return '0';
        }

        $parts = explode(':', $time);

        $hours = isset($parts[0]) ? (int)$parts[0] : 0;
        $minutes = isset($parts[1]) ? (int)$parts[1] : 0;

        $hourString = $hours == 1 ? 'hour' : 'hours';
        $minuteString = $minutes == 1 ? 'minute' : 'minutes';

        $formattedTime = [];

        if ($hours > 0) {
            $formattedTime[] = "$hours $hourString";
        }

        if ($minutes > 0) {
            $formattedTime[] = "$minutes $minuteString";
        }

        return empty($formattedTime) ? '0' : implode(' and ', $formattedTime);
    }
}


function languages()
{
    return [
        'en' => ['name' => 'English', 'flag' => 'us'],
        'ar' => ['name' => 'Arabic', 'flag' => 'sa'],
        'bn' => ['name' => 'Bengali', 'flag' => 'bd'],
        'zh' => ['name' => 'Chinese', 'flag' => 'cn'],
        'fr' => ['name' => 'French', 'flag' => 'fr'],
        'de' => ['name' => 'German', 'flag' => 'de'],
        'hi' => ['name' => 'Hindi', 'flag' => 'in'],
        'es' => ['name' => 'Spanish', 'flag' => 'es'],
        'ja' => ['name' => 'Japanese', 'flag' => 'jp'],
        'rum' => ['name' => 'Romanian', 'flag' => 'ro'],
        'vi' => ['name' => 'Vietnamese', 'flag' => 'vn'],
        'it' => ['name' => 'Italian', 'flag' => 'it'],
        'th' => ['name' => 'Thai', 'flag' => 'th'],
        'bs' => ['name' => 'Bosnian', 'flag' => 'ba'],
        'nl' => ['name' => 'Dutch', 'flag' => 'nl'],
        'pt' => ['name' => 'Portuguese', 'flag' => 'pt'],
        'pl' => ['name' => 'Polish', 'flag' => 'pl'],
        'he' => ['name' => 'Hebrew', 'flag' => 'il'],
        'hu' => ['name' => 'Hungarian', 'flag' => 'hu'],
        'fi' => ['name' => 'Finnish', 'flag' => 'fi'],
        'el' => ['name' => 'Greek', 'flag' => 'gr'],
        'ko' => ['name' => 'Korean', 'flag' => 'kr'],
        'ms' => ['name' => 'Malay', 'flag' => 'my'],
        'id' => ['name' => 'Indonesian', 'flag' => 'id'],
        'fa' => ['name' => 'Persian', 'flag' => 'ir'],
        'tr' => ['name' => 'Turkish', 'flag' => 'tr'],
        'sr' => ['name' => 'Serbian', 'flag' => 'rs'],
        'km' => ['name' => 'Khmer', 'flag' => 'khm'],
        'uk' => ['name' => 'Ukrainian', 'flag' => 'ua'],
        'lo' => ['name' => 'Lao', 'flag' => 'la'],
        'ru' => ['name' => 'Russian', 'flag' => 'ru'],
        'cs' => ['name' => 'Czech', 'flag' => 'cz'],
        'kn' => ['name' => 'Kannada', 'flag' => 'ka'],
        'mr' => ['name' => 'Marathi', 'flag' => 'mh'],
        'sv' => ['name' => 'Swedish', 'flag' => 'se'],
        'da' => ['name' => 'Danish', 'flag' => 'dk'],
        'ur' => ['name' => 'Urdu', 'flag' => 'pk'],
        'sq' => ['name' => 'Albanian', 'flag' => 'al'],
        'sk' => ['name' => 'Slovak', 'flag' => 'sk'],
        'bur' => ['name' => 'Burmese', 'flag' => 'mm'],
        'ti' => ['name' => 'Tigrinya', 'flag' => 'er'],
        'kz' => ['name' => 'Kazakh', 'flag' => 'kz'],
        'az' => ['name' => 'Azerbaijani', 'flag' => 'az'],
        'zh-cn' => ['name' => 'Chinese (CN)', 'flag' => 'zh-cn'],
        'zh-tw' => ['name' => 'Chinese (TW)', 'flag' => 'zh-tw'],
        'pt-br' => ['name' => 'Portuguese (BR)', 'flag' => 'pt-br'],
        'tz' => ['name' => 'Swahili', 'flag' => 'tz'],
        'ps' => ['name' => 'Pashto', 'flag' => 'af'],
        'prs' => ['name' => 'Dari', 'flag' => 'afdari'],
        'ca' => ['name' => 'Catalan', 'flag' => 'ad'],
        'bt' => ['name' => 'Dzongkha', 'flag' => 'dz'],
        'drcfr' => ['name' => 'Congo (DRC)', 'flag' => 'drc'],
        'cgfr' => ['name' => 'Congo (Republic)', 'flag' => 'cg'],
        'escr' => ['name' => 'Costa Rica (Spanish)', 'flag' => 'cr'],
        'enbw' => ['name' => 'Botswana (English)', 'flag' => 'bw'],
        'bws' => ['name' => 'Botswana (Setswana)', 'flag' => 'bws'],
        'deat' => ['name' => 'Austria(German)', 'flag' => 'at'],
        'enbs' => ['name' => 'Bahamas(English)', 'flag' => 'bs'],
        'arbh' => ['name' => 'Bahrain(Arabic)', 'flag' => 'bh'],
        'pt-ao' => ['name' => 'Angola(Portuguese)', 'flag' => 'ao'],
        'es-ar' => ['name' => 'Argentina(Spanish)', 'flag' => 'ar'],
        'hy' => ['name' => 'Armenian', 'flag' => 'am'],
        'au-en' => ['name' => 'Australia', 'flag' => 'au'],
        'bb-en' => ['name' => 'Barbados(English)', 'flag' => 'bb'],
        'be' => ['name' => 'Belarusian', 'flag' => 'by'],
        'nl-be' => ['name' => 'Belgium(Dutch)', 'flag' => 'be'],
        'bz-en' => ['name' => 'Belize(English)', 'flag' => 'bz'],
        'bj-fr' => ['name' => 'Benin(French)', 'flag' => 'bj'],
        'bo-es' => ['name' => 'Bolivia(Spanish)', 'flag' => 'bo'],
        'bn-ms' => ['name' => 'Brunei(Malay)', 'flag' => 'bn'],
        'bg' => ['name' => 'Bulgarian', 'flag' => 'bg'],
        'bf-fr' => ['name' => 'Burkina Faso(French)', 'flag' => 'bf'],
        'cm-fr' => ['name' => 'Cameroon(French)', 'flag' => 'cm'],
        'ca-en' => ['name' => 'Canada(English)', 'flag' => 'ca'],
        'cl-es' => ['name' => 'Chile(Spanish)', 'flag' => 'cl'],
        'co-es' => ['name' => 'Colombia(Spanish)', 'flag' => 'co'],
        'km-ar' => ['name' => 'Comoros(Arabic)', 'flag' => 'km'],
        'hr' => ['name' => 'Croatian', 'flag' => 'hr'],
        'cu-es' => ['name' => 'Cuba(Spanish)', 'flag' => 'cu'],
        'cy-el' => ['name' => 'Cyprus(Greek)', 'flag' => 'cy'],
        'dj-fr' => ['name' => 'Djibouti(French)', 'flag' => 'dj'],
        'dm-en' => ['name' => 'Dominica(English)', 'flag' => 'dm'],
        'tet' => ['name' => 'Tetum', 'flag' => 'tl'],
        'ec-es' => ['name' => 'Ecuador(Spanish)', 'flag' => 'ec'],
        'eg-ar' => ['name' => 'Egypt(Arabic)', 'flag' => 'eg'],
        'sv-es' => ['name' => 'El Salvador(Spanish)', 'flag' => 'sv'],
        'gq-es' => ['name' => 'Equatorial Guinea(Spanish)', 'flag' => 'gq'],
        'et' => ['name' => 'Estonian', 'flag' => 'ee'],
        'ss' => ['name' => 'Swati', 'flag' => 'sz'],
        'am' => ['name' => 'Amharic', 'flag' => 'et'],
        'fj' => ['name' => 'Fijian', 'flag' => 'fj'],
        'ga-fr' => ['name' => 'Gabon(French)', 'flag' => 'ga'],
        'gm-en' => ['name' => 'Gambia(English)', 'flag' => 'gm'],
        'ka' => ['name' => 'Georgian', 'flag' => 'ge'],
        'gh-en' => ['name' => 'Ghana(English)', 'flag' => 'gh'],
        'gd-en' => ['name' => 'Grenada(English)', 'flag' => 'gd'],
        'gt-en' => ['name' => 'Guatemala(English)', 'flag' => 'gt'],
        'gn-fr' => ['name' => 'Guinea(French)', 'flag' => 'gn'],
        'gy-en' => ['name' => 'Guyana(English)', 'flag' => 'gy'],
        'ht-fr' => ['name' => 'Haiti(French)', 'flag' => 'ht'],
        'hn-es' => ['name' => 'Honduras(Spanish)', 'flag' => 'hn'],
    ];
}

// BUSINESS PANEL

// user role permission
if (!function_exists('visible_permission')) {
    function visible_permission($permission)
    {
        $user = auth()->user();

        // Ensure the user is authenticated and has a business_id
        if (!$user || !$user->business_id) {
            return false;
        }

        // Handle visibility field directly as an array or decode it if it's a string
        $permissions = is_array($user->visibility)
            ? $user->visibility
            : json_decode($user->visibility, true);

        return $permissions[$permission] ?? false;
    }
}

function get_business_option($key)
{
    $cacheKey = "business_setting_" . auth()->user()->business_id;

    return Cache::remember($cacheKey, now()->addDay(), function () use ($key) {
        if ($key == 'business-settings') {
            return Option::where('key', 'business-settings')
                ->whereJsonContains('value->business_id', auth()->user()->business_id)
                ->first()
                ->value ?? null;
        }
        return null;
    });
}

function plan_data($business_id = null)
{
    $business_id = $business_id ?? auth()->user()->business_id;

    return cache_remember('plan-data-' . $business_id, function () use ($business_id) {
        $planSubscribe = PlanSubscribe::with('plan:id,subscriptionName')->where('business_id', $business_id)->latest()->first();

        if ($planSubscribe) {
            $business = Business::findOrFail($planSubscribe->business_id);
            $planSubscribe->will_expire = $business->will_expire;
        }
        return $planSubscribe;
    });
}

function branch_count()
{
    $business_id = auth()->user()->business_id;

    return cache_remember('branch-count-' . $business_id, function () use ($business_id) {
        $totalBranch = Branch::where('business_id', $business_id)->count();

        return $totalBranch;
    });
}

function multibranch_active()
{
    return plan_data()['allow_multibranch'] ?? false;
}

function business_currency($business_id = null)
{
    $business_id = $business_id ?? auth()->user()->business_id;

    return cache_remember("business_currency_{$business_id}", function () use ($business_id) {
        $businessCurrency = UserCurrency::where('business_id', $business_id)->first() ?? Currency::where('is_default', 1)->first();;

        if ($businessCurrency) {
            return (object)[
                'name' => $businessCurrency->name,
                'rate' => $businessCurrency->rate,
                'code' => $businessCurrency->code,
                'symbol' => $businessCurrency->symbol,
                'position' => $businessCurrency->position,
            ];
        }

        return default_currency();
    });
}

function sale_rounding(?float $amount = null, ?string $round_option = null): float|string
{
    $business_id = auth()->user()->business_id;

    // If $round_option is not passed, try to fetch from settings
    if (is_null($round_option)) {
        $round_option = cache_remember("business_sale_rounding_{$business_id}", function () use ($business_id) {
            return Option::where('key', 'business-settings')
                ->whereJsonContains('value->business_id', $business_id)
                ->first()
                ->value['sale_rounding_option'] ?? 'none';
        });
    }

    if (is_null($amount)) {
        return $round_option;
    }

    // Apply rounding if amount is provided
    return match ($round_option) {
        'round_up' => ceil($amount),
        'nearest_whole_number' => round($amount),
        'nearest_0.05' => round($amount * 20) / 20,
        'nearest_0.1' => round($amount * 10) / 10,
        'nearest_0.5' => round($amount * 2) / 2,
        default => $amount,
    };
}

function moduleCheck($module)
{
    $module = Module::find($module);

    if ($module && $module->isEnabled()) {
        return true;
    }

    return false;
}

function remaining_days($date)
{
    $today = \Carbon\Carbon::today();
    $expiry = \Carbon\Carbon::parse($date);
    $diff = $today->diffInDays($expiry, false);

    return $diff > 0 ? "$diff days" : "";
}

// update RemainingBalance
function updateBalance($amount, string $type, $branch_id = null)
{
    $amount = is_numeric($amount) ? (float)$amount : 0;
    $businessId = auth()->user()->business_id;

    // if active branch, then update active branch
    $branch = auth()->user()->active_branch;
    if ($branch) {
        if ($type == 'increment') {
            $branch->increment('branchRemainingBalance', $amount);
        } elseif ($type == 'decrement') {
            $branch->decrement('branchRemainingBalance', $amount);
        }
        return;
    }

    //If branch_id is provided, update that branch
    if ($branch_id) {
        $branch = Branch::find($branch_id);
        if ($branch) {
            if ($type == 'increment') {
                $branch->increment('branchRemainingBalance', $amount);
            } elseif ($type == 'decrement') {
                $branch->decrement('branchRemainingBalance', $amount);
            }
        }
        return;
    }

    // If no branch, update business balance
    $business = Business::find($businessId);
    if ($business) {
        if ($type == 'increment') {
            $business->increment('remainingShopBalance', $amount);
        } elseif ($type == 'decrement') {
            $business->decrement('remainingShopBalance', $amount);
        }
    }
}

function manipulateBranchData($business_id)
{
    $business = auth()->user()->business;
    $shop_owner = User::where(['business_id' => $business_id, 'role' => 'shop-owner'])->firstOrFail();

    $branch = Branch::create([
        'is_main' => 1,
        'email' => $shop_owner->email,
        'name' => $business->companyName,
        'phone' => $business->phoneNumber,
        'address' => $business->address
    ]);

    $updates = [
        'users'            => ['branch_id' => $branch->id, 'where' => ['role' => 'staff']],
        'stocks'           => ['branch_id' => $branch->id, 'where' => ['business_id' => $business_id]],
        'product_settings' => ['branch_id' => $branch->id],
        'sale_returns'     => ['branch_id' => $branch->id],
        'purchase_returns' => ['branch_id' => $branch->id],
        'expenses'         => ['branch_id' => $branch->id],
        'incomes'          => ['branch_id' => $branch->id],
        'sales'            => ['branch_id' => $branch->id],
        'purchases'        => ['branch_id' => $branch->id],
        'due_collects'     => ['branch_id' => $branch->id]
    ];

    foreach ($updates as $table => $data) {
        $query = DB::table($table);
        if (!empty($data['where'])) {
            $query->where($data['where']);
            unset($data['where']);
        }
        $query->update($data);
    }

    if (moduleCheck('HrmAddon')) {
        DB::table('holidays')->update(['branch_id' => $branch->id]);
        DB::table('attendances')->update(['branch_id' => $branch->id]);
        DB::table('leaves')->update(['branch_id' => $branch->id]);
        DB::table('payrolls')->update(['branch_id' => $branch->id]);
        DB::table('employees')->update(['branch_id' => $branch->id]);
    }

    if (moduleCheck('WarehouseAddon')) {
        DB::table('warehouses')->update(['branch_id' => $branch->id]);
        DB::table('transfers')->update([
            'from_branch_id' => $branch->id,
            'to_branch_id'   => $branch->id
        ]);
    }

    return true;
}

function get_root_domain()
{
    $appUrl = env('APP_URL');
    return parse_url($appUrl, PHP_URL_HOST);
}

function checkDomainStatus($domain)
{
    $result = [
        'domain' => $domain,
        'exists' => false,
        'http'   => false,
        'https'  => false,
    ];

    // 1. Check if domain resolves (DNS record exists)
    if (dns_get_record($domain, DNS_A) || dns_get_record($domain, DNS_AAAA)) {
        $result['exists'] = true;

        // 2. Check HTTP (port 80)
        try {
            $response = Http::timeout(5)->get("http://{$domain}");
            if ($response->successful()) {
                $result['http'] = true;
            }
        } catch (\Exception $e) {
            $result['http'] = false;
        }

        // 3. Check HTTPS (port 443)
        try {
            $response = Http::timeout(5)->get("https://{$domain}");
            if ($response->successful()) {
                $result['https'] = true;
            }
        } catch (\Exception $e) {
            $result['https'] = false;
        }
    }

    return $result;
}
