-- ============================================================
-- LENA BAKERY — DATABASE SCHEMA
-- Import vào phpMyAdmin hoặc chạy: mysql -u root -p lena_bakery < schema.sql
-- ============================================================

-- Bảng đơn hàng
CREATE TABLE IF NOT EXISTS orders (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_code  VARCHAR(20) NOT NULL UNIQUE,            -- VD: LB-20260816-0001
    
    -- Thông tin khách
    customer_name   VARCHAR(100) NOT NULL,
    customer_phone  VARCHAR(20)  NOT NULL,
    customer_addr   VARCHAR(255) DEFAULT NULL,          -- NULL nếu pickup
    
    -- Hình thức giao & Thanh toán
    shipping_method ENUM('pickup','delivery') NOT NULL DEFAULT 'pickup',
    shipping_fee    INT UNSIGNED DEFAULT 0,             -- VND
    payment_method  ENUM('transfer','cod') NOT NULL DEFAULT 'transfer', -- transfer = VietQR, cod = khi nhận hàng
    
    -- Tài chính
    subtotal    INT UNSIGNED NOT NULL DEFAULT 0,        -- tổng sản phẩm
    tip         INT UNSIGNED DEFAULT 0,                 -- tiền tip
    total       INT UNSIGNED NOT NULL DEFAULT 0,        -- subtotal + shipping + tip
    
    -- Nội dung khác
    note        TEXT DEFAULT NULL,                      -- lời nhắn của khách
    
    -- Trạng thái
    status      ENUM('new','confirmed','making','done','cancelled') NOT NULL DEFAULT 'new',
    
    -- Thời gian
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng sản phẩm trong đơn
CREATE TABLE IF NOT EXISTS order_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id    INT UNSIGNED NOT NULL,
    product_id  VARCHAR(50)  NOT NULL,                  -- VD: 'mini', '350ml', 'tin750'
    product_name VARCHAR(100) NOT NULL,
    flavor      VARCHAR(50)  DEFAULT NULL,              -- cacao / matcha
    topping     VARCHAR(100) DEFAULT NULL,              -- dâu / cherry / mix / none
    quantity    TINYINT UNSIGNED NOT NULL DEFAULT 1,
    unit_price  INT UNSIGNED NOT NULL,                  -- VND
    subtotal    INT UNSIGNED NOT NULL,                  -- qty * unit_price
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng tin nhắn từ form IB (in-app messaging)
CREATE TABLE IF NOT EXISTS messages (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_name VARCHAR(100) NOT NULL,
    sender_phone VARCHAR(20) DEFAULT NULL,
    content     TEXT NOT NULL,
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Index để query nhanh
CREATE INDEX idx_orders_status    ON orders(status);
CREATE INDEX idx_orders_created   ON orders(created_at);
CREATE INDEX idx_messages_is_read ON messages(is_read);
