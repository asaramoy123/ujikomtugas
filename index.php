<?php require 'inc.php';
if (empty($_SESSION['u'])) { header('Location: login.php'); exit; }
header('Location: ' . ($_SESSION['u']['role']==='admin' ? 'obat.php' : 'pengadaan.php'));
