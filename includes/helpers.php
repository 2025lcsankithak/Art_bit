<?php
function e($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function csrf_token(){if(empty($_SESSION['csrf_token']))$_SESSION['csrf_token']=bin2hex(random_bytes(32));return $_SESSION['csrf_token'];}
function csrf_field(){return '<input type="hidden" name="csrf_token" value="'.e(csrf_token()).'">';}
function phone_country_codes(){return ['India'=>'+91','United States'=>'+1','Canada'=>'+1','United Kingdom'=>'+44','Australia'=>'+61','New Zealand'=>'+64','Singapore'=>'+65','United Arab Emirates'=>'+971','Saudi Arabia'=>'+966','Pakistan'=>'+92','Bangladesh'=>'+880','Sri Lanka'=>'+94','Nepal'=>'+977','China'=>'+86','Japan'=>'+81','South Korea'=>'+82','Germany'=>'+49','France'=>'+33','Italy'=>'+39','Spain'=>'+34','Netherlands'=>'+31','Switzerland'=>'+41','Sweden'=>'+46','Norway'=>'+47','Denmark'=>'+45','Ireland'=>'+353','South Africa'=>'+27','Brazil'=>'+55','Mexico'=>'+52','Indonesia'=>'+62','Malaysia'=>'+60','Philippines'=>'+63','Thailand'=>'+66'];}
function phone_tel($phone){$phone=trim((string)$phone);return preg_replace('/[^0-9+]/','',$phone);}
function money($n){return '₹'.number_format((float)$n);}
