-- Migration: Add test_drives table to existing database
-- Run: mysql -u root -p vinfast_db < database/migrate_test_drives.sql

USE vinfast_db;

CREATE TABLE IF NOT EXISTS test_drives (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name           VARCHAR(100) NOT NULL,
  email          VARCHAR(150) NOT NULL,
  phone          VARCHAR(20)  DEFAULT NULL,
  product_id     INT UNSIGNED DEFAULT NULL,
  province       VARCHAR(100) DEFAULT NULL,
  showroom       VARCHAR(200) DEFAULT NULL,
  preferred_date DATE         DEFAULT NULL,
  note           TEXT         DEFAULT NULL,
  status         ENUM('pending','confirmed','cancelled','done') NOT NULL DEFAULT 'pending',
  created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

