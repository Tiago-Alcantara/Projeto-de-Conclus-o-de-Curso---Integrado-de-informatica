<?php
class doctor extends config
{
	function postDoctor($form)
	{
		$name = $form['name'];
		$crm = $form['crm'];
		$specialty = $form['specialty'];
		$hospitalId = $form['hospitalId'];
		$photo = $form['photo'];

		if ($form['photo'] == '') {
			$sql = "INSERT INTO `doctor` (Id, Name, CRM, Specialty, Photo) VALUES (NULL, '$name', '$crm', '$specialty', NULL)";
		} else {
			$sql = "INSERT INTO `doctor` (Id, Name, CRM, Specialty, Photo) VALUES (NULL, '$name', '$crm', '$specialty', '$photo')";
		}

		$this->mysqli->query($sql);

		$sqlSelect = "SELECT LAST_INSERT_ID() AS Id FROM `doctor`";
		$query = $this->mysqli->query($sqlSelect);
		$id = $query->fetch_array();
		$doctorId = $id["Id"];

		if ($this->getDoctorHospital($hospitalId, $doctorId) == 0 && $hospitalId != 0) {
			$sql = "INSERT INTO `doctorhospital` (DoctorId, HospitalId) VALUES ($id, $hospitalId)";
			$this->mysqli->query($sql);
		}

		header('location:doctor.php?id=' . $doctorId);
	}

	function editDoctor($form)
	{
		$name = $form['name'];
		$crm = $form['crm'];
		$specialty = $form['specialty'];
		$hospitalId = $form['hospitalId'];
		$id = $form['id'];
		$photo = $form['photo'];

		$photoExists = $this->getPhoto($id);

		$sql = " UPDATE `doctor` SET Name = '$name', CRM = '$crm', Specialty = '$specialty' ";

		if ($form['photo'] != '') {
			if ($form['photo'] != $photoExists['Photo'] && $photoExists['Photo'] != NULL) {
				unlink('assets/images/' . $photoExists['Photo']);
			}

			$sql .= " , Photo = '$photo' ";
		}

		$sql .= " WHERE Id = $id ";

		$this->mysqli->query($sql);

		if ($this->getDoctorHospital($hospitalId, $id) == 0 && $hospitalId != 0) {
			$sql = "INSERT INTO `doctorhospital` (DoctorId, HospitalId) VALUES ($id, $hospitalId)";
			$this->mysqli->query($sql);
		}
	}

	function getPhoto($id)
	{
		$sql = "select Id, Photo from doctor WHERE Id = $id";
		$query = $this->mysqli->query($sql);
		return $query->fetch_array();
	}

	function getDoctorHospital($hospitalId, $doctorId)
	{
		$sqlSelect = "SELECT * FROM `doctorhospital` WHERE HospitalId = $hospitalId AND DoctorId = $doctorId";
		$query = $this->mysqli->query($sqlSelect);

		if ($query->num_rows > 0) {
			return 1;
		} else {
			return 0;
		}
	}

	function deleteHospitalList($id, $hospitalId)
	{
		$sql = "DELETE FROM `doctorhospital` WHERE DoctorId = $id AND HospitalId = $hospitalId";
		$query = $this->mysqli->query($sql);
	}

	function deleteDoctor($id)
	{
		$sql = "DELETE FROM `doctor` WHERE Id = $id";
		$query = $this->mysqli->query($sql);
	}

	function doctorList($filter = [])
	{
		if ($filter == []) {
			$name = '';
			$specialty = '';
			$hospitalId = '';
		} else {
			$name = $filter['name'];
			$specialty = $filter['specialty'];
			$hospitalId = $filter['hospitalId'];
		}

		$sql = " SELECT * FROM `doctor` WHERE 1 = 1 ";

		if ($name != null && $name != '') {
			$sql .= " AND Name LIKE '%$name%' ";
		}

		if ($specialty != null && $specialty != '') {
			$sql .= " AND Specialty LIKE '%$specialty%' ";
		}

		$query = $this->mysqli->query($sql);

		$sqlSelect = "SELECT * FROM `doctorhospital` WHERE 1 = 1";

		if ($hospitalId != null && $hospitalId != '') {
			$sqlSelect .= " AND HospitalId = $hospitalId ";
		}

		$querySelect = $this->mysqli->query($sqlSelect);
		$return['HospitalIds'] = array();
		$lastDoctor = 0;
		foreach ($query as $indice => $result) {
			foreach ($querySelect as $value) {
				if ($result["Id"] == $value['DoctorId']) {
					if ($lastDoctor == $value['DoctorId']) {
						$lastId = $return['HospitalIds'][$indice];
						if (is_array($return['HospitalIds'][$indice])) {
							array_push($return['HospitalIds'][$indice], $value['HospitalId']);
						} else {
							$return['HospitalIds'][$indice] = array();
							array_push($return['HospitalIds'][$indice], $lastId, $value['HospitalId']);
						}
					} else {
						array_push($return['HospitalIds'], $value['HospitalId']);
					}
					$lastDoctor = $value['DoctorId'];
				}
			}
		}

		if ($hospitalId != null && $hospitalId != '') {
			$sql = " SELECT * FROM `doctor` D LEFT JOIN doctorhospital DH ON DH.DoctorId = D.Id WHERE DH.HospitalId = $hospitalId ";
		}
		$query = $this->mysqli->query($sql);
		array_push($return, $query);
		return $return;
	}

	function doctorSingle($id)
	{
		$sql = "SELECT * FROM `doctor` WHERE Id = '$id'";
		$query = $this->mysqli->query($sql);

		$sqlSelect = "SELECT * FROM `doctorhospital` WHERE DoctorId = $id";
		$querySelect = $this->mysqli->query($sqlSelect);
		$return['HospitalIds'] = array();
		array_push($return, $query);
		foreach ($querySelect as $value) {
			array_push($return['HospitalIds'], $value['HospitalId']);
		}

		return $return;
	}
}
