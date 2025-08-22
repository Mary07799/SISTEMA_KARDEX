<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Menú Principal</title>
<style>
    body {
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #eee;
        background: #000;
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

    /* ===== MENÚ LATERAL ===== */
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        width: 220px;
        height: 100%;
        background: rgba(0, 0, 0, 0.85);
        padding-top: 20px;
        display: flex;
        flex-direction: column;
        box-shadow: 2px 0 5px rgba(0,0,0,0.5);
        z-index: 100;
    }
    .sidebar h3 {
        text-align: center;
        color: #00e6e6;
        margin-bottom: 15px;
        text-shadow: 0 0 5px #00e6e6;
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

    /* ===== CONTENIDO PRINCIPAL ===== */
    .container {
        margin-left: 240px;
        padding: 40px 20px;
        text-align: center;
    }
    h1 {
        font-size: 2.5em;
        margin-bottom: 40px;
        text-shadow: 0 0 10px #fff, 0 0 20px #00e6e6, 0 0 30px #00e6e6;
    }
    .menu-links {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 20px;
    }
    .menu-links a {
        color: #00e6e6;
        text-decoration: none;
        font-weight: bold;
        border: 2px solid #00e6e6;
        padding: 12px 25px;
        border-radius: 6px;
        transition: 0.3s;
        font-size: 16px;
        background: rgba(255,255,255,0.05);
    }
    .menu-links a:hover {
        background: #00e6e6;
        color: #000;
        box-shadow: 0 0 10px #00e6e6, 0 0 20px #00e6e6, 0 0 30px #00e6e6;
    }
</style>
<script>
    function confirmarCierreSesion(e) {
        e.preventDefault();
        if (confirm("¿Seguro que deseas cerrar sesión?")) {
            window.location.href = "cerrar_sesion.php";
        }
    }
</script>
</head>
<body>
<canvas id="canvas"></canvas>

<!-- ===== MENÚ LATERAL ===== -->
<div class="sidebar">
    <h3>📊 Paccioli</h3>
    <div class="logout" onclick="confirmarCierreSesion(event)">🚪 Cerrar sesión</div>
    <a href="menu.php">🏠 Inicio</a>
    <a href="empresas.php">🏢 Mis Empresas</a>
    <a href="productos.php">📦 Mis Productos</a>
    <a href="movimientos.php">📋 Movimientos</a>
    <a href="reportes.php">📑 Reportes</a>
    <a href="configuracion.php">⚙️ Configuración</a>
</div>

<!-- ===== CONTENIDO PRINCIPAL ===== -->
<div class="container">
    <h1>Bienvenid@, <?php echo htmlspecialchars($_SESSION['usuario']); ?>!</h1>
    <div class="menu-links">
        <a href="empresas.php">Empresas</a>
        <a href="productos.php">Inventario</a>
        <a href="movimientos.php">Movimientos (Kardex)</a>
        <?php if($_SESSION['rol'] === 'docente'): ?>
            <a href="supervision.php">Supervisión</a>
        <?php endif; ?>
        <a href="reportes.php">Reportes</a>
    </div>
</div>

<script>
// ===== EFECTO DE FONDO =====
const c = document.getElementById("canvas");
const ctx = c.getContext("2d");

function resize() {
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
    }
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
