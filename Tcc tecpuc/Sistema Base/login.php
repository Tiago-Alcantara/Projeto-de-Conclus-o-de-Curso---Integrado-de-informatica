<?php
	session_start();
	require('classes/config.class.php');
	require('classes/usuario.class.php');
	require('classes/log.class.php');
	
	if (isset($_SESSION['idusuario'])) {
		header('location:index.php');
	}
	
	$user = new user;
	$log = new logs;
	
	if ($_POST) {
		$idpessoa = $user->loginExists($_POST['login']);
		$blocked = $log->block($idpessoa);
		
		if ($blocked) {
			session_destroy();
			echo "Usuário bloqueado pelo sistema."; 
		} else {
			if ($user->login($_POST['login'], $_POST['senha'])) {
					header('location:index.php');
			} else {
				if ($idpessoa != 0) {
					$log->insertUserLog($idpessoa);
				}
				echo "Login e/ou senha incorretos";
			}
		}
	}
?>

<pre>
<form method="post">
	Login: <input type="text" name="login">
	Senha: <input type="password" name="senha">
	
	<button type="submit">login</button>
	<a class="btn btn-primary" href="user.php">Criar usuário</a>
</form>