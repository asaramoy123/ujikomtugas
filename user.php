<?php require 'inc.php'; need('admin');
$a = $_POST['aksi'] ?? ''; $err = '';
try {
  if ($a==='tambah') { $h=password_hash($_POST['pass'],PASSWORD_DEFAULT); $s=$db->prepare('INSERT INTO tb_user (username,pass,role,nama) VALUES (?,?,?,?)'); $s->bind_param('ssss',$_POST['username'],$h,$_POST['role'],$_POST['nama']); $s->execute(); }
  if ($a==='ubah') {
    $s=$db->prepare('UPDATE tb_user SET username=?,role=?,nama=? WHERE id_user=?'); $s->bind_param('sssi',$_POST['username'],$_POST['role'],$_POST['nama'],$_POST['id']); $s->execute();
    if ($_POST['pass']!=='') { $h=password_hash($_POST['pass'],PASSWORD_DEFAULT); $s=$db->prepare('UPDATE tb_user SET pass=? WHERE id_user=?'); $s->bind_param('si',$h,$_POST['id']); $s->execute(); }
  }
  if ($a==='hapus') {
    if ((int)$_POST['id']===$_SESSION['u']['id']) throw new Exception('Tidak bisa menghapus akun sendiri.');
    $s=$db->prepare('DELETE FROM tb_user WHERE id_user=?'); $s->bind_param('i',$_POST['id']); $s->execute();
  }
} catch (mysqli_sql_exception $x) { $err = 'Gagal: username sudah dipakai atau user punya data pengadaan.'; }
  catch (Exception $x) { $err = $x->getMessage(); }
head('Data User'); if($err) echo "<p class='err'>".e($err)."</p>"; ?>
<table><tr><th>ID</th><th>Nama</th><th>Username</th><th>Role</th><th>Password baru</th><th>Aksi</th></tr>
<?php foreach ($db->query('SELECT * FROM tb_user ORDER BY id_user') as $u): ?>
<tr><form method="post"><td><?=$u['id_user']?><input type="hidden" name="id" value="<?=$u['id_user']?>"></td>
<td><input name="nama" value="<?=e($u['nama'])?>" required></td>
<td><input name="username" value="<?=e($u['username'])?>" required></td>
<td><select name="role"><?php foreach(['admin','supplier'] as $r) echo "<option ".($r==$u['role']?'selected':'').">$r</option>"; ?></select></td>
<td><input name="pass" type="password" placeholder="(kosong = tetap)"></td>
<td><button name="aksi" value="ubah">Simpan</button> <button name="aksi" value="hapus" onclick="return confirm('Hapus?')">Hapus</button></td></form></tr>
<?php endforeach; ?></table>
<h3>Tambah User</h3>
<form method="post"><input name="nama" placeholder="Nama" required><input name="username" placeholder="Username" required>
<input name="pass" type="password" placeholder="Password" required><select name="role"><option>supplier</option><option>admin</option></select>
<button name="aksi" value="tambah">Tambah</button></form>
<?php foot();
