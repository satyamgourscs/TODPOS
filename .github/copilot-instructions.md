# TODPOS - Copilot Instructions for AI Agents

## Project Overview
TODPOS is a **Laravel 10-based multi-tenancy POS & ERP system** with modular architecture. It's a subscription-based SaaS supporting multiple businesses with granular permission control, diverse payment gateways, and multi-branch capabilities.

### Key Architecture Patterns
- **Multi-tenancy model**: Each `Business` record isolates data; queries filter by `auth()->user()->business_id`
- **Modular design**: Enabled via `nwidart/laravel-modules`; check `Nwidart\Modules\Facades\Module::find()`
- **Subscription-based access**: `PlanSubscribe` links businesses to feature tiers; use `plan_data()` helper
- **Permission system**: Spatie's `laravel-permission` + custom role-based visibility in `User::$visibility` array
- **Dual authentication**: Web (session) for admin/staff, API (Sanctum) for mobile apps

---

## Critical Developer Workflows

### Running Core Commands
```bash
# Setup: composer install, npm install, migrate, seed
php artisan migrate
php artisan db:seed  # Creates demo data with business_id=1, admin user

# Development
npm run dev          # Vite watch mode for Tailwind CSS + Alpine.js
php artisan serve    # Start dev server

# Testing
php artisan test     # PHPUnit (Unit + Feature suites)
php artisan test --filter=SaleTest

# Cache/Debugging
php artisan cache:clear && php artisan config:clear  # Route to /cache-clear also works
php artisan tinker   # REPL for quick queries

# Database Utilities
php artisan make:migration create_table_name
php artisan make:model ModelName -m  # With migration
```

### Common Demo Credentials
- Admin: `admin@gmail.com` / `password`
- Test business ID: `1` (seeded in `BusinessSeeder`)

---

## Multi-Tenancy & Data Isolation Pattern

**Every resource must respect business_id scoping:**

```php
// ✅ CORRECT: Queries include business_id filter
Sale::where('business_id', auth()->user()->business_id)->get();

// ❌ WRONG: Missing business_id check allows data leakage
Sale::where('status', 1)->get();  // Could access other business sales!

// ✅ Helper: Use plan_data() for subscription features
if (plan_data()->allow_multibranch) { ... }

// ✅ Dynamic permission check
auth()->user()->hasPermission('sales.create')  // Uses visibility JSON
```

**Files to reference:**
- [app/Helpers/Helper.php](app/Helpers/Helper.php#L457-L475) - Contains `plan_data()`, `branch_count()`, `business_currency()`
- [app/Models/User.php](app/Models/User.php#L88-L105) - `hasPermission()` logic
- [app/Models/Business.php](app/Models/Business.php#L1-L40) - Business entity with relationships

---

## Payment Gateway Integration

**Multiple payment adapters in `app/Library/`:**
- Razorpay, PayPal, Stripe, Paystack, Mollie, Flutterwave, Mercado, Paytm, PhonePe, SSL Commerz, etc.

**Flow pattern:**
1. Controller calls `Gateway::make_payment($array)` static method
2. Gateway class creates session data + redirects to payment provider or modal
3. Provider callback posts to gateway's `status()` method
4. Verify signature/payment → save to `session('payment_info')` → redirect success/failed

**Key files:**
- [app/Http/Controllers/PaymentController.php](app/Http/Controllers/PaymentController.php#L52-L80) - Main payment orchestration
- [app/Library/Razorpay.php](app/Library/Razorpay.php#L1-L50) - Example: Full implementation with signature verification
- [routes/web.php](routes/web.php#L28-L57) - All gateway routes (prefix `App\Library`)

---

## Modular Architecture

**Enable/disable features per business using modules:**
```php
// Check if addon is installed AND enabled
if (moduleCheck('MultiBranchAddon')) {
    // MultiBranchAddon exists in Modules/ folder
}

// Feature flag from ProductSetting
product_setting()->modules['show_product_type_single'] ?? false
```

**Module locations:**
- Core modules in `Modules/` directory (e.g., `Modules/Business`, `Modules/Inventory`)
- Status stored in `modules_statuses.json` 
- Dynamic loading via `Nwidart\Modules\Facades\Module`

---

## Permission & Role System

**Role hierarchy:**
- `superadmin` - Global admin access
- `shop-owner` - Business owner (automatic full access to owned business)
- `staff` - Employee with per-module visibility controls
- `affiliator` - Affiliate program participant

**Permission pattern from Spatie:**
```php
// In controllers - use middleware
Route::middleware('permission:sales-create')->post('/sales', [SaleController::class, 'store']);

// Custom user visibility (non-Spatie approach for staff)
$user->visibility = [
    'sales' => ['create' => '1', 'read' => '1', 'update' => '0'],
    'inventory' => ['create' => '1', 'read' => '1'],
];

// Usage in policies
if (!$user->hasPermission('sales.update')) abort(403);
```

**Files:**
- [database/seeders/PermissionSeeder.php](database/seeders/PermissionSeeder.php#L1-L50) - Creates all permissions
- [app/Http/Middleware/CheckPermission.php](app/Http/Middleware/CheckPermission.php) - Custom permission checker

---

## API (Mobile App) vs Web Differences

**API Routes:** `/api/v1/` - Sanctum token-based, JSON responses
- [routes/api.php](routes/api.php) - All API endpoints with `auth:sanctum` middleware
- Token expiration: Check `config/sanctum.php` (`'expiration' => null`)
- `setAccessTokenExpiration()` manually sets DB expiry

**Web Routes:** `/` prefix - Session-based, Blade templates
- Separate controllers in `App\Http\Controllers\Admin\*` vs `App\Http\Controllers\Api\*`
- Different response types (views vs JSON)

**Authentication flow:**
```php
// API: Create token after login
$user->createToken('token_name')->plainTextToken

// Web: Session cookie (handled by middleware)
```

---

## Database & Caching Strategy

**Caching patterns (TTL 5000s by default):**
```php
// Use cache_remember() helper (custom in Helper.php)
$data = cache_remember('key', function () {
    return Database::query();
}, 5000);

// Invalidate on mutations
Cache::forget('plan-data-' . $business_id);

// Business-scoped caches
'branch-count-' . auth()->user()->business_id
'business_currency_' . $business_id
```

**Key tables for multi-tenancy:**
- `businesses` - Main tenant
- `users` - Links to business_id
- `sales`, `purchases`, `products` - All have `business_id` FK
- `plan_subscribes` - Links business to subscription tier
- `options` - Store JSON settings keyed by business

---

## Common File Organization

```
app/
  Http/Controllers/
    Admin/            # Admin panel (requires admin middleware)
    Api/              # Mobile API (Sanctum auth)
    Auth/             # Login/register
    Web/              # Public web + business dashboard
  Library/            # Payment gateways, external integrations
  Models/             # Eloquent models with business_id scoping
  Helpers/
    Helper.php        # Global functions (plan_data, business_currency, etc.)
    HasUploader.php   # File upload trait
  Mail/               # Mailable classes
  Notifications/      # Notification classes
  Exceptions/
    Handler.php       # Global exception handler
config/
  app.php             # Timezone: 'Asia/Dhaka'
  permission.php      # Spatie permission configuration
  installer.php       # SaaS installer requirements
database/
  migrations/         # Create tables with business_id FK
  seeders/            # PermissionSeeder, BusinessSeeder, GatewaySeeder
routes/
  api.php             # /api/v1/* endpoints
  web.php             # Public & admin routes
  admin.php           # Admin routes (auto-prefixed)
  auth.php            # Auth routes
resources/
  views/
    admin/            # Admin Blade templates
    payments/         # Payment gateway views (Razorpay modal, Paystack form)
```

---

## Key Conventions & Patterns

### Request Validation
```php
// All FormRequests in app/Http/Requests/
// Use namespace: App\Http\Requests\Api\StoreProductRequest

$request->validate([
    'name' => 'required|string|max:255',
    'price' => 'required|numeric|min:0',
]);
```

### API Response Format
```php
// Standard JSON response
return response()->json([
    'message' => __('Resource created successfully'),
    'data' => $resource,
    'status' => 'success'
], 201);

// Error response
return response()->json([
    'message' => __('Validation failed'),
    'errors' => $validator->errors()
], 422);
```

### Localization
```php
// 60+ language files in lang/ directory (am.json, ar.json, bn.json, en.json, etc.)
// Use helper: __('Key name') or __('products.created')
__('The provided password is incorrect.')
```

### File Uploads
- Use `HasUploader` trait in controllers
- Saved to `public/uploads/`
- Pattern: `uploads/YY/MM/timestamp-random.extension`

### Demo Mode
- Check middleware: `app/Http/Middleware/DemoMode.php`
- When `env('DEMO_MODE', false)` is true, destructive actions return 499 error
- Used for public demo instance

---

## Troubleshooting Common Issues

### "SQLSTATE[42S02]: Table not found"
→ Run `php artisan migrate` or check if service is using wrong `business_id` scope

### "Unauthorized" on API calls
→ Verify token via `auth:sanctum` middleware and `Authorization: Bearer token` header

### Missing payment gateway response
→ Check session keys: `session('razorpay_credentials')`, `session('payment_info')` exist before rendering view

### Permission denied on admin routes
→ Verify user role (must not be 'shop-owner'/'staff') or check `DemoMode` middleware blocklist

### Business data visible to wrong user
→ Audit queries for missing `where('business_id', ...)` filter - common in joins

---

## Resources
- [Laravel Docs](https://laravel.com/docs/10)
- [Spatie Permissions](https://spatie.be/docs/laravel-permission/v6/introduction)
- [Sanctum (API Auth)](https://laravel.com/docs/10/sanctum)
- [nwidart/laravel-modules](https://nwidart.com/laravel-modules/v10/installation-and-setup)
- Project Demo: `admin@gmail.com` / `password`
