# UTAR Server Side Web Application Group Assignment

**Project Name:** Student Co-curricular Management System  
**Course Context:** UTAR Server Side Web Application (Group Assignment)

## Project Background

This project is a server-side web application developed to help UTAR students manage their co-curricular portfolio in one centralized system.

Students can register an account, log in securely, and maintain structured records for:
- Event participation
- Club memberships and roles
- Merit contribution hours
- Achievements and recognitions

An admin role is also supported to review overall user activity summaries across all modules.

## Objectives

- Build a practical PHP + MySQL server-side application.
- Apply authentication, authorization, and session management concepts.
- Implement complete CRUD workflows across multiple related modules.
- Provide a clean, responsive, user-friendly interface for student record management.

## Team Members

| Student ID | Name | Module / Responsibility |
| --- | --- | --- |
| 2301305 | Sean Looi Tze Farn | Achievement Tracker |
| 2300510 | Hoe Cheng Xuan | Merit Tracker |
| 2301376 | Wang Zi Shen | Club Tracker |
| 2301664 | Chow Yong Xiang | Event Tracker |

## Core Features

### 1) Authentication & Access Control
- User registration with validation:
  - Required full name, email, password, confirm password
  - Email format validation
  - Duplicate email check
  - Password length check (minimum 6 characters)
- Secure login with hashed password verification (`password_hash` / `password_verify`)
- Session-based authentication
- Remember email option using cookie (`remember_email`)
- Logout and session destruction
- Route protection using:
  - `includes/auth_check.php` for logged-in users
  - `includes/admin_check.php` for admin-only access

### 2) Dashboard Module
- Personalized welcome dashboard after login
- Quick module access cards:
  - Event Tracker
  - Club Tracker
  - Merit Tracker
  - Achievement Tracker
- Admin card shown only for users with `role = admin`

### 3) Event Tracker (`event_module`)
- Add event records with fields:
  - Event name, organiser, date, location type, location, description
- Dynamic location selection UX:
  - Online platforms
  - Campus venue presets
  - Custom external location
- Event list with:
  - Total participation count summary
  - Filter by location type (`all / online / campus / other`)
  - Sorting options (newest, oldest, A-Z by name)
- Edit and delete event records
- Per-user data isolation (users can only access their own records)

### 4) Club Tracker (`club_module`)
- Add club record (club name, role, join date, remarks)
- Club list with:
  - Total record count summary
  - Sorting (join date asc/desc, club name A-Z, role A-Z)
- Edit and delete club records
- Per-user ownership checks on updates/deletes

### 5) Merit Tracker (`merit_module`)
- Add merit record (activity name, hours, start date, end date, remarks)
- Validation for:
  - Required fields
  - Non-negative numeric hours
  - End date not earlier than start date
- Merit list with:
  - Total records and total contribution hours summary
  - Sorting (date, hours high/low, activity name A-Z)
- Edit and delete merit records
- Per-user ownership checks on all operations

### 6) Achievement Tracker (`achievement_module`)
- Add achievement (title, type, organiser, date received, remarks)
- Achievement type options (Certificate, Award, Medal, Trophy, Other)
- Achievement list with:
  - Total achievement count summary
  - Sorting (date asc/desc, title A-Z, type A-Z)
- Edit and delete achievement records
- Per-user ownership checks enforced in edit/delete queries

### 7) Admin Dashboard (`admin_module`)
- Admin-only page protected by role check
- Search users by name/email
- Aggregated system-level summary cards:
  - Total users
  - Total events
  - Total clubs
  - Total merit records
  - Total achievements
- Per-user overview cards showing:
  - Email and role
  - Event/club/merit/achievement counts

## Tech Stack

- **Backend:** PHP (procedural style with MySQLi prepared statements)
- **Database:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3, vanilla JavaScript
- **Server Environment:** Apache (XAMPP-friendly)
- **Session/Auth:** Native PHP sessions + cookies

## Project Structure

```text
Assignment/
├── achievement_module/
│   ├── achievements.php
│   ├── add_achievement.php
│   ├── edit_achievement.php
│   └── delete_achievement.php
├── admin_module/
│   └── admin_dashboard.php
├── club_module/
│   ├── clubs.php
│   ├── add_club.php
│   ├── edit_club.php
│   └── delete_club.php
├── config/
│   └── db.php
├── database/
│   └── cocurricular.sql
├── event_module/
│   ├── event_index.php
│   ├── event_add.php
│   ├── event_edit.php
│   └── event_delete.php
├── includes/
│   ├── auth_check.php
│   ├── admin_check.php
│   ├── header.php
│   ├── nav.php
│   └── footer.php
├── merit_module/
│   ├── merits.php
│   ├── add_merit.php
│   ├── edit_merit.php
│   └── delete_merit.php
├── assets/
│   └── style.css
├── index.php
├── login.php
├── register.php
├── dashboard.php
└── logout.php
```

## Database Design

SQL schema file: `database/cocurricular.sql`

### Tables
- `users`
  - `user_id`, `full_name`, `email`, `password`, `role`, `created_at`
- `events`
  - linked to `users.user_id`
- `clubs`
  - linked to `users.user_id`
- `merits`
  - linked to `users.user_id`
- `achievements`
  - linked to `users.user_id`

All module tables use foreign keys with `ON DELETE CASCADE` to maintain consistency when a user is removed.

## Quick Start (XAMPP)

### Prerequisites
- XAMPP (Apache + MySQL)
- PHP 8.x recommended
- Web browser

### Setup Steps

1. Place project folder in XAMPP `htdocs`:
   - `c:\xampp\htdocs\Assignment`

2. Start services in XAMPP Control Panel:
   - Apache
   - MySQL

3. Create/import database:
   - Open `http://localhost/phpmyadmin`
   - Import `database/cocurricular.sql`

4. Verify DB config in `config/db.php`:
   - Host: `localhost`
   - Username: `root`
   - Password: `` (empty by default)
   - Database: `cocurricular_db`

5. Run the app:
   - Open `http://localhost/Assignment/`

## Admin Account Setup

Newly registered accounts default to `student`.  
To test admin features, promote a user manually in MySQL:

```sql
UPDATE users
SET role = 'admin'
WHERE email = 'your_admin_email@example.com';
```

Then log in with that account and access:
- Dashboard admin card, or
- `http://localhost/Assignment/admin_module/admin_dashboard.php`

## Key Routes

- Home: `/Assignment/index.php`
- Register: `/Assignment/register.php`
- Login: `/Assignment/login.php`
- Dashboard: `/Assignment/dashboard.php`
- Events: `/Assignment/event_module/event_index.php`
- Clubs: `/Assignment/club_module/clubs.php`
- Merits: `/Assignment/merit_module/merits.php`
- Achievements: `/Assignment/achievement_module/achievements.php`
- Admin Dashboard: `/Assignment/admin_module/admin_dashboard.php`

## Security & Data Integrity Characteristics

- Prepared statements used for database operations
- Password hashing for stored credentials
- Session-guarded private routes
- Role-based admin authorization
- Per-user record ownership enforced in CRUD queries
- Server-side input validation for required and numeric/date fields

## UI/UX Characteristics

- Consistent shared layout through `includes/header.php`, `nav.php`, `footer.php`
- Responsive styling (`assets/style.css`) with modern card-based module pages
- Success/error feedback boxes for user actions
- Empty-state guidance for first-time records
- In-module sorting/filtering for better data browsing

## Screenshots

> Place screenshot files inside `docs/screenshots/` using the filenames below.

### Landing Page

![Landing Page](docs/screenshots/landing-page.png)

### Register Page

![Register Page](docs/screenshots/register-page.png)

### Dashboard Page

![Dashboard Page](docs/screenshots/dashboard-page.png)

## Limitations / Future Improvements

- Add CSRF protection tokens for form submissions
- Add stronger password policy and optional email verification
- Add pagination for large datasets
- Add export/report features (PDF/CSV)
- Add automated test coverage

## Academic Note

This project is developed for academic learning and demonstration under:
**UTAR Server Side Web Application Group Assignment**.
