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
