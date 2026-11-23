<?php
session_start();
$db = new mysqli('localhost', 'root', '', 'db_klinik');
if ($db->connect_error) die('Koneksi database gagal');
$db->set_charset('utf8mb4');
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES); }
function rp($n){ return 'Rp ' . number_format($n, 0, ',', '.'); }
function need(...$roles){
  if (empty($_SESSION['u'])) { header('Location: login.php'); exit; }
  if (!in_array($_SESSION['u']['role'], $roles)) { header('Location: index.php'); exit; }
}
function head($t){
  $u = $_SESSION['u'] ?? null;
  echo "<!DOCTYPE html><html lang='id'><head><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'><title>".e($t)."</title><link rel='stylesheet' href='style.css'></head><body><nav><b>💊 Pengadaan Obat</b>";
  if ($u && $u['role']==='admin') echo "<a href='obat.php'>Data Obat</a><a href='user.php'>Data User</a>";
  if ($u) echo "<a href='pengadaan.php'>Pengadaan</a><span>".e($u['nama'])." ({$u['role']})</span><a href='logout.php'>Keluar</a>";
  echo "</nav><main><h2>".e($t)."</h2>";
}
function foot(){ echo "</main></body></html>"; }
