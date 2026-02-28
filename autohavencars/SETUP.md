# AutoHavenCars Setup Guide

## Quick Start with XAMPP

### Step 1: Start XAMPP Services
1. Open XAMPP Control Panel
2. Click "Start" for **Apache**
3. Click "Start" for **MySQL**

### Step 2: Create Database
1. Open your browser
2. Go to: `http://localhost/phpmyadmin`
3. Click on "SQL" tab
4. Copy and paste the contents of `database/schema.sql`
5. Click "Go" to execute

Alternatively, you can:
   - Click "New" to create a database named `autohavencars`
   - Click on the `autohavencars` database
- Go to "Import" tab
- Choose `database/schema.sql` file
- Click "Go"

### Step 3: Place Project Files
If your project is in `C:\Users\Elitebook\Documents\carselling\`:

**Option A: Move to XAMPP htdocs**
- Copy all files to `C:\xampp\htdocs\autohavencars\`
- Access via: `http://localhost/autohavencars/`

**Option B: Use Virtual Host (Advanced)**
- Configure Apache virtual host to point to your project directory
- Access via: `http://localhost/` (if configured)

**Option C: Access Directly**
- If XAMPP is configured to allow access outside htdocs
- Access via: `http://localhost/carselling/` (adjust path as needed)

### Step 4: Verify Directory Permissions
Ensure these directories exist and are writable:
- `assets/uploads/` - for car images
- `assets/images/` - for placeholder images

### Step 5: Test the Application
1. Open browser: `http://localhost/autohavencars/` (or your configured path)
2. You should see the AutoHavenCars homepage
3. Try logging in with:
   - Email: `admin@autohavencars.com`
   - Password: `password`

## Troubleshooting

### Database Connection Error
- **Problem**: "Connection failed" error
- **Solution**: 
  - Verify MySQL is running in XAMPP
  - Check `config/database.php` settings
  - Default XAMPP: user=`root`, password=`` (empty)

### Images Not Displaying
- **Problem**: Car images show as broken
- **Solution**:
  - Create `assets/images/placeholder-car.jpg` (any car image)
  - Or upload images when posting cars
  - Check `assets/uploads/` directory exists

### Page Shows 404 Error
- **Problem**: Page not found
- **Solution**:
  - Verify files are in correct location
  - Check Apache is running
  - Verify URL path matches your directory structure

### Cannot Upload Images
- **Problem**: Image upload fails
- **Solution**:
  - Check `assets/uploads/` directory exists
  - Verify directory permissions (should be writable)
  - Check PHP upload settings in `php.ini`:
    - `upload_max_filesize = 10M`
    - `post_max_size = 10M`

## File Structure After Setup

```
C:\xampp\htdocs\autohavencars\  (or your project directory)
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── uploads/  (auto-created when uploading)
├── config/
├── database/
├── includes/
├── index.php
├── listings.php
├── car-details.php
├── post-car.php
├── login.php
├── register.php
├── logout.php
├── my-cars.php
└── README.md
```

## Next Steps

1. ✅ Database created
2. ✅ Files in place
3. ✅ XAMPP running
4. 🎉 Start using AutoHavenCars!

Visit the homepage and start browsing or listing cars!

