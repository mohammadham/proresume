# Watermark Badge Feature - Todo List

## Phase 1: Database Migrations ✅ COMPLETED
- [x] Create migration for `basic_settings` table (watermark_status, watermark_text, watermark_url, watermark_image)
- [x] Create migration for `user_basic_settings` table (watermark_status, watermark_text, watermark_url, watermark_image)
- [x] PHP syntax check passed for both migrations

## Phase 2: Update Models ✅ COMPLETED
- [x] Add watermark fields to `app/Models/BasicSetting.php` fillable array
- [x] Add watermark fields to `app/Models/User/BasicSetting.php` fillable array
- [x] PHP syntax check passed for both models

## Phase 3: Create Watermark Partial View ✅ COMPLETED
- [x] Create `resources/views/partials/watermark.blade.php`
- [x] Supports both image and text modes
- [x] Responsive design (mobile: 80px from bottom)
- [x] RTL support
- [x] PHP syntax check passed

## Phase 4: Integrate Watermark into Footer ✅ COMPLETED
- [x] Update `resources/views/front/partials/footer.blade.php`
- [x] Include watermark partial with proper data passing
- [x] PHP syntax check pending

## Phase 5: Admin Controller & Views ✅ COMPLETED
- [x] Add `watermark()` method to `Admin\BasicController.php`
- [x] Add `updateWatermark()` method to `Admin\BasicController.php`
- [x] Create `resources/views/admin/basic/watermark.blade.php`
- [x] Form with radio buttons for status (Active/Deactive)
- [x] Text field for watermark text
- [x] URL field for watermark link
- [x] File upload for watermark image (max 2MB, jpg/jpeg/png/svg)
- [x] Current image preview
- [x] Old image cleanup on replacement
- [x] PHP syntax check passed for controller and view

## Phase 6: User Controller & Views ✅ COMPLETED
- [x] Add `watermark()` method to `User\BasicSettingController.php`
- [x] Add `updateWatermark()` method to `User\BasicSettingController.php`
- [x] Create `resources/views/user/settings/watermark.blade.php`
- [x] Same form fields as admin but scoped to user
- [x] User-specific image upload directory
- [x] Old image cleanup on replacement
- [x] PHP syntax check passed for controller and view

## Phase 7: Routes ✅ COMPLETED
- [x] Add admin watermark routes in `routes/web.php`:
  - GET `/admin/watermark` → `Admin\BasicController@watermark`
  - POST `/admin/watermark/update` → `Admin\BasicController@updateWatermark`
- [x] Add user watermark routes in `routes/web.php`:
  - GET `/user/watermark` → `User\BasicSettingController@watermark`
  - POST `/user/watermark/update` → `User\BasicSettingController@updateWatermark`
- [x] PHP syntax check passed

## Phase 8: Final Verification ⏳ PENDING
- [ ] Run all migrations
- [ ] Test admin watermark page loads
- [ ] Test user watermark page loads
- [ ] Test watermark appears on public pages when enabled
- [ ] Test watermark does NOT appear on admin panels
- [ ] Test watermark does NOT appear on user panels
- [ ] Test watermark text mode works
- [ ] Test watermark image mode works
- [ ] Test watermark link URL works
- [ ] Test responsive design on mobile
- [ ] Test RTL layout
- [ ] Test image upload and cleanup
- [ ] Test no styling breakage when disabled
- [ ] Final PHP syntax check on all modified files

## Summary of Created Files
1. `updater/database/migrations/2026_08_30_000003_add_watermark_to_basic_settings.php`
2. `updater/database/migrations/2026_08_30_000004_add_watermark_to_user_basic_settings.php`
3. `resources/views/partials/watermark.blade.php`
4. `resources/views/admin/basic/watermark.blade.php`
5. `resources/views/user/settings/watermark.blade.php`

## Summary of Modified Files
1. `app/Models/BasicSetting.php` - Added fillable fields
2. `app/Models/User/BasicSetting.php` - Added fillable fields
3. `app/Http/Controllers/Admin/BasicController.php` - Added watermark methods
4. `app/Http/Controllers/User/BasicSettingController.php` - Added watermark methods
5. `resources/views/front/partials/footer.blade.php` - Added watermark include
6. `routes/web.php` - Added admin and user watermark routes

## Next Steps After Migrations
1. Run `php artisan migrate` to apply database changes
2. Test admin panel: Navigate to Admin → Settings → Watermark Badge
3. Test user panel: Navigate to User → Settings → Watermark Badge
4. Enable watermark and verify it appears on public pages
5. Verify watermark is hidden from admin/user panels