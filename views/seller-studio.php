<?php
if($view==='seller'&&$me&&$me['role']==='Seller'):
 $liveArts=[];$soldArts=[];$endedArts=[];
 foreach($sellerArts as $art){
  if(strtotime($art['ends_at'])>time())$liveArts[]=$art;
  elseif(!empty($art['winner_name']))$soldArts[]=$art;
  else $endedArts[]=$art;
 }
 $collectionGroups=['Live items'=>$liveArts,'Sold items'=>$soldArts,'Ended but not sold'=>$endedArts];
?>
<section class="dashboard">
 <div class="dash-head"><div><div class="eyebrow">SELLER STUDIO</div><h1>Your art, <em>in motion.</em></h1></div><button class="btn btn-dark" onclick="document.querySelector('#create').showModal()">＋ List artwork</button></div>
 <div class="metrics"><div class="metric"><span>My artworks</span><b><?= (int)($dash['total']??0)?></b><small>All time</small></div><div class="metric"><span>Active auctions</span><b><?= (int)($dash['active']??0)?></b><small>Live right now</small></div><div class="metric"><span>Total bids</span><b><?= (int)($dash['bids']??0)?></b><small>Across your work</small></div><div class="metric"><span>Total earnings</span><b><?=money($dash['earnings']??0)?></b><small>From completed sales</small></div></div>
 <?php foreach($collectionGroups as $groupName=>$groupArts):?>
  <div class="section-title seller-group-heading"><div><div class="eyebrow">YOUR COLLECTION</div><h2><?=e($groupName)?></h2></div><span class="lot-count"><?=count($groupArts)?> ITEMS</span></div>
  <div class="seller-list">
   <?php if(!$groupArts):?><div class="empty seller-group-empty">No <?=strtolower(e($groupName))?> yet.</div><?php endif;?>
   <?php foreach($groupArts as $a):$isActive=strtotime($a['ends_at'])>time();$isSold=!$isActive&&!empty($a['winner_name']);$canEdit=$isActive||(!$isSold&&(int)$a['bid_count']===0);$dateDialog='edit-end-'.$a['id'];?>
    <div class="seller-row"><a class="seller-art-link" href="?artwork=<?=$a['id']?>"><span class="seller-thumb" style="background-image:url('<?=e($a['image_url'])?>')"></span><span class="seller-work"><b><?=e($a['title'])?></b><small><?=e($a['category'])?> · Ends <?=e($a['ends_at'])?></small><?php if($isActive):?><small>Time left: <span class="timer" data-end="<?=e($a['ends_at'])?>" data-end-epoch="<?=strtotime($a['ends_at'])*1000?>">--:--:--</span></small><?php endif;?></span><span class="seller-bid"><b><?=money($a['current_bid'])?></b><small><?= (int)$a['bidders']?> bidders · <?= (int)$a['bid_count']?> bids</small></span><span class="status-chip <?=$isActive?'':($isSold?'sold-chip':'ended-chip')?>"><?=$isActive?'LIVE':($isSold?'SOLD':'ENDED')?></span></a>
     <?php if($isSold):?><details class="buyer-details"><summary><?=e($a['winner_name'])?></summary><div class="buyer-contact"><b><?=e($a['winner_name'])?></b><a href="mailto:<?=e($a['winner_email'])?>"><?=e($a['winner_email'])?></a></div></details><?php endif;?>
     <?php if($canEdit):?><button class="edit-date-btn" type="button" onclick="document.getElementById('<?=$dateDialog?>').showModal()">Edit date</button><?php endif;?>
    </div>
    <?php if($canEdit):?><dialog id="<?=$dateDialog?>" class="date-dialog"><button class="close" type="button" onclick="this.closest('dialog').close()">×</button><div class="eyebrow">AUCTION SCHEDULE</div><h2>Edit end <em>date.</em></h2><p class="form-intro">Set a future end time for <?=e($a['title'])?>. An ended item with no bids will become live again.</p><form method="post"><input type="hidden" name="action" value="update_auction_end"><input type="hidden" name="artwork_id" value="<?=$a['id']?>"><label for="end-<?=$a['id']?>">Auction ends</label><input id="end-<?=$a['id']?>" name="ends_at" type="datetime-local" min="<?=date('Y-m-d\\TH:i')?>" value="<?=date('Y-m-d\\TH:i',strtotime($a['ends_at']))?>" required><button class="btn btn-dark full">Save new end date</button></form></dialog><?php endif;?>
   <?php endforeach;?>
  </div>
 <?php endforeach;?>
</section>
<?php endif;?>
