-- UPDATE Products Table - Chỉ chạy file này để cập nhật dữ liệu
-- Không cần chạy lại SQL toàn bộ

USE Soudemy_Demo;

-- Xóa sản phẩm cũ và thêm sản phẩm mới
TRUNCATE TABLE products;

-- Insert sample products với ảnh đúng
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
