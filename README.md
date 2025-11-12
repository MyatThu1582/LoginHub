# 🧭 LoginHub – User Authentication System
### PHP • MySQL • Bootstrap 5 • Google Login Ready

---

## 🧩 1. Introduction
**LoginHub** is a secure and modern PHP Login & Registration system with email verification, password reset, dark mode, and Google OAuth integration.  
It’s built with **PDO**, **Bootstrap 5**, and **Composer**, designed to be plug-and-play for any project.

---

## ⚙️ 2. Requirements
- PHP **8.0+**
- MySQL or MariaDB
- Composer (for Google API)
- Mail server or Mailtrap (for testing)
- Enabled extensions: `pdo_mysql`, `openssl`, `mbstring`

---

## 🗂️ 3. Folder Structure

```
Login_Register_System/
│
├── config.php
├── index.php
├── register.php
├── login.php
├── logout.php
├── dashboard.php
├── forgot_password.php
├── reset_password.php
├── activate.php
├── google_login.php
├── google_callback.php
│
├── vendor/                # (auto-created by Composer)
├── assets/
│   ├── css/
│   └── js/
├── sql/
│   └── loginhub.sql       # Database structure
├── README.md
└── style.css
```

---

## 🧱 4. Database Setup

1. Create a new database, e.g. `loginhub_db`.  
2. Import the SQL file located at:
   ```
   /sql/loginhub.sql
   ```
3. Update your database credentials in **config.php**:
   ```php
   $host = 'localhost';
   $db   = 'loginhub_db';
   $user = 'root';
   $pass = '';
   $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
   ```

---

## ✉️ 5. Email Configuration

Update these values in your **config.php** to enable account activation and password reset emails:

```php
$mail_host = 'smtp.mailtrap.io';
$mail_port = 2525;
$mail_user = 'YOUR_MAILTRAP_USER';
$mail_pass = 'YOUR_MAILTRAP_PASS';
$mail_from = 'no-reply@yourdomain.com';
$mail_from_name = 'LoginHub Support';
```

📩 Tip: use [Mailtrap.io](https://mailtrap.io) for testing emails safely.

---

## 🔐 6. Google Login Setup

### Step 1 – Enable Google OAuth
- Go to: [https://console.cloud.google.com/apis/credentials](https://console.cloud.google.com/apis/credentials)
- Create a **New OAuth Client ID** (type: Web Application)
- Add your authorized redirect URI:

```
http://localhost/mt_php/Login_Register_System/google_callback.php
```

### Step 2 – Copy your credentials
Paste your credentials into `google_login.php` and `google_callback.php`:

```php
$client->setClientId('YOUR_CLIENT_ID');
$client->setClientSecret('YOUR_CLIENT_SECRET');
$client->setRedirectUri('http://localhost/mt_php/Login_Register_System/google_callback.php');
```

### Step 3 – Install Google Client
Run this in your project folder:
```
composer require google/apiclient:^2.13
```

✅ Done! Google Login button will now redirect users for authentication.

---

## 🧑‍💻 7. Usage Flow

1. **Register** a new user account  
2. **Check your email** for activation link  
3. **Activate** the account  
4. **Login** using email/password OR Google  
5. **Access Dashboard** after authentication  
6. **Forgot Password?** → Request reset link via email  

---

## 🎨 8. Features Summary
| Feature | Description |
|----------|-------------|
| 📨 Email Verification | Activates user accounts securely |
| 🔑 Password Reset | Sends unique token to reset password |
| 🌙 Dark Mode | Auto-saved with localStorage |
| 🌐 Google Login | One-click social authentication |
| 🧠 Secure PDO Queries | Protects against SQL injection |
| 💎 Clean UI | Bootstrap 5 + Icons |
| 🧰 Modular Code | Easy to customize or extend |

---

## 🚫 9. Excluded from Git (for smaller size)

If you’re pushing to GitHub, ignore these folders:
```
/vendor/
/node_modules/
.env
```

Create a `.gitignore` file with:
```
vendor/
```

When users download, they’ll run:
```
composer install
```
to regenerate the vendor folder.

---

## 🧾 10. License
This project follows the **CodeCanyon Regular License** terms.  
Do not redistribute or resell without modification or proper license.

---

## 💬 11. Support
For installation help, contact through your CodeCanyon **“Support”** tab.  
Responses are usually within 24–48 hours.
