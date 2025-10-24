CREATE DATABASE IF NOT EXISTS salesdb;
USE salesdb;

CREATE TABLE IF NOT EXISTS sales (
    sale_id INT PRIMARY KEY AUTO_INCREMENT,
    sale_date DATE NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_sale_date ON sales(sale_date);

-- Sample data
INSERT INTO sales (sale_date, product_name, quantity, price) VALUES
('2024-01-05','Notebook', 5, 40.00),
('2024-01-15','Pen', 20, 5.00),
('2024-02-02','Notebook', 3, 40.00),
('2024-02-10','Pencil', 15, 3.00),
('2024-03-04','Pen', 30, 5.00),
('2024-03-20','Notebook', 10, 40.00),
('2024-04-06','Bag', 2, 500.00),
('2024-04-12','Pen', 10, 5.00),
('2024-05-21','Bag', 1, 500.00),
('2024-06-03','Notebook', 7, 40.00),
('2024-07-08','Pen', 12, 5.00),
('2024-07-30','Pencil', 20, 3.00),
('2024-08-15','Notebook', 9, 40.00),
('2024-09-10','Bag', 1, 500.00),
('2024-10-05','Pen', 25, 5.00),
('2024-10-20','Notebook', 4, 40.00);
