<?php if($selected&&$detail):$auctionEnded=strtotime($detail['ends_at'])<=time();?>
<section class="detail">
 <a class="back" href="index.php">← Back to discover</a>
 <?php if(($_GET['bid']??'')==='ok'):?><div class="notice">Your bid has been placed. Good luck!</div><?php elseif(($_GET['wishlist']??'')==='saved'):?><div class="notice">Artwork saved to your wishlist.</div><?php elseif(($_GET['wishlist']??'')==='removed'):?><div class="notice">Artwork removed from your wishlist.</div><?php endif;?>
 <div class="detail-grid">
  <div class="detail-image" style="background-image:url('<?=e($detail['image_url'])?>')"><span class="pill <?= $auctionEnded?'':'live' ?>"><?=$auctionEnded?($auctionWinner?'SOLD':'AUCTION ENDED'):'● LIVE AUCTION'?></span></div>
  <div class="detail-info">
   <div class="eyebrow"><?=e($detail['category'])?> · ORIGINAL ARTWORK</div><h1><?=e($detail['title'])?></h1><p class="byline">by <?php if($me&&$me['role']==='Buyer'):?><a class="contact-name-trigger" href="#contact-profile" onclick="event.preventDefault();document.getElementById('contact-profile').showModal()" aria-haspopup="dialog"><?=e($detail['seller'])?></a><?php else:?><strong><?=e($detail['seller'])?></strong><?php endif;?> <span class="verified">✓ Trusted seller</span></p><p class="description"><?=e($detail['description'])?></p><?php if($detail['seller_location']||$detail['seller_bio']):?><div class="seller-profile-snippet"><?php if($detail['seller_location']):?><small>Based in <?=e($detail['seller_location'])?></small><?php endif;?><?php if($detail['seller_bio']):?><p><?=nl2br(e($detail['seller_bio']))?></p><?php endif;?></div><?php endif;?><?php if($auctionEnded&&$auctionWinner):?><p class="sold-description">Sold to <strong><?=e($auctionWinner['name'])?></strong></p><?php endif;?>
   <div class="price-box"><div><span class="label"><?=$auctionEnded?'FINAL BID':'CURRENT BID'?></span><strong><?=money($detail['current_bid'])?></strong><small>Starting at <?=money($detail['base_price'])?></small></div><div class="auction-stats"><b><?= (int)$detail['bidders']?></b><span>bidders</span></div><div class="auction-stats"><?php if($auctionEnded):?><b>ENDED</b><span><?=e($detail['ends_at'])?></span><?php else:?><b class="timer" data-end="<?=e($detail['ends_at'])?>">--:--:--</b><span>time left</span><?php endif;?></div></div>
   <?php if($auctionEnded):?>
    <?php if($auctionWinner):?><div class="winner-card"><div class="eyebrow">SOLD TO</div><strong><?=e($auctionWinner['name'])?></strong><span>Winning bid <?=money($auctionWinner['amount'])?></span>
      <?php if($me&&$me['id']==$detail['seller_id']):?><a href="mailto:<?=e($auctionWinner['email'])?>">Contact winner: <?=e($auctionWinner['email'])?></a><?php elseif($me&&$me['id']==$auctionWinner['id']):?><a href="mailto:<?=e($detail['seller_email'])?>">Contact seller: <?=e($detail['seller_email'])?></a><?php if($detail['seller_phone']):?><a href="tel:<?=e(phone_tel($detail['seller_phone']))?>">Call seller: <?=e($detail['seller_phone'])?></a><?php endif;?><?php endif;?>
    </div><?php else:?><div class="winner-card"><div class="eyebrow">AUCTION ENDED</div><strong>No bids were placed</strong></div><?php endif;?>
   <?php else:?>
    <form method="post" class="bid-form"><?=csrf_field()?><input type="hidden" name="action" value="bid"><input type="hidden" name="artwork_id" value="<?=$detail['id']?>"><label>Your bid (match <?=money($detail['current_bid'])?> or add <?=money(100)?>; earliest match wins a tie)</label><div class="bid-input"><span>₹</span><input name="amount" type="number" min="<?=$detail['current_bid']?>" data-current-bid="<?=$detail['current_bid']?>" step="100" value="<?=$detail['current_bid']+100?>" required><button class="btn btn-dark" <?=!$me||$me['role']!=='Buyer'?'disabled':''?>>Place bid →</button></div></form>
    <div class="suggestions"><span>Smart bid suggestions</span><button type="button" onclick="setBid(100)">+₹100 <small>Minimum</small></button><button type="button" onclick="setBid(500)">+₹500 <small>Competitive</small></button><button type="button" onclick="setBid(1000)">+₹1,000 <small>Strong</small></button></div>
   <?php endif;?>
   <?php if($me&&$me['role']==='Buyer'):?><form method="post"><?=csrf_field()?><input type="hidden" name="action" value="wishlist"><input type="hidden" name="artwork_id" value="<?=$detail['id']?>"><button class="text-btn"><?= $isWishlisted ? '♥ Saved — remove from wishlist' : '♡ Save to wishlist' ?></button></form><?php endif;?>
  </div>
 </div>
</section>
<?php
$contactProfile = null;
$contactHeading = '';
if ($me && $me['role'] === 'Buyer') {
 $contactProfile = ['name'=>$detail['seller'], 'email'=>$detail['seller_email'], 'phone'=>$detail['seller_phone'], 'location'=>$detail['seller_location'], 'bio'=>$detail['seller_bio']];
 $contactHeading = 'Seller details';
} elseif ($me && $me['role'] === 'Seller' && (int)$me['id'] === (int)$detail['seller_id'] && $auctionWinner) {
 $contactProfile = $auctionWinner;
 $contactHeading = 'Winning buyer details';
}
if ($contactProfile):
?>
<dialog class="contact-profile-dialog" id="contact-profile">
 <button class="close" type="button" aria-label="Close details" onclick="this.closest('dialog').close()">×</button>
 <div class="eyebrow"><?=e($contactHeading)?></div><h2><?=e($contactProfile['name'])?></h2>
 <div class="contact-profile-fields">
  <?php if(!empty($contactProfile['email'])):?><a href="mailto:<?=e($contactProfile['email'])?>"><span>Email</span><?=e($contactProfile['email'])?></a><?php endif;?>
  <?php if(!empty($contactProfile['phone'])):?><a href="tel:<?=e(phone_tel($contactProfile['phone']))?>"><span>Phone</span><?=e($contactProfile['phone'])?></a><?php endif;?>
  <?php if(!empty($contactProfile['location'])):?><div><span>Location</span><?=e($contactProfile['location'])?></div><?php endif;?>
  <?php if(!empty($contactProfile['bio'])):?><div><span>About</span><?=nl2br(e($contactProfile['bio']))?></div><?php endif;?>
 </div>
</dialog>

<?php endif;?>
<?php endif;?>
