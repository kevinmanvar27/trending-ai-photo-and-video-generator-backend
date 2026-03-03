-- ============================================
-- VISION API FIX - Manual Database Update
-- ============================================
-- Run this in phpMyAdmin if the migration fails
-- or if you need to manually fix the vision model
-- ============================================

-- Step 1: Check current vision model setting
SELECT * FROM settings WHERE `key` = 'grok_vision_model';

-- Step 2: Insert or update the vision model setting
INSERT INTO settings (`key`, `value`, `type`, `group`, created_at, updated_at) 
VALUES ('grok_vision_model', 'grok-2-vision-1212', 'text', 'api', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    `value` = 'grok-2-vision-1212',
    updated_at = NOW();

-- Step 3: Verify the update
SELECT * FROM settings WHERE `key` = 'grok_vision_model';

-- Expected result:
-- key: grok_vision_model
-- value: grok-2-vision-1212
-- type: text
-- group: api

-- ============================================
-- Optional: View all Grok-related settings
-- ============================================
SELECT * FROM settings WHERE `key` LIKE 'grok%' ORDER BY `key`;

-- ============================================
-- Optional: Fix if you have old grok-3 value
-- ============================================
UPDATE settings 
SET `value` = 'grok-2-vision-1212', updated_at = NOW() 
WHERE `key` = 'grok_vision_model' AND `value` = 'grok-3';
