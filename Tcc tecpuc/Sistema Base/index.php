<?php
	session_start();
	require('classes/config.class.php');
	require('classes/usuario.class.php');
	
	if (!isset($_SESSION['idusuario'])) {
		header('location:login.php');
	}
?>

<p>Logado com sucesso</p>
<br />
<a href="userlist.php">Lista de Usuários</a>
<br />
<a class="btn btn-primary" href="logout.php">Logout</a>