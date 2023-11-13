<?php
	require('classes/perfil.class.php');
	
	class user extends config {
		function login($login,$senha){
			$senha = $this->encode($senha);
			$sql = "select * from pessoa where login = '$login' and senha = '$senha'";
			
			$query = $this->mysqli->query($sql);
			
			while($result = $query->fetch_assoc()) {
				$idusuario = $result['idpessoa'];
				$email = $result['email'];
				$idperfil = $result['perfil_idperfil'];
			}
			
			if($query->num_rows == 1){
				$_SESSION['idusuario'] = $idusuario;
				$_SESSION['email'] = $email;
				$_SESSION['idperfil'] = $idperfil;
				return true;
			}else{
				return false;
			}
		}
		
		function postUser($form) {
			$nome = $form['nome'];
			$email = $form['email'];
			$login = $form['login'];
			$datacadastro = $form['datacadastro'];
			$senha = $this->encode($form['senha']);
			$sql = "select * from pessoa where login = '$login'";
			
			$query = $this->mysqli->query($sql);
			
			if($query->num_rows == 1){
				return 0;
			}else{
				$sqlInsert = "INSERT INTO pessoa (`idpessoa`, `nome`, `email`, `senha`, `login`, `datacadastro`, `perfil_idperfil`) VALUES(NULL, '$nome', '$email', '$senha', '$login', '$datacadastro', 2)";
				$queryInsert = $this->mysqli->query($sqlInsert);
				
				return 1;
			}
		}
		
		function loginExists($login) {
			$sql = "select * from pessoa where login = '$login'";
			
			$query = $this->mysqli->query($sql);
			
			if($query->num_rows == 1){
				while($result = $query->fetch_assoc()) {
					return $result['idpessoa'];
				}
			}else{
				return 0;
			}
		}
		
		function userList() {
			$perfil = new perfil;
			$sql = "select idpessoa, nome, email, login, datacadastro, perfil_idperfil from pessoa";
			
			$query = $this->mysqli->query($sql);
			
			while($result = $query->fetch_assoc()) {
				echo "Id: " . $result['idpessoa'];
				echo "<br />";
				echo "Nome: " . $result['nome'];
				echo "<br />";
				echo "Email: " . $result['email'];
				echo "<br />";
				echo "Login: " . $result['login'];
				echo "<br />";
				echo "Data de Cadastro: " . $this->data($result['datacadastro']);
				echo "<br />";
				echo "Perfil: " . $perfil->perfilSingle($result['perfil_idperfil']);
				echo "<br /><hr><br />";
			}
		}
		
		function encode($senha) {
			$array = str_split($senha);
			foreach($array as $hash) {
			$hashed = hash('sha256', $senha);
				$senha = $hashed;
			}
			
			return $senha;
		}
		
		function data($data){
			date_default_timezone_set('America/Sao_Paulo');
			return date("d/m/Y", strtotime($data));
		}
	}
?>