<?php
$view = $_GET['view'] ?? 'discover';
$selected = isset($_GET['artwork']) ? (int) $_GET['artwork'] : 0;
$search = trim($_GET['q'] ?? '');
$sort = ($_GET['sort'] ?? 'ending_soon') === 'ending_later' ? 'ending_later' : 'ending_soon';
$where = $view === 'search' && $search !== '' ? ['1=1'] : ['a.ends_at > NOW()'];
$params = [];

if ($search !== '') {
    $where[] = '(a.title LIKE ? OR a.description LIKE ? OR a.category LIKE ?)';
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}
if (!empty($_GET['category'])) {
    $where[] = 'a.category = ?';
    $params[] = $_GET['category'];
}
$arts = [];
$detail = null;
$isWishlisted = false;
$dash = [];
$sellerArts = [];
$sellerBidHistory = [];
$buyerStats = ['active_bids' => 0, 'won_auctions' => 0, 'wishlist_count' => 0, 'won_value' => 0];
$buyerBids = [];
$buyerWishlist = [];
$auctionWinner = null;
$profile = null;

if ($pdo) {
    if ($me) {
        $q = $pdo->prepare('SELECT id, name, email, role, phone, location, bio, upi_id FROM users WHERE id = ?');
        $q->execute([$me['id']]);
        $profile = $q->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    $sql = 'SELECT a.*, u.name seller, COALESCE(MAX(b.amount), a.base_price) current_bid,
                   COUNT(DISTINCT b.buyer_id) bidders, COUNT(b.id) bid_count,
                   (SELECT u2.name FROM bids wb JOIN users u2 ON u2.id=wb.buyer_id WHERE wb.artwork_id=a.id ORDER BY wb.amount DESC, wb.created_at ASC, wb.id ASC LIMIT 1) winner_name,
                   (SELECT COUNT(*) FROM wishlist w WHERE w.artwork_id = a.id) saves
            FROM artworks a JOIN users u ON u.id = a.seller_id
            LEFT JOIN bids b ON b.artwork_id = a.id
            WHERE ' . implode(' AND ', $where) . '
            GROUP BY a.id ORDER BY a.ends_at ' . ($sort === 'ending_soon' ? 'ASC' : 'DESC');
    $q = $pdo->prepare($sql);
    $q->execute($params);
    $arts = $q->fetchAll(PDO::FETCH_ASSOC);

    if ($selected) {
        $q = $pdo->prepare('SELECT a.*, u.name seller, u.email seller_email, u.phone seller_phone, u.location seller_location, u.bio seller_bio, COALESCE(MAX(b.amount), a.base_price) current_bid,
                                   COUNT(DISTINCT b.buyer_id) bidders
                            FROM artworks a JOIN users u ON u.id = a.seller_id
                            LEFT JOIN bids b ON b.artwork_id = a.id
                            WHERE a.id = ? GROUP BY a.id');
        $q->execute([$selected]);
        $detail = $q->fetch(PDO::FETCH_ASSOC);

        if ($detail && strtotime($detail['ends_at']) <= time()) {
            $q = $pdo->prepare('SELECT u.id, u.name, b.amount FROM bids b JOIN users u ON u.id = b.buyer_id WHERE b.artwork_id = ? ORDER BY b.amount DESC, b.created_at ASC, b.id ASC LIMIT 1');
            $q->execute([$selected]);
            $auctionWinner = $q->fetch(PDO::FETCH_ASSOC) ?: null;
            if ($auctionWinner && $me && $me['role'] === 'Seller' && (int)$me['id'] === (int)$detail['seller_id']) {
                $q = $pdo->prepare('SELECT email, phone, location, bio FROM users WHERE id=?');
                $q->execute([$auctionWinner['id']]);
                $auctionWinner = array_merge($auctionWinner, $q->fetch(PDO::FETCH_ASSOC) ?: []);
            }
        }

        if ($detail && $me && $me['role'] === 'Buyer') {
            $q = $pdo->prepare('SELECT 1 FROM wishlist WHERE user_id = ? AND artwork_id = ?');
            $q->execute([$me['id'], $selected]);
            $isWishlisted = (bool) $q->fetchColumn();
        }
    }

    $cats = $pdo->query('SELECT DISTINCT category FROM artworks ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);

    if ($me && $me['role'] === 'Seller') {
        $q = $pdo->prepare('SELECT COUNT(*) total,
                                   (SELECT COUNT(*) FROM artworks WHERE seller_id = ? AND ends_at > NOW()) active,
                                   (SELECT COUNT(*) FROM bids b JOIN artworks a ON a.id = b.artwork_id WHERE a.seller_id = ?) bids,
                                   (SELECT COALESCE(SUM(w.amount), 0) FROM bids w JOIN artworks a ON a.id=w.artwork_id
                                    WHERE a.seller_id=? AND a.ends_at<NOW()
                                    AND w.id=(SELECT b2.id FROM bids b2 WHERE b2.artwork_id=a.id ORDER BY b2.amount DESC,b2.created_at ASC,b2.id ASC LIMIT 1)) earnings
                            FROM artworks WHERE seller_id = ?');
        $q->execute([$me['id'], $me['id'], $me['id'], $me['id']]);
        $dash = $q->fetch(PDO::FETCH_ASSOC);

        $q = $pdo->prepare('SELECT a.*, COALESCE(MAX(b.amount), a.base_price) current_bid,
                                   COUNT(DISTINCT b.buyer_id) bidders, COUNT(b.id) bid_count,
                                   CASE WHEN a.ends_at<=NOW() THEN (SELECT u.name FROM bids wb JOIN users u ON u.id=wb.buyer_id WHERE wb.artwork_id=a.id ORDER BY wb.amount DESC, wb.created_at ASC, wb.id ASC LIMIT 1) ELSE NULL END winner_name,
                                   CASE WHEN a.ends_at<=NOW() THEN (SELECT u.email FROM bids wb JOIN users u ON u.id=wb.buyer_id WHERE wb.artwork_id=a.id ORDER BY wb.amount DESC, wb.created_at ASC, wb.id ASC LIMIT 1) ELSE NULL END winner_email,
                                   CASE WHEN a.ends_at<=NOW() THEN (SELECT u.phone FROM bids wb JOIN users u ON u.id=wb.buyer_id WHERE wb.artwork_id=a.id ORDER BY wb.amount DESC, wb.created_at ASC, wb.id ASC LIMIT 1) ELSE NULL END winner_phone,
                                   CASE WHEN a.ends_at<=NOW() THEN (SELECT u.location FROM bids wb JOIN users u ON u.id=wb.buyer_id WHERE wb.artwork_id=a.id ORDER BY wb.amount DESC, wb.created_at ASC, wb.id ASC LIMIT 1) ELSE NULL END winner_location,
                                   CASE WHEN a.ends_at<=NOW() THEN (SELECT u.bio FROM bids wb JOIN users u ON u.id=wb.buyer_id WHERE wb.artwork_id=a.id ORDER BY wb.amount DESC, wb.created_at ASC, wb.id ASC LIMIT 1) ELSE NULL END winner_bio
                            FROM artworks a LEFT JOIN bids b ON b.artwork_id = a.id
                            WHERE a.seller_id = ? GROUP BY a.id ORDER BY a.ends_at DESC');
        $q->execute([$me['id']]);
        $sellerArts = $q->fetchAll(PDO::FETCH_ASSOC);

        $q = $pdo->prepare('SELECT b.artwork_id, b.amount, b.created_at, u.name buyer_name
                            FROM bids b JOIN artworks a ON a.id=b.artwork_id
                            JOIN users u ON u.id=b.buyer_id
                            WHERE a.seller_id=? ORDER BY b.artwork_id, b.created_at DESC, b.id DESC');
        $q->execute([$me['id']]);
        foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $historyBid) {
            $artId = (int)$historyBid['artwork_id'];
            $sellerBidHistory[$artId][] = ['bidder'=>$historyBid['buyer_name'], 'amount'=>$historyBid['amount'], 'created_at'=>$historyBid['created_at']];
        }
    }

    if ($me && $me['role'] === 'Buyer') {
        $q = $pdo->prepare('SELECT
            (SELECT COUNT(DISTINCT a.id) FROM bids b JOIN artworks a ON a.id = b.artwork_id
             WHERE b.buyer_id = ? AND a.ends_at > NOW()) active_bids,
            (SELECT COUNT(*) FROM bids b JOIN artworks a ON a.id=b.artwork_id
             WHERE b.buyer_id=? AND a.ends_at<=NOW()
             AND b.id=(SELECT b2.id FROM bids b2 WHERE b2.artwork_id=a.id ORDER BY b2.amount DESC,b2.created_at ASC,b2.id ASC LIMIT 1)) won_auctions,
            (SELECT COUNT(*) FROM wishlist WHERE user_id = ?) wishlist_count,
            (SELECT COALESCE(SUM(b.amount), 0) FROM bids b JOIN artworks a ON a.id=b.artwork_id
             WHERE b.buyer_id=? AND a.ends_at<=NOW()
             AND b.id=(SELECT b2.id FROM bids b2 WHERE b2.artwork_id=a.id ORDER BY b2.amount DESC,b2.created_at ASC,b2.id ASC LIMIT 1)) won_value');
        $q->execute([$me['id'], $me['id'], $me['id'], $me['id']]);
        $buyerStats = $q->fetch(PDO::FETCH_ASSOC) ?: $buyerStats;

        $q = $pdo->prepare('SELECT a.id, a.title, a.category, a.image_url, a.ends_at,
                                   CASE WHEN a.ends_at<=NOW() AND (SELECT wb.buyer_id FROM bids wb WHERE wb.artwork_id=a.id ORDER BY wb.amount DESC, wb.created_at ASC, wb.id ASC LIMIT 1)=? THEN seller.upi_id ELSE NULL END seller_upi,
                                   (SELECT wb.buyer_id FROM bids wb WHERE wb.artwork_id=a.id ORDER BY wb.amount DESC, wb.created_at ASC, wb.id ASC LIMIT 1) winner_buyer_id,
                                   MAX(mine.amount) my_bid,
                                   COALESCE(MAX(all_bids.amount), a.base_price) current_bid,
                                   COUNT(DISTINCT all_bids.buyer_id) bidders
                            FROM bids mine
                            JOIN artworks a ON a.id = mine.artwork_id
                            JOIN users seller ON seller.id = a.seller_id
                            LEFT JOIN bids all_bids ON all_bids.artwork_id = a.id
                            WHERE mine.buyer_id = ?
                            GROUP BY a.id ORDER BY a.ends_at DESC');
        $q->execute([$me['id'], $me['id']]);
        $buyerBids = $q->fetchAll(PDO::FETCH_ASSOC);

        $q = $pdo->prepare('SELECT a.id, a.title, a.category, a.image_url, a.ends_at,
                                   COALESCE(MAX(b.amount), a.base_price) current_bid
                            FROM wishlist w
                            JOIN artworks a ON a.id = w.artwork_id
                            LEFT JOIN bids b ON b.artwork_id = a.id
                            WHERE w.user_id = ?
                            GROUP BY a.id ORDER BY w.created_at DESC');
        $q->execute([$me['id']]);
        $buyerWishlist = $q->fetchAll(PDO::FETCH_ASSOC);
    }
}
