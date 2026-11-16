
CREATE DATABASE IF NOT EXISTS artbid
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE artbid;

SET FOREIGN_KEY_CHECKS = 0;

-- =====================================
-- 1. USERS TABLE (PARENT TABLE)
-- =====================================

CREATE TABLE `users` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(120) NOT NULL,
    `email` varchar(190) NOT NULL,
    `password` varchar(255) NOT NULL,
    `role` enum('Seller','Buyer') NOT NULL DEFAULT 'Buyer',
    `phone` varchar(32) DEFAULT NULL,
    `location` varchar(160) DEFAULT NULL,
    `bio` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `upi_id` varchar(100) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users`
(`id`, `name`, `email`, `password`, `role`, `phone`, `location`, `bio`, `created_at`, `upi_id`)
VALUES
(1,'Ananya Rao','ananya.seller@artbid.test','$2y$10$ClB0UXkJAytqyNQwFVRj9uqR0uXOcZ2.oThP4K7SkmiPEeECmYoeW','Seller','+91 7349390942','Karnataka, India',NULL,'2026-11-15 06:36:21','8904918945@axl'),
(2,'Kabir Mehta','kabir.seller@artbid.test','$2y$10$ClB0UXkJAytqyNQwFVRj9uqR0uXOcZ2.oThP4K7SkmiPEeECmYoeW','Seller',NULL,NULL,NULL,'2026-11-15 06:36:21',NULL),
(3,'Mira Shah','mira.buyer@artbid.test','$2y$10$ClB0UXkJAytqyNQwFVRj9uqR0uXOcZ2.oThP4K7SkmiPEeECmYoeW','Buyer',NULL,NULL,NULL,'2026-11-15 06:36:21',NULL),
(4,'Arjun Das','arjun.buyer@artbid.test','$2y$10$ClB0UXkJAytqyNQwFVRj9uqR0uXOcZ2.oThP4K7SkmiPEeECmYoeW','Buyer','+91 7349390942','Karnataka, India',NULL,'2026-11-15 06:36:21',NULL);

-- =====================================
-- 2. ARTWORKS TABLE
-- =====================================

CREATE TABLE `artworks` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `seller_id` int(10) unsigned NOT NULL,
    `title` varchar(180) NOT NULL,
    `description` text NOT NULL,
    `category` varchar(80) NOT NULL,
    `image_url` varchar(1000) DEFAULT NULL,
    `base_price` decimal(12,2) NOT NULL,
    `starts_at` datetime NOT NULL,
    `ends_at` datetime NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `seller_id` (`seller_id`),
    KEY `auction_ends` (`ends_at`),
    KEY `artwork_category` (`category`),
    CONSTRAINT `artworks_ibfk_1`
    FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `artworks`
(`id`, `seller_id`, `title`, `description`, `category`, `image_url`, `base_price`, `starts_at`, `ends_at`, `created_at`)
VALUES
(1,1,'Human, Unbound','A contemporary portrait study with expressive color and texture.','Portrait','https://images.unsplash.com/photo-1579783901586-d88db74b4fe4?w=900',8100.00,'2026-11-15 12:06:21','2026-11-15 12:15:00','2026-11-15 06:36:21'),
(2,1,'Soft Geometry','Minimal geometric shapes in a calm palette of rose and sand.','Digital Art','https://images.unsplash.com/photo-1541701494587-cb58502866ab?w=900',3100.00,'2026-11-15 12:06:21','2026-11-18 12:06:21','2026-11-15 06:36:21'),
(3,1,'The Blue Door','A photographic print celebrating old doors and saturated color.','Photography','https://images.unsplash.com/photo-1549887534-1541e9326642?w=900',2800.00,'2026-11-15 12:06:21','2026-11-15 12:10:00','2026-11-15 06:36:21'),
(4,1,'Still Life No. 4','A contemporary still life with citrus, glass, and late afternoon light.','Still Life','https://images.unsplash.com/photo-1578301978693-85fa9c0320b9?w=900',3600.00,'2026-11-15 12:06:21','2026-11-19 12:06:21','2026-11-15 06:36:21'),
(5,1,'Quiet Courtyard','A sunlit courtyard painted in warm earth tones.','Painting','https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=900',4200.00,'2026-11-15 12:06:21','2026-11-18 12:06:21','2026-11-15 06:36:21'),
(6,1,'Monsoon in Blue','Layered blue and indigo forms inspired by the first rain.','Abstract','https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=900',5000.00,'2026-11-15 12:06:21','2026-11-17 12:06:21','2026-11-15 06:36:21'),
(7,2,'Coastal Light','A quiet coastal scene in layered pastel tones.','Landscape','https://images.unsplash.com/photo-1470770841072-f978cf4d019e?w=900',6200.00,'2026-11-15 12:06:21','2026-11-17 12:06:21','2026-11-15 06:36:21'),
(8,2,'Garden in Motion','Expressive green brushwork inspired by a city garden.','Painting','https://images.unsplash.com/photo-1577083288073-40892c0860a4?w=900',7400.00,'2026-11-15 12:06:21','2026-11-16 12:06:21','2026-11-15 06:36:21'),
(9,2,'Golden Hour Study','A small abstract canvas in ochre, gold, and soft cream.','Abstract','https://images.unsplash.com/photo-1549490349-8643362247b5?w=900',5500.00,'2026-11-15 12:06:21','2026-11-17 12:06:21','2026-11-15 06:36:21'),
(10,2,'City After Rain','A loose, atmospheric study of a city street after rainfall.','Painting','https://images.unsplash.com/photo-1577083552431-6e5fd01aa342?w=900',6800.00,'2026-11-15 12:06:21','2026-11-16 12:06:21','2026-11-15 06:36:21');

-- =====================================
-- 3. BIDS TABLE
-- =====================================

CREATE TABLE `bids` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `artwork_id` int(10) unsigned NOT NULL,
    `buyer_id` int(10) unsigned NOT NULL,
    `amount` decimal(12,2) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `bid_artwork` (`artwork_id`,`amount`),
    KEY `bid_buyer` (`buyer_id`),
    CONSTRAINT `bids_ibfk_1`
    FOREIGN KEY (`artwork_id`) REFERENCES `artworks` (`id`)
    ON DELETE CASCADE,
    CONSTRAINT `bids_ibfk_2`
    FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `bids`
(`id`, `artwork_id`, `buyer_id`, `amount`, `created_at`)
VALUES
(3,3,4,3000.00,'2026-11-15 06:36:21'),
(4,4,4,4000.00,'2026-11-15 06:36:21'),
(5,5,4,4500.00,'2026-11-15 06:36:21'),
(7,7,3,6700.00,'2026-11-15 06:36:21'),
(8,8,3,8200.00,'2026-11-15 06:36:21'),
(9,9,3,6100.00,'2026-11-15 06:36:21'),
(10,10,3,7500.00,'2026-11-15 06:36:21'),
(11,4,4,4500.00,'2026-11-15 08:15:17'),
(12,6,4,6000.00,'2026-11-15 08:16:26'),
(14,6,4,6200.00,'2026-11-15 08:19:29'),
(15,2,3,3600.00,'2026-11-15 09:25:52');

-- =====================================
-- 4. WISHLIST TABLE
-- =====================================

CREATE TABLE `wishlist` (
    `user_id` int(10) unsigned NOT NULL,
    `artwork_id` int(10) unsigned NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`user_id`,`artwork_id`),
    KEY `artwork_id` (`artwork_id`),
    CONSTRAINT `wishlist_ibfk_1`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE,
    CONSTRAINT `wishlist_ibfk_2`
    FOREIGN KEY (`artwork_id`) REFERENCES `artworks` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `wishlist`
(`user_id`, `artwork_id`, `created_at`)
VALUES
(3,5,'2026-11-15 06:36:21'),
(3,7,'2026-11-15 06:36:21'),
(3,8,'2026-11-15 06:36:21'),
(3,9,'2026-11-15 06:36:21'),
(3,10,'2026-11-15 06:36:21'),
(4,1,'2026-11-15 06:36:21'),
(4,2,'2026-11-15 06:36:21'),
(4,4,'2026-11-15 06:36:21'),
(4,6,'2026-11-15 06:36:21');

SET FOREIGN_KEY_CHECKS = 1;
