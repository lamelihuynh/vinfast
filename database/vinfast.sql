-- VinFast Website — Full Sample Data Dump
-- Owner: All members (common)
-- Import: mysql -u root -p vinfast_db < database/vinfast.sql

--SOURCE schema.sql;

-- Sample products
INSERT IGNORE INTO products (category_id,name,slug,description,price,specs,images,is_active)
VALUES
(1,'VinFast Vento','vinfast-vento',
 'Stylish electric scooter for urban commuting.',
 21000000,
 '{"range":"80 km","max_speed":"80 km/h","battery":"4.2 kWh","charge":"4.5 h"}',
 '["products/sample-vento.jpg"]',1),
(1,'VinFast Evo200','vinfast-evo200',
 'High-performance e-motorbike with smart connectivity.',
 25000000,
 '{"range":"100 km","max_speed":"90 km/h","battery":"5 kWh","charge":"4 h"}',
 '["products/sample-evo200.jpg"]',1),
(2,'VinFast VF5 Plus','vinfast-vf5-plus',
 'Compact electric SUV with modern design.',
 458000000,
 '{"range":"326 km","power":"102 hp","battery":"37.26 kWh","seats":5}',
 '["products/sample-vf5.jpg"]',1);

-- Sample FAQ
INSERT IGNORE INTO faqs (question,answer,sort_order) VALUES
('How do I register an account?',
 'Click Register in the top nav, fill in your name, email and password.',1),
('What is the battery warranty?',
 'VinFast provides a 10-year / 200,000 km warranty on batteries and motors.',2),
('How do I book a test drive?',
 'Go to the vehicle detail page and click "Book Test Drive". Our team contacts you within 24 h.',3);
