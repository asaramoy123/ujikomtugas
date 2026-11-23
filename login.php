<?php require 'inc.php';
$err = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $s = $db->prepare('SELECT * FROM tb_user WHERE username=?');
  $s->bind_param('s', $_POST['username']); $s->execute();
  $u = $s->get_result()->fetch_assoc();
  if ($u && password_verify($_POST['pass'], $u['pass'])) {
    $_SESSION['u'] = ['id'=>$u['id_user'],'nama'=>$u['nama'],'role'=>$u['role']];
    header('Location: index.php'); exit;
  }
  $err = 'Username atau password salah';
}
head('Login'); if($err) echo "<p class='err'>".e($err)."</p>"; ?>
<form method="post">
  <input name="username" placeholder="Username" required>
  <input name="pass" type="password" placeholder="Password" required>
  <button>Masuk</button>
</form>
<?php foot();
