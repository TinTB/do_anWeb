-- Create database
CREATE DATABASE IF NOT EXISTS Soudemy_Demo;
USE Soudemy_Demo;

-- Bảng người dùng
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    avatar VARCHAR(255),
    payment_method VARCHAR(50),
    bank_card_info TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng sản phẩm
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    description TEXT,
    rating INT DEFAULT 5,
    stock INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng giỏ hàng
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Bảng đơn hàng
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10,2),
    shipping_address TEXT,
    payment_method VARCHAR(50),
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Bảng chi tiết đơn hàng
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT,
    price DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Bảng mã giảm giá
CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount DECIMAL(5,2) NOT NULL,
    min_order DECIMAL(10,2) DEFAULT 0,
    usage_limit INT DEFAULT 1,
    used_count INT DEFAULT 0,
    expires_at DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng sử dụng coupon
CREATE TABLE coupon_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT,
    user_id INT,
    order_id INT,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Bảng banners
CREATE TABLE banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    image VARCHAR(255),
    link VARCHAR(255),
    position VARCHAR(50),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample admin user (password: admin123)
INSERT INTO users (username, email, password, full_name, phone, role) 
VALUES ('admin', 'admin@soudemy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '0123456789', 'admin');

-- Insert sample products
INSERT INTO products (name, category, price, image, description, stock, rating) VALUES
('Modern Comfort Sofa', 'sofa', 750.00, 'images/Sofa/sofa1.png', 'Premium comfortable sofa for your living room', 15, 5),
('Elegant Sectional Sofa', 'sofa', 899.00, 'images/Sofa/sofa2.png', 'Luxury sectional sofa with premium fabric', 10, 5),
('Classic Brown Sofa', 'sofa', 650.00, 'images/Sofa/sofa3.png', 'Beautiful classic sofa with elegant design', 8, 5),
('Beige Sectional Sofa', 'sofa', 950.00, 'images/Sofa/sofa4.png', 'Spacious sectional sofa perfect for families', 12, 5),
('Modern Dining Table', 'table', 456.00, 'images/table/table1.png', 'Contemporary dining table for family gatherings', 8, 5),
('Contemporary Coffee Table', 'table', 299.00, 'images/table/table2.png', 'Stylish coffee table for your living room', 12, 5),
('Wooden Dining Table', 'table', 389.00, 'images/table/table3.png', 'Rustic wooden table with natural finish', 6, 5),
('Glass Coffee Table', 'table', 245.00, 'images/table/table4.png', 'Modern glass table with steel base', 10, 5),
('Modern Table Lamp', 'lamp', 156.00, 'images/lamp/lamp1.png', 'Elegant table lamp with modern design', 20, 5),
('Contemporary Floor Lamp', 'lamp', 234.00, 'images/lamp/lamp2.png', 'Tall floor lamp for ambient lighting', 15, 5),
('Desk Lamp', 'lamp', 89.00, 'images/lamp/lamp3.png', 'Compact desk lamp for work areas', 18, 5),
('Pendant Light', 'lamp', 178.00, 'images/lamp/lamp4.png', 'Modern pendant lamp for dining areas', 14, 5),
('Modern Queen Bed', 'bed', 899.00, 'images/bed/giuong1.png', 'Comfortable queen size bed with storage', 6, 5),
('Luxury King Bed', 'bed', 1299.00, 'images/bed/giuong2.png', 'Premium king size bed with headboard', 4, 5),
('Twin Platform Bed', 'bed', 549.00, 'images/bed/giuong3.png', 'Sleek twin bed with storage drawers', 9, 5),
('Upholstered Bed', 'bed', 799.00, 'images/bed/giuong4.png', 'Elegant upholstered bed with cushioned headboard', 7, 5),
('Modern Bookshelf', 'bookshelf', 345.00, 'images/bookshelf/ke1.png', 'Stylish bookshelf for your collection', 10, 5),
('Contemporary Display Shelf', 'bookshelf', 289.00, 'images/bookshelf/ke2.png', 'Modern display shelf for decorations', 8, 5),
('Wooden Storage Shelf', 'bookshelf', 215.00, 'images/bookshelf/ke3.png', 'Wooden shelves with rustic appeal', 12, 5),
('Metal Frame Shelf', 'bookshelf', 269.00, 'images/bookshelf/ke4.png', 'Industrial style shelf with metal frame', 11, 5);

-- Insert sample coupons
INSERT INTO coupons (code, discount, min_order, usage_limit, expires_at) VALUES
('WELCOME10', 10.00, 100.00, 1, '2024-12-31'),
('SUMMER25', 25.00, 200.00, 2, '2024-08-31'),
('FREESHIP', 15.00, 150.00, 1, '2024-10-31');

-- Insert sample banners
INSERT INTO banners (title, description, image, link, position, is_active) VALUES
('Summer Sale', 'Get up to 50% off on all furniture', 'images/banner1.jpg', 'shop.php?category=sofa', 'home_top', true),
('New Arrivals', 'Discover our latest furniture collection', 'images/banner2.jpg', 'shop.php', 'home_middle', true),
('Free Shipping', 'Free shipping on orders over $500', 'images/banner3.jpg', 'shop.php', 'home_bottom', true);