<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'docente') {
    header("Location: login.php");
    exit();
}

$mysqli = new mysqli("localhost", "root", "Usu@rio_admin123", "c_paccioli");
if ($mysqli->connect_errno) {
    die("Error de conexión: " . $mysqli->connect_error);
}

$sql_empresas = "SELECT e.*, u.nombre AS estudiante_nombre 
                 FROM empresas e 
                 JOIN usuarios u ON e.usuario_id = u.id 
                 ORDER BY e.fecha_creacion DESC";
$result_empresas = $mysqli->query($sql_empresas);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['empresa_id'])) {
    $empresa_id = (int)$_POST['empresa_id'];
    $docente_id = $_SESSION['id'];
    $observacion = $mysqli->real_escape_string($_POST['observacion']);
    $calificacion = (int)$_POST['calificacion'];

    if ($calificacion < 0 || $calificacion > 100) {
        $error = "La calificación debe estar entre 0 y 100.";
    } else {
        $sql_check = "SELECT id FROM supervisiones WHERE empresa_id = $empresa_id AND docente_id = $docente_id";
        $res_check = $mysqli->query($sql_check);

        if ($res_check->num_rows > 0) {
            $row = $res_check->fetch_assoc();
            $id_supervision = $row['id'];
            $sql_update = "UPDATE supervisiones 
                           SET observacion='$observacion', calificacion=$calificacion, creado_en=NOW() 
                           WHERE id = $id_supervision";
            $mysqli->query($sql_update);
        } else {
            $sql_insert = "INSERT INTO supervisiones (empresa_id, docente_id, observacion, calificacion) 
                           VALUES ($empresa_id, $docente_id, '$observacion', $calificacion)";
            $mysqli->query($sql_insert);
        }
        header("Location: supervision.php");
        exit();
    }
}

$sql_supervisiones = "SELECT s.*, e.nombre AS empresa_nombre, u.nombre AS docente_nombre 
                      FROM supervisiones s
                      JOIN empresas e ON s.empresa_id = e.id
                      JOIN usuarios u ON s.docente_id = u.id
                      ORDER BY s.creado_en DESC";
$result_supervisiones = $mysqli->query($sql_supervisiones);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Supervisión - Sistema Paccioli</title>
<style>
    body {
        margin:0;
        font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
        color:#eee;
        background:#000;
        overflow-x:hidden;
    }
    canvas {
        position:fixed;
        top:0;left:0;
        width:100%;
        height:100%;
        z-index:-1;
    }
    .sidebar {
        position:fixed;
        left:0;top:0;
        width:220px;height:100%;
        background:rgba(0,0,0,0.85);
        padding-top:20px;
        display:flex;flex-direction:column;
        box-shadow:2px 0 5px rgba(0,0,0,0.5);
        z-index:100;
    }
    .sidebar h3 {
        text-align:center;
        color:#00e6e6;
        margin-bottom:15px;
        text-shadow:0 0 5px #00e6e6;
    }
    .logout {
        text-align:center;
        background:rgba(255,255,255,0.05);
        margin:0 20px 20px 20px;
        padding:10px;
        border-radius:6px;
        color:#ff6666;
        cursor:pointer;
        font-weight:bold;
        transition:0.3s;
    }
    .logout:hover {
        background:rgba(255,100,100,0.2);
    }
    .sidebar a {
        text-decoration:none;
        color:#eee;
        padding:12px 20px;
        display:block;
        transition:0.3s;
    }
    .sidebar a:hover {
        background:rgba(0,230,230,0.2);
        color:#00e6e6;
    }
    .container {
        margin-left:240px;
        padding:40px 20px;
        max-width:800px;
    }
    h2 {
        color:#00e6e6;
        text-align:center;
        margin-bottom:20px;
        text-shadow:0 0 5px #00e6e6;
    }
    form {
        background:rgba(255,255,255,0.05);
        padding:16px;
        border-radius:8px;
        margin-bottom:30px;
    }
    label {
        display:block;
        margin-bottom:5px;
        color:#00e6e6;
        font-weight:bold;
    }
    select,textarea,input {
        width:100%;
        padding:8px;
        margin-bottom:12px;
        border-radius:6px;
        border:1px solid #00b8b8;
        background:rgba(255,255,255,0.1);
        color:#fff;
        resize:none;
    }
    select option { background:#111; color:#fff; }
    button {
        background:#00e6e6;
        color:#000;
        padding:8px 16px;
        border:none;
        border-radius:6px;
        cursor:pointer;
        font-weight:bold;
        transition:0.3s;
    }
    button:hover {
        background:#00b8b8;
    }
    table {
        width:100%;
        border-collapse:collapse;
        background:rgba(255,255,255,0.05);
        border-radius:8px;
        overflow:hidden;
        font-size:14px;
    }
    th,td {
        padding:10px;
        text-align:left;
    }
    th {
        background:#004d4d;
        color:#00e6e6;
    }
    tr:nth-child(even) {
        background:rgba(255,255,255,0.05);
    }
    tr:hover {
        background:rgba(0,230,230,0.1);
    }
    .error { color:#f66; margin-bottom:15px; }
</style>
<script>
    function confirmarCierreSesion(e){
        e.preventDefault();
        if(confirm("¿Seguro que deseas cerrar sesión?")){
            window.location.href="cerrar_sesion.php";
        }
    }
</script>
</head>
<body>
<canvas id="canvas"></canvas>

<div class="sidebar">
    <h3>📊 Paccioli</h3>
    <div class="logout" onclick="confirmarCierreSesion(event)">🚪 Cerrar sesión</div>
    <a href="menu.php">🏠 Inicio</a>
    <a href="empresas.php">🏢 Mis Empresas</a>
    <a href="productos.php">📦 Mis Productos</a>
    <a href="movimientos.php">📋 Movimientos</a>
    <a href="reportes.php">📑 Reportes</a>
    <a href="supervision.php">✅ Supervisión</a>
    <a href="configuracion.php">⚙️ Configuración</a>
</div>

<div class="container">
    <h2>Supervisión y Calificaciones</h2>

    <?php if (!empty($error)) { echo "<div class='error'>$error</div>"; } ?>

    <form method="post" action="">
        <label>Empresa / Práctica:</label>
        <select name="empresa_id" required>
            <option value="">Seleccione empresa</option>
            <?php while ($empresa = $result_empresas->fetch_assoc()): ?>
            <option value="<?php echo $empresa['id']; ?>">
                <?php echo htmlspecialchars($empresa['nombre'] . " (Estudiante: " . $empresa['estudiante_nombre'] . ")"); ?>
            </option>
            <?php endwhile; ?>
        </select>

        <label>Observación:</label>
        <textarea name="observacion" rows="3"></textarea>

        <label>Calificación (0-100):</label>
        <input type="number" name="calificacion" min="0" max="100" required>

        <button type="submit">Guardar Supervisión</button>
    </form>

    <h2>Historial de Supervisiones</h2>

    <?php if ($result_supervisiones && $result_supervisiones->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Empresa</th><th>Docente</th><th>Observación</th><th>Calificación</th><th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($sup = $result_supervisiones->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($sup['empresa_nombre']); ?></td>
                <td><?php echo htmlspecialchars($sup['docente_nombre']); ?></td>
                <td><?php echo nl2br(htmlspecialchars($sup['observacion'])); ?></td>
                <td><?php echo $sup['calificacion']; ?></td>
                <td><?php echo $sup['creado_en']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No hay supervisiones registradas.</p>
    <?php endif; ?>
</div>

<script>
// ===== EFECTO DE FONDO =====
const c=document.getElementById("canvas");const ctx=c.getContext("2d");
function resize(){const box=c.getBoundingClientRect();c.width=box.width;c.height=box.height;}resize();window.onresize=resize;
const light={x:160,y:200};const colors=["#f5c156","#e6616b","#5cd3ad"];
function drawLight(){ctx.beginPath();ctx.arc(light.x,light.y,1000,0,2*Math.PI);
let g=ctx.createRadialGradient(light.x,light.y,0,light.x,light.y,1000);
g.addColorStop(0,"#3b4654");g.addColorStop(1,"#2c343f");ctx.fillStyle=g;ctx.fill();
ctx.beginPath();ctx.arc(light.x,light.y,20,0,2*Math.PI);
g=ctx.createRadialGradient(light.x,light.y,0,light.x,light.y,5);
g.addColorStop(0,"#fff");g.addColorStop(1,"#3b4654");ctx.fillStyle=g;ctx.fill();}
function Box(){this.half=Math.floor(Math.random()*50+1);this.x=Math.random()*c.width;this.y=Math.random()*c.height;
this.r=Math.random()*Math.PI;this.color=colors[Math.floor(Math.random()*colors.length)];this.sl=2000;
this.dots=()=>{const f=(Math.PI*2)/4;const sin=k=>this.half*Math.sin(this.r+f*k);
const cos=k=>this.half*Math.cos(this.r+f*k);return[...Array(4)].map((_,k)=>({x:this.x+sin(k),y:this.y+cos(k)}));};
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
const dy=(boxes[b].y+boxes[b].half)-(boxes[i].y+boxes[i].half);const d=Math.hypot(dx,dy);
if(d<boxes[b].half+boxes[i].half){boxes[b].half=Math.max(1,boxes[b].half-1);boxes[i].half=Math.max(1,boxes[i].half-1);}}}}
function animate(){ctx.clearRect(0,0,c.width,c.height);drawLight();
boxes.forEach(b=>b.rotate());boxes.forEach(b=>b.shadow());boxes.forEach((b,i)=>{collide(i);b.draw();});
requestAnimationFrame(animate);}animate();
c.onmousemove=e=>{light.x=e.offsetX??e.layerX;light.y=e.offsetY??e.layerY;};
</script>
</body>
</html>
