<?php
require('class/userType.class.php');

class user extends config
{
	function login($login, $password)
	{
		$password = $this->encode($password);
		$sql = "select * from user where (Username = '$login' OR Email = '$login') and Password = '$password'";

		$query = $this->mysqli->query($sql);

		while ($result = $query->fetch_assoc()) {
			$idusuario = $result['Id'];
			$user = $result['Username'];
			$email = $result['Email'];
			$idperfil = $result['UserTypeId'];
			$changePass = $result['ChangePassword'];
		}

		if ($query->num_rows == 1) {
			if ($idperfil == 2) {
				$sqlPat = "select * from patient where UserId = $idusuario";
				$queryPat = $this->mysqli->query($sqlPat);

				while ($result = $queryPat->fetch_assoc()) {
					$idpaciente = $result['Id'];
				}
			}

			if ($idperfil == 3) {
				$sqlHosp = "select * from hospital where UserId = $idusuario";
				$queryHosp = $this->mysqli->query($sqlHosp);

				while ($result = $queryHosp->fetch_assoc()) {
					$idhospital = $result['Id'];
				}
			}

			if ($idperfil == 4) {
				$idhospital = 1;
			}

			$_SESSION['idusuario'] = $idusuario;
			$_SESSION['login'] = $user;
			$_SESSION['email'] = $email;
			$_SESSION['idperfil'] = $idperfil;
			$_SESSION['changePass'] = $changePass;
			$_SESSION['idpaciente'] = $idpaciente;
			$_SESSION['idhospital'] = $idhospital;
			return true;
		} else {
			return false;
		}
	}

	function postUser($form)
	{
		$login = $form['login'];
		$email = $form['email'];
		$password = $this->encode($form['senha']);

		$loginExists = $this->loginExists($login);
		$emailExists = $this->loginExists($email);

		if ($loginExists != 0 || $emailExists != 0) {
			return 0;
		} else {
			$sqlInsert = "INSERT INTO User (`Id`, `Username`, `Email`, `Password`, `CreateDate`, `UserTypeId`) VALUES(NULL, '$login', '$email', '$password', CURRENT_TIMESTAMP(), 2)";
			$queryInsert = $this->mysqli->query($sqlInsert);

			$sqlSelect = "SELECT LAST_INSERT_ID() AS Id FROM `patient`";
			$query = $this->mysqli->query($sqlSelect);
			$id = $query->fetch_array();

			return $id["Id"];
		}
	}

	function editUser($form)
	{
		$id = $form['id'];
		$login = $form['login'];
		$email = $form['email'];
		$photo = $form['photo'];

		$loginExists = $this->loginExists($login);
		$emailExists = $this->loginExists($email);
		$photoExists = $this->getPhoto($id);

		if (($loginExists != 0 && $loginExists != $id) || ($emailExists != 0 && $emailExists != $id)) {
			return 0;
		} else {
			$sql = " UPDATE `user` SET Username = '$login', Email = '$email' ";

			if ($form['senha'] != '') {
				$password = $this->encode($form['senha']);
				$sql .= " , Password = '$password' ";
			}

			if ($form['photo'] != '') {
				if ($form['photo'] != $photoExists['Photo'] && $photoExists['Photo'] != NULL) {
					unlink('assets/images/' . $photoExists['Photo']);
				}

				$sql .= " , Photo = '$photo' ";
			}

			$sql .= " WHERE Id = $id ";
			$this->mysqli->query($sql);
			$_SESSION['login'] = $login;
			return $id;
		}
	}

	function getPhoto($id)
	{
		$sql = "select Id, Photo from user WHERE Id = $id";
		$query = $this->mysqli->query($sql);
		return $query->fetch_array();
	}

	function userSingle($id)
	{
		$sql = "select Id, Username, Email, Photo from user WHERE Id = $id";
		$query = $this->mysqli->query($sql);
		return $query->fetch_array();
	}

	function loginExists($login)
	{
		$sql = "select * from user where Username = '$login' OR Email = '$login'";

		$query = $this->mysqli->query($sql);

		if ($query->num_rows == 1) {
			while ($result = $query->fetch_assoc()) {
				return $result['Id'];
			}
		} else {
			return 0;
		}
	}

	function emailExists($email)
	{
		$sql = "select * from user where Email = '$email'";

		$query = $this->mysqli->query($sql);

		if ($query->num_rows == 1) {
			while ($result = $query->fetch_assoc()) {
				return $result['Id'];
			}
		} else {
			return 0;
		}
	}

	function newPassword($email)
	{
		$log = new logs;
		if ($this->emailExists($email) != 0) {
			$random = $this->random_strings();
			$password = $this->encode($random);
			$id = $this->emailExists($email);
			$sql = "update user set `Password` = '$password', ChangePassword = 1 where Email = '$email'";
			$query = $this->mysqli->query($sql);

			if ($log->block($id)) {
				$log->deleteByUserId($id);
			}

			$body = 'Sua nova senha e ' . $random;
			mail($email, 'Sua senha foi alterada!', $body, 'From: fundz.game@gmail.com');
			return true;
		} else {
			return false;
		}
	}

	function changePassword($oldPassword, $newPassword)
	{
		if ($this->login($_SESSION['login'], $oldPassword)) {
			$password = $this->encode($newPassword);
			$login = $_SESSION['login'];
			$sql = "update user set `Password` = '$password', ChangePassword = 0 where (Email = '$login' OR UserName = '$login')";
			$query = $this->mysqli->query($sql);

			return true;
		} else {
			return false;
		}
	}
}
