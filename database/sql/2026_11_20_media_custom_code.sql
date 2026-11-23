-- For a host whose database was imported before 2026-11-20 (same as migrations 2026_11_20_000100 and 2026_11_20_000200). Run once in phpMyAdmin.
ALTER TABLE `media_assets` ADD COLUMN `is_inline` tinyint(1) NOT NULL DEFAULT 0 AFTER `kind`;

ALTER TABLE `page_modules` ADD COLUMN `css_class` varchar(120) NULL, ADD COLUMN `css_id` varchar(64) NULL;

CREATE TABLE IF NOT EXISTS `custom_snippets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(120) NOT NULL,
  `type` varchar(3) NOT NULL,
  `code` mediumtext NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Make your own account superadmin (the only role that sees "custom style and script"):
-- UPDATE `users` SET `role` = 'superadmin' WHERE `email` = 'YOUR-EMAIL';

-- Badge form opens only while an edition is marked current. Either tick "current" on an edition in the panel, or:
-- INSERT INTO `editions` (`code`, `name`, `is_current`, `created_at`, `updated_at`) VALUES ('2027', 'Benis Fairs 2027', 1, NOW(), NOW());
