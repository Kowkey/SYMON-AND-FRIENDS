# Laravel Eloquent Relationships & Authentication System

A Laravel web application demonstrating the three core Eloquent relationships (**One-to-One**, **One-to-Many**, **Many-to-Many**) with **SQLite**, paired with a complete user registration and login authentication system featuring brute-force protection, session security, and duplicate submission prevention.

---

## Features

### Eloquent Relationships (with SQLite)

| Relationship | Models | Description |
|---|---|---|
| **One-to-One** | `User` ↔ `Profile` | Each user has exactly one profile. Foreign key `user_id` on `profiles` table with a `unique` constraint. |
| **One-to-Many** | `User` ↔ `Post` | A user can author many posts, but each post belongs to only one user. |
| **Many-to-Many** | `User` ↔ `Role` | A user can hold multiple roles, and each role can be shared by multiple users. Connected via a `role_user` pivot table. |

### Authentication & Security

1. **Login Attempt Limit** — The system locks a user's account for 60 seconds after 5 consecutive failed login attempts to prevent brute-force and password guessing attacks.
2. **Authentication Protection** — Guests cannot access the dashboard without logging in. Authenticated users are automatically redirected away from the login and registration pages.
3. **Prevent Duplicate Submissions** — Submit buttons are disabled immediately on click (with a loading spinner) and a server-side cache lock prevents duplicate records even from rapid burst clicks.
4. **Prevent Form Resubmission** — All form submissions use the **Post/Redirect/Get (PRG)** pattern. Refreshing the page after submitting data will never trigger a browser resubmission dialog or create duplicate records.

---

## Tech Stack

- **Framework:** Laravel 12
- **Language:** PHP 8.2+
- **Database:** SQLite (file-based, zero configuration)
- **Frontend:** Blade templates with custom CSS (no build tools required)
- **Testing:** PHPUnit

---

## Screenshots

### Login Page
- Olive/sage card centered over an abstract green geometric background
- Email & password fields with "Remember Account" checkbox
- Blue "LOG IN" button
- Brute-force protection warning after failed attempts

### Registration Page
- Full Name, Email Address, Password (with show/hide toggle), Confirm Password
- Phone Number and Short Bio fields (creates the One-to-One Profile)
- Automatically assigns a default "Member" role (Many-to-Many)

### Dashboard (Main Activity Page)
- Welcome banner with user info and Log Out button
- 4 security feature info cards at the top
- 3 interactive Eloquent relationship panels:
  - **One-to-One:** View and update user profile (phone number, bio)
  - **One-to-Many:** Create new posts and view a list of authored posts
  - **Many-to-Many:** Assign/remove roles using checkboxes with sync

---

## Installation & Setup

### Prerequisites

- PHP 8.2 or higher
- Composer

### Steps

```bash
# 1. Clone the repository
git clone https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git
cd YOUR_REPO_NAME

# 2. Install PHP dependencies
composer install

# 3. Create environment file
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Create the SQLite database file
# (On Windows PowerShell)
New-Item -ItemType File database/database.sqlite -Force
# (On Mac/Linux)
touch database/database.sqlite

# 6. Run database migrations
php artisan migrate

# 7. Start the development server
php artisan serve
```

Then open your browser to **http://127.0.0.1:8000**.

---

## Project Structure

```
app/
├── Console/Commands/
│   ├── DemoOneToOne.php          # Artisan command to demo 1:1 relationship
│   ├── DemoOneToMany.php         # Artisan command to demo 1:N relationship
│   └── DemoManyToMany.php        # Artisan command to demo N:M relationship
├── Http/Controllers/
│   ├── AuthController.php        # Login, Register, Logout with rate limiting
│   └── DashboardController.php   # Main page with all 3 relationships
└── Models/
    ├── User.php                  # hasOne(Profile), hasMany(Post), belongsToMany(Role)
    ├── Profile.php               # belongsTo(User)
    ├── Post.php                  # belongsTo(User)
    └── Role.php                  # belongsToMany(User)

database/
├── database.sqlite               # SQLite database file
└── migrations/
    ├── create_users_table         # Users table
    ├── create_profiles_table      # Profiles table (1:1, user_id UNIQUE)
    ├── create_posts_table         # Posts table (1:N)
    ├── create_roles_table         # Roles table (N:M)
    └── create_role_user_table     # Pivot table (N:M)

resources/views/
├── layouts/app.blade.php         # Main layout with CSS & JS
├── auth/
│   ├── login.blade.php           # Login page
│   └── register.blade.php        # Registration page
└── dashboard.blade.php           # Main activity page (after login)

routes/
└── web.php                       # All web routes (guest & auth groups)

tests/Feature/
├── AuthSecurityTest.php          # 7 tests for all 4 security requirements
├── OneToOneRelationshipTest.php  # 3 tests for User ↔ Profile
├── OneToManyRelationshipTest.php # 3 tests for User ↔ Post
└── ManyToManyRelationshipTest.php# 4 tests for User ↔ Role
```

---

## Artisan Demo Commands

You can also demonstrate each relationship directly from the terminal:

```bash
# One-to-One (User ↔ Profile)
php artisan app:demo-one-to-one

# One-to-Many (User ↔ Posts)
php artisan app:demo-one-to-many

# Many-to-Many (User ↔ Roles)
php artisan app:demo-many-to-many
```

---

## Running Tests

```bash
php artisan test
```

**Expected output (19 tests, 79 assertions):**

```
PASS  Tests\Feature\AuthSecurityTest
✓ user is locked out after five failed login attempts
✓ rate limiter is cleared after successful login
✓ unauthenticated users cannot access main dashboard
✓ authenticated users are redirected away from login and register
✓ user registration creates profile and redirects
✓ creating post uses post redirect get and prevents duplicate
✓ server blocks immediate burst duplicate post submission

PASS  Tests\Feature\OneToOneRelationshipTest
✓ user can have one profile
✓ profile belongs to user
✓ deleting user cascades and deletes profile

PASS  Tests\Feature\OneToManyRelationshipTest
✓ user can have many posts
✓ post belongs to user
✓ deleting user cascades and deletes all posts

PASS  Tests\Feature\ManyToManyRelationshipTest
✓ user can have multiple roles and vice versa
✓ sync replaces assigned roles
✓ detach removes role
✓ deleting user removes pivot records cascade

Tests: 19 passed (79 assertions)
```

---

## Eloquent Relationship Summary

### One-to-One (User ↔ Profile)
```php
// User model
public function profile(): HasOne {
    return $this->hasOne(Profile::class);
}

// Profile model
public function user(): BelongsTo {
    return $this->belongsTo(User::class);
}

// Usage
$user->profile->phone_number;
$profile->user->name;
```

### One-to-Many (User ↔ Post)
```php
// User model
public function posts(): HasMany {
    return $this->hasMany(Post::class);
}

// Post model
public function user(): BelongsTo {
    return $this->belongsTo(User::class);
}

// Usage
$user->posts;                    // Collection of posts
$user->posts()->create([...]);   // Create via relationship
$post->user->name;               // Inverse access
```

### Many-to-Many (User ↔ Role)
```php
// User model
public function roles(): BelongsToMany {
    return $this->belongsToMany(Role::class)->withTimestamps();
}

// Role model
public function users(): BelongsToMany {
    return $this->belongsToMany(User::class)->withTimestamps();
}

// Usage
$user->roles()->attach($roleId);       // Add role
$user->roles()->detach($roleId);       // Remove role
$user->roles()->sync([$id1, $id2]);    // Replace all roles
$role->users;                          // All users with this role
```

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
