# Admin Folder Rename Instructions

The admin folder has been renamed from `admin` to `adminautohaven` in the code.

## Required Action

You need to manually rename the folder in your file system:

1. **Rename the folder:**
   - Old: `admin/`
   - New: `adminautohaven/`

2. **How to rename:**
   - In Windows File Explorer, navigate to your project folder
   - Right-click on the `admin` folder
   - Select "Rename"
   - Change it to `adminautohaven`

## What Was Changed

All code references have been updated:
- ✅ `includes/header.php` - Admin link updated
- ✅ `login.php` - Admin redirect updated

The admin files themselves use relative paths (`../config/`, `../includes/`) so they will work correctly after the folder is renamed.

## Access

After renaming, access the admin panel at:
- `http://localhost/autohavencars/adminautohaven/index.php`





