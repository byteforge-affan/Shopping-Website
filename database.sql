SET NAMES utf8mb4; 
CREATE DATABASE IF NOT EXISTS arts_store
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; 
USE arts_store;

-- =====================================================
-- TABLES
-- =====================================================

-- CATEGORIES
CREATE TABLE categories (
  cat_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  cat_name VARCHAR(100) NOT NULL, 
  cat_slug VARCHAR(100) NOT NULL UNIQUE,
  cat_desc VARCHAR(255),
  cat_image VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- PRODUCTS
CREATE TABLE products (
  product_id    INT(11)       NOT NULL AUTO_INCREMENT PRIMARY KEY,
  product_code  VARCHAR(15)   NOT NULL UNIQUE,
  product_name  VARCHAR(200)  NOT NULL,
  description   TEXT          DEFAULT NULL,
  price         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  stock         INT(11)       NOT NULL DEFAULT 0,
  cat_id        INT(11)       DEFAULT NULL,
  product_image VARCHAR(255)  DEFAULT NULL,
  is_new        TINYINT(1)    NOT NULL DEFAULT 0,
  has_warranty  TINYINT(1)    NOT NULL DEFAULT 0,
  is_active     TINYINT(1)    NOT NULL DEFAULT 1,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_cat_id    (cat_id),
  KEY idx_is_new    (is_new),
  KEY idx_is_active (is_active),
  CONSTRAINT fk_products_cat
    FOREIGN KEY (cat_id) REFERENCES categories(cat_id)
    ON DELETE SET NULL ON UPDATE CASCADE
);

-- ADMIN
CREATE TABLE admin (
  admin_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(150),
  phone VARCHAR(20),
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','employee') DEFAULT 'employee',
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE customers (
  customer_id INT(11)      NOT NULL AUTO_INCREMENT PRIMARY KEY,
  full_name   VARCHAR(150) NOT NULL,
  email       VARCHAR(150) NOT NULL UNIQUE,
  phone       VARCHAR(20)  DEFAULT NULL,
  address     TEXT         DEFAULT NULL,
  city        VARCHAR(100) DEFAULT NULL,
  password    VARCHAR(255) NOT NULL,
  is_active   TINYINT(1)   NOT NULL DEFAULT 1,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_email     (email),
  KEY idx_is_active (is_active)
);

-- ORDERS
CREATE TABLE orders (
  order_id        INT(11)       NOT NULL AUTO_INCREMENT PRIMARY KEY,
  order_number    VARCHAR(20)   NOT NULL UNIQUE,
  checkout_token  CHAR(64)       DEFAULT NULL UNIQUE COMMENT 'Idempotency key for checkout submissions',
  customer_id     INT(11)       NOT NULL,
  delivery_type   TINYINT(4)    NOT NULL COMMENT '1=Card 2=Cheque 3=COD',
  payment_details JSON          DEFAULT NULL COMMENT 'Structured payment info per delivery_type',
  address         TEXT          NOT NULL,
  city            VARCHAR(100)  DEFAULT NULL,
  notes           TEXT          DEFAULT NULL,
  subtotal        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  shipping        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status          ENUM('pending','confirmed','dispatched','delivered','cancelled')
                                NOT NULL DEFAULT 'pending',
  created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_customer_id (customer_id),
  KEY idx_status      (status),
  KEY idx_created_at  (created_at),
  CONSTRAINT fk_orders_customer
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
    ON DELETE CASCADE ON UPDATE CASCADE
);

-- ORDER ITEMS
CREATE TABLE order_items (
  item_id    INT(11)       NOT NULL AUTO_INCREMENT PRIMARY KEY,
  order_id   INT(11)       NOT NULL,
  product_id INT(11)       NOT NULL,
  qty        INT(11)       NOT NULL DEFAULT 1,
  price      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  KEY idx_order_id   (order_id),
  KEY idx_product_id (product_id),
  CONSTRAINT fk_items_order
    FOREIGN KEY (order_id)   REFERENCES orders(order_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_items_product
    FOREIGN KEY (product_id) REFERENCES products(product_id)
    ON DELETE RESTRICT ON UPDATE CASCADE
);



-- ============================================================
--  order_requests table — full fresh create
-- ============================================================
CREATE TABLE order_requests (
  request_id           INT(11)      NOT NULL AUTO_INCREMENT PRIMARY KEY,
  order_id             INT(11)      NOT NULL,
  customer_id          INT(11)      NOT NULL,
  type                 ENUM('replace','return') NOT NULL,
  reason               TEXT         DEFAULT NULL,
  status               ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  refund_note          TEXT         DEFAULT NULL COMMENT 'Admin refund note for return approvals',
  replacement_order_id INT(11)      DEFAULT NULL COMMENT 'New order ID created on replace approval',
  created_at           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_order_id              (order_id),
  KEY idx_customer_id           (customer_id),
  KEY idx_status                (status),
  CONSTRAINT fk_req_order
    FOREIGN KEY (order_id)    REFERENCES orders(order_id)    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_req_customer
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE ON UPDATE CASCADE
); 

-- FEEDBACK
CREATE TABLE feedback (
  feedback_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150),
  subject VARCHAR(255),
  message TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- WISHLIST
CREATE TABLE wishlist (
  wishlist_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  customer_id INT(11) NOT NULL,
  product_id INT(11) NOT NULL,
  added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_wish (customer_id, product_id),
  KEY product_id (product_id),
  CONSTRAINT fk_wishlist_customer FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE,
  CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- =====================================================
-- DATA
-- =====================================================

-- ── CATEGORIES (from new file — has real uploaded images) ──
INSERT INTO `categories` (`cat_id`, `cat_name`, `cat_slug`, `cat_desc`, `cat_image`, `created_at`) VALUES
(1, 'Gift Articles',   'gift-articles',   'Unique & beautiful gifts for every special occasion',        'uploads/categories/cat_1773712715_301.png', '2026-03-17 01:30:26'),
(2, 'Greeting Cards',  'greeting-cards',  'Heartfelt cards for birthdays, Eid, weddings & more',       'uploads/categories/cat_1773700264_875.jpg', '2026-03-17 01:30:26'),
(3, 'Hand Bags',       'hand-bags',       'Stylish & trendy bags for every look and occasion',          'uploads/categories/cat_1773700273_104.jpg', '2026-03-17 01:30:26'),
(5, 'Beauty Products', 'beauty-products', 'Glow up with our curated beauty & skincare collection',      'uploads/categories/cat_1773703268_194.jpg', '2026-03-17 01:30:26'),
(4, 'Wallet',          'wallet',          'Premium quality wallets for ladies and gentlemen',           'uploads/categories/cat_1773778282_221.jfif', '2026-03-18 01:11:22');

-- ── PRODUCTS (from new file — has real uploaded images) ──
INSERT INTO products (product_id, product_code, product_name, description, price, stock, cat_id, product_image, is_new, has_warranty, created_at) VALUES
-- Gift Articles (cat_id = 1)
(1,  'GA00001', 'Crystal Gift Box',            'Elegant crystal gift box perfect for birthdays and special occasions',                            1500.00, 20, 1, 'uploads/products/prod_1773751106_297.jpg',  1, 0, '2026-03-17 07:19:25'),
(2,  'GA00002', 'Luxury Hamper Set',            'Premium luxury hamper with assorted goodies for gifting',                                         3800.00, 10, 1, 'uploads/products/prod_1773750667_652.jpg',  0, 0, '2026-03-17 07:19:25'),
(3,  'GA00003', 'Scented Candle Gift',          'Beautiful scented candle set in decorative packaging',                                            1200.00, 25, 1, 'uploads/products/prod_1773750119_800.jpg',  1, 0, '2026-03-17 07:19:25'),
(4,  'GA00004', 'Personalized Mug',             'Custom printed mug with name or photo, perfect for gifting',                                       900.00, 30, 1, 'uploads/products/prod_1773749840_962.jpg',  0, 0, '2026-03-17 07:19:25'),
(5,  'GA00005', 'Teddy Bear Plush',             'Soft and cute teddy bear, ideal for special occasions and surprises',                             1300.00, 15, 1, 'uploads/products/prod_1773749899_461.png',  1, 0, '2026-03-17 07:19:25'),
-- Greeting Cards (cat_id = 2)
(6,  'GC00001', 'Eid Mubarak Card Pack',        'Beautiful Eid Mubarak card pack with 6 cards and envelopes',                                       350.00, 50, 2, 'uploads/products/prod_1773761718_652.jpg',  1, 0, '2026-03-17 07:19:25'),
(7,  'GC00002', 'Birthday Greeting Card',       'Colorful birthday greeting card with glitter finish',                                              250.00, 60, 2, 'uploads/products/prod_1773761852_466.jpg',  0, 0, '2026-03-17 07:19:25'),
(8,  'GC00003', 'Wedding Congratulations Card', 'Elegant wedding congratulations card with golden print',                                           300.00, 40, 2, 'uploads/products/prod_1773761875_696.jpg',  0, 0, '2026-03-17 07:19:25'),
(9,  'GC00004', 'New Year Card Pack',           'Festive new year greeting card pack with 8 cards and envelopes',                                   400.00, 45, 2, 'uploads/products/prod_1773761903_727.jpg',  1, 0, '2026-03-17 07:19:25'),
(10, 'GC00005', 'Thank You Card Set',           'Elegant thank you card set with 5 cards for any occasion',                                         280.00, 55, 2, 'uploads/products/prod_1773761925_135.jpg',  0, 0, '2026-03-17 07:19:25'),
-- Hand Bags (cat_id = 3)
(11, 'HB00001', 'Leather Tote Bag',             'Premium leather tote bag with spacious compartments for daily use',                               4500.00, 15, 3, 'uploads/products/prod_1773762193_792.jpg',  1, 0, '2026-03-17 07:19:25'),
(12, 'HB00002', 'Ladies Shoulder Bag',          'Stylish ladies shoulder bag with adjustable strap and zip closure',                               3200.00, 20, 3, 'uploads/products/prod_1773762269_695.jpg',  0, 0, '2026-03-17 07:19:25'),
(13, 'HB00003', 'Mini Crossbody Bag',           'Trendy mini crossbody bag perfect for outings and casual wear',                                   2800.00, 18, 3, 'uploads/products/prod_1773762904_993.jpg',  1, 0, '2026-03-17 07:19:25'),
(14, 'HB00004', 'Quilted Chain Bag',            'Elegant quilted chain bag with gold hardware and zip closure',                                    3600.00, 12, 3, 'uploads/products/prod_1773762797_601.png',  0, 0, '2026-03-17 07:19:25'),
(15, 'HB00005', 'Woven Straw Bag',              'Trendy woven straw bag perfect for summer and beach outings',                                     2200.00, 22, 3, 'uploads/products/prod_1773762821_823.png',  1, 0, '2026-03-17 07:19:25'),
-- Wallets (cat_id = 4)
(16, 'WL00001', 'Ladies Leather Wallet',        'Slim genuine leather wallet with multiple card slots, zippered coin pocket and bill compartment',  1800.00, 30, 4, 'uploads/products/prod_1773778338_493.png',  0, 0, '2026-03-17 07:19:25'),
(17, 'WL00002', 'Mens Bifold Wallet',           'Classic mens bifold wallet from premium leather with RFID blocking, 6 card slots, 2 bill compartments', 1500.00, 25, 4, 'uploads/products/prod_1773778365_456.jfif', 0, 0, '2026-03-17 07:19:25'),
(18, 'WL00003', 'Zip Around Wallet',            'Stylish zip around wallet with 360 degree zipper, 8 card slots, 2 bill sections and coin pocket',  2200.00, 20, 4, 'uploads/products/prod_1773778395_780.jpg',  1, 0, '2026-03-17 07:19:25'),
(19, 'WL00004', 'Card Holder Wallet',           'Minimalist slim card holder from quality leather, holds up to 8 cards with easy pull tab',          950.00, 35, 4, 'uploads/products/prod_1773778423_111.jfif', 0, 0, '2026-03-17 07:19:25'),
(20, 'WL00005', 'Flap Snap Wallet',             'Elegant flap snap closure wallet with built-in mirror, multiple card slots and notes compartment',  1650.00, 28, 4, 'uploads/products/prod_1773778441_169.jfif', 1, 0, '2026-03-17 07:19:25'),
-- Beauty Products (cat_id = 5)
(21, 'BP00001', 'Lipstick Gift Set',            'Set of 4 premium lipsticks in trending shades with mirror box',                                   1200.00, 35, 5, 'uploads/products/prod_1773777733_515.jpg',  1, 0, '2026-03-17 07:19:25'),
(22, 'BP00002', 'Face Glow Serum',              'Brightening face glow serum with vitamin C and hyaluronic acid',                                  2500.00, 20, 5, 'uploads/products/prod_1773777754_747.jfif', 0, 0, '2026-03-17 07:19:25'),
(23, 'BP00003', 'Perfume Gift Set',             'Luxury perfume gift set with 3 fragrances in beautiful packaging',                                3500.00, 15, 5, 'uploads/products/prod_1773777782_600.jfif', 1, 0, '2026-03-17 07:19:25'),
(24, 'BP00004', 'Makeup Brush Set',             'Professional makeup brush set with 12 brushes and leather pouch',                                 1800.00, 25, 5, 'uploads/products/prod_1773777829_197.jpg',  0, 0, '2026-03-17 07:19:25'),
(25, 'BP00005', 'Rose Face Mask Pack',          'Nourishing rose face mask pack with 5 sheets for glowing skin',                                    850.00, 40, 5, 'uploads/products/prod_1773777448_403.jpg',  1, 0, '2026-03-17 07:19:25');


-- ── ADMIN ──
INSERT INTO `admin` (`admin_id`, `full_name`, `username`, `email`, `password`, `role`, `is_active`, `created_at`) VALUES
(1, 'Admin User', 'admin',     'admin@artsstore.pk', '0192023a7bbd73250516f069df18b500', 'admin',    1, '2026-03-17 01:30:27'),
(4, 'Ali Hassan', 'employee1', 'ali@artsstore.pk',   '0314ee502c6f4e284128ad14e84e37d5', 'employee', 1, '2026-03-17 01:30:27');

-- ── CUSTOMER ──
INSERT INTO customers (customer_id, full_name, email, phone, address, city, password, created_at) VALUES
(1, 'Ahmed Khan', 'ahmed@gmail.com',      '03001111111', 'House 12 Block B Gulshan', 'Karachi',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-03-17 01:30:26'),
   (2, 'Muhannad Saad', 'saad@gmail.com', '03015658973', 'Street 4, Sector G-7/1, Islamabad', 'Islamabad', '$2y$10$50Z3svDIApddVzS1FZD87uZI/8zoSiH5ENNZBfTJvaSSl5Jd.xT62',  '2026-03-26 18:40:05'),
(3, 'SaraKhan', 'sara@gmail.com', '03247925647', '12-A, Block R, Model Town, Lahore', 'Lahore', '$2y$10$nJKKR2B4cLiz75duRdKSxeZ9CUgn/e6802cyDhZ47OMJxQ5HVwmuG',  '2026-03-26 18:46:50');

-- ── ORDERS ──
INSERT INTO orders (order_id, order_number, customer_id, delivery_type, payment_details, address, city, notes, subtotal, shipping, total, status, created_at,updated_at) VALUES
(1, '3000002045778907', 1, 2,
   '{"method":"cheque","cheque_number":"0012345","bank_name":"HBL","branch":"Karachi Main","cheque_date":"2026-03-19","account_name":"Ahmed Khan"}',
   'House 12 Block B Gulshan, Karachi', 'Karachi', '',
   3000.00, 200.00, 3200.00, 'dispatched', '2026-03-19 19:46:38', '2026-03-20 10:00:00'),
   (2, '1000001139881624', 2, 1, '{"method":"card","card_type":"other","card_name":"SAAD","card_last4":"0255","card_expiry":"03 / 28"}', 'Street 4, Sector G-7/1, Islamabad', 'Islamabad', '', 4500.00, 200.00, 4700.00, 'delivered', '2026-03-26 18:43:41', '2026-03-27 09:30:00'),
(3, '3000001210628423', 3, 3, '{"method":"cod"}', '12-A, Block R, Model Town, Lahore', 'Lahore', '', 6200.00, 0.00, 6200.00, 'dispatched', '2026-03-26 18:48:24', '2026-03-27 10:00:00');

-- ── ORDER ITEMS ──
INSERT INTO order_items (item_id,order_id, product_id, qty, price) VALUES
(1,1, 1,  1, 1500.00),
(2,2, 11, 1, 4500.00),
(3,3, 12, 1, 3200.00),
(4,3, 21, 1, 1200.00),
(5,3, 24, 1, 1800.00);



-- ── FEEDBACKS ──
INSERT INTO `feedback` (`email`, `subject`, `message`, `created_at`) VALUES
('sara.ahmed@gmail.com',   'Great Experience!',        'I recently ordered from Arts Store and the experience was amazing. The Leather Tote Bag I ordered was exactly as described and arrived in perfect condition. Packaging was neat and delivery was on time. Will definitely order again!', '2026-03-19 20:15:00'),
('ali.raza@hotmail.com',   'Fast Delivery',            'Very happy with my purchase. The product quality is excellent and the delivery was faster than expected. Customer service was also very helpful when I had a query. Highly recommend Arts Store to everyone!', '2026-03-19 21:30:00'),
('fatima.k@yahoo.com',     'Product Quality Issue',    'The item I received had a small defect on the packaging but the product itself was fine. Would appreciate better quality control. However the overall shopping experience was smooth and easy. I would still shop here again.', '2026-03-20 09:10:00'),
('usman.malik@gmail.com',  'Loved the Gift Set!',      'Bought the Perfume Gift Set as a birthday present for my wife and she absolutely loved it. The presentation box was beautiful and the fragrances are wonderful. This is now my go-to store for gifts. Thank you Arts Store!', '2026-03-20 11:45:00'),
('hira.ch@gmail.com',      'Website is Easy to Use',   'Very smooth and simple website. Found exactly what I was looking for within minutes. Checkout process was quick. The Cash on Delivery option is very convenient for people like me who prefer not to use cards online. Great work!', '2026-03-20 14:22:00');

-- =====================================================