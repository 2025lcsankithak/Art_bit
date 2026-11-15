<?php if (!$selected && $view === 'dashboard' && $me && $me['role'] === 'Buyer'): ?>
<section class="dashboard">
    <?php if (($_GET['wishlist'] ?? '') === 'removed'): ?><div class="notice">Artwork removed from your wishlist.</div><?php endif; ?>
    <?php if (($_GET['bid'] ?? '') === 'removed'): ?><div class="notice">Your bid was withdrawn.</div><?php endif; ?>
    <div class="dash-head">
        <div>
            <div class="eyebrow">YOUR ARTBID</div>
            <h1>Welcome back, <em><?= e($me['name']) ?>.</em></h1>
        </div>
    </div>

    <div class="metrics">
        <div class="metric"><span>Active bids</span><b><?= (int) $buyerStats['active_bids'] ?></b><small>Auctions you’re in</small></div>
        <div class="metric"><span>Won auctions</span><b><?= (int) $buyerStats['won_auctions'] ?></b><small>Your collection grows</small></div>
        <div class="metric"><span>Wishlist</span><b><?= (int) $buyerStats['wishlist_count'] ?></b><small>Art you love</small></div>
        <div class="metric"><span>Total won value</span><b><?= money($buyerStats['won_value']) ?></b><small>Winning bids on ended auctions</small></div>
    </div>

    <div class="section-title buyer-bids-title" id="my-bids">
        <div><div class="eyebrow">YOUR AUCTION ACTIVITY</div><h2>My bids</h2></div>
        <span class="lot-count"><?= count($buyerBids) ?> ARTWORKS</span>
    </div>

    <?php if ($buyerBids): ?>
        <div class="buyer-bids">
            <div class="buyer-bids-head"><span>Artwork</span><span>Your bid</span><span>Current bid</span><span>Status</span><span>Withdraw</span><span>Payment</span></div>
            <?php foreach ($buyerBids as $bid):
                $ended = strtotime($bid['ends_at']) <= time();
                $leading = (float) $bid['my_bid'] >= (float) $bid['current_bid'];
                $won = $ended && (int)$bid['winner_buyer_id'] === (int)$me['id'];
                $status = $ended ? ($won ? 'Won' : 'Ended') : ($leading ? 'Leading' : 'Outbid');
            ?>
                <div class="buyer-bid-row">
                    <a class="buyer-artwork" href="?artwork=<?= (int) $bid['id'] ?>"><span class="buyer-thumb" style="background-image:url('<?= e($bid['image_url']) ?>')"></span><span><b><?= e($bid['title']) ?></b><small><?= e($bid['category']) ?></small></span></a>
                    <b><?= money($bid['my_bid']) ?></b>
                    <b><?= money($bid['current_bid']) ?></b>
                    <span class="buyer-status <?= strtolower($status) ?>"><?= e($status) ?></span>
                    <div class="bid-withdraw-cell">
                    <?php if (!$ended): ?>
                        <form method="post" class="bid-remove-form" onsubmit="return confirm('Withdraw all your bids on this artwork?')"><?=csrf_field()?>
                            <input type="hidden" name="action" value="withdraw_bid">
                            <input type="hidden" name="artwork_id" value="<?= (int) $bid['id'] ?>">
                            <button class="remove-btn" type="submit">Withdraw</button>
                        </form>
                    <?php else: ?><span class="bid-action-empty">—</span><?php endif; ?>
                    </div>
                    <div class="bid-payment-cell">
                    <?php if ($status === 'Won' && !empty($bid['seller_upi'])): ?><button class="btn btn-dark winner-pay-toggle" type="button" data-upi-toggle aria-expanded="false" aria-controls="winner-upi-<?= (int)$bid['id'] ?>">Pay now</button><?php else: ?><span class="bid-action-empty">—</span><?php endif; ?>
                    </div>
                </div>
                <?php if ($status === 'Won' && !empty($bid['seller_upi'])): ?>
                    <div class="winner-upi-details" id="winner-upi-<?= (int)$bid['id'] ?>" hidden><div class="winner-qr-wrap"><span>SCAN TO PAY</span><div class="upi-qr" data-upi-uri="<?=e('upi://pay?pa='.rawurlencode($bid['seller_upi']).'&pn='.rawurlencode($bid['seller_name']).'&am='.rawurlencode((string)$bid['my_bid']).'&cu=INR')?>" role="img" aria-label="UPI payment QR code"></div><b><?=money($bid['my_bid'])?></b><small>Scan with a UPI app to pay the seller</small></div><a class="btn btn-dark" href="upi://pay?pa=<?= rawurlencode($bid['seller_upi']) ?>&amp;pn=<?= rawurlencode($bid['seller_name']) ?>&amp;am=<?= rawurlencode((string)$bid['my_bid']) ?>&amp;cu=INR">Open UPI app</a></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty buyer-empty">You haven’t placed a bid yet. <a href="index.php">Explore live auctions ↗</a></div>
    <?php endif; ?>

    <div class="section-title buyer-bids-title" id="wishlist">
        <div><div class="eyebrow">SAVED FOR LATER</div><h2>My wishlist</h2></div>
        <span class="lot-count"><?= count($buyerWishlist) ?> SAVED</span>
    </div>
    <?php if ($buyerWishlist): ?>
        <div class="buyer-bids wishlist-list">
            <?php foreach ($buyerWishlist as $saved): ?>
                <div class="buyer-bid-row wishlist-row">
                    <a class="buyer-artwork" href="?artwork=<?= (int) $saved['id'] ?>">
                        <span class="buyer-thumb" style="background-image:url('<?= e($saved['image_url']) ?>')"></span>
                        <span><b><?= e($saved['title']) ?></b><small><?= e($saved['category']) ?></small></span>
                    </a>
                    <b><?= money($saved['current_bid']) ?></b>
                    <span class="wishlist-auction-state"><?= strtotime($saved['ends_at']) > time() ? 'Auction live' : 'Auction ended' ?></span>
                    <form method="post" class="wishlist-remove-form" onsubmit="return confirm('Remove this artwork from your wishlist?')"><?=csrf_field()?>
                        <input type="hidden" name="action" value="wishlist">
                        <input type="hidden" name="artwork_id" value="<?= (int) $saved['id'] ?>">
                        <input type="hidden" name="return_to" value="dashboard">
                        <button class="remove-btn" type="submit">Remove</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty buyer-empty">Your wishlist is empty. Save artwork you’d like to revisit.</div>
    <?php endif; ?>
</section>
<?php endif; ?>
