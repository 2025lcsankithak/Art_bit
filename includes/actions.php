<?php
if($_SERVER['REQUEST_METHOD']==='POST' && $pdo){
 $action=$_POST['action']??'';
 try {
  $sessionToken=$_SESSION['csrf_token']??'';$submittedToken=$_POST['csrf_token']??'';
  if(!is_string($sessionToken)||$sessionToken===''||!is_string($submittedToken)||!hash_equals($sessionToken,$submittedToken))throw new Exception('This form expired. Refresh the page and try again.');
  if($action==='register'){
   $q=$pdo->prepare('INSERT INTO users(name,email,password,role) VALUES(?,?,?,?)');
   $q->execute([trim($_POST['name']),strtolower(trim($_POST['email'])),password_hash($_POST['password'],PASSWORD_DEFAULT),$_POST['role']==='Seller'?'Seller':'Buyer']);
   session_regenerate_id(true);$_SESSION['csrf_token']=bin2hex(random_bytes(32));$_SESSION['user']=['id'=>$pdo->lastInsertId(),'name'=>trim($_POST['name']),'role'=>$_POST['role']]; header('Location: ?registered=1');exit;
  }
  if($action==='login'){$q=$pdo->prepare('SELECT * FROM users WHERE email=?');$q->execute([strtolower(trim($_POST['email']))]);$u=$q->fetch(PDO::FETCH_ASSOC);if($u&&password_verify($_POST['password'],$u['password'])){session_regenerate_id(true);$_SESSION['csrf_token']=bin2hex(random_bytes(32));$_SESSION['user']=['id'=>$u['id'],'name'=>$u['name'],'role'=>$u['role']];header('Location: ?signed_in=1');exit;}throw new Exception('Email or password is incorrect.');}
  if($action==='logout'){session_destroy();header('Location: index.php');exit;}
  if($action==='bid'&&$me){if($me['role']!=='Buyer')throw new Exception('Only buyer accounts can place bids.');$id=(int)$_POST['artwork_id'];$pdo->beginTransaction();$q=$pdo->prepare('SELECT * FROM artworks WHERE id=? FOR UPDATE');$q->execute([$id]);$a=$q->fetch(PDO::FETCH_ASSOC);$q=$pdo->prepare('SELECT COALESCE(MAX(amount),?) FROM bids WHERE artwork_id=?');$q->execute([$a['base_price']??0,$id]);$top=(float)$q->fetchColumn();$amount=(float)$_POST['amount'];$isTie=round($amount,2)===round($top,2);if(!$a||$a['seller_id']==$me['id']||strtotime($a['ends_at'])<=time()||(!$isTie&&$amount<$top+100))throw new Exception('Match the current bid or bid at least ₹100 more. The earliest matching bid wins a tie.');$pdo->prepare('INSERT INTO bids(artwork_id,buyer_id,amount) VALUES(?,?,?)')->execute([$id,$me['id'],$amount]);$pdo->commit();header('Location: ?artwork='.$id.'&bid=ok');exit;}
  if($action==='withdraw_bid'){
   if(!$me||$me['role']!=='Buyer')throw new Exception('Sign in with a buyer account to withdraw a bid.');
   $id=(int)($_POST['artwork_id']??0);
   $pdo->beginTransaction();
   $q=$pdo->prepare('SELECT id, ends_at FROM artworks WHERE id=? FOR UPDATE');$q->execute([$id]);$a=$q->fetch(PDO::FETCH_ASSOC);
   if(!$a||strtotime($a['ends_at'])<=time())throw new Exception('Bids can only be withdrawn while the auction is active.');
   $q=$pdo->prepare('DELETE FROM bids WHERE artwork_id=? AND buyer_id=?');$q->execute([$id,$me['id']]);
   if($q->rowCount()===0)throw new Exception('No bid was found to withdraw.');
   $pdo->commit();
   header('Location: ?view=dashboard&bid=removed#my-bids');exit;
  }
  if($action==='wishlist'){
   if(!$me||$me['role']!=='Buyer')throw new Exception('Sign in with a buyer account to save artwork.');
   $id=(int)($_POST['artwork_id']??0);
   $q=$pdo->prepare('SELECT id FROM artworks WHERE id=?');$q->execute([$id]);
   if(!$q->fetchColumn())throw new Exception('That artwork could not be found.');
   $q=$pdo->prepare('DELETE FROM wishlist WHERE user_id=? AND artwork_id=?');$q->execute([$me['id'],$id]);
   $state='removed';
   if($q->rowCount()===0){$q=$pdo->prepare('INSERT IGNORE INTO wishlist(user_id,artwork_id) VALUES(?,?)');$q->execute([$me['id'],$id]);$state='saved';}
   if(($_POST['return_to']??'')==='dashboard'){
    header('Location: ?view=dashboard&wishlist='.$state.'#wishlist');
   }else{
    header('Location: ?artwork='.$id.'&wishlist='.$state);
   }
   exit;
  }
  if($action==='update_profile'){
   if(!$me)throw new Exception('Sign in to edit your profile.');
   $name=trim($_POST['name']??'');$email=strtolower(trim($_POST['email']??''));
   $phoneNumber=trim($_POST['phone_number']??'');$phoneCode=trim($_POST['phone_country_code']??'');$location=trim($_POST['location']??'');$bio=trim($_POST['bio']??'');
   $upiId=trim($_POST['upi_id']??'');
   if($name===''||strlen($name)>120||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>190)throw new Exception('Enter a valid name and email address.');
   if(!in_array($phoneCode,phone_country_codes(),true)||strlen($phoneNumber)>24||!preg_match('/^[0-9().\s-]*$/',$phoneNumber)||strlen($location)>160||strlen($bio)>1000)throw new Exception('Check your phone, location, and bio fields.');
   if($me['role']==='Seller'&&$upiId!==''&&(strlen($upiId)>100||!preg_match('/^[A-Za-z0-9._-]{2,64}@[A-Za-z0-9.-]{2,32}$/',$upiId)))throw new Exception('Enter a valid UPI ID, such as name@bank.');
   $q=$pdo->prepare('SELECT id FROM users WHERE email=? AND id<>?');$q->execute([$email,$me['id']]);
   if($q->fetchColumn())throw new Exception('That email address is already in use.');
   $phoneDigits=preg_replace('/\D/','',$phoneNumber);$countryDigits=substr($phoneCode,1);
   if($phoneDigits!==''&&strlen($phoneDigits)>10&&strpos($phoneDigits,$countryDigits)===0)$phoneDigits=substr($phoneDigits,strlen($countryDigits));
   $phone=$phoneDigits!==''?$phoneCode.' '.$phoneDigits:null;
   if($me['role']==='Seller'){$q=$pdo->prepare('UPDATE users SET name=?,email=?,phone=?,location=?,bio=?,upi_id=? WHERE id=?');$q->execute([$name,$email,$phone,$location?:null,$bio?:null,$upiId?:null,$me['id']]);}
   else{$q=$pdo->prepare('UPDATE users SET name=?,email=?,phone=?,location=?,bio=? WHERE id=?');$q->execute([$name,$email,$phone,$location?:null,$bio?:null,$me['id']]);}
   $_SESSION['user']['name']=$name;
   header('Location: ?view=profile&updated=1');exit;
  }
  if($action==='change_password'){
   if(!$me)throw new Exception('Sign in to change your password.');
   $currentPassword=$_POST['current_password']??'';$newPassword=$_POST['new_password']??'';$confirmPassword=$_POST['confirm_password']??'';
   if(strlen($newPassword)<8)throw new Exception('Your new password must be at least 8 characters.');
   if($newPassword!==$confirmPassword)throw new Exception('The new passwords do not match.');
   $q=$pdo->prepare('SELECT password FROM users WHERE id=?');$q->execute([$me['id']]);$passwordHash=$q->fetchColumn();
   if(!$passwordHash||!password_verify($currentPassword,$passwordHash))throw new Exception('Your current password is incorrect.');
   $q=$pdo->prepare('UPDATE users SET password=? WHERE id=?');$q->execute([password_hash($newPassword,PASSWORD_DEFAULT),$me['id']]);
   header('Location: ?view=profile&password_updated=1');exit;
  }
  if($action==='update_auction_end'){
   if(!$me||$me['role']!=='Seller')throw new Exception('Sign in with a seller account to change an auction date.');
   $id=(int)($_POST['artwork_id']??0);$endInput=$_POST['ends_at']??'';$end=str_replace('T',' ',$endInput);
   if(!$id||!$endInput||strtotime($end)<=time())throw new Exception('Choose an auction end time in the future.');
   $q=$pdo->prepare('SELECT a.id FROM artworks a WHERE a.id=? AND a.seller_id=? AND (a.ends_at>NOW() OR NOT EXISTS (SELECT 1 FROM bids b WHERE b.artwork_id=a.id))');$q->execute([$id,$me['id']]);
   if(!$q->fetchColumn())throw new Exception('Only active auctions or ended items with no bids can be edited.');
   $q=$pdo->prepare('UPDATE artworks SET ends_at=? WHERE id=? AND seller_id=? AND (ends_at>NOW() OR NOT EXISTS (SELECT 1 FROM bids b WHERE b.artwork_id=artworks.id))');$q->execute([$end,$id,$me['id']]);
   header('Location: ?view=seller&date_updated=1');exit;
  }
  if($action==='create'){
   if(!$me||$me['role']!=='Seller')throw new Exception('Sign in with a seller account to list artwork.');
   $title=trim($_POST['title']??'');$description=trim($_POST['description']??'');
   $category=trim($_POST['category']??'');$image=trim($_POST['image_url']??'');
   $price=filter_var($_POST['base_price']??null,FILTER_VALIDATE_FLOAT);
   $endInput=$_POST['ends_at']??'';$end=str_replace('T',' ',$endInput);
   $start=date('Y-m-d H:i:s');
   if($title===''||$description===''||$category==='')throw new Exception('Add a title, description, and category for your artwork.');
   if($price===false||$price<100)throw new Exception('The starting price must be at least ₹100.');
   if($image!==''&&!filter_var($image,FILTER_VALIDATE_URL))throw new Exception('Enter a valid image URL or leave that field blank.');
   if(!$endInput||strtotime($end)<=time())throw new Exception('Choose an auction end time in the future.');
   $pdo->prepare('INSERT INTO artworks(seller_id,title,description,category,image_url,base_price,starts_at,ends_at) VALUES(?,?,?,?,?,?,?,?)')->execute([$me['id'],$title,$description,$category,$image,$price,$start,$end]);
   header('Location: ?view=seller&listed=1');exit;
  }
 } catch(Throwable $x){if($pdo->inTransaction())$pdo->rollBack();$notice=$x->getMessage();if(in_array($action,['login','register'],true)){$reopenAuth=true;$reopenAuthMode=$action;}}
}
