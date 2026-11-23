-- For a host whose database was imported before 2026-11-19 (same as migration 2026_11_19_000100). Safe to run once in phpMyAdmin.
CREATE TABLE IF NOT EXISTS `page_placements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) unsigned NOT NULL,
  `placeable_type` varchar(32) NOT NULL,
  `placeable_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_placements_placeable_type_placeable_id_page_id_unique` (`placeable_type`,`placeable_id`,`page_id`),
  KEY `page_placements_page_id_index` (`page_id`),
  CONSTRAINT `page_placements_page_id_foreign` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `categorizables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `categorizable_type` varchar(32) NOT NULL,
  `categorizable_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categorizables_unique` (`categorizable_type`,`categorizable_id`,`category_id`),
  KEY `categorizables_category_id_index` (`category_id`),
  CONSTRAINT `categorizables_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
