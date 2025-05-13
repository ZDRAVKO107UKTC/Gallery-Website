# 📷 Gallery Website

A **PHP-based web application** for managing an image gallery with user and admin roles. Users can upload images to their personal gallery, while admins manage users and all images.

---

## ✨ Features

- ✅ User registration and login
- 📁 Upload images with captions
- 🖼️ User-only image gallery
- 🔐 Role-based access (admin/user)
- 🛠️ Admin dashboard:
  - View, create, edit, delete users
  - View and delete uploaded images
- 🎨 Tailwind CSS styling
- 🔒 Secure password hashing

---

## 🛠️ Technologies Used

- PHP
- MySQL
- Tailwind CSS
- HTML5

---

## 🚀 Setup Instructions

1. **Clone the repository**

   ```bash
   git clone https://github.com/ZDRAVKO107UKTC/Gallery-Website.git
2. **Import the database**

   - Open your MySQL interface (phpMyAdmin or terminal).
   - Create a new database called `gallery_db`:

     ```sql
     CREATE DATABASE gallery_db;
     ```

   - Then import the SQL schema from the provided `DB.sql` file:

     - Using **phpMyAdmin**:
       - Select the `gallery_db` database.
       - Go to **Import** → Select `DB.sql` → Click **Go**.
     
     - Using **terminal**:

       ```bash
       mysql -u root -p gallery_db < DB.sql
       ```

     Replace `root` with your MySQL username if different.

3. **Configure the database connection**

   Open the `db.php` file and update it to match your local database settings:

   ```php
   <?php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "gallery_db";

   $conn = mysqli_connect($servername, $username, $password, $dbname);

   if (!$conn) {
       die("Connection failed: " . mysqli_connect_error());
   }
   ?>
4. **Create upload directories**

   The app saves profile and gallery images locally. Make sure these folders exist:

   ```bash
   mkdir -p uploads/profiles
   mkdir -p uploads/gallery
   chmod -R 777 uploads
5. **Run the application**

   - **Option 1: Using XAMPP/WAMP**
     - Start Apache and MySQL.
     - Place the project inside `htdocs/` (XAMPP) or `www/` (WAMP).
     - Open your browser and go to:
       ```
       http://localhost/Gallery-Website/
       ```

   - **Option 2: Using PHP's built-in development server**
     - Open terminal in the project root and run:
       ```bash
       php -S localhost:8000
       ```
     - Visit:
       ```
       http://localhost:8000/
       ```

---

## 👤 Usage

- Register a new user
- Log in to access your private gallery
- Upload images with optional captions
- View your own images
- Update your profile picture (optional)
- Log out securely

---

## 🧑‍💼 Admin Access

- To promote a user to admin:
  ```sql
  UPDATE users SET role_id = 1 WHERE email = 'your_email@example.com';
---

## 🙌 Credits

Developed with ❤️ by [@ZDRAVKO107UKTC](https://github.com/ZDRAVKO107UKTC)

This project was created as part of a school or personal development initiative to practice full-stack web development using PHP, MySQL, and Tailwind CSS. The structure is simple, but scalable for small gallery-based web systems.

---

## 📬 Contact & Support

If you find issues, want to contribute, or have questions:

- Open an issue in this repository
- Or contact the author via GitHub: [@ZDRAVKO107UKTC](https://github.com/ZDRAVKO107UKTC)

---

> 🛡️ **Disclaimer:** This project is intended for educational use. For production usage, security enhancements and validation layers are strongly recommended.
