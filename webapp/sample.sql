-- sample.sql: tạo database và bảng tasks với vài bản ghi mẫu
CREATE DATABASE IF NOT EXISTS `webapp_demo` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `webapp_demo`;

CREATE TABLE IF NOT EXISTS `tasks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tasks` (`title`, `description`) VALUES
('Ví dụ 1', 'Đây là bản ghi mẫu 1'),
('Ghi chú test', 'Một mô tả'),
('Kiểm thử tìm kiếm', 'Dùng từ khóa tìm kiếm');

-- Phones/shop schema for demo
CREATE TABLE IF NOT EXISTS `phones` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `model` VARCHAR(255) NOT NULL,
  `brand` VARCHAR(255) DEFAULT NULL,
  `price` DECIMAL(10,2) DEFAULT 0.00,
  `stock` INT DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `phone_id` INT UNSIGNED NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `status` VARCHAR(50) NOT NULL DEFAULT 'created',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY (`phone_id`),
  CONSTRAINT `orders_phone_fk` FOREIGN KEY (`phone_id`) REFERENCES `phones`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `phones` (`model`, `brand`, `price`, `stock`) VALUES
('Galaxy S21','Samsung',799.00,10),
('iPhone 13','Apple',899.00,5),
('Pixel 6','Google',599.00,8);
