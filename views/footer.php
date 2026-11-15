</main><footer><a class="brand" href="index.php"><span class="brand-mark">a.</span> artbid</a><span>Art belongs where it’s loved.</span><span>© 2025 ArtBid · A better way to collect.</span></footer>
<?php require __DIR__ . '/modals.php'; ?>
<?php if($reopenAuth):?><script>document.addEventListener('DOMContentLoaded',function(){var authDialog=document.getElementById('auth');if(authDialog)authDialog.showModal();if('<?=e($reopenAuthMode)?>'==='register'){var registerTab=document.querySelector('#auth .tab:nth-child(2)');if(registerTab)registerTab.click();}});</script><?php endif;?>
<script src="assets/js/qrcode.min.js?v=20261115-1"></script><script src="assets/js/app.js?v=20261115-6"></script></body></html>
