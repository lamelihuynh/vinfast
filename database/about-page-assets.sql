-- About page image references (files already in public/images/uploads/about-page/)
-- Run after schema.sql and products.sql:
--   Get-Content database/about-page-assets.sql | mysql -u root vinfast_db
SET NAMES utf8mb4;
USE vinfast_db;

INSERT INTO site_settings (`key`, value) VALUES
  ('about_intro_image',      'about-page/intro_image.jpg'),
  ('about_hero_image',       'about-page/hero_image.jpg'),
  ('about_vision_image',     'about-page/vision_image.png'),
  ('about_mission_image',    'about-page/mission_image.png'),
  ('about_philosophy_image', 'about-page/philosophy_image.png')
ON DUPLICATE KEY UPDATE value = VALUES(value);

INSERT INTO page_assets (page_type, asset_key, file_path) VALUES
  ('about', 'timeline_2017_main',      'about-page/timeline_2017_main.jpg'),
  ('about', 'timeline_2018_main',      'about-page/timeline_2018_main.jpg'),
  ('about', 'timeline_2018_secondary', 'about-page/timeline_2018_secondary.webp'),
  ('about', 'timeline_2019_main',      'about-page/timeline_2019_main.jpg'),
  ('about', 'timeline_2020_main',      'about-page/timeline_2020_main.jpg'),
  ('about', 'timeline_2020_secondary', 'about-page/timeline_2020_secondary.png'),
  ('about', 'timeline_2021_main',      'about-page/timeline_2021_main.webp'),
  ('about', 'timeline_2022_main',      'about-page/timeline_2022_main.jpg'),
  ('about', 'timeline_2023_main',      'about-page/timeline_2023_main.jpg'),
  ('about', 'timeline_2024_main',      'about-page/timeline_2024_main.jpg'),
  ('about', 'timeline_2025_main',      'about-page/timeline_2025_main.png'),
  ('about', 'timeline_2026_main',      'about-page/timeline_2026_main.jpg')
ON DUPLICATE KEY UPDATE file_path = VALUES(file_path);
