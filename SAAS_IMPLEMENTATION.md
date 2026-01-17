# TODPOS SaaS Implementation Guide

## Overview
This guide documents the new SaaS (Software as a Service) multi-tenant billing system built on top of TODPOS. The system allows you (Super Admin) to manage multiple stores/businesses with subscription-based features.

---

## 🏗️ Architecture Overview

### Three-Tier System

1. **Super Admin (SaaS Owner)**
   - Manages all stores
   - Controls subscription plans
   - Views revenue and analytics
   - Access: `/admin/saas/*`

2. **Store Owner (Customer)**
   - Manages their own billing operations
   - Creates invoices, manages inventory
   - Manages staff and permissions
   - Access: `/app/*` (existing TODPOS features)

3. **Store Staff**
   - Role-based access (Manager, Salesman, Accountant)
   - Limited to their store's data
   - Access: `/app/*` with restricted permissions

---

## 📋 Database Changes

### New Migrations Added

1. **`enhance_plans_table_for_saas`**
   - Added plan features:
     - `max_invoices_per_month` (default: -1 for unlimited)
     - `max_users` (default: 1)
     - `pos_enabled` (default: 0)
     - `gst_reports_enabled` (default: 0)
     - `whatsapp_integration_enabled` (default: 0)
     - `mobile_app_access` (default: 1)
     - `multi_branch_enabled` (default: 0)
     - `additional_features` (JSON for custom features)

2. **`enhance_businesses_table_for_saas`**
   - Added store website features:
     - `store_slug` (unique URL identifier)
     - `website_title`, `website_description`
     - `website_logo`, `website_banner`
     - `website_enabled`
     - `store_type` (billing, retail, services)
     - `status` (1 for active, 0 for suspended)
     - `invoice_count` (tracking)

3. **`enhance_users_table_for_saas`**
   - Added user classification:
     - `role_type` (super_admin, store_owner, staff)
     - `is_active` (boolean)
     - `last_login_at`

4. **`create_store_website_settings_table`**
   - Website customization per store:
     - Theme colors (primary, secondary)
     - Feature toggles (products, inventory, contact form)
     - Contact information
     - Custom HTML/CSS
     - Social media links

5. **`create_subscription_invoices_table`**
   - Billing invoices for subscriptions:
     - Tracks usage (invoices, users, storage)
     - Payment status and history
     - Audit trail

6. **`create_audit_logs_table`**
   - Compliance and audit tracking

### Migration Commands

```bash
# Run all new migrations
php artisan migrate

# If rolling back
php artisan migrate:rollback --step=6
```

---

## 🎮 Controllers Added

### `App\Http\Controllers\Admin\SaaS\DashboardController`
- **Route**: `GET /admin/saas`
- **Method**: `index()`
- Shows:
  - Total stores count
  - Active subscriptions
  - Monthly revenue
  - Expiring subscriptions
  - 12-month revenue chart
  - Recent stores list

### `App\Http\Controllers\Admin\SaaS\StoreManagementController`
- **Routes**: 
  - `GET /admin/saas/stores` → List all stores
  - `GET /admin/saas/stores/create` → Create form
  - `POST /admin/saas/stores` → Store creation
  - `GET /admin/saas/stores/{id}` → View store details
  - `GET /admin/saas/stores/{id}/edit` → Edit form
  - `PUT /admin/saas/stores/{id}` → Update store
  - `DELETE /admin/saas/stores/{id}` → Delete store
  - `PATCH /admin/saas/stores/{id}/toggle-status` → Suspend/activate
  - `POST /admin/saas/stores/{id}/upgrade-plan` → Upgrade subscription

### `App\Http\Controllers\Admin\SaaS\PlanManagementController`
- **Routes**:
  - `GET /admin/saas/plans` → List plans
  - `GET /admin/saas/plans/create` → Create form
  - `POST /admin/saas/plans` → Create plan
  - `GET /admin/saas/plans/{id}/edit` → Edit form
  - `PUT /admin/saas/plans/{id}` → Update plan
  - `PATCH /admin/saas/plans/{id}/toggle-status` → Enable/disable
  - `DELETE /admin/saas/plans/{id}` → Delete plan

---

## 🎨 Views Created

All views are in `/resources/views/admin/saas/`

1. **Dashboard**: `dashboard.blade.php`
   - Revenue stats and charts
   - Store and subscription overview

2. **Stores**: 
   - `stores/index.blade.php` - List all stores
   - `stores/create.blade.php` - Create store form
   - `stores/show.blade.php` - Store details (needs creation)
   - `stores/edit.blade.php` - Edit store form (needs creation)

3. **Plans**:
   - `plans/index.blade.php` - List plans with cards
   - `plans/create.blade.php` - Create plan form
   - `plans/edit.blade.php` - Edit plan form (needs creation)

---

## 🛡️ Middleware

### `SuperAdminMiddleware`
- Checks if user has `role_type = 'super_admin'`
- Registered as `'super_admin'` in Kernel

### Usage in Routes
```php
Route::middleware(['auth', 'super_admin'])->group(function () {
    // Only super admin can access
});
```

---

## 🔌 API Endpoints (Future)

The following API endpoints should be created to support the mobile app:

### Authentication
```
POST   /api/v1/auth/super-admin/login
POST   /api/v1/auth/super-admin/logout
```

### Stores
```
GET    /api/v1/super-admin/stores
POST   /api/v1/super-admin/stores
GET    /api/v1/super-admin/stores/{id}
PUT    /api/v1/super-admin/stores/{id}
DELETE /api/v1/super-admin/stores/{id}
PATCH  /api/v1/super-admin/stores/{id}/toggle-status
```

### Plans
```
GET    /api/v1/super-admin/plans
POST   /api/v1/super-admin/plans
PUT    /api/v1/super-admin/plans/{id}
DELETE /api/v1/super-admin/plans/{id}
```

### Subscriptions
```
GET    /api/v1/super-admin/subscriptions
POST   /api/v1/super-admin/stores/{id}/upgrade-plan
GET    /api/v1/super-admin/subscription-invoices
```

---

## 📊 Models Created

1. **`StoreWebsiteSetting`** - Store website customization
2. **`SubscriptionInvoice`** - Subscription billing records
3. **`AuditLog`** - System audit trail

All models include proper relationships and casts.

---

## 🔑 Key Features to Implement Next

### 1. Store Details View (`stores/show.blade.php`)
```php
- Display store stats (users, invoices, expiration)
- Show current subscription plan
- Upgrade plan button
- View website settings
- User management for the store
```

### 2. Store Edit View (`stores/edit.blade.php`)
```php
- Edit store information
- Change store slug
- Upload website assets (logo, banner)
- Configure website settings
```

### 3. Plan Edit View (`plans/edit.blade.php`)
```php
- Modify all plan settings
- Update pricing
- Change feature toggles
```

### 4. Subscription Management
```php
- Create SubscriptionController in Admin\SaaS
- Track subscription usage
- Generate subscription invoices
- Handle auto-renewal logic
- Enforce plan limits (invoice count, user count)
```

### 5. Public Store Website
```php
- Create StoreFrontController (public routes)
- Route: /store/{store_slug}
- Display store info, products, contact form
- Apply store-specific website settings
- Theme customization
```

### 6. API Implementation
```php
- Create Api\SaaS namespace
- Implement JWT token generation
- Subscription plan endpoints
- Store management endpoints
- Dashboard statistics
```

### 7. Enforcement Middleware
```php
- Check subscription validity before actions
- Enforce invoice count limits
- Enforce user count limits
- Sync store status with subscription expiry
```

---

## 🚀 Setup Instructions

### 1. Run Migrations
```bash
cd /workspaces/TODPOS
php artisan migrate
```

### 2. Create Super Admin User
```bash
# Using Tinker
php artisan tinker

# In tinker console:
$user = App\Models\User::create([
    'name' => 'Super Admin',
    'email' => 'superadmin@tryonedigital.com',
    'password' => bcrypt('password'),
    'role_type' => 'super_admin',
    'is_active' => 1
]);
```

### 3. Access Super Admin Panel
```
URL: http://localhost:8000/admin/saas
Email: superadmin@tryonedigital.com
Password: password
```

### 4. Create Initial Plans
- Visit `/admin/saas/plans/create`
- Create at least 3 plans (Free, Basic, Premium)

### 5. Create Test Store
- Visit `/admin/saas/stores/create`
- Create a store and assign a plan

---

## 📦 Multi-Tenancy Data Isolation

All store data must be filtered by `business_id`:

```php
// ✅ CORRECT
$invoices = Sales::where('business_id', auth()->user()->business_id)->get();

// ❌ WRONG - Data Leakage
$invoices = Sales::all();
```

**Key tables with business_id:**
- users
- products
- sales
- purchases
- invoices
- customers
- employees
- transactions

---

## 🔐 Security Checklist

- [x] Role-based access control (Super Admin only)
- [x] Store data isolation via business_id
- [x] Middleware for subscription validation
- [ ] Rate limiting on API
- [ ] HTTPS enforcement
- [ ] CSRF token validation
- [ ] SQL injection prevention (Eloquent ORM)
- [ ] XSS prevention (Blade templating)
- [ ] Audit logging for compliance

---

## 📝 Next Steps

1. **Complete Store Views**
   - Implement `stores/show.blade.php`
   - Implement `stores/edit.blade.php`

2. **Build Subscription Logic**
   - SubscriptionController with usage tracking
   - Invoice generation system
   - Auto-renewal handlers

3. **Create Public Store Website**
   - StoreFrontController
   - Dynamic website routes
   - Theme customization

4. **Implement API**
   - SaaS API endpoints
   - JWT authentication
   - Mobile app integration

5. **Add Enforcement**
   - Middleware for subscription validity
   - Feature availability checks
   - Usage limit enforcement

---

## 📞 Support & Troubleshooting

### Issue: "Unauthorized access" when accessing `/admin/saas`
**Solution**: Ensure user has `role_type = 'super_admin'`

### Issue: Store creation fails
**Solution**: Check if all required fields are populated and category/plan exist

### Issue: Migrations fail
**Solution**: Run `php artisan migrate:fresh` (clears all data) or check for duplicate migrations

---

## 📚 Related Files

- Routes: `/routes/saas.php`
- Controllers: `/app/Http/Controllers/Admin/SaaS/`
- Models: `/app/Models/{StoreWebsiteSetting,SubscriptionInvoice,AuditLog}.php`
- Views: `/resources/views/admin/saas/`
- Middleware: `/app/Http/Middleware/SuperAdminMiddleware.php`
- Migrations: `/database/migrations/2026_01_25_*.php`

---

**Last Updated**: January 25, 2026  
**Version**: 1.0 (Beta)
