<?php
session_start();
require('class/config.class.php');
require('inc/_global/views/head_start.php');
require('inc/_global/views/head_end.php');
require('class/menu.class.php');
require('class/module.class.php');
require('class/user.class.php');
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['login'])) {
    header('location:login.php');
}

$user = new user;
$config = new config;
$menu = new menu;
$id = $_GET["id"];
$result = $user->userSingle($id);
$pasta = "assets/images/";

if ($id == 0) {
    header('location:home.php');
}

if ($_POST) {
    $_POST['id'] = $id;

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
        if ($_POST['login'] == '' || $_POST['email'] == '') {
            $config->alert("Preencher informações de usuário.");
        } else if ($_POST['senha'] == $_POST['confirmar']) {
            if ($user->editUser($_POST) == 0) {
                $config->alert("Login e/ou Email já cadastrados.");
            }
            $result = $user->userSingle($id);
        } else {
            $config->alert("Senhas não correspondentes.");
        }
    }
}
?>

<body>
    <div id="divLoading" class="loading" style="display: none">
        <div class="loader"></div>
    </div>

    <div id="page-container" class="sidebar-o sidebar-dark enable-page-overlay side-scroll page-header-dark">
        <nav id="sidebar" aria-label="Main Navigation">
            <?php $menu->getMenu(); ?>
        </nav>

        <header id="page-header" class="dark header-dark">
            <div class="content-header">
                <div class="d-flex align-items-center">
                    <?php if ($_SESSION['idperfil'] == 4) {
                        $module = new module;
                        $query = $module->select(); ?>
                        <button type="button" class="btn btn-sm btn-dual" style="right: 110px; position: absolute;" data-toggle="modal" data-target="#modalModule">
                            <span class="d-none d-sm-inline-block">Consultar</span>
                        </button>
                    <?php } ?>
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
                        <h1 class="flex-sm-fill h3 my-2">Perfil</h1>
                    </div>
                    <div class="block-content" style="padding-top: 0px;">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row push">
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
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        Login: <input type="text" class="form-control" name="login" value="<?php echo $result["Username"] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        Email: <input type="email" class="form-control" name="email" value="<?php echo $result["Email"] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        Senha: <input type="password" class="form-control" name="senha">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        Confirmar Senha: <input type="password" class="form-control" name="confirmar">
                                    </div>
                                </div>
                                <div class="col-lg-12" style="text-align: right;">
                                    <a href="home.php" class="btn btn-danger"> Cancelar </a>
                                    <button type="submit" class="btn btn-success" name="submit"> Salvar </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

<?php if ($_SESSION['idperfil'] == 4) { ?>
    <div class="modal" id="modalModule" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenter" style="display: none;" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="block block-themed block-transparent mb-0">
                    <div class="block-header bg-primary-dark">
                        <h3 class="block-title">Acidentes nos últimos 10 minutos</h3>
                        <div class="block-options">
                            <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
                                <i class="fa fa-fw fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="block-content font-size-sm">
                        <?php foreach ($query as $result) {
                            foreach ($result as $indice => $resultado) { ?>
                                <div class="col-lg-12" style="font-size: 18px;">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        Paciente: <a href="patient.php?id=<?php echo $resultado["Id"] ?>"><?php echo $resultado["Name"] ?><a>
                                    </div>
                                </div>
                                <div class="col-lg-12" style="font-size: 18px;" style="margin-bottom: 0;">
                                    <div class="form-group">
                                        Hora: <?php echo date("d/m/Y H:i:s", strtotime($resultado["Time"])); ?>
                                    </div>
                                </div>
                                <?php if ((count($result) - 1) != $indice) { ?>
                                    <hr>
                            <?php }
                            } ?>
                        <?php } ?>
                    </div>
                    <div class="block-content block-content-full text-right border-top">
                        <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>