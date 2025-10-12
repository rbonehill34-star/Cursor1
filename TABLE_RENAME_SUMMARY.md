# Table Rename: login → users

## Summary
Successfully renamed the `login` table to `users` throughout the entire Cursor1 practice portal codebase.

## Files Modified

### SQL Database Files (8 files)
1. ✅ **rename_login_to_users.sql** (NEW) - Migration script to rename the table
2. ✅ **setup_database.sql** - Updated to create `users` table
3. ✅ **setup_database_cpanel.sql** - Updated to create `users` table with foreign keys
4. ✅ **add_login_table.sql** - Updated to create `users` table (kept for backward compatibility)
5. ✅ **add_users_table.sql** (NEW) - New file with updated table name
6. ✅ **update_database.sql** - Updated all references and foreign keys
7. ✅ **jobs_database_setup.sql** - Updated foreign key references
8. ✅ **cpanel_database_setup_guide.md** - Updated documentation

### PHP Files (3 files)
1. ✅ **jobs/add.php** - Updated query: `SELECT id, username FROM users`
2. ✅ **login/index.php** - Updated query: `SELECT id, username, password, account_type FROM users`
3. ✅ **register/index.php** - Updated queries:
   - Check username: `SELECT id FROM users`
   - Insert user: `INSERT INTO users`

## What Changed

### Database Structure
- Table name: `login` → `users`
- All foreign key constraints updated to reference `users(id)`
- All indexes updated to reference `users` table
- All INSERT/UPDATE/SELECT statements updated

### Foreign Key Relationships
The following tables have foreign keys that now reference `users`:
- **timesheet**: `user_id` references `users(id)`
- **jobs**: 
  - `partner_id` references `users(id)`
  - `manager_id` references `users(id)`
  - `preparer_id` references `users(id)`

## Migration Instructions

### For Existing Databases

If you already have a database with the `login` table, run this migration:

```bash
# For local development (MySQL/XAMPP)
mysql -u root cursor1 < rename_login_to_users.sql

# For cPanel
# Use phpMyAdmin and import rename_login_to_users.sql
```

**Important Notes:**
- The migration script will automatically:
  - Drop all foreign key constraints that reference the old `login` table
  - Rename the `login` table to `users`
  - Recreate all foreign key constraints with the new `users` table name
- Your data will be preserved during the migration
- No data loss will occur

### For New Database Installations

If you're setting up a new database, use one of these updated setup scripts:

**Local Development:**
```bash
mysql -u root < setup_database.sql
```

**cPanel:**
- Import `setup_database_cpanel.sql` via phpMyAdmin
- Follow the guide in `cpanel_database_setup_guide.md`

## Verification Steps

After running the migration or setup, verify the changes:

1. **Check table exists:**
   ```sql
   SHOW TABLES LIKE 'users';
   ```

2. **Verify foreign keys:**
   ```sql
   SHOW CREATE TABLE timesheet;
   SHOW CREATE TABLE jobs;
   ```

3. **Test login functionality:**
   - Go to `/login`
   - Try logging in with existing credentials
   - Should work without any issues

4. **Test registration:**
   - Go to `/register`
   - Create a new test account
   - Verify it appears in the `users` table

5. **Test job management:**
   - Go to `/jobs/add`
   - Check that partner/manager/preparer dropdowns populate correctly

## Rollback Instructions

If you need to revert the changes (not recommended):

```sql
-- Rename back to login
RENAME TABLE users TO login;

-- Update foreign keys manually
-- (Reverse the process in rename_login_to_users.sql)
```

## Files You Can Delete (Optional)

These files are now redundant but kept for backward compatibility:
- `add_login_table.sql` - Replaced by `add_users_table.sql`

## Testing Checklist

- [ ] Login page works correctly
- [ ] Registration page works correctly
- [ ] Job form shows users in dropdowns
- [ ] Timesheet functionality works
- [ ] User roles and permissions work correctly
- [ ] No database errors in PHP error logs

## Technical Details

### SQL Changes Summary
- **CREATE TABLE**: Changed from `login` to `users`
- **FOREIGN KEY**: Updated all references from `login(id)` to `users(id)`
- **INDEX**: Updated from `idx_username ON login(username)` to `idx_username ON users(username)`
- **INSERT/UPDATE/SELECT**: All queries updated to use `users` table

### PHP Changes Summary
- All PDO queries updated to use `users` table
- No changes to session variables or application logic
- No changes to table structure or column names

## Questions or Issues?

If you encounter any problems:
1. Check PHP error logs
2. Check MySQL error logs
3. Verify foreign key constraints are correctly set up
4. Ensure all files are updated (don't mix old and new files)

---
**Migration Date:** October 12, 2025
**Status:** ✅ Complete
**Files Modified:** 11 files (8 SQL, 3 PHP)
**Data Impact:** None - Structural change only

