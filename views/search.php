<?php if(!$selected&&$view==='search'):?>
<section class="market search-page">
 <div class="section-title"><div><div class="eyebrow">ARTBID COLLECTION</div><h2>Search <em>all artworks.</em></h2></div><span class="lot-count"><?=count($arts)?> RESULTS</span></div>
 <form class="filters" method="get" action="index.php">
  <input type="hidden" name="view" value="search">
  <label class="search"><span aria-hidden="true">⌕</span><input type="search" name="q" placeholder="Search titles, descriptions, categories..." value="<?=e($search)?>" aria-label="Search all artworks"></label>
  <select name="category"><option value="">All categories</option><?php foreach(($cats??[]) as $c):?><option value="<?=e($c)?>" <?=($_GET['category']??'')===$c?'selected':''?>><?=e($c)?></option><?php endforeach;?></select>
  <select name="sort" aria-label="Sort results"><option value="ending_soon" <?=$sort==='ending_soon'?'selected':''?>>Ending soon</option><option value="ending_later" <?=$sort==='ending_later'?'selected':''?>>Ending later</option></select>
  <button class="btn btn-dark">Search</button>
 </form>
 <div class="art-grid">
  <?php foreach($arts as $i=>$a):$ended=strtotime($a['ends_at'])<=time();$sold=$ended&&!empty($a['winner_name']);?>
   <article class="art-card">
    <a href="?artwork=<?=$a['id']?>" class="art-image" style="background-image:url('<?=e($a['image_url']?:'https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=900')?>')"><span class="pill <?=$sold?'sold-chip':($ended?'ended-chip':(strtotime($a['ends_at'])<time()+3600?'hot':''))?>"><?=$sold?'SOLD':($ended?'ENDED':(strtotime($a['ends_at'])<time()+3600?'ENDING SOON':'LIVE AUCTION'))?></span><span class="image-arrow">↗</span></a>
    <div class="card-meta"><span><?=e(strtoupper($a['category']))?></span><span><?=str_pad((string)($i+1),2,'0',STR_PAD_LEFT)?> / <?=count($arts)?></span></div>
    <a href="?artwork=<?=$a['id']?>" class="art-title"><?=e($a['title'])?></a><div class="artist">by <?=e($a['seller'])?></div>
    <?php if($sold):?><div class="search-sold-to">Sold to <?=e($a['winner_name'])?></div><?php endif;?>
    <div class="card-bottom"><div><span class="label"><?=$ended?'FINAL BID':'CURRENT BID'?></span><b><?=money($a['current_bid'])?></b></div><div class="card-right"><?php if(!$ended):?><span><?= (int)$a['bidders']?> bidders</span><span class="timer" data-end="<?=e($a['ends_at'])?>">--:--:--</span><?php else:?><span><?= (int)$a['bid_count']?> bids</span><span><?=e($a['ends_at'])?></span><?php endif;?></div></div>
   </article>
  <?php endforeach;?>
  <?php if(!$arts):?><div class="empty">No artworks match your search.</div><?php endif;?>
 </div>
</section>
<?php endif;?>
