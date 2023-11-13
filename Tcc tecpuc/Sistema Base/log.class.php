<?php
	class logs extends config{
		function insertUserLog($idpessoa) {
			$sqlInsert = "INSERT INTO `log`(`idlog`, `datahora`, `pessoa_idpessoa`) VALUES(NULL, CURRENT_TIMESTAMP(), '$idpessoa')";
			$queryInsert = $this->mysqli->query($sqlInsert);
		}
		
		function block($idpessoa) {
			$sql = "select * from `log` where pessoa_idpessoa = '$idpessoa'";
			
			$query = $this->mysqli->query($sql);
			
			if($query->num_rows == 3){
				return true;
			}else{
				return false;
			}
		}
		
		function deleteByIdPessoa($idpessoa) {
			$sql = "delete `log` where pessoa_idpessoa = '$idpessoa'";
		}
	}
?>