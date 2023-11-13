<?php
	class acesso extends config{
		function insertAccesso($pagina, $idperfil) {
			$sqlInsert = "INSERT INTO `acesso`(`idacesso`, `pagina`, `perfil_idperfil`) VALUES(NULL, $pagina, '$idperfil')";
			$queryInsert = $this->mysqli->query($sqlInsert);
		}
		
		function acessoPermissao($pagina) { 
			$sql = "select * from `acesso` where pagina = '$pagina'";
			
			$query = $this->mysqli->query($sql);
			
			return $query;
		}
	}
?>