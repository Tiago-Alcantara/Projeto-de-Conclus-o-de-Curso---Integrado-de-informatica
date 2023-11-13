<?php
	session_start();
	require('classes/config.class.php');
	require('classes/usuario.class.php');
	require('classes/acesso.class.php');
	
	$acesso = new acesso;
	
	if (!isset($_SESSION['idusuario'])) {
		header('location:login.php');
	} else {
		$perfilid = $acesso->acessoPermissao(basename( __FILE__ ));
		$permissao = false;
		foreach ($perfilid as $valor) {
			if ($valor['perfil_idperfil'] == $_SESSION['idperfil']) {
				$permissao = true;
			}
		}
		
		if (!$permissao) {
			header('location:index.php');
		}
	}
	
	$user = new user;
	
	echo $user->userList();
?>

<a href="index.php">Home</a>