# NomadNest — Member 3 Setup Guide (PHP + SQL)

## Your files

```
nomadnest/
├── nomadnest.sql          ← Run this FIRST in phpMyAdmin
├── includes/
│   ├── db.php             ← Database connection (shared with whole team)
│   └── auth_check.php     ← Session helpers (shared with whole team)
└── api/
    ├── register.php       ← POST: create account
    ├── login.php          ← POST: sign in
    ├── logout.php         ← GET:  sign out + redirect
    ├── spaces.php         ← GET:  filtered spaces list
    ├── space.php          ← GET:  single space detail + booked dates
    ├── book.php           ← POST: create a booking
    ├── dashboard.php      ← GET:  member dashboard data
    ├── manager.php        ← GET/POST: host dashboard data + actions
    ├── members.php        ← GET/POST: member directory + connections
    ├── messages.php       ← GET/POST: conversations + send message
    └── reviews.php        ← POST: submit a review
```

---

## Step 1 — Install Laragon

1. Go to **laragon.net** and download the **Full** version
2. Install it (default options are fine)
3. Open Laragon and click **Start All**
4. A green light means MySQL and Apache are running

---

## Step 2 — Put the project in the right folder

1. In Laragon, click the **www** button — it opens `C:\laragon\www\`
2. Create a folder called `nomadnest` inside it
3. Paste all the project files there

Your project is now at: `http://nomadnest.test`

---

## Step 3 — Create the database

1. In Laragon, click **Database** (or go to `http://localhost/phpmyadmin`)
2. Log in: username `root`, password is empty (just leave it blank)
3. Click **SQL** tab at the top
4. Open `nomadnest.sql` in Notepad, copy everything, paste it in phpMyAdmin
5. Click **Go**

The database is now created with all tables and sample data.

---

## Step 4 — Test it works

Open your browser and go to:
```
http://nomadnest.test/api/spaces.php
```
You should see a JSON response with 6 spaces. If you do, everything works.

---

## API reference for your teammates

Tell Member 2 (JS) these are the endpoints they call with `fetch()`:

| Method | URL | What it does |
|--------|-----|--------------|
| POST | `/api/register.php` | Create account |
| POST | `/api/login.php` | Log in |
| GET  | `/api/logout.php` | Log out |
| GET  | `/api/spaces.php?city=Paris&max_price=50` | Filter spaces |
| GET  | `/api/space.php?id=1` | Space detail |
| GET  | `/api/space.php?id=1&booked=1` | Booked dates for calendar |
| POST | `/api/book.php` | Create booking |
| GET  | `/api/dashboard.php` | Member dashboard data |
| GET  | `/api/manager.php` | Host dashboard data |
| POST | `/api/manager.php` | Approve/cancel booking, pause listing |
| GET  | `/api/members.php?city=Paris` | Member directory |
| POST | `/api/members.php` | Send connection request |
| GET  | `/api/messages.php` | Conversation list |
| GET  | `/api/messages.php?with=2` | Thread with user #2 |
| POST | `/api/messages.php` | Send a message |
| POST | `/api/reviews.php` | Submit a review |

---

## Test accounts (seed data)

| Email | Password | Role |
|-------|----------|------|
| lea@nomadnest.com | password123 | member |
| daniel@nomadnest.com | password123 | member |
| camille@nomadnest.com | password123 | host |
| marcus@nomadnest.com | password123 | host |

---

## Notes

- `db.php` uses PDO with prepared statements — this protects against SQL injection automatically.
- `auth_check.php` must be included at the top of every protected page. Call `require_login()` for any logged-in user, `require_host()` for host-only pages.
- All API files return JSON. Member 2 reads `response.success` to know if it worked.
- The service fee is 9% of the base price, calculated in `book.php`.
