CREATE DATABASE IF NOT EXISTS artbid CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE artbid;
CREATE TABLE IF NOT EXISTS users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 role ENUM('Seller','Buyer') NOT NULL DEFAULT 'Buyer',
 phone VARCHAR(32) DEFAULT NULL,
 location VARCHAR(160) DEFAULT NULL,
 bio TEXT DEFAULT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS artworks (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 seller_id INT UNSIGNED NOT NULL,
 title VARCHAR(180) NOT NULL,
 description TEXT NOT NULL,
 category VARCHAR(80) NOT NULL,
 image_url VARCHAR(1000) DEFAULT NULL,
 base_price DECIMAL(12,2) NOT NULL,
 starts_at DATETIME NOT NULL,
 ends_at DATETIME NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX auction_ends (ends_at), INDEX artwork_category (category)
);
CREATE TABLE IF NOT EXISTS bids (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 artwork_id INT UNSIGNED NOT NULL,
 buyer_id INT UNSIGNED NOT NULL,
 amount DECIMAL(12,2) NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (artwork_id) REFERENCES artworks(id) ON DELETE CASCADE,
 FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX bid_artwork (artwork_id, amount), INDEX bid_buyer (buyer_id)
);
CREATE TABLE IF NOT EXISTS wishlist (
 user_id INT UNSIGNED NOT NULL,
 artwork_id INT UNSIGNED NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (user_id, artwork_id),
 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY (artwork_id) REFERENCES artworks(id) ON DELETE CASCADE
);

-- Upgrade older databases without duplicating profile columns.
SET @add_phone = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE users ADD COLUMN phone VARCHAR(32) DEFAULT NULL', 'SELECT 1') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'users' AND column_name = 'phone');
PREPARE artbid_stmt FROM @add_phone; EXECUTE artbid_stmt; DEALLOCATE PREPARE artbid_stmt;
SET @add_location = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE users ADD COLUMN location VARCHAR(160) DEFAULT NULL', 'SELECT 1') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'users' AND column_name = 'location');
PREPARE artbid_stmt FROM @add_location; EXECUTE artbid_stmt; DEALLOCATE PREPARE artbid_stmt;
SET @add_bio = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE users ADD COLUMN bio TEXT DEFAULT NULL', 'SELECT 1') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'users' AND column_name = 'bio');
PREPARE artbid_stmt FROM @add_bio; EXECUTE artbid_stmt; DEALLOCATE PREPARE artbid_stmt;
USE artbid;

-- Demo password for every account below: password123
-- These are bcrypt hashes generated with PHP password_hash().
INSERT INTO users (name, email, password, role) VALUES
('Ananya Rao', 'ananya.seller@artbid.test', '$2y$10$ClB0UXkJAytqyNQwFVRj9uqR0uXOcZ2.oThP4K7SkmiPEeECmYoeW', 'Seller'),
('Kabir Mehta', 'kabir.seller@artbid.test', '$2y$10$ClB0UXkJAytqyNQwFVRj9uqR0uXOcZ2.oThP4K7SkmiPEeECmYoeW', 'Seller'),
('Mira Shah', 'mira.buyer@artbid.test', '$2y$10$ClB0UXkJAytqyNQwFVRj9uqR0uXOcZ2.oThP4K7SkmiPEeECmYoeW', 'Buyer'),
('Arjun Das', 'arjun.buyer@artbid.test', '$2y$10$ClB0UXkJAytqyNQwFVRj9uqR0uXOcZ2.oThP4K7SkmiPEeECmYoeW', 'Buyer')
ON DUPLICATE KEY UPDATE name = VALUES(name), password = VALUES(password), role = VALUES(role);

-- Ten live artworks split across the two sellers.
INSERT INTO artworks (seller_id, title, description, category, image_url, base_price, starts_at, ends_at)
SELECT u.id, x.title, x.description, x.category, x.image_url, x.base_price, NOW(), DATE_ADD(NOW(), INTERVAL x.days_open DAY)
FROM users u
JOIN (
 SELECT 'Monsoon in Blue' title, 'Layered blue and indigo forms inspired by the first rain.' description, 'Abstract' category, 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=900' image_url, 5000.00 base_price, 2 days_open, 'ananya.seller@artbid.test' seller_email
 UNION ALL SELECT 'Quiet Courtyard', 'A sunlit courtyard painted in warm earth tones.', 'Painting', 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=900', 4200, 3, 'ananya.seller@artbid.test'
 UNION ALL SELECT 'City After Rain', 'A loose, atmospheric study of a city street after rainfall.', 'Painting', 'https://images.unsplash.com/photo-1577083552431-6e5fd01aa342?w=900', 6800, 1, 'kabir.seller@artbid.test'
 UNION ALL SELECT 'Still Life No. 4', 'A contemporary still life with citrus, glass, and late afternoon light.', 'Still Life', 'https://images.unsplash.com/photo-1578301978693-85fa9c0320b9?w=900', 3600, 4, 'ananya.seller@artbid.test'
 UNION ALL SELECT 'Golden Hour Study', 'A small abstract canvas in ochre, gold, and soft cream.', 'Abstract', 'https://images.unsplash.com/photo-1549490349-8643362247b5?w=900', 5500, 2, 'kabir.seller@artbid.test'
 UNION ALL SELECT 'The Blue Door', 'A photographic print celebrating old doors and saturated color.', 'Photography', 'https://images.unsplash.com/photo-1549887534-1541e9326642?w=900', 2800, 5, 'ananya.seller@artbid.test'
 UNION ALL SELECT 'Garden in Motion', 'Expressive green brushwork inspired by a city garden.', 'Painting', 'https://images.unsplash.com/photo-1577083288073-40892c0860a4?w=900', 7400, 1, 'kabir.seller@artbid.test'
 UNION ALL SELECT 'Soft Geometry', 'Minimal geometric shapes in a calm palette of rose and sand.', 'Digital Art', 'https://images.unsplash.com/photo-1541701494587-cb58502866ab?w=900', 3100, 3, 'ananya.seller@artbid.test'
 UNION ALL SELECT 'Coastal Light', 'A quiet coastal scene in layered pastel tones.', 'Landscape', 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?w=900', 6200, 2, 'kabir.seller@artbid.test'
 UNION ALL SELECT 'Human, Unbound', 'A contemporary portrait study with expressive color and texture.', 'Portrait', 'https://images.unsplash.com/photo-1579783901586-d88db74b4fe4?w=900', 8100, 4, 'ananya.seller@artbid.test'
) x ON u.email = x.seller_email
WHERE NOT EXISTS (SELECT 1 FROM artworks a WHERE a.title = x.title);

-- Ten sample bids from the two buyer accounts.
INSERT INTO bids (artwork_id, buyer_id, amount)
SELECT a.id, u.id, a.base_price + x.increment
FROM (
 SELECT 'Monsoon in Blue' title, 'mira.buyer@artbid.test' buyer_email, 500 increment
 UNION ALL SELECT 'Quiet Courtyard', 'arjun.buyer@artbid.test', 300
 UNION ALL SELECT 'City After Rain', 'mira.buyer@artbid.test', 700
 UNION ALL SELECT 'Still Life No. 4', 'arjun.buyer@artbid.test', 400
 UNION ALL SELECT 'Golden Hour Study', 'mira.buyer@artbid.test', 600
 UNION ALL SELECT 'The Blue Door', 'arjun.buyer@artbid.test', 200
 UNION ALL SELECT 'Garden in Motion', 'mira.buyer@artbid.test', 800
 UNION ALL SELECT 'Soft Geometry', 'arjun.buyer@artbid.test', 300
 UNION ALL SELECT 'Coastal Light', 'mira.buyer@artbid.test', 500
 UNION ALL SELECT 'Human, Unbound', 'arjun.buyer@artbid.test', 900
) x
JOIN artworks a ON a.title = x.title
JOIN users u ON u.email = x.buyer_email
WHERE NOT EXISTS (SELECT 1 FROM bids b WHERE b.artwork_id = a.id AND b.buyer_id = u.id);

-- Ten saved artworks spread across both buyers.
INSERT IGNORE INTO wishlist (user_id, artwork_id)
SELECT u.id, a.id
FROM (
 SELECT 'mira.buyer@artbid.test' buyer_email, 'Quiet Courtyard' title
 UNION ALL SELECT 'mira.buyer@artbid.test', 'City After Rain'
 UNION ALL SELECT 'mira.buyer@artbid.test', 'Golden Hour Study'
 UNION ALL SELECT 'mira.buyer@artbid.test', 'Garden in Motion'
 UNION ALL SELECT 'mira.buyer@artbid.test', 'Coastal Light'
 UNION ALL SELECT 'arjun.buyer@artbid.test', 'Monsoon in Blue'
 UNION ALL SELECT 'arjun.buyer@artbid.test', 'Still Life No. 4'
 UNION ALL SELECT 'arjun.buyer@artbid.test', 'The Blue Door'
 UNION ALL SELECT 'arjun.buyer@artbid.test', 'Soft Geometry'
 UNION ALL SELECT 'arjun.buyer@artbid.test', 'Human, Unbound'
) x
JOIN users u ON u.email = x.buyer_email
JOIN artworks a ON a.title = x.title;
