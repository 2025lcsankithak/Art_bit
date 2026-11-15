<?php if($view==='profile'&&$me&&$profile):?>
<?php
$phoneCodes=phone_country_codes();
$storedPhone=trim($profile['phone']??'');
$selectedPhoneCode='+91';
foreach($phoneCodes as $code){if(str_starts_with($storedPhone,$code)){ $selectedPhoneCode=$code; $storedPhone=trim(substr($storedPhone,strlen($code))); break; }}
?>
<section class="dashboard profile-page">
 <div class="profile-hero">
  <div class="profile-avatar"><?=e(strtoupper(substr($profile['name'],0,1)))?></div>
  <div class="profile-identity"><div class="eyebrow">ARTBID MEMBER PROFILE</div><h1><?=e($profile['name'])?></h1><span class="profile-role"><?=e($profile['role'])?> account</span></div>
  <div class="profile-hero-note"><span>YOUR SPACE</span><b>Make it yours.</b></div>
 </div>
 <div class="profile-grid">
  <form class="profile-form profile-details-form" method="post"><?=csrf_field()?>
   <input type="hidden" name="action" value="update_profile">
   <div class="eyebrow">PROFILE DETAILS</div><h2>About you</h2><p class="form-intro">Keep your account and contact details up to date. Sellers can see these details if you win an auction.</p>
   <label for="profile-name">Name</label><input id="profile-name" name="name" maxlength="120" value="<?=e($profile['name'])?>" required>
   <label for="profile-email">Email address</label><input id="profile-email" name="email" type="email" maxlength="190" value="<?=e($profile['email'])?>" required>
   <div class="profile-divider"></div><div class="eyebrow"><?=$profile['role']==='Seller'?'SELLER DETAILS':'BUYER DETAILS'?></div>
   <label for="profile-phone">Phone number</label><div class="phone-field"><select name="phone_country_code" aria-label="Country calling code"><?php foreach($phoneCodes as $country=>$code):?><option value="<?=e($code)?>" <?=$selectedPhoneCode===$code?'selected':''?>><?=e($country)?> (<?=e($code)?>)</option><?php endforeach;?></select><input id="profile-phone" name="phone_number" type="tel" maxlength="24" autocomplete="tel-national" value="<?=e($storedPhone)?>" placeholder="98765 43210"></div>
   <label for="profile-location">Location</label><input id="profile-location" name="location" maxlength="160" autocomplete="address-level2" value="<?=e($profile['location']??'')?>" placeholder="City, country">
   <label for="profile-bio"><?=$profile['role']==='Seller'?'About your work':'About you'?></label><textarea id="profile-bio" name="bio" rows="4" maxlength="1000" placeholder="<?=$profile['role']==='Seller'?'Your artistic practice, materials, or background':'Add any helpful details for sellers after a successful auction'?>"><?=e($profile['bio']??'')?></textarea>
   <?php if($profile['role']==='Seller'):?><label for="profile-upi">UPI ID for auction payments</label><input id="profile-upi" name="upi_id" maxlength="100" value="<?=e($profile['upi_id']??'')?>" placeholder="name@bank" autocomplete="off"><small class="field-hint">This is shown only to the winning buyer in My bids.</small><?php endif;?>
   <button class="btn btn-dark full">Save profile</button>
  </form>

  <form class="profile-form password-form" method="post"><?=csrf_field()?>
   <div class="password-icon" aria-hidden="true">✦</div><input type="hidden" name="action" value="change_password">
   <div class="eyebrow">ACCOUNT SECURITY</div><h2>Change password</h2><p class="form-intro">Choose a password you haven’t used before. You’ll need your current password to confirm the change.</p>
   <label for="current-password">Current password</label><input id="current-password" name="current_password" type="password" autocomplete="current-password" required>
   <label for="new-password">New password</label><input id="new-password" name="new_password" type="password" minlength="8" autocomplete="new-password" placeholder="At least 8 characters" required>
   <label for="confirm-password">Confirm new password</label><input id="confirm-password" name="confirm_password" type="password" minlength="8" autocomplete="new-password" required>
   <button class="btn btn-dark full">Change password</button>
  </form>
 </div>
</section>
<?php endif;?>
