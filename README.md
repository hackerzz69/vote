# Vote Application

A secure, modular PHP voting platform with user authentication, admin panel, and voting link management. Built using MVC principles, PDO (via FluentPDO), and modern best practices.

## Features

- User registration, login, and session management
- Admin panel for managing users, votes, and voting links
- Secure authentication with multi-factor authentication (MFA) support
- SQL injection protection via parameterized queries
- Installer with optional self-deletion for security
- Modern UI using Bootstrap and Twig templates

## Project Structure

```
.
├── app/
│   ├── constants.php         # Site configuration constants
│   ├── functions.php         # Global helper functions
│   ├── init.php              # App bootstrap/init logic
│   ├── controllers/          # MVC controllers (Admin, API, Login, Users, etc.)
│   ├── core/                 # Core framework (App, Controller, Model, Security, etc.)
│   ├── models/               # Database models (Users, Votes, VoteLinks, Sessions)
│   ├── plugins/              # Third-party and custom plugins (Twig, etc.)
│   └── views/                # Twig templates for all pages
├── database.sql              # MySQL schema for initial setup
├── index.php                 # Main entry point
├── install/                  # Installer scripts and logs
├── public/                   # Public assets (CSS, JS, images, fonts)
└── test.php                  # Test script (if present)
```

## Security

- All database access uses prepared statements via FluentPDO, protecting against SQL injection.
- The `Security` class manages authentication, session validation, and access control.
- Installer can optionally delete itself after setup for improved security.

## Setup

1. Clone the repository and place it in your web server root.
2. Run `composer install` inside the `app/plugins/` directory to install PHP dependencies.
3. Run the installer at `/install/` in your browser.
4. Follow the prompts to configure the database and admin account.
5. (Optional) Enable the "auto-delete installer" option for security.
6. Access the site via `/index.php` or your configured web root.

## Requirements

- PHP 7.4+ with PDO and mysqli extensions
- MySQL/MariaDB
- Composer (for dependency management, if you wish to update plugins)

## Development

- Controllers are in `app/controllers/`
- Models are in `app/models/`
- Core framework logic is in `app/core/`
- Templates are in `app/views/` (Twig format)
- Public assets are in `public/`

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.
