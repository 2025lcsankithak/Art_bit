<?php if($selected&&$detail):$auctionEnded=strtotime($detail['ends_at'])<=time();?>
<section class="detail">
 <a class="back" href="index.php">← Back to discover</a>
 <?php if(($_GET['bid']??'')==='ok'):?><div class="notice">Your bid has been placed. Good luck!</div><?php elseif(($_GET['wishlist']??'')==='saved'):?><div class="notice">Artwork saved to your wishlist.</div><?php elseif(($_GET['wishlist']??'')==='removed'):?><div class="notice">Artwork removed from your wishlist.</div><?php endif;?>
 <div class="detail-grid">
  <div class="detail-image" style="background-image:url('<?=e($detail['image_url'])?>')"><span class="pill <?= $auctionEnded?'':'live' ?>"><?=$auctionEnded?($auctionWinner?'SOLD':'AUCTION ENDED'):'● LIVE AUCTION'?></span></div>
  <div class="detail-info">
   <div class="eyebrow"><?=e($detail['category'])?> · ORIGINAL ARTWORK</div><h1><?=e($detail['title'])?></h1><p class="byline">by <strong><?=e($detail['seller'])?></strong> <span class="verified">✓ Trusted seller</span></p><p class="description"><?=e($detail['description'])?></p><?php if($detail['seller_location']||$detail['seller_bio']):?><div class="seller-profile-snippet"><?php if($detail['seller_location']):?><small>Based in <?=e($detail['seller_location'])?></small><?php endif;?><?php if($detail['seller_bio']):?><p><?=nl2br(e($detail['seller_bio']))?></p><?php endif;?></div><?php endif;?><?php if($auctionEnded&&$auctionWinner):?><p class="sold-description">Sold to <strong><?=e($auctionWinner['name'])?></strong></p><?php endif;?>
   <div class="price-box"><div><span class="label"><?=$auctionEnded?'FINAL BID':'CURRENT BID'?></span><strong><?=money($detail['current_bid'])?></strong><small>Starting at <?=money($detail['base_price'])?></small></div><div class="auction-stats"><b><?= (int)$detail['bidders']?></b><span>bidders</span></div><div class="auction-stats"><?php if($auctionEnded):?><b>ENDED</b><span><?=e($detail['ends_at'])?></span><?php else:?><b class="timer" data-end="<?=e($detail['ends_at'])?>">--:--:--</b><span>time left</span><?php endif;?></div></div>
   <?php if($auctionEnded):?>
    <?php if($auctionWinner):?><div class="winner-card"><div class="eyebrow">SOLD TO</div><strong><?=e($auctionWinner['name'])?></strong><span>Winning bid <?=money($auctionWinner['amount'])?></span>
      <?php if($me&&$me['id']==$detail['seller_id']):?><a href="mailto:<?=e($auctionWinner['email'])?>">Contact winner: <?=e($auctionWinner['email'])?></a><?php elseif($me&&$me['id']==$auctionWinner['id']):?><a href="mailto:<?=e($detail['seller_email'])?>">Contact seller: <?=e($detail['seller_email'])?></a><?php if($detail['seller_phone']):?><a href="tel:<?=e($detail['seller_phone'])?>">Call seller: <?=e($detail['seller_phone'])?></a><?php endif;?><?php endif;?>
    </div><?php else:?><div class="winner-card"><div class="eyebrow">AUCTION ENDED</div><strong>No bids were placed</strong></div><?php endif;?>
   <?php else:?>
    <form method="post" class="bid-form"><input type="hidden" name="action" value="bid"><input type="hidden" name="artwork_id" value="<?=$detail['id']?>"><label>Your bid (min <?=money($detail['current_bid']+100)?>)</label><div class="bid-input"><span>₹</span><input name="amount" type="number" min="<?=$detail['current_bid']+100?>" step="100" value="<?=$detail['current_bid']+100?>" required><button class="btn btn-dark" <?=!$me||$me['role']!=='Buyer'?'disabled':''?>>Place bid →</button></div></form>
    <div class="suggestions"><span>Smart bid suggestions</span><button type="button" onclick="setBid(100)">+₹100 <small>Minimum</small></button><button type="button" onclick="setBid(500)">+₹500 <small>Competitive</small></button><button type="button" onclick="setBid(1000)">+₹1,000 <small>Strong</small></button></div>
   <?php endif;?>
   <?php if($me&&$me['role']==='Buyer'):?><form method="post"><input type="hidden" name="action" value="wishlist"><input type="hidden" name="artwork_id" value="<?=$detail['id']?>"><button class="text-btn"><?= $isWishlisted ? '♥ Saved — remove from wishlist' : '♡ Save to wishlist' ?></button></form><?php endif;?>
  </div>
 </div>
</section>
<?php endif;?>
