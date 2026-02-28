# AutoHavenCars - Car Buying & Selling Platform

AutoHavenCars is a modern web application for buying and selling cars, built with PHP, MySQL, HTML, CSS, and JavaScript, designed to work with XAMPP.

## Features

- 🚗 Browse and search cars with advanced filters
- 📝 List your car for sale
- 👤 User registration and authentication
- 📸 Image upload for car listings
- 💰 Price filtering and search
- 📱 Responsive design for all devices
- 🔍 Detailed car information pages

## Requirements

- XAMPP (Apache, MySQL, PHP)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser

## Installation

1. **Start XAMPP**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

2. **Setup Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the `database/schema.sql` file or run it in SQL tab
   - This will create the `autohavencars` database with sample data

3. **Place Files**
   - Copy all project files to `C:\xampp\htdocs\autohavencars\` (or your XAMPP htdocs directory)
   - Or if you're already in the project directory, ensure it's accessible via XAMPP

4. **Configure Database** (if needed)
   - Edit `config/database.php` if your MySQL credentials differ
   - Default XAMPP settings:
     - Host: localhost
     - User: root
     - Password: (empty)
     - Database: autohavencars

5. **Create Upload Directory**
   - Create the `assets/uploads/` directory if it doesn't exist
   - Ensure it has write permissions

6. **Access the Website**
   - Open your browser and go to: `http://localhost/autohavencars/` or `http://localhost/carselling/`

## Default Login Credentials

For testing purposes, you can use these accounts:

- **Admin Account:**
  - Email: admin@autohavencars.com
  - Password: password

- **User Account:**
  - Email: john@example.com
  - Password: password

## Project Structure

```
autohavencars/
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   ├── images/
│   │   └── placeholder-car.jpg (create this)
│   └── uploads/ (auto-created)
├── config/
│   └── database.php
├── database/
│   └── schema.sql
├── includes/
│   ├── header.php
│   └── footer.php
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

## Features in Detail

### Home Page
- Hero section with call-to-action
- Search functionality
- Featured car listings
- Why choose us section

### Browse Cars
- Advanced filtering (make, price range, year)
- Grid view of all available cars
- Quick view of car details

### Car Details
- Full car specifications
- Contact seller information
- High-quality image display

### Post Car
- Comprehensive form for listing cars
- Image upload functionality
- All car details and specifications

### User Authentication
- Secure registration
- Login/logout functionality
- Session management

## Security Notes

- Change default passwords in production
- Use proper password hashing (already implemented)
- Validate and sanitize all user inputs
- Implement CSRF protection for production
- Use prepared statements (already implemented)

## Customization

- Edit `assets/css/style.css` to change colors and styling
- Modify `config/database.php` for database settings
- Update navigation in `includes/header.php`
- Add more car makes/models in the search filters

## Troubleshooting

1. **Database connection error:**
   - Ensure MySQL is running in XAMPP
   - Check database credentials in `config/database.php`
   - Verify database exists (run schema.sql)

2. **Images not uploading:**
   - Check `assets/uploads/` directory exists
   - Ensure directory has write permissions
   - Check PHP upload settings in php.ini

3. **Page not loading:**
   - Verify Apache is running
   - Check file paths are correct
   - Ensure files are in htdocs directory

## License

This project is open source and available for educational purposes.

## Support

For issues or questions, please check the code comments or refer to PHP/MySQL documentation.

