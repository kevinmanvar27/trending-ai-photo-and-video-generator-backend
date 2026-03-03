#!/bin/bash

# ============================================
# Vision API Fix - Verification Script
# ============================================
# Run this script to verify the fix is working
# Usage: bash verify_fix.sh
# ============================================

echo "🔍 Verifying Vision API Fix..."
echo "================================"
echo ""

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Not in Laravel root directory"
    echo "Please run this script from the project root"
    exit 1
fi

echo "✅ Found Laravel project"
echo ""

# Step 1: Check config file
echo "📝 Step 1: Checking config file..."
if grep -q "grok-2-vision-1212" config/image-prompt.php; then
    echo "✅ Config file has correct vision model"
else
    echo "❌ Config file still has old model"
    echo "   Expected: grok-2-vision-1212"
    echo "   Please update config/image-prompt.php"
fi
echo ""

# Step 2: Check migration exists
echo "📝 Step 2: Checking migration..."
if ls database/migrations/*add_grok_vision_model_setting.php 1> /dev/null 2>&1; then
    echo "✅ Migration file exists"
else
    echo "❌ Migration file not found"
fi
echo ""

# Step 3: Check database setting
echo "📝 Step 3: Checking database setting..."
php artisan tinker --execute="echo App\Models\Setting::get('grok_vision_model') ?? 'NOT SET';" 2>/dev/null
if [ $? -eq 0 ]; then
    echo "✅ Database check completed"
else
    echo "⚠️  Could not check database (this is OK if not connected)"
fi
echo ""

# Step 4: Clear cache
echo "📝 Step 4: Clearing cache..."
php artisan config:clear > /dev/null 2>&1
php artisan cache:clear > /dev/null 2>&1
echo "✅ Cache cleared"
echo ""

# Step 5: Check controller
echo "📝 Step 5: Checking SettingController..."
if grep -q "grok_vision_model" app/Http/Controllers/Admin/SettingController.php; then
    echo "✅ SettingController has vision model support"
else
    echo "❌ SettingController missing vision model support"
fi
echo ""

# Step 6: Check view
echo "📝 Step 6: Checking admin settings view..."
if grep -q "grok_vision_model" resources/views/admin/settings/index.blade.php; then
    echo "✅ Admin settings view has vision model field"
else
    echo "❌ Admin settings view missing vision model field"
fi
echo ""

# Summary
echo "================================"
echo "📊 VERIFICATION SUMMARY"
echo "================================"
echo ""
echo "Next steps:"
echo "1. Visit your admin panel: /admin/settings"
echo "2. Go to API Settings tab"
echo "3. Check 'Vision Model' field shows: grok-2-vision-1212"
echo "4. Save settings"
echo "5. Test image upload on frontend"
echo ""
echo "✅ Verification complete!"
