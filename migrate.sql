-- Inventory module. Review and run manually on the school database.
-- Additive migration: does not change existing fee, wallet, student or payment data.
-- Amounts are stored as integer paise (100 paise = one currency unit).
-- Do not record these purchases a second time as school expenses.
CREATE TABLE IF NOT EXISTS inventory_products (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    sku VARCHAR(60) NULL,
    category VARCHAR(80) NOT NULL DEFAULT '',
    unit VARCHAR(30) NOT NULL DEFAULT 'piece',
    selling_price_paise BIGINT NOT NULL DEFAULT 0,
    low_stock_level INT UNSIGNED NOT NULL DEFAULT 5,
    stock_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    stock_value_paise BIGINT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY inventory_product_sku (sku),
    KEY inventory_product_search (is_active, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventory_documents (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    kind ENUM('purchase','sale','gift','damage','return') NOT NULL,
    student_id VARCHAR(50) NULL,
    customer_name VARCHAR(150) NOT NULL DEFAULT '',
    class_name VARCHAR(100) NOT NULL DEFAULT '',
    supplier VARCHAR(150) NOT NULL DEFAULT '',
    reference VARCHAR(100) NOT NULL DEFAULT '',
    note VARCHAR(500) NOT NULL DEFAULT '',
    total_paise BIGINT NOT NULL DEFAULT 0,
    cost_paise BIGINT NOT NULL DEFAULT 0,
    paid_paise BIGINT NOT NULL DEFAULT 0,
    status ENUM('posted','returned') NOT NULL DEFAULT 'posted',
    original_document_id BIGINT UNSIGNED NULL,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY inventory_one_full_return (original_document_id),
    KEY inventory_document_date (created_at, kind),
    KEY inventory_student_sales (student_id, kind, status),
    CONSTRAINT inventory_return_original FOREIGN KEY (original_document_id) REFERENCES inventory_documents (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventory_document_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    document_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    unit VARCHAR(30) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    stock_delta INT NOT NULL,
    unit_price_paise BIGINT NOT NULL,
    line_total_paise BIGINT NOT NULL,
    line_cost_paise BIGINT NOT NULL,
    stock_after INT UNSIGNED NOT NULL,
    UNIQUE KEY inventory_document_product (document_id, product_id),
    KEY inventory_product_history (product_id, id),
    CONSTRAINT inventory_item_document FOREIGN KEY (document_id) REFERENCES inventory_documents (id),
    CONSTRAINT inventory_item_product FOREIGN KEY (product_id) REFERENCES inventory_products (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventory_payments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    document_id BIGINT UNSIGNED NOT NULL,
    return_document_id BIGINT UNSIGNED NULL,
    amount_paise BIGINT NOT NULL,
    method ENUM('cash','online','bank') NOT NULL,
    reference VARCHAR(100) NOT NULL DEFAULT '',
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY inventory_payment_document (document_id, id),
    KEY inventory_payment_date (created_at),
    CONSTRAINT inventory_payment_sale FOREIGN KEY (document_id) REFERENCES inventory_documents (id),
    CONSTRAINT inventory_payment_return FOREIGN KEY (return_document_id) REFERENCES inventory_documents (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Prevents a double click or retried request from recording a transaction twice.
CREATE TABLE IF NOT EXISTS inventory_requests (
    request_key CHAR(36) NOT NULL PRIMARY KEY,
    payload_hash CHAR(64) NOT NULL,
    created_by INT NOT NULL,
    result_json TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- No permissions are granted automatically. Super admins retain full access.
-- Assign "manage_inventory" through Manage Admins > Manage Role after migration.
