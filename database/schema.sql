-- VinFast Website — Database Schema
-- Owner: All members (common)
-- Run: mysql -u root -p vinfast_db < database/schema.sql

SET NAMES utf8mb4;
CREATE DATABASE IF NOT EXISTS vinfast_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vinfast_db;

CREATE TABLE IF NOT EXISTS users (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100)  NOT NULL,
  email      VARCHAR(150)  NOT NULL UNIQUE,
  password   VARCHAR(255)  NOT NULL,
  role       ENUM('member','admin') NOT NULL DEFAULT 'member',
  avatar     VARCHAR(255)  DEFAULT NULL,
  is_locked  TINYINT(1)    NOT NULL DEFAULT 0,
  created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS site_settings (
  id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(100) NOT NULL UNIQUE,
  value TEXT         DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS contacts (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(150) NOT NULL,
  phone      VARCHAR(20)  DEFAULT NULL,
  message    TEXT         NOT NULL,
  status     ENUM('unread','read','replied') NOT NULL DEFAULT 'unread',
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS faqs (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  question   VARCHAR(500) NOT NULL,
  answer     TEXT         NOT NULL,
  sort_order SMALLINT     NOT NULL DEFAULT 0,
  is_active  TINYINT(1)   NOT NULL DEFAULT 1,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  name        VARCHAR(200) NOT NULL,
  slug        VARCHAR(220) NOT NULL UNIQUE,
  description TEXT         DEFAULT NULL,
  specs       JSON         DEFAULT NULL,
  price       DECIMAL(15,0) NOT NULL DEFAULT 0,
  images      JSON         DEFAULT NULL,
  is_active   TINYINT(1)   NOT NULL DEFAULT 1,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS carts (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  quantity   SMALLINT     NOT NULL DEFAULT 1,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cart (user_id,product_id),
  FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  type       ENUM('deposit','test_drive') NOT NULL DEFAULT 'deposit',
  status     ENUM('pending','confirmed','cancelled','done') NOT NULL DEFAULT 'pending',
  note       TEXT         DEFAULT NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS news (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title            VARCHAR(300) NOT NULL,
  slug             VARCHAR(320) NOT NULL UNIQUE,
  body             LONGTEXT     NOT NULL,
  catalog          ENUM("Công ty", "Ô tô điện", "Xe máy điện"),
  views            INT DEFAULT 0,
  created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  news_state       ENUM("Hiển thị", "Ẩn") NOT NULL DEFAULT("Hiển thị")
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS news_tags (
  news_id         INT UNSIGNED AUTO_INCREMENT,
  tags            VARCHAR(50) NOT NULL,
  PRIMARY KEY(news_id, tags),
  FOREIGN KEY(news_id) REFERENCES news(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS news_img_info (
  news_id         INT UNSIGNED AUTO_INCREMENT,
  img_link        VARCHAR(300),
  img_des         VARCHAR(300) NOT NULL,
  PRIMARY KEY(news_id, img_link, img_des),
  FOREIGN KEY(news_id) REFERENCES news(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS comments (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED NOT NULL,
  product_id  INT UNSIGNED DEFAULT NULL,
  rating      TINYINT NOT NULL DEFAULT 0,
  body        TEXT         NOT NULL,
  helpful_count INT NOT NULL DEFAULT 0,
  is_approved TINYINT(1)   NOT NULL DEFAULT 0,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed: default admin (password: Admin@123 — re-hash before production!)
INSERT IGNORE INTO users (name,email,password,role) VALUES
  ('Admin VinFast','admin@vinfast.vn',
   '$2y$10$ReplaceThisWithARealBcryptHash','admin');

-- Seed: categories
INSERT IGNORE INTO categories (name,slug) VALUES
  ('Electric Motorbike','electric-motorbike'),
  ('Electric Car','electric-car');

-- Seed: site settings
