-- Migration: Remove obsolete table `teacher_accounts` and fix `student_attendance` foreign key

-- Step 1: Drop the obsolete foreign key on `student_attendance` that points to `teacher_accounts`
ALTER TABLE `student_attendance`
  DROP FOREIGN KEY `fk_student_attendance_on_teacher_account_delete`;

-- Step 2: Clean up any invalid teacher IDs in `student_attendance` before adding the new constraint
UPDATE `student_attendance`
SET `processed_by_teacher` = NULL
WHERE `processed_by_teacher` IS NOT NULL
  AND `processed_by_teacher` NOT IN (SELECT `id` FROM `teachers`);

-- Step 3: Add foreign key referencing `teachers` (`id`) with ON DELETE SET NULL
ALTER TABLE `student_attendance`
  ADD CONSTRAINT `fk_student_attendance_processed_by_teacher`
  FOREIGN KEY (`processed_by_teacher`) REFERENCES `teachers` (`id`)
  ON DELETE SET NULL;

-- Step 4: Drop the obsolete `teacher_accounts` table
DROP TABLE IF EXISTS `teacher_accounts`;

-- --------------------------------------------------------
-- Migration: Parent App School Store & Orders Support
-- --------------------------------------------------------

-- Reservations keep physical stock/cost intact until delivery, and protect counter sales.
-- Safe to re-run when the earlier Store migration was already imported.
SET @store_reserved_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='inventory_products' AND column_name='reserved_quantity'),
  'DO 0',
  'ALTER TABLE inventory_products ADD COLUMN reserved_quantity int unsigned NOT NULL DEFAULT 0 AFTER stock_quantity'
);
PREPARE store_reserved_stmt FROM @store_reserved_sql;
EXECUTE store_reserved_stmt;
DEALLOCATE PREPARE store_reserved_stmt;

CREATE TABLE IF NOT EXISTS `inventory_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `total_paise` bigint NOT NULL DEFAULT '0',
  `payment_method` enum('online','pay_at_school') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pay_at_school',
  `payment_status` enum('pending','paid','failed','refund_pending','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `razorpay_order_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razorpay_payment_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refunded_paise` bigint unsigned NOT NULL DEFAULT 0,
  `order_status` enum('placed','confirmed','ready_for_pickup','delivered','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'placed',
  `note` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `request_key` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_id` bigint unsigned DEFAULT NULL,
  `delivered_by` int DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_inventory_orders_order_number` (`order_number`),
  UNIQUE KEY `idx_inventory_orders_request` (`parent_phone`, `request_key`),
  UNIQUE KEY `idx_inventory_orders_razorpay_order` (`razorpay_order_id`),
  UNIQUE KEY `idx_inventory_orders_razorpay_payment` (`razorpay_payment_id`),
  UNIQUE KEY `idx_inventory_orders_document` (`document_id`),
  KEY `idx_inventory_orders_student_id` (`student_id`),
  KEY `idx_inventory_orders_status` (`order_status`),
  KEY `idx_inventory_orders_payment_status` (`payment_status`),
  KEY `idx_inventory_orders_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Offline partial payment and settlement upgrade. Existing bills start with zero waived.
-- Signed amount also preserves the reversal on a full-return document.
SET @inventory_waived_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='inventory_documents' AND column_name='waived_paise'),
  'DO 0',
  'ALTER TABLE inventory_documents ADD COLUMN waived_paise bigint NOT NULL DEFAULT 0 AFTER paid_paise'
);
PREPARE inventory_waived_stmt FROM @inventory_waived_sql;
EXECUTE inventory_waived_stmt;
DEALLOCATE PREPARE inventory_waived_stmt;

CREATE TABLE IF NOT EXISTS `inventory_bill_adjustments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `document_id` bigint unsigned NOT NULL,
  `return_document_id` bigint unsigned DEFAULT NULL,
  `amount_paise` bigint NOT NULL,
  `reason` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_inventory_adjustment_bill` (`document_id`),
  KEY `idx_inventory_adjustment_date` (`created_at`),
  CONSTRAINT `fk_inventory_adjustment_bill` FOREIGN KEY (`document_id`) REFERENCES `inventory_documents` (`id`),
  CONSTRAINT `fk_inventory_adjustment_return` FOREIGN KEY (`return_document_id`) REFERENCES `inventory_documents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Provider cash movements are independent of the date stock is handed over.
CREATE TABLE IF NOT EXISTS `inventory_order_payment_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `event_key` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount_paise` bigint NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_store_payment_event` (`event_key`),
  KEY `idx_store_payment_order` (`order_id`),
  KEY `idx_store_payment_date` (`created_at`),
  CONSTRAINT `fk_store_payment_order` FOREIGN KEY (`order_id`) REFERENCES `inventory_orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inventory_order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'piece',
  `quantity` int unsigned NOT NULL DEFAULT '1',
  `unit_price_paise` bigint NOT NULL DEFAULT '0',
  `line_total_paise` bigint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_inventory_order_items_order_id` (`order_id`),
  UNIQUE KEY `idx_inventory_order_items_product` (`order_id`, `product_id`),
  KEY `idx_inventory_order_items_product_id` (`product_id`),
  CONSTRAINT `fk_inventory_order_items_order_id` FOREIGN KEY (`order_id`) REFERENCES `inventory_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
