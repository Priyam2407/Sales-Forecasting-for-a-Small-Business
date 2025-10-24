Sales Forecasting System (HTML/CSS/JS + PHP + MySQL)
----------------------------------------------------
What's inside:
- index.php            : Dashboard (Monthly & Product charts)
- add_sale.php         : Form to add sale records
- view_sales.php       : View all sales table
- forecast.php         : Run simple moving-average forecast (3-month avg)
- export_csv.php       : Export sales CSV
- db.php               : Database connection config (edit credentials)
- init_db.sql          : SQL to create database & sample data
- assets/css/styles.css
- assets/js/main.js    : Chart.js init & simple UI JS
- sample_data.sql      : sample INSERTs for testing

Quick setup:
1. Install XAMPP or a PHP + MySQL stack and start Apache & MySQL.
2. Place the folder 'sales_forecast_php' inside your webserver folder (e.g., C:\xampp\htdocs\).
3. Edit db.php and set DB_USER, DB_PASS if needed.
4. Import init_db.sql into MySQL:
   - Using terminal: mysql -u root -p < init_db.sql
   - Or use phpMyAdmin and import the file.
5. Open in browser: http://localhost/sales_forecast_php/index.php
6. Add sales via 'Add Sale', view them, run Forecast.

Notes:
- Charts use Chart.js CDN. Make sure your machine has internet to load CDN.
- Forecast implemented as simple 3-month moving average on monthly revenue.
- For production, secure DB credentials and add input validation.
