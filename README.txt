====================================================
SALES FORECASTING SYSTEM
====================================================

TECHNOLOGIES:
- Frontend: HTML, CSS (assets/css/styles.css), JavaScript (assets/js/main.js, Chart.js CDN)
- Backend: PHP
- Database: MySQL

----------------------------------------------------
FILE STRUCTURE & FUNCTIONALITY
----------------------------------------------------

CORE APPLICATION FILES:
- index.php             : Dashboard view. Displays monthly and product sales charts (using Chart.js) and key metrics.
- add_sale.php          : Form to input and save new sales records into the database.
- view_sales.php        : Lists all sales records in a sortable/filterable table view.
- editsales.php         : Handles the logic and form for updating an existing sales record.
- deletesales.php       : Handles the logic for removing a specific sales record from the database.
- forecast.php          : Runs the forecasting algorithm. Currently implements a simple 3-month moving average on monthly revenue.
- export_csv.php        : Generates and exports the complete sales data as a CSV file for external analysis.

CONFIGURATION & SETUP:
- db.php                : Database connection configuration (host, user, password, database name). IMPORTANT: Edit credentials here.
- init_db.sql           : SQL script to create the necessary database schema (tables) and populate it with sample data.
- sample_data.sql       : Contains sample INSERT statements for testing purposes.

ASSETS:
- assets/css/styles.css : Stylesheet for the application's appearance.
- assets/js/main.js     : JavaScript file responsible for UI interactions and initializing Chart.js visualizations.

----------------------------------------------------
QUICK SETUP GUIDE
----------------------------------------------------

1.  INSTALL STACK:
    Ensure you have a complete PHP + MySQL development environment installed (e.g., **XAMPP**, WAMP, MAMP, or a custom stack). Start both the **Apache (Web Server)** and **MySQL (Database)** services.

2.  DEPLOY FILES:
    Place the entire 'sales_forecast_php' folder inside your web server's document root (e.g., C:\xampp\htdocs\).

3.  CONFIGURE DATABASE CONNECTION:
    Open `db.php` and update the `DB_USER`, `DB_PASS`, `DB_NAME`, and `DB_HOST` constants if they differ from your default settings (e.g., if you're not using 'root' with no password).

4.  INITIALIZE DATABASE:
    Import the `init_db.sql` file into your MySQL server. This creates the database and populates it with initial records.

    * **Using phpMyAdmin:** Navigate to phpMyAdmin, create a new database (if necessary), and use the 'Import' feature to upload `init_db.sql`.
    * **Using Terminal (CLI):**
        ```bash
        mysql -u root -p < init_db.sql
        ```
        (You will be prompted for your MySQL root password.)

5.  ACCESS THE SYSTEM:
    Open your web browser and navigate to the application's URL:
    `http://localhost/sales_forecast_php/index.php`

----------------------------------------------------
NOTES AND RECOMMENDATIONS
----------------------------------------------------

* **Chart.js Dependency:** The charts rely on the Chart.js library loaded via a **CDN (Content Delivery Network)**. Your machine must have an active internet connection to load the charts correctly.
* **Forecasting Method:** The `forecast.php` page uses a **Simple 3-Month Moving Average** for prediction, which is a basic time-series method.
* **Security (IMPORTANT):** For any production deployment, you **MUST** implement proper security measures:
    * **Input Validation:** Sanitize and validate all user inputs in `add_sale.php` and `editsales.php` to prevent **SQL Injection** and other vulnerabilities.
    * **Database Credentials:** Store database credentials securely (e.g., using environment variables) instead of hardcoding them in `db.php`.
