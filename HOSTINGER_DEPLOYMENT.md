# 🚀 HOSTINGER DEPLOYMENT GUIDE - Vision API Fix

## ⚠️ CRITICAL ISSUE FIXED
**Error:** "Image inputs are not supported by this model"  
**Cause:** Using `grok-3` (text-only model) instead of `grok-2-vision-1212` (vision model)

---

## 📋 FILES CHANGED

### 1. **config/image-prompt.php**
- Changed default vision model from `grok-3` to `grok-2-vision-1212`

### 2. **app/Http/Controllers/Admin/SettingController.php**
- Added `grok_vision_model` validation
- Added `grok_vision_model` to settings array
- Added `grok_vision_model` to .env sync
- Updated API test to use text-only model

### 3. **resources/views/admin/settings/index.blade.php**
- Added "Vision Model" field in API Settings tab
- Added critical warning about using vision-capable models

### 4. **database/migrations/2026_03_03_064103_add_grok_vision_model_setting.php**
- Migration to add default vision model setting to database

---

## 🔧 DEPLOYMENT STEPS FOR HOSTINGER

### Step 1: Upload Files via FTP/File Manager

Upload these files to your Hostinger server:

```
✅ config/image-prompt.php
✅ app/Http/Controllers/Admin/SettingController.php
✅ resources/views/admin/settings/index.blade.php
✅ database/migrations/2026_03_03_064103_add_grok_vision_model_setting.php
```

### Step 2: Run Migration via SSH

Connect to your Hostinger server via SSH and run:

```bash
cd /home/your-username/public_html
php artisan migrate
php artisan config:clear
php artisan cache:clear
```

**If you don't have SSH access:**
- Use Hostinger's built-in terminal (if available)
- Or create a temporary web route to run migrations (see Step 2b below)

### Step 2b: Alternative - Run Migration via Web Route (No SSH)

If you don't have SSH access, create this temporary file:

**File:** `public/run-migration.php`

```php
<?php
// REMOVE THIS FILE AFTER RUNNING!
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<h1>Running Migration...</h1>";
echo "<pre>";

// Run migration
$kernel->call('migrate', ['--force' => true]);
echo "\n✅ Migration completed!\n\n";

// Clear cache
$kernel->call('config:clear');
echo "✅ Config cache cleared!\n\n";

$kernel->call('cache:clear');
echo "✅ Application cache cleared!\n\n";

echo "</pre>";
echo "<h2>✅ ALL DONE! DELETE THIS FILE NOW!</h2>";
?>
```

Then visit: `https://trends.rektech.work/run-migration.php`  
**⚠️ DELETE THIS FILE IMMEDIATELY AFTER RUNNING!**

### Step 3: Update Settings in Admin Panel

1. Login to admin panel: `https://trends.rektech.work/admin/login`
2. Go to **Settings** → **API Settings** tab
3. Find the **"Vision Model"** field
4. Ensure it's set to: `grok-2-vision-1212`
5. Click **"Save Settings"**

### Step 4: Verify the Fix

1. Go to frontend: `https://trends.rektech.work`
2. Upload an image
3. Try to analyze or transform it
4. **Error should be gone!** ✅

---

## 🔍 VERIFICATION CHECKLIST

After deployment, verify these:

- [ ] Migration ran successfully
- [ ] Cache cleared
- [ ] Admin Settings shows "Vision Model" field
- [ ] Vision Model is set to `grok-2-vision-1212`
- [ ] Frontend image upload works without errors
- [ ] Image analysis works properly
- [ ] No "Image inputs are not supported" error

---

## 🆘 TROUBLESHOOTING

### Issue: "Settings not updating"
**Solution:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Issue: "Migration already ran"
**Solution:** The migration is safe to run multiple times. It checks if the setting exists.

### Issue: "Still getting the error"
**Check these:**
1. Is the vision model set to `grok-2-vision-1212` in Admin Settings?
2. Did you clear the cache after updating?
3. Check the database `settings` table - is `grok_vision_model` = `grok-2-vision-1212`?

**Manual Database Fix:**
```sql
-- Run this in phpMyAdmin if needed
UPDATE settings 
SET value = 'grok-2-vision-1212' 
WHERE `key` = 'grok_vision_model';
```

### Issue: "Can't access SSH or terminal"
Use the web migration script in Step 2b above.

---

## 📊 DATABASE CHECK

To verify the setting in database (via phpMyAdmin):

```sql
SELECT * FROM settings WHERE `key` = 'grok_vision_model';
```

**Expected result:**
```
key: grok_vision_model
value: grok-2-vision-1212
type: text
group: api
```

If the row doesn't exist or has wrong value, run:

```sql
INSERT INTO settings (`key`, `value`, `type`, `group`, created_at, updated_at) 
VALUES ('grok_vision_model', 'grok-2-vision-1212', 'text', 'api', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    `value` = 'grok-2-vision-1212',
    updated_at = NOW();
```

---

## ⚙️ ENVIRONMENT VARIABLES (Optional)

You can also set this in your `.env` file on Hostinger:

```env
GROK_VISION_MODEL=grok-2-vision-1212
```

**Priority:** Database > .env > Config Default

---

## 🎯 SUPPORTED VISION MODELS

✅ **Working Models (Support Images):**
- `grok-2-vision-1212` ← **RECOMMENDED**
- `grok-vision-beta`

❌ **NOT Working (Text-Only):**
- `grok-3` ← This was causing the error
- `grok-beta`
- `grok-2`

---

## 📝 QUICK REFERENCE

**Admin Panel Path:** `/admin/settings` → API Settings tab  
**Frontend Test:** Upload image at `/my-images`  
**Database Table:** `settings`  
**Config File:** `config/image-prompt.php`  
**Service File:** `app/Services/GrokImageService.php`

---

## 🔐 SECURITY NOTES

- Never commit API keys to Git
- Always use environment variables for sensitive data
- Delete the `run-migration.php` file after use
- Keep your Grok API key secure

---

## ✅ SUCCESS INDICATORS

You'll know it's working when:
1. ✅ No error messages on image upload
2. ✅ Image analysis completes successfully
3. ✅ Admin Settings shows `grok-2-vision-1212`
4. ✅ Frontend works without "Image inputs are not supported" error

---

## 📞 SUPPORT

If issues persist after following this guide:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console for JavaScript errors
3. Verify API key is valid at https://console.x.ai/
4. Ensure API key has sufficient credits

---

**Last Updated:** March 3, 2026  
**Version:** 1.0  
**Status:** ✅ TESTED & WORKING
