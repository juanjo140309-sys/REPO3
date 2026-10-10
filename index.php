<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

function h(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$mensaje = '';
$error = '';
$editar = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $destinatario = trim((string)($_POST['destinatario'] ?? ''));
    $descripcion = trim((string)($_POST['descripcion'] ?? ''));
    $direccion = trim((string)($_POST['direccion'] ?? ''));
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($accion === 'eliminar') {
        if (!$id) {
            $error = 'El envío seleccionado no es válido.';
        } else {
            $stmt = $pdo->prepare('DELETE FROM envios WHERE id = ?');
            $stmt->execute([$id]);
            $mensaje = $stmt->rowCount() ? 'Envío eliminado correctamente.' : 'No se encontró ese envío.';
        }
    } elseif (in_array($accion, ['crear', 'actualizar'], true)) {
        if ($destinatario === '' || $descripcion === '' || $direccion === '') {
            $error = 'Completa todos los campos antes de guardar.';
        } elseif (mb_strlen($destinatario) > 150 || mb_strlen($direccion) > 255) {
            $error = 'El destinatario admite hasta 150 caracteres y la dirección hasta 255.';
        } elseif ($accion === 'crear') {
            $stmt = $pdo->prepare('INSERT INTO envios (destinatario, descripcion, direccion) VALUES (?, ?, ?)');
            $stmt->execute([$destinatario, $descripcion, $direccion]);
            $mensaje = 'Envío registrado correctamente.';
        } elseif (!$id) {
            $error = 'El identificador del envío no es válido.';
        } else {
            $stmt = $pdo->prepare('UPDATE envios SET destinatario = ?, descripcion = ?, direccion = ? WHERE id = ?');
            $stmt->execute([$destinatario, $descripcion, $direccion, $id]);
            $mensaje = $stmt->rowCount() ? 'Envío actualizado correctamente.' : 'No hubo cambios o el envío no existe.';
        }
    }
}

if (isset($_GET['editar'])) {
    $idEditar = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);
    if ($idEditar) {
        $stmt = $pdo->prepare('SELECT * FROM envios WHERE id = ?');
        $stmt->execute([$idEditar]);
        $editar = $stmt->fetch() ?: null;
        if (!$editar) $error = 'No se encontró el envío que deseas editar.';
    }
}

$q = trim((string)($_GET['q'] ?? ''));
if ($q !== '') {
    $stmt = $pdo->prepare('SELECT * FROM envios WHERE destinatario LIKE ? OR descripcion LIKE ? OR direccion LIKE ? ORDER BY id DESC');
    $like = '%' . $q . '%';
    $stmt->execute([$like, $like, $like]);
    $envios = $stmt->fetchAll();
} else {
    $envios = $pdo->query('SELECT * FROM envios ORDER BY id DESC')->fetchAll();
}
$total = (int)$pdo->query('SELECT COUNT(*) FROM envios')->fetchColumn();
$valDest = (string)($editar['destinatario'] ?? '');
$valDesc = (string)($editar['descripcion'] ?? '');
$valDir = (string)($editar['direccion'] ?? '');
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gestión de Envíos</title>
<style>
:root{--azul:#174ea6;--azul2:#2563eb;--fondo:#f3f6fb;--texto:#172033;--borde:#dce3ef;--verde:#16845b;--rojo:#c62828}
*{box-sizing:border-box}body{margin:0;background:var(--fondo);color:var(--texto);font:16px/1.5 system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
header{background:linear-gradient(120deg,#123875,#2878d0);color:white;padding:28px 18px}
.cabecera,.contenedor{max-width:1150px;margin:auto}.cabecera h1{margin:0;font-size:clamp(1.6rem,4vw,2.2rem)}.cabecera p{margin:5px 0 0;color:#e5efff}
.contenedor{padding:24px 16px 40px}.resumen{display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:18px}
.pastilla{background:white;border:1px solid var(--borde);padding:10px 14px;border-radius:12px}
.columnas{display:grid;grid-template-columns:minmax(260px,350px) minmax(0,1fr);gap:20px;align-items:start}
.panel{background:white;border:1px solid var(--borde);border-radius:16px;padding:20px;box-shadow:0 5px 20px #1d35570a}
h2{font-size:1.15rem;margin:0 0 16px}label{display:block;font-weight:650;margin:13px 0 6px}
input,textarea{font:inherit;width:100%;padding:11px 12px;border:1px solid #c8d2e2;border-radius:9px;background:white;color:var(--texto)}
input:focus,textarea:focus{outline:3px solid #2563eb2b;border-color:var(--azul2)}textarea{min-height:105px;resize:vertical}
button,.boton{display:inline-block;border:0;border-radius:9px;padding:10px 14px;font:inherit;font-weight:650;cursor:pointer;text-decoration:none;text-align:center}
.primario{background:var(--azul2);color:white}.secundario{background:#e9eef7;color:#233653}.peligro{background:#fee9e7;color:#9b1c1c}
.acciones{display:flex;gap:7px;flex-wrap:wrap}.acciones form{margin:0}.alerta{padding:12px 14px;border-radius:10px;margin-bottom:16px}.exito{background:#e4f6ed;color:#11623f}.error{background:#ffeded;color:#9b1c1c}
.busqueda{display:flex;gap:8px;margin-bottom:14px}.busqueda input{min-width:0}.tabla-contenedor{overflow-x:auto}table{border-collapse:collapse;width:100%;min-width:620px}th,td{text-align:left;padding:12px 10px;border-bottom:1px solid #e8edf5;vertical-align:top}th{font-size:.85rem;text-transform:uppercase;letter-spacing:.03em;color:#52617a;background:#f8faff}td{overflow-wrap:anywhere}.id{font-weight:750;color:var(--azul)}.vacio{text-align:center;padding:28px;color:#627087}.fecha{font-size:.82rem;color:#66758c}
@media(max-width:800px){.columnas{grid-template-columns:1fr}.panel{padding:16px}.contenedor{padding:16px 10px 30px}.busqueda{flex-wrap:wrap}.busqueda input{flex:1 1 180px}}
</style>
</head>
<body>
<header><div class="cabecera"><h1>Gestión de Envíos</h1><p>Registra, consulta, edita y elimina los datos de tus envíos.</p></div></header>
<main class="contenedor">
<div class="resumen"><div class="pastilla">📦 Envíos registrados: <strong><?= $total ?></strong></div><div class="fecha">Los datos se guardan en MySQL.</div></div>
<?php if ($mensaje !== ''): ?><div class="alerta exito" role="status"><?= h($mensaje) ?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="alerta error" role="alert"><?= h($error) ?></div><?php endif; ?>
<div class="columnas">
<section class="panel">
<h2><?= $editar ? 'Editar envío #' . (int)$editar['id'] : 'Registrar un envío' ?></h2>
<form method="post" action="index.php">
<input type="hidden" name="accion" value="<?= $editar ? 'actualizar' : 'crear' ?>">
<?php if ($editar): ?><input type="hidden" name="id" value="<?= (int)$editar['id'] ?>"><?php endif; ?>
<label for="destinatario">Destinatario</label>
<input id="destinatario" name="destinatario" maxlength="150" required value="<?= h($valDest) ?>" placeholder="Nombre de quien recibe">
<label for="descripcion">Descripción</label>
<textarea id="descripcion" name="descripcion" required placeholder="Describe el contenido del envío"><?= h($valDesc) ?></textarea>
<label for="direccion">Dirección de entrega</label>
<input id="direccion" name="direccion" maxlength="255" required value="<?= h($valDir) ?>" placeholder="Calle, número, barrio, ciudad">
<div class="acciones" style="margin-top:18px">
<button class="primario" type="submit"><?= $editar ? 'Guardar cambios' : 'Registrar envío' ?></button>
<?php if ($editar): ?><a class="boton secundario" href="index.php">Cancelar</a><?php else: ?><button class="secundario" type="reset">Limpiar</button><?php endif; ?>
</div>
</form>
</section>
<section class="panel">
<h2>Envíos registrados</h2>
<form class="busqueda" method="get" action="index.php">
<input name="q" value="<?= h($q) ?>" aria-label="Buscar envíos" placeholder="Buscar por destinatario, descripción o dirección">
<button class="primario" type="submit">Buscar</button>
<?php if ($q !== ''): ?><a class="boton secundario" href="index.php">Ver todos</a><?php endif; ?>
</form>
<div class="tabla-contenedor">
<table>
<thead><tr><th>ID</th><th>Información del envío</th><th>Acciones</th></tr></thead>
<tbody>
<?php if (!$envios): ?><tr><td colspan="3" class="vacio">No hay envíos para mostrar.</td></tr>
<?php else: foreach ($envios as $envio): ?>
<tr>
<td class="id">#<?= (int)$envio['id'] ?></td>
<td><strong><?= h($envio['destinatario']) ?></strong><br><span><?= nl2br(h($envio['descripcion'])) ?></span><br><span>📍 <?= h($envio['direccion']) ?></span><br><span class="fecha">Creado: <?= h($envio['fecha_creacion']) ?></span></td>
<td><div class="acciones">
<a class="boton secundario" href="index.php?editar=<?= (int)$envio['id'] ?>">Editar</a>
<form method="post" action="index.php" onsubmit="return confirm('¿Seguro que deseas eliminar este envío?');">
<input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?= (int)$envio['id'] ?>">
<button class="peligro" type="submit">Eliminar</button>
</form>
</div></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>
</section>
</div>
</main>
</body>
</html>
