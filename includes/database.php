<?php
$host='localhost'; $db='artbid'; $user='root'; $pass='';
try { $pdo=new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4",$user,$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]); }
catch(Throwable $e){ $pdo=null; }
