<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$mysqli = new mysqli("localhost", "root", "Usu@rio_admin123", "c_paccioli");
if ($mysqli->connect_errno) {
    die("Error de conexión: " . $mysqli->connect_error);
}

$usuario_id = $_SESSION['id'];

// Crear nueva empresa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nombre_empresa'])) {
    $nombre_empresa = $mysqli->real_escape_string($_POST['nombre_empresa']);
    $descripcion = $mysqli->real_escape_string($_POST['descripcion']);
    $sql = "INSERT INTO empresas (usuario_id, nombre, descripcion) VALUES ($usuario_id, '$nombre_empresa', '$descripcion')";
    $mysqli->query($sql);
    header("Location: empresas.php");
    exit();
}

// Obtener empresas del usuario
$sql = "SELECT * FROM empresas WHERE usuario_id = $usuario_id ORDER BY fecha_creacion DESC";
$result = $mysqli->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Empresas - Sistema Paccioli</title>
<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #eee;
    overflow-x: hidden;
}
canvas {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
}
/* === MENÚ LATERAL === */
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 220px;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    padding-top: 20px;
    display: flex;
    flex-direction: column;
    box-shadow: 2px 0 5px rgba(0,0,0,0.5);
    z-index: 100;
}
.sidebar h3 {
    text-align: center;
    color: #00e6e6;
    margin-bottom: 10px;
}
.logout {
    text-align: center;
    background: rgba(255,255,255,0.05);
    margin: 0 20px 20px 20px;
    padding: 10px;
    border-radius: 6px;
    color: #ff6666;
    cursor: pointer;
    font-weight: bold;
    transition: 0.3s;
}
.logout:hover {
    background: rgba(255,100,100,0.2);
}
.sidebar a {
    text-decoration: none;
    color: #eee;
    padding: 12px 20px;
    display: block;
    transition: 0.3s;
}
.sidebar a:hover {
    background: rgba(0,230,230,0.2);
    color: #00e6e6;
}

/* === CONTENEDOR PRINCIPAL === */
.container {
    padding-left: 240px;
    padding-right: 20px;
    margin-top: 40px;
    display: flex;
    justify-content: center;
    box-sizing: border-box;
}
.inner-content {
    width: 100%;
    max-width: 600px;
    padding: 20px;
    background: rgba(0,0,0,0.7);
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    display: flex;
    flex-direction: column;
    align-items: center;
}
h2 {
    color: #00e6e6;
    text-align: center;
    margin-bottom: 20px;
}
form {
    background: rgba(255,255,255,0.05);
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 30px;
    width: 100%;
    box-sizing: border-box;
}
label {
    display: block;
    margin-bottom: 5px;
    color: #00e6e6;
    font-weight: bold;
}
input, textarea {
    width: 100%;
    padding: 8px;
    margin-bottom: 12px;
    border-radius: 6px;
    border: 1px solid #00b8b8;
    background: rgba(255,255,255,0.1);
    color: #fff;
    resize: none;
}
input:focus, textarea:focus {
    outline: none;
    border-color: #00e6e6;
    box-shadow: 0 0 5px #00e6e6;
}
button {
    background: #00e6e6;
    color: #000;
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    font-size: 14px;
    transition: 0.3s;
}
button:hover {
    background: #00b8b8;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: rgba(255,255,255,0.05);
    border-radius: 8px;
    overflow: hidden;
    font-size: 14px;
}
th, td {
    padding: 10px;
    text-align: left;
}
th {
    background: #004d4d;
    color: #00e6e6;
}
tr:nth-child(even) {
    background: rgba(255,255,255,0.05);
}
tr:hover {
    background: rgba(0,230,230,0.1);
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

</style>
</head>
<body>
<canvas id="canvas"></canvas>

<div class="sidebar">
    <h3>📊 Paccioli</h3>
    <div class="logout" onclick="window.location='cerrar_sesion.php'">🚪 Cerrar sesión</div>
    <a href="menu.php">🏠 Inicio</a>
    <a href="empresas.php">🏢 Mis Empresas</a>
    <a href="productos.php">📦 Mis Productos</a>
    <a href="movimientos.php">📋 Movimientos</a>
    <a href="reportes.php">📑 Reportes</a>
    <a href="configuracion.php">⚙️ Configuración</a>
</div>

<div class="container">
    <div class="inner-content">
        <h2>Mis Empresas</h2>
        <form method="post" action="">
            <label>Nombre de la empresa:</label>
            <input type="text" name="nombre_empresa" required>
            <label>Descripción:</label>
            <textarea name="descripcion" rows="3"></textarea>
            <button type="submit">Crear Empresa</button>
        </form>

        <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <thead>
                <tr><th>Nombre</th><th>Descripción</th><th>Fecha de creación</th></tr>
            </thead>
            <tbody>
                <?php while ($empresa = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($empresa['nombre']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($empresa['descripcion'])); ?></td>
                    <td><?php echo $empresa['fecha_creacion']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>No tienes empresas registradas.</p>
        <?php endif; ?>
    </div>
</div>

<script>
// ========== EFECTO DE FONDO ==========
const c = document.getElementById("canvas");
const ctx = c.getContext("2d");
function resize(){const box=c.getBoundingClientRect();c.width=box.width;c.height=box.height;}resize();
window.onresize=resize;
const light={x:160,y:200};const colors=["#f5c156","#e6616b","#5cd3ad"];
function drawLight(){ctx.beginPath();ctx.arc(light.x,light.y,1000,0,2*Math.PI);
let g=ctx.createRadialGradient(light.x,light.y,0,light.x,light.y,1000);
g.addColorStop(0,"#3b4654");g.addColorStop(1,"#2c343f");ctx.fillStyle=g;ctx.fill();
ctx.beginPath();ctx.arc(light.x,light.y,20,0,2*Math.PI);
g=ctx.createRadialGradient(light.x,light.y,0,light.x,light.y,5);
g.addColorStop(0,"#fff");g.addColorStop(1,"#3b4654");ctx.fillStyle=g;ctx.fill();}
function Box(){this.half=Math.floor(Math.random()*50+1);this.x=Math.random()*c.width;
this.y=Math.random()*c.height;this.r=Math.random()*Math.PI;this.color=colors[Math.floor(Math.random()*colors.length)];
this.sl=2000;this.dots=()=>{const f=(Math.PI*2)/4;
const sin=k=>this.half*Math.sin(this.r+f*k);const cos=k=>this.half*Math.cos(this.r+f*k);
return[...Array(4)].map((_,k)=>({x:this.x+sin(k),y:this.y+cos(k)}));};
this.rotate=()=>{const s=(60-this.half)/20;this.r+=s*0.002;this.x+=s;this.y+=s;
if(this.y-this.half>c.height)this.y-=c.height+100;if(this.x-this.half>c.width)this.x-=c.width+100;};
this.shadow=()=>{const d=this.dots();const pts=d.map(p=>{const ang=Math.atan2(light.y-p.y,light.x-p.x);
return{start:p,end:{x:p.x+this.sl*Math.sin(-ang-Math.PI/2),y:p.y+this.sl*Math.cos(-ang-Math.PI/2)}}});
for(let i=3;i>=0;i--){const n=i===3?0:i+1;ctx.beginPath();ctx.moveTo(pts[i].start.x,pts[i].start.y);
ctx.lineTo(pts[n].start.x,pts[n].start.y);ctx.lineTo(pts[n].end.x,pts[n].end.y);
ctx.lineTo(pts[i].end.x,pts[i].end.y);ctx.fillStyle="#2c343f";ctx.fill();}};
this.draw=()=>{const d=this.dots();ctx.beginPath();ctx.moveTo(d[0].x,d[0].y);
d.slice(1).forEach(p=>ctx.lineTo(p.x,p.y));ctx.fillStyle=this.color;ctx.fill();}}
const boxes=[];while(boxes.length<14)boxes.push(new Box());
function collide(b){for(let i=boxes.length-1;i>=0;i--){if(i!==b){const dx=(boxes[b].x+boxes[b].half)-(boxes[i].x+boxes[i].half);
const dy=(boxes[b].y+boxes[b].half)-(boxes[i].y+boxes[i].half);
const d=Math.hypot(dx,dy);if(d<boxes[b].half+boxes[i].half){boxes[b].half=Math.max(1,boxes[b].half-1);
boxes[i].half=Math.max(1,boxes[i].half-1);}}}}
function animate(){ctx.clearRect(0,0,c.width,c.height);drawLight();
boxes.forEach(b=>b.rotate());boxes.forEach(b=>b.shadow());
boxes.forEach((b,i)=>{collide(i);b.draw();});requestAnimationFrame(animate);}
animate();
c.onmousemove=e=>{light.x=e.offsetX??e.layerX;light.y=e.offsetY??e.layerY;};
</script>
</body>
</html>
