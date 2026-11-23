<?php require 'inc.php'; need('admin','supplier');
$adm = $_SESSION['u']['role']==='admin'; $me = $_SESSION['u']['id']; $err = '';
function milik($db,$id,$adm,$me){
  $s=$db->prepare('SELECT * FROM tb_pengadaan WHERE id_pengadaan=?'); $s->bind_param('i',$id); $s->execute();
  $r=$s->get_result()->fetch_assoc(); return ($r && ($adm || $r['id_user']==$me)) ? $r : null;
}
function stok($db,$obat,$d){ $s=$db->prepare('UPDATE tb_obat SET stok=GREATEST(stok+?,0) WHERE id_obat=?'); $s->bind_param('ii',$d,$obat); $s->execute(); }
$a = $_POST['aksi'] ?? '';
try {
  if ($a==='simpan') {
    $obat=(int)$_POST['id_obat']; $jml=max(1,(int)$_POST['jumlah_beli']); $hrg=max(0,(int)$_POST['harga_beli']);
    $tgl=$_POST['tgl_masuk']; $usr=$adm ? (int)$_POST['id_user'] : $me; $id=(int)($_POST['id'] ?? 0);
    $db->begin_transaction();
    if ($id===0) {
      $s=$db->prepare('INSERT INTO tb_pengadaan (id_obat,id_user,jumlah_beli,harga_beli,tgl_masuk) VALUES (?,?,?,?,?)');
      $s->bind_param('iiiis',$obat,$usr,$jml,$hrg,$tgl); $s->execute(); stok($db,$obat,$jml);
    } elseif ($old = milik($db,$id,$adm,$me)) {
      stok($db,$old['id_obat'],-$old['jumlah_beli']);
      $s=$db->prepare('UPDATE tb_pengadaan SET id_obat=?,id_user=?,jumlah_beli=?,harga_beli=?,tgl_masuk=? WHERE id_pengadaan=?');
      $s->bind_param('iiiisi',$obat,$usr,$jml,$hrg,$tgl,$id); $s->execute(); stok($db,$obat,$jml);
    }
    $db->commit();
  }
  if ($a==='hapus' && ($old = milik($db,(int)$_POST['id'],$adm,$me))) {
    $db->begin_transaction();
    $s=$db->prepare('DELETE FROM tb_pengadaan WHERE id_pengadaan=?'); $s->bind_param('i',$old['id_pengadaan']); $s->execute();
    stok($db,$old['id_obat'],-$old['jumlah_beli']); $db->commit();
  }
} catch (mysqli_sql_exception $x) { $db->rollback(); $err = 'Gagal menyimpan data.'; }
$edit = isset($_GET['edit']) ? milik($db,(int)$_GET['edit'],$adm,$me) : null;
head('Data Pengadaan Obat'); if($err) echo "<p class='err'>$err</p>";
$sql = 'SELECT p.*, o.nama_obat, o.satuan, u.nama AS supplier FROM tb_pengadaan p JOIN tb_obat o ON p.id_obat=o.id_obat JOIN tb_user u ON p.id_user=u.id_user'
     . ($adm ? '' : ' WHERE p.id_user='.(int)$me) . ' ORDER BY p.tgl_masuk DESC, p.id_pengadaan DESC';
$sum = 0; ?>
<table><tr><th>ID</th><th>Tanggal</th><th>Obat</th><th>Supplier</th><th>Jumlah</th><th>Harga Beli</th><th>Total</th><th>Aksi</th></tr>
<?php foreach ($db->query($sql) as $p): $tot=$p['jumlah_beli']*$p['harga_beli']; $sum+=$tot; ?>
<tr><td><?=$p['id_pengadaan']?></td><td><?=$p['tgl_masuk']?></td><td><?=e($p['nama_obat'])?></td><td><?=e($p['supplier'])?></td>
<td><?=$p['jumlah_beli'].' '.e($p['satuan'])?></td><td><?=rp($p['harga_beli'])?></td><td><?=rp($tot)?></td>
<td><a class="btn" href="?edit=<?=$p['id_pengadaan']?>">Ubah</a>
<form method="post" style="display:inline"><input type="hidden" name="id" value="<?=$p['id_pengadaan']?>"><button name="aksi" value="hapus" onclick="return confirm('Hapus?')">Hapus</button></form></td></tr>
<?php endforeach; ?></table>
<p><b>Total biaya pengadaan: <?=rp($sum)?></b></p>
<h3><?= $edit ? 'Ubah Pengadaan #'.$edit['id_pengadaan'] : 'Tambah Pengadaan' ?></h3>
<form method="post">
<input type="hidden" name="id" value="<?=$edit['id_pengadaan'] ?? 0?>">
<select name="id_obat"><?php foreach ($db->query('SELECT * FROM tb_obat ORDER BY nama_obat') as $o) echo "<option value='{$o['id_obat']}' ".(($edit['id_obat'] ?? 0)==$o['id_obat']?'selected':'').">".e($o['nama_obat'])." (stok {$o['stok']})</option>"; ?></select>
<?php if ($adm): ?><select name="id_user"><?php foreach ($db->query("SELECT * FROM tb_user WHERE role='supplier'") as $u) echo "<option value='{$u['id_user']}' ".(($edit['id_user'] ?? 0)==$u['id_user']?'selected':'').">".e($u['nama'])."</option>"; ?></select><?php endif; ?>
<input type="number" name="jumlah_beli" placeholder="Jumlah beli" min="1" value="<?=$edit['jumlah_beli'] ?? ''?>" required>
<input type="number" name="harga_beli" placeholder="Harga beli satuan" min="0" value="<?=$edit['harga_beli'] ?? ''?>" required>
<input type="date" name="tgl_masuk" value="<?=$edit['tgl_masuk'] ?? date('Y-m-d')?>" required>
<button name="aksi" value="simpan">Simpan</button> <?php if($edit) echo "<a class='btn' href='pengadaan.php'>Batal</a>"; ?>
</form>
<?php foot();
