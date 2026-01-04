-- Sword Framework Database Schema
-- Settings tablosu - Tema ayarları için

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text,
  `type` varchar(50) DEFAULT 'general',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_type` (`key`, `type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Varsayılan tema ayarları
INSERT INTO `settings` (`key`, `value`, `type`) VALUES
('frontend_theme', 'default', 'theme'),
('admin_theme', 'default', 'theme');