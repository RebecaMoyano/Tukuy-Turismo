<?php
$is_logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$user_name_from_session = $is_logged_in ? htmlspecialchars($_SESSION['nombre_usuario']) : 'Usuario'; 
?>

<nav class="navbar navbar-expand-lg bg-body-tertiary justify-content-center">
    <div class="container-fluid">
        <a class="navbar-brand" href="/index.php">
            <img src="/assets/img/tukuy-logo.png" id="img-logo" alt="logo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor02" 
            aria-controls="navbarColor02" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarColor02">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link me-4" href="/index.php">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link me-4" href="/index.php#catalogo">Tours</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/contact.php">Contacto</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/about-us.php">Conócenos</a>
                </li> 
            </ul>
            <?php if (!$is_logged_in): //si no esta logueado muestro los botones ?> 
                <ul class="navbar-nav" id="guestNavItems">
                    <li class="nav-item">
                        <a href="/login.php?action=register" id="btnCuenta" style="text-decoration:none;" class="nav-link">Crear cuenta</a>
                    </li>
                    <li class="nav-item">
                        <a href="/login.php?action=login" id="btnSesion" style="text-decoration:none;" class="nav-link">Iniciar sesión</a>
                    </li>
                </ul>
            <?php else: // si esta logueado, quito los botones y muestro el select ?>
                <ul class="navbar-nav" id="loggedInNavItems" style="position: relative;">
                    <li class="nav-item" style="position: relative;">
                        <span class="nav-link" id="userDisplayName" style="cursor: pointer; color: white;">
                            Hola, <span id="userNameDisplay"><?php echo htmlspecialchars($user_name_from_session); ?></span> <i class="fas fa-caret-down"></i>
                        </span>
                        <select id="userOptionsSelect" class="form-select"
                                style="position: absolute; top: 100%; left: 0; width: 100px; z-index:2;">
                            <option value="" style="z-index:3;">Opciones de Usuario</option>
                            <option value="/user-reservas.php">Mis Reservas</option>
                            <option value="/logout.php">Cerrar Sesión</option>
                        </select>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>