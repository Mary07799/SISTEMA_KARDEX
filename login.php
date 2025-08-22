<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header("Location: menu.php");
    exit();
}

// ✅ Conexión
$conn = new mysqli("localhost", "root", "Usu@rio_admin123", "c_paccioli");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$error = "";
$registro_exitoso = "";

// ✅ Registro si es nuevo usuario
if (isset($_POST['registrar'])) {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $usuario = $conn->real_escape_string($_POST['usuario']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol = $conn->real_escape_string($_POST['rol']);

    $check = $conn->query("SELECT id FROM usuarios WHERE usuario='$usuario'");
    if ($check->num_rows > 0) {
        $error = "El usuario ya existe.";
    } else {
        $sql = "INSERT INTO usuarios (nombre, usuario, password, rol) VALUES ('$nombre','$usuario','$password','$rol')";
        if ($conn->query($sql)) {
            $registro_exitoso = "Registro exitoso, ahora puede iniciar sesión.";
        } else {
            $error = "Error al registrar.";
        }
    }
}

// ✅ Login
if (isset($_POST['entrar'])) {
    $usuario = $conn->real_escape_string($_POST['usuario']);
    $password = $_POST['password'];
    $rol = $conn->real_escape_string($_POST['rol']);

    $sql = "SELECT * FROM usuarios WHERE usuario='$usuario' AND rol='$rol' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // ✅ AHORA SE GUARDA TAMBIÉN EL ID
            $_SESSION['id'] = $user['id'];
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['rol'] = $user['rol'];
            header("Location: menu.php");
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "Usuario o rol incorrecto.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Iniciar Sesión</title>
  <style>
      /* Reset básico */
      *{margin:0;padding:0;box-sizing:border-box;font-family:"Segoe UI",sans-serif}

      /* ===== FONDO ===== */
      body{
        background:#222;
        height:100vh;
        overflow:hidden;
      }



      select {
    width: 100%;
    padding: 8px;
    margin-bottom: 12px;
    border-radius: 6px;
    border: 1px solid #00b8b8;
    background: rgba(0, 0, 0, 0.7); /* ✅ Fondo más oscuro para mayor contraste */
    color: #00e6e6; /* ✅ Texto turquesa */
    font-weight: bold; /* ✅ Texto destacado */
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;

    /* ✅ Flecha personalizada */
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%2300e6e6"><path d="M7 10l5 5 5-5z"/></svg>');
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 16px;
    padding-right: 30px; /* espacio para flecha */
}

select option {
    background: #1a1a1a; /* ✅ Fondo oscuro en lista desplegable */
    color: #fff; /* ✅ Texto blanco */
}

select option:checked {
    background: #00e6e6; /* ✅ Opción seleccionada en turquesa */
    color: #000; /* ✅ Texto negro para que contraste */
}

select:hover {
    border-color: #00e6e6;
    box-shadow: 0 0 5px #00e6e6;
}
      .background-shapes{
        position:fixed;
        inset:0;
        overflow:hidden;
        pointer-events:none;
        z-index:1;
      }
      .square,.circle,.triangle{
        position:absolute;
        top:50%;left:50%;
        transform:translate(-50%,-50%);
        width:50px;height:50px;
      }
      .square{background:linear-gradient(#303030,#757575)}
      .circle{background:#1cd99d;border-radius:50%}
      .triangle{
        background:#f5f5f5;
        clip-path:polygon(50% 0%,0% 100%,100% 100%);
        -webkit-clip-path:polygon(50% 0%,0% 100%,100% 100%);
      }

      /* ===== LOGIN ===== */
      .login-container{
        position:relative;
        z-index:10;
        height:100vh;
        display:flex;
        align-items:center;
        justify-content:center;
      }
      .login-card{
        width:320px;
        padding:2.5rem 2rem;
        background:rgba(255,255,255,0.08);
        border:1px solid rgba(255,255,255,0.15);
        backdrop-filter:blur(8px);
        border-radius:1rem;
        color:#fff;
        display:flex;
        flex-direction:column;
        gap:1rem;
      }
      .login-card h2{
        text-align:center;
        margin-bottom:.5rem;
        font-weight:600;
      }
      .login-card label{
        display:flex;
        flex-direction:column;
        font-size:.9rem;
        gap:.3rem;
      }
      .login-card input, .login-card select{
        padding:.6rem .8rem;
        border:none;
        border-radius:.5rem;
        background:rgba(255,255,255,0.15);
        color:#fff;
      }
      .login-card input::placeholder{color:#ddd}
      .login-card button{
        padding:.7rem;
        margin-top:.5rem;
        border:none;
        border-radius:.5rem;
        background:#1cd99d;
        color:#000;
        font-weight:600;
        cursor:pointer;
        transition:opacity .2s;
      }
      .login-card button:hover{opacity:.8}
      .error{color:#ff4b4b;text-align:center;font-size:.85rem}
      .success{color:#1cd99d;text-align:center;font-size:.85rem}
      .toggle{color:#1cd99d;cursor:pointer;text-align:center;margin-top:.5rem;font-size:.8rem}
      .toggle:hover{text-decoration:underline}
  </style>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
</head>
<body>
  <!-- Fondo animado -->
  <div class="background-shapes">
      <div class="square"></div>
      <div class="circle"></div>
      <div class="triangle"></div>
  </div>

  <main class="login-container">
    <form class="login-card" method="POST" id="loginForm">
      <h2 id="formTitle">Iniciar sesión</h2>

      <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
      <?php if (!empty($registro_exitoso)) echo "<p class='success'>$registro_exitoso</p>"; ?>

      <div id="nombreField" style="display:none">
        <label>Nombre completo
          <input type="text" name="nombre" placeholder="Ej: Juan Pérez">
        </label>
      </div>

      <label>Usuario
        <input type="text" name="usuario" placeholder="Ingrese el usuario" required>
      </label>

      <label>Contraseña
        <input type="password" name="password" placeholder="Ingrese la contraseña" required>
      </label>

      <label>Rol
        <select name="rol" required>
            <option value="">Seleccione rol</option>
            <option value="estudiante">Estudiante</option>
            <option value="docente">Docente</option>
        </select>
      </label>

      <button type="submit" name="entrar" id="entrarBtn">Entrar</button>
      <button type="submit" name="registrar" id="registrarBtn" style="display:none">Registrarme</button>

      <p class="toggle" id="toggleLink">¿Nuevo aquí? Regístrate</p>
    </form>
  </main>

  <script>
    // Fondo animado
    function animateBackground() {
      anime({
        targets: '.square, .circle, .triangle',
        translateX: () => anime.random(-500, 500),
        translateY: () => anime.random(-300, 300),
        rotate:     () => anime.random(0, 360),
        scale:      () => anime.random(0.2, 2),
        duration: 2500,
        easing: 'easeInOutQuad',
        complete: animateBackground
      });
    }
    animateBackground();

    // Toggle entre login y registro
    const toggleLink=document.getElementById("toggleLink");
    const nombreField=document.getElementById("nombreField");
    const entrarBtn=document.getElementById("entrarBtn");
    const registrarBtn=document.getElementById("registrarBtn");
    const formTitle=document.getElementById("formTitle");

    toggleLink.addEventListener("click",()=>{
      if(nombreField.style.display==="none"){
        nombreField.style.display="block";
        entrarBtn.style.display="none";
        registrarBtn.style.display="block";
        formTitle.textContent="Registro nuevo";
        toggleLink.textContent="¿Ya tienes cuenta? Inicia sesión";
      }else{
        nombreField.style.display="none";
        entrarBtn.style.display="block";
        registrarBtn.style.display="none";
        formTitle.textContent="Iniciar sesión";
        toggleLink.textContent="¿Nuevo aquí? Regístrate";
      }
    });
  </script>
</body>
</html>
