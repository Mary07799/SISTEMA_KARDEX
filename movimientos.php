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

// Obtener productos disponibles para el usuario
$sql = "SELECT p.id, p.nombre, e.nombre AS empresa_nombre
        FROM productos p
        JOIN empresas e ON p.empresa_id = e.id
        WHERE e.usuario_id = $usuario_id
        ORDER BY p.nombre ASC";
$result = $mysqli->query($sql);

// Insertar nuevo movimiento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto_id = (int)$_POST['producto_id'];
    $tipo = $_POST['tipo'];
    $cantidad = (int)$_POST['cantidad'];
    $descripcion = $mysqli->real_escape_string($_POST['descripcion']);

    if ($cantidad <= 0) {
        $error = "La cantidad debe ser mayor que cero.";
    } else {
        $sql_stock = "SELECT stock FROM productos WHERE id = $producto_id";
        $res_stock = $mysqli->query($sql_stock);
        if ($res_stock && $res_stock->num_rows === 1) {
            $row = $res_stock->fetch_assoc();
            $stock_actual = $row['stock'];

            if ($tipo === 'salida' && $cantidad > $stock_actual) {
                $error = "No hay suficiente stock para esta salida.";
            } else {
                $nuevo_stock = ($tipo === 'entrada') ? ($stock_actual + $cantidad) : ($stock_actual - $cantidad);
                $mysqli->query("UPDATE productos SET stock = $nuevo_stock WHERE id = $producto_id");

                $sql_insert = "INSERT INTO movimientos (producto_id, tipo, cantidad, descripcion) VALUES ($producto_id, '$tipo', $cantidad, '$descripcion')";
                $mysqli->query($sql_insert);

                header("Location: movimientos.php");
                exit();
            }
        } else {
            $error = "Producto no encontrado.";
        }
    }
}

$sql_movimientos = "SELECT m.*, p.nombre AS producto_nombre, e.nombre AS empresa_nombre
                    FROM movimientos m
                    JOIN productos p ON m.producto_id = p.id
                    JOIN empresas e ON p.empresa_id = e.id
                    WHERE e.usuario_id = $usuario_id
                    ORDER BY m.fecha DESC";
$result_movimientos = $mysqli->query($sql_movimientos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Movimientos (Kardex) - Sistema Paccioli</title>
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
    padding-left: 240px; /* espacio para el menú lateral */
    padding-right: 20px;
    margin-top: 40px;
    display: flex;
    justify-content: center; /* centra horizontalmente el contenido */
    box-sizing: border-box;
}

/* === CONTENIDO INTERNO CENTRADO === */
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

/* === TÍTULOS Y FORMULARIO === */
h2 {
    color: #00e6e6;
    text-align: center;
    margin-bottom: 20px;
    font-size: 20px;
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

select, input, textarea {
    width: 100%;
    padding: 8px;
    margin-bottom: 12px;
    border-radius: 6px;
    border: 1px solid #00b8b8;
    background: rgba(255,255,255,0.1);
    color: #fff;
    font-size: 14px;
    resize: none;
}

select:focus, input:focus, textarea:focus {
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

/* === TABLA DE MOVIMIENTOS === */
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

/* === MENSAJES Y ENLACES === */
.error {
    color: #f66;
    margin-bottom: 15px;
    text-align: center;
}

p a {
    color: #00e6e6;
    text-decoration: none;
}

p a:hover {
    text-decoration: underline;
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
    <div class="logout" onclick="window.location='logout.php'">🚪 Cerrar sesión</div>
    <a href="menu.php">🏠 Inicio</a>
    <a href="empresas.php">🏢 Mis Empresas</a>
    <a href="productos.php">📦 Mis Productos</a>
    <a href="movimientos.php">📋 Movimientos</a>
    <a href="reportes.php">📑 Reportes</a>
    <a href="configuracion.php">⚙️ Configuración</a>
</div>

<div class="container">
    <div class="inner-content">
    <h2>Registrar Movimiento (Kardex)</h2>

    <?php if (!empty($error)) { echo "<div class='error'>$error</div>"; } ?>

    <form method="post" action="">
        <label>Producto:</label>
        <select name="producto_id" required>
            <option value="">Seleccione producto</option>
            <?php while ($prod = $result->fetch_assoc()): ?>
            <option value="<?php echo $prod['id']; ?>"><?php echo htmlspecialchars($prod['empresa_nombre'] . " - " . $prod['nombre']); ?></option>
            <?php endwhile; ?>
        </select>

        <label>Tipo de movimiento:</label>
        <select name="tipo" required>
            <option value="entrada">Entrada</option>
            <option value="salida">Salida</option>
        </select>

        <label>Cantidad:</label>
        <input type="number" name="cantidad" min="1" required>

        <label>Descripción (opcional):</label>
        <textarea name="descripcion" rows="2"></textarea>

        <button type="submit">Registrar Movimiento</button>
    </form>

    <h2>Historial de Movimientos</h2>

    <?php if ($result_movimientos && $result_movimientos->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Empresa</th>
                <th>Producto</th>
                <th>Tipo</th>
                <th>Cantidad</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($mov = $result_movimientos->fetch_assoc()): ?>
            <tr>
                <td><?php echo $mov['fecha']; ?></td>
                <td><?php echo htmlspecialchars($mov['empresa_nombre']); ?></td>
                <td><?php echo htmlspecialchars($mov['producto_nombre']); ?></td>
                <td><?php echo ucfirst($mov['tipo']); ?></td>
                <td><?php echo $mov['cantidad']; ?></td>
                <td><?php echo nl2br(htmlspecialchars($mov['descripcion'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No hay movimientos registrados.</p>
    <?php endif; ?>

    <p><a href="menu.php">Volver al menú</a></p>
</div>
</div>


<script>
// =================== EFECTO DE FONDO "CAJAS Y LUZ" ===================
const c = document.getElementById("canvas");
const ctx = c.getContext("2d");

function resize(){
  const box = c.getBoundingClientRect();
  c.width = box.width;
  c.height = box.height;
}
resize();
window.onresize = resize;

const light = {x:160, y:200};
const colors = ["#f5c156", "#e6616b", "#5cd3ad"];

function drawLight(){
  ctx.beginPath();
  ctx.arc(light.x, light.y, 1000, 0, 2*Math.PI);
  let g = ctx.createRadialGradient(light.x, light.y, 0, light.x, light.y, 1000);
  g.addColorStop(0, "#3b4654");
  g.addColorStop(1, "#2c343f");
  ctx.fillStyle = g;
  ctx.fill();

  ctx.beginPath();
  ctx.arc(light.x, light.y, 20, 0, 2*Math.PI);
  g = ctx.createRadialGradient(light.x, light.y, 0, light.x, light.y, 5);
  g.addColorStop(0, "#fff");
  g.addColorStop(1, "#3b4654");
  ctx.fillStyle = g;
  ctx.fill();
}

function Box(){
  this.half = Math.floor(Math.random()*50 + 1);
  this.x = Math.random()*c.width;
  this.y = Math.random()*c.height;
  this.r = Math.random()*Math.PI;
  this.color = colors[Math.floor(Math.random()*colors.length)];
  this.sl = 2000;

  this.dots = () => {
    const f = (Math.PI*2)/4;
    const sin = k => this.half*Math.sin(this.r + f*k);
    const cos = k => this.half*Math.cos(this.r + f*k);
    return [...Array(4)].map((_,k)=>({x:this.x+sin(k), y:this.y+cos(k)}));
  };
  this.rotate = () => {
    const s = (60-this.half)/20;
    this.r += s*0.002;
    this.x += s;
    this.y += s;
    if(this.y-this.half>c.height) this.y -= c.height+100;
    if(this.x-this.half>c.width)  this.x -= c.width+100;
  };
  this.shadow = () => {
    const d = this.dots();
    const pts = d.map(p=>{
      const ang = Math.atan2(light.y-p.y, light.x-p.x);
      return {
        start:p,
        end:{
          x:p.x+this.sl*Math.sin(-ang-Math.PI/2),
          y:p.y+this.sl*Math.cos(-ang-Math.PI/2)
        }
      };
    });
    for(let i=3;i>=0;i--){
      const n = i===3?0:i+1;
      ctx.beginPath();
      ctx.moveTo(pts[i].start.x, pts[i].start.y);
      ctx.lineTo(pts[n].start.x, pts[n].start.y);
      ctx.lineTo(pts[n].end.x,   pts[n].end.y);
      ctx.lineTo(pts[i].end.x,   pts[i].end.y);
      ctx.fillStyle = "#2c343f";
      ctx.fill();
    }
  };
  this.draw = () => {
    const d = this.dots();
    ctx.beginPath();
    ctx.moveTo(d[0].x,d[0].y);
    d.slice(1).forEach(p=>ctx.lineTo(p.x,p.y));
    ctx.fillStyle = this.color;
    ctx.fill();
  };
}
const boxes = [];
while(boxes.length<14) boxes.push(new Box());

function collide(b){
  for(let i=boxes.length-1;i>=0;i--){
    if(i!==b){
      const dx = (boxes[b].x+boxes[b].half)-(boxes[i].x+boxes[i].half);
      const dy = (boxes[b].y+boxes[b].half)-(boxes[i].y+boxes[i].half);
      const d  = Math.hypot(dx,dy);
      if(d<boxes[b].half+boxes[i].half){
        boxes[b].half = Math.max(1, boxes[b].half-1);
        boxes[i].half = Math.max(1, boxes[i].half-1);
      }
    }
  }
}
function animate(){
  ctx.clearRect(0,0,c.width,c.height);
  drawLight();
  boxes.forEach(b=>b.rotate());
  boxes.forEach(b=>b.shadow());
  boxes.forEach((b,i)=>{collide(i); b.draw();});
  requestAnimationFrame(animate);
}
animate();

c.onmousemove = e=>{
  light.x = e.offsetX ?? e.layerX;
  light.y = e.offsetY ?? e.layerY;
};
</script>

</body>
</html>
