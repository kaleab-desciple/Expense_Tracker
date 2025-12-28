# Expense Tracker

A comprehensive web-based application designed to help users manage their personal finances effectively. This project allows users to track expenses, manage incomes, set budgets, and monitor financial goals through an intuitive dashboard and detailed reports.

Repository: [https://github.com/kaleab-desciple/Expense_Tracker](https://github.com/kaleab-desciple/Expense_Tracker)

## Features

- **User Authentication**: Secure Login and Registration system with password hashing.
- **Dashboard**: Visual overview of recent expenses and spending distribution by category.
- **Expense Management**: Add, edit, delete, and categorize daily expenses.
- **Income Tracking**: Record and manage various income sources.
- **Budgeting**: Set spending limits for specific categories and time periods.
- **Goal Setting**: Create financial goals (e.g., "New Car") and track savings progress with visual progress bars.
- **Debt/Budget Analysis**: Monitor remaining budget and identify overspending (Debt).
- **Reports**: Visual charts (Bar, Doughnut) and detailed tables comparing budgets vs. actual expenses.
- **Date Filtering**: Filter transaction history by specific date ranges.

## Technologies Used

- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, Bootstrap
- **Scripting**: JavaScript, jQuery, Chart.js

## Installation

### 1. Clone the Repository
```bash
git clone https://github.com/kaleab-desciple/Expense_Tracker.git
cd Expense_Tracker
```

### 2. Database Setup
1. Create a MySQL database named `Expense_Tracker`.
2. Import the following SQL schema to create the necessary tables:

```sql
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `birthday` date DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `role` varchar(50) DEFAULT 'user',
  `datetime_registered` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `datetime_added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text,
  `date` date NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `incomes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text,
  `date` date NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `budgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `limit_amount` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `goals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `target_amount` decimal(10,2) DEFAULT '0.00',
  `current_amount` decimal(10,2) DEFAULT '0.00',
  `deadline` date DEFAULT NULL,
  PRIMARY KEY (`id`)
);
```

### 3. Configuration
1. Open `Includes/Functions/functions.php`.
2. Update the database connection details if necessary:
```php
$dbname = "Expense_Tracker";
$host = "localhost";
$user = "root"; // Your database username
$password = ""; // Your database password
```

### 4. Run the Application
1. Place the project folder in your web server's root directory (e.g., `htdocs` for XAMPP or `/var/www/html` for Apache).
2. Open your browser and navigate to `http://localhost/Expense_Tracker`.
