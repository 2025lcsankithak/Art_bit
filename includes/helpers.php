<?php
function e($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function money($n){return '₹'.number_format((float)$n);}
