# LuxuryStay

LuxuryStay is a PHP and MySQL accommodation booking and property-management platform for Sri Lankan stays. It includes a public accommodation catalogue, customer accounts and bookings, owner tools, and an administration area.

## Features

- Browse, search, and filter accommodation listings.
- View property galleries, rooms, amenities, maps, reviews, and availability.
- Customer registration, authentication, booking, payment simulation, and notifications.
- Owner property, room, image, availability, booking, profile, and report management.
- Admin management for users, owners, properties, bookings, reviews, and reports.
- Responsive Bootstrap-based interface with accessible mobile navigation.

## Technology

- PHP 8.0 or later
- MySQL or MariaDB
- Bootstrap 5 and Bootstrap Icons (loaded from CDN)
- Chart.js (loaded from CDN)
- Dompdf for PDF reports (installed with Composer)

## Project structure

```text
LuxuryStay/
├── admin/                 # Administrator pages
├── api/                   # Authenticated AJAX endpoints
├── assets/                # CSS, JavaScript, images, and icons
├── config/                # Application configuration
├── database/              # Schema, migrations, and seed scripts
├── includes/              # Shared PHP bootstrap, layout, and helpers
├── owner/                 # Property-owner pages
├── uploads/               # Writable property and profile image storage
├── user/                  # Customer account pages
├── index.php              # Public homepage
├── properties.php         # Accommodation listings and filters
├── property.php           # Property details
├── composer.json          # PHP dependencies
└── .htaccess              # Apache security and directory settings
```

## Run locally

1. Install PHP 8+, MySQL/MariaDB, and Composer. XAMPP is suitable for local development.
2. Clone the repository into your web server document root.
3. Create a database named `luxurystay` and import `database/database.sql`.
4. Install PHP dependencies:

   ```bash
   composer install --no-dev --optimize-autoloader
   ```

5. Configure the application with environment variables:

   ```text
   APP_URL=http://localhost/LuxuryStay
   DB_HOST=localhost
   DB_NAME=luxurystay
   DB_USER=your_database_user
   DB_PASS=your_database_password
   APP_DEBUG=true
   ```

   If environment variables are not available in local development, the defaults in `config/config.php` target a local MySQL database named `luxurystay`. Do not commit production credentials.

6. Ensure the web server can write to `uploads/properties/` and `uploads/profile/`.
7. Open the configured `APP_URL` in your browser.


## Security and operational notes

- Never commit `.env` files, production database passwords, or user-uploaded private media.
- `config/config.php` reads configuration from environment variables.
- Development utilities and database files are blocked from direct HTTP access by `.htaccess` when Apache `mod_rewrite` is enabled.
- The payment page is a demo flow, not a real payment-gateway integration.
- CDN assets require an internet connection unless you self-host Bootstrap, Icons, Google Fonts, and Chart.js.


## License

Educational project. Review third-party service and image licences before commercial deployment.
