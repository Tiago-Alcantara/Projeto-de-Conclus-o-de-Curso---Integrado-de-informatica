<?php
session_start();
require('class/config.class.php');
require 'inc/_global/views/head_start.php';
require 'inc/_global/views/head_end.php';
require 'class/menu.class.php';
require 'class/doctor.class.php';
require 'class/hospital.class.php';
require('class/access.class.php');
$url = basename(__FILE__);
$access = new access;

if (!isset($_SESSION['login'])) {
	header('location:login.php');
} else {
	$perfilid = $access->accessPermission($url);
	$permission = false;
	foreach ($perfilid as $valor) {
		if ($valor['UserTypeId'] == $_SESSION['idperfil']) {
			$permission = true;
		}
	}

	if (!$permission) {
		header('location:home.php');
	}
}

$doctor = new doctor;
$config = new config;
$menu = new menu;
$hospital = new hospital;
$id = $_GET["id"];
$single = $doctor->doctorSingle($id);
$result = $single[0]->fetch_array();
$pasta = "assets/images/";
$urlActive = 'doctorList.php';
$config->active($urlActive);

$hospList = $hospital->hospitalList();

if ($_POST) {
	$_POST["id"] = $id;

	if (basename($_FILES["fileToUpload"]["name"]) == '') {
		$_POST['photo'] = '';
	} else {
		$arquivo = $pasta . basename($_FILES["fileToUpload"]["name"]);
		$_POST['photo'] = basename($_FILES["fileToUpload"]["name"]);

		$uploadOk = 1;
		$imageFileType = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));

		if (isset($_POST["submit"])) {
			$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
			if ($check !== false) {
				$uploadOk = 1;
			} else {
				$config->alert("Arquivo não é uma imagem.");
				$uploadOk = 0;
			}
		}

		if (file_exists($arquivo)) {
			$config->alert("Já existe um arquivo com este nome.");
			$uploadOk = 0;
		}

		if ($_FILES["fileToUpload"]["size"] > 5000000) {
			$config->alert("Arquivo muito grande para ser realizado o upload.");
			$uploadOk = 0;
		}

		if (
			$imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
			&& $imageFileType != "gif"
		) {
			$config->alert("Apenas arquivos JPG, JPEG, PNG & GIF São permitidos.");
			$uploadOk = 0;
		}

		if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $arquivo)) {
			$config->alert("Não foi possível enviar o arquivo.");
		}
	}

	if ((isset($uploadOk) && $uploadOk == 1) || !isset($uploadOk)) {
		if ($_SESSION["idperfil"] == 3) {
			$hospSingle = $hospital->getByUserId($_SESSION["idusuario"]);
			$_POST["hospitalId"] = $hospSingle["Id"];
		}

		if ($_POST['hospitalId'] == 0 && $_SESSION["idperfil"] == 1 && count($single['HospitalIds']) == 0) {
			$config->alert('Selecione um hospital.');
		} else {
			if ($id == 0) {
				$doctor->postDoctor($_POST);
			} else {
				$doctor->editDoctor($_POST);
				$single = $doctor->doctorSingle($id);
				$result = $single[0]->fetch_array();
			}
		}
	}
}
?>

<script>
	function confirm(id, hospitalId) {
		swal.fire({
			text: 'Tem certeza que deseja excluir o hospital?',
			showCancelButton: true,
			cancelButtonColor: '#d33'
		}).then((result) => {
			if (result.value) {
				url = 'hospitalList.delete.php?id=' + id + '&hospitalId=' + hospitalId;
				window.location.href = url;
			}
		});
	}
</script>

<div id="divLoading" class="loading" style="display: none">
	<div class="loader"></div>
</div>

<body>
	<div id="page-container" class="sidebar-o sidebar-dark enable-page-overlay side-scroll page-header-dark">
		<nav id="sidebar" aria-label="Main Navigation">
			<?php $menu->getMenu(); ?>
		</nav>

		<header id="page-header" class="dark header-dark">
			<div class="content-header">
				<div class="d-flex align-items-center">
					<a type="button" class="btn btn-sm btn-dual" style="right: 40px; position: absolute;" href="profile.php?id=<?php echo $_SESSION['idusuario'] ?>">
						<span class="d-none d-sm-inline-block"><?php echo $_SESSION['login'] ?></span>
					</a>
				</div>
			</div>
		</header>

		<main id="main-container">
			<div class="content">
				<div class="block">
					<div class="block-header">
						<h1 class="flex-sm-fill h3 my-2">Hospital</h1>
					</div>
					<div class="block-content" style="padding-top: 0px;">
						<form method="POST" enctype="multipart/form-data">
							<div class="row push field_wrapper" style="margin-bottom: 0;">
								<div class="col-lg-3" style="display: flex; justify-content: center;">
									<div style="width: 150px; border: solid 4px; height: 150px; border-radius: 85px; 
                                            background-image: url(<?php if ($result["Photo"] != NULL) {
																		echo $pasta . $result["Photo"];
																	} else {
																		echo 'assets/images/avatars/avatar0.jpg';
																	} ?>); 
                                            margin-bottom: 20px; float: left; background-size: 150px 150px;"></div>
								</div>
								<div class="col-lg-9" style="display: flex;">
									<div class="form-group" style="display: inline-block; align-self: flex-end; width: 100%;">
										Foto: <input type="file" class="form-control" name="fileToUpload" style="height: auto !important;">
									</div>
								</div>
								<div class="col-lg-9">
									<div class="form-group">
										Nome: <input type="text" class="form-control" name="name" value="<?php echo $result["Name"] ?>" required>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="form-group">
										CRM: <input type="text" class="form-control" name="crm" value="<?php echo $result["CRM"] ?>" required>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										Especialidade: <input type="text" class="form-control" name="specialty" value="<?php echo $result["Specialty"] ?>" required>
									</div>
								</div>
								<?php if ($_SESSION["idperfil"] == 1) { ?>
									<div class="col-lg-6">
										<div class="form-group">
											Hospital:
											<select name="hospitalId" class="form-control">
												<option value="0">Selecionar</option>
												<?php while ($item = $hospList->fetch_array()) { ?>
													<option value="<?php echo $item['Id'] ?>"><?php echo $item['Name'] ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="col-lg-12">
										<div class="form-group field_wrapper">
											<?php foreach ($single['HospitalIds'] as $indice => $valor) { ?>
												<div style="background-color: #dadada; padding: 3px 10px 3px 10px; border-radius: 25px; font-size: 14px; float: left; margin-right: 10px;">
													<div style="width: 100%;">
														<div style="margin: 4px 7px 4px 7px; float: left;">
															<?php $name = $hospital->hospitalSingle($valor);
															echo $name['Name']; ?>
														</div>
														<?php if (count($single['HospitalIds']) != 1) { ?>
															<button type="button" class="btn btn-sm" onclick="confirm(<?php echo $id ?>, <?php echo $valor ?>)" data-toggle="tooltip" title="Deletar" style="text-align: right;">
																<i class="fa fa-fw fa-times remove_button"></i>
															</button>
														<?php } ?>
													</div>
												</div>
											<?php } ?>
										</div>
									</div>
								<?php } ?>
							</div>
							<div class="row push">
								<div class="col-lg-12" style="text-align: right;">
									<a href="doctorList.php" class="btn btn-danger"> Cancelar </a>
									<button type="submit" class="btn btn-success"> Salvar </button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</main>
	</div>
</body>