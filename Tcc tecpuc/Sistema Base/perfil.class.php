<?php
	class perfil extends config{
		function listaPerfil() {
			$sql = "select * from perfil order by idperfil";
			
			$query = $this->mysqli->query($sql);
			
			return $query;
		}
		
		function perfilSingle($idperfil) {
			$sql = "select * from perfil where idperfil = '$idperfil'";
			
			$query = $this->mysqli->query($sql);
			
			while($result = $query->fetch_array()) {
				return $result['perfil'];
			}
		}
	}
?>