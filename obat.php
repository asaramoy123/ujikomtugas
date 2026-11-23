<?php require 'inc.php'; need('admin');
$a = $_POST['aksi'] ?? ''; $err = '';
try {
  if ($a==='tambah') { $s=$db->prepare('INSERT INTO tb_obat (nama_obat,satuan,stok) VALUES (?,?,?)'); $s->bind_param('ssi',$_POST['nama_obat'],$_POST['satuan'],$_POST['stok']); $s->execute(); }
  if ($a==='ubah')   { $s=$db->prepare('UPDATE tb_obat SET nama_obat=?,satuan=?,stok=? WHERE id_obat=?'); $s->bind_param('ssii',$_POST['nama_obat'],$_POST['satuan'],$_POST['stok'],$_POST['id']); $s->execute(); }
  if ($a==='hapus')  { $s=$db->prepare('DELETE FROM tb_obat WHERE id_obat=?'); $s->bind_param('i',$_POST['id']); $s->execute(); }
} catch (mysqli_sql_exception $x) { $err = 'Obat sudah dipakai pada data pengadaan, tidak bisa dihapus.'; }
head('Data Obat'); if($err) echo "<p class='err'>$err</p>"; ?>
<table><tr><th>ID</th><th>Nama Obat</th><th>Satuan</th><th>Stok</th><th>Aksi</th></tr>
<?php foreach ($db->query('SELECT * FROM tb_obat ORDER BY id_obat') as $o): ?>
<tr><form method="post"><td><?=$o['id_obat']?><input type="hidden" name="id" value="<?=$o['id_obat']?>"></td>
<td><input name="nama_obat" value="<?=e($o['nama_obat'])?>" required></td>
<td><input name="satuan" value="<?=e($o['satuan'])?>" required></td>
<td><input type="number" name="stok" value="<?=$o['stok']?>" min="0" required></td>
<td><button name="aksi" value="ubah">Simpan</button> <button name="aksi" value="hapus" onclick="return confirm('Hapus?')">Hapus</button></td></form></tr>
<?php endforeach; ?></table>
<h3>Tambah Obat</h3>
<form method="post"><input name="nama_obat" placeholder="Nama obat" required><input name="satuan" placeholder="Satuan (Tablet/Botol)" required>
<input type="number" name="stok" placeholder="Stok awal" value="0" min="0" required><button name="aksi" value="tambah">Tambah</button></form>
<?php foot();
