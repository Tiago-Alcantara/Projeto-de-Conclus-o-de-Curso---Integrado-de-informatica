<?php
	session_start();
	require('classes/config.class.php');
	require('classes/usuario.class.php');
	require('classes/perfil.class.php');
	
	if (isset($_SESSION['login'])) {
		header('location:index.php');
	}
	
	$user = new user;
	$perfil = new perfil;
	$lista = $perfil->listaPerfil();
	
	if ($_POST) {
		if ($_POST['perfil_idperfil'] == 0) {
			echo "Selecione um perfil.";
		} else {
			if ($_POST['senha'] == $_POST['confirmar']) {
				if ($user->postUser($_POST) == 1) {
					if ($user->login($_POST['login'], $_POST['senha'])) {
						$_SESSION['idusuario'] = $_POST['idusuario'];
						$_SESSION['email'] = $_POST['email'];
						$_SESSION['idperfil'] = $_POST['idperfil'];
						header('location:index.php');
					}
				} else if ($user->postUser($_POST) == 0) {
					echo "Usuário já possui cadastro.";
					header('location:login.php');
				} else {
					echo "Usuário não cadastrado.";
				}
			} else {
				echo "Senhas não correspondentes.";
			}
		}
	}
?>

<pre>
<form method="post">
	Nome: <input type="text" name="nome" required>
	Email: <input type="email" name="email" required>
	Login: <input type="text" name="login" required>
	Senha: <input type="password" name="senha" required>
	Confirmar Senha: <input type="password" name="confirmar" required>
	Data de Cadastro: <input type="date" name="datacadastro" required>
	<!--Perfil: <select name="perfil_idperfil" required>
		<option value="0">selecione</option>
		<?php
			//foreach($lista as $valor) {
		?>
		<option value="<?php //echo $valor['idperfil']; ?>"><?php //echo $valor['perfil']; ?></option>
		<?php //} ?>
	</select>
	
	<button type="submit">Criar</button>-->
</form>