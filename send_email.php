<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['ok'=>false,'error'=>'Method not allowed']); exit; }

$EMAIL_RESERVAS = 'reservas@parkingfacil05.es';
$EMAIL_ADMIN    = 'administracion@parkingfacil05.es';
$FROM_NAME      = 'Parking Fácil 05';

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) { echo json_encode(['ok'=>false,'error'=>'Invalid JSON']); exit; }

$type = $data['type'] ?? 'reserva';

function sendEmail($to, $subject, $htmlBody, $fromEmail, $fromName, $attachment = null) {
    $boundary = md5(uniqid(rand(), true));
    $fromNameEncoded = '=?UTF-8?B?' . base64_encode($fromName) . '?=';

    if ($attachment && !empty($attachment['base64'])) {
        $headers  = "From: {$fromNameEncoded} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        $body  = "--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($htmlBody)) . "\r\n";

        $b64clean = preg_replace('/^data:[^;]+;base64,/', '', $attachment['base64']);
        $ext  = strtolower(pathinfo($attachment['name'], PATHINFO_EXTENSION));
        $mime = in_array($ext, ['jpg','jpeg','png','gif','webp']) ? "image/{$ext}" : 'application/octet-stream';

        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: {$mime}; name=\"{$attachment['name']}\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n";
        $body .= "Content-Disposition: attachment; filename=\"{$attachment['name']}\"\r\n\r\n";
        $body .= chunk_split($b64clean) . "\r\n";
        $body .= "--{$boundary}--";
    } else {
        $headers  = "From: {$fromNameEncoded} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: base64\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        $body = chunk_split(base64_encode($htmlBody));
    }

    $subjectEncoded = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    return mail($to, $subjectEncoded, $body, $headers);
}

function emailWrap($title, $content, $color = '#f0b429') {
    return "<!DOCTYPE html><html><head><meta charset='UTF-8'/></head>
<body style='margin:0;padding:0;background:#0a0a0a;font-family:Arial,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background:#0a0a0a;padding:40px 20px;'>
<tr><td align='center'>
<table width='600' cellpadding='0' cellspacing='0' style='background:#1c1c1c;border-radius:12px;border:1px solid #2a2a2a;overflow:hidden;max-width:600px;'>
<tr><td style='background:#161616;padding:28px 36px;border-bottom:2px solid {$color};'>
<div style='font-size:22px;letter-spacing:2px;color:{$color};font-family:Arial,sans-serif;font-weight:700;'>PARKING FACIL 05</div>
<div style='margin-top:6px;color:#888;font-size:12px;'>Aeropuerto Barcelona - El Prat de Llobregat</div>
</td></tr>
<tr><td style='padding:28px 36px;'>
<div style='font-size:18px;font-weight:700;color:#f0ede8;margin-bottom:20px;letter-spacing:1px;'>{$title}</div>
{$content}
</td></tr>
<tr><td style='background:#111;padding:16px 36px;border-top:1px solid #2a2a2a;'>
<div style='color:#555;font-size:11px;line-height:1.6;'>Luxury Parking Wash, S.L. - NIF B19765296 - 08820 El Prat de Llobregat, Barcelona<br/>
Tel: 623 515 086 - reservas@parkingfacil05.es</div>
</td></tr>
</table>
</td></tr>
</table>
</body></html>";
}

function fila($label, $value) {
    return "<div style='display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid #2a2a2a;'>
<span style='color:#888;font-size:13px;'>{$label}</span>
<span style='color:#f0ede8;font-size:13px;font-weight:600;text-align:right;max-width:60%;'>{$value}</span>
</div>";
}

if ($type === 'reserva') {
    $ref      = htmlspecialchars($data['ref_numero']      ?? '-');
    $nombre   = htmlspecialchars($data['cliente_nombre']  ?? '-');
    $rawEmail = $data['cliente_email'] ?? '';
    $email    = filter_var($rawEmail, FILTER_VALIDATE_EMAIL);
    if (!$email) { echo json_encode(['ok'=>false,'error'=>'Invalid email']); exit; }

    $tel        = htmlspecialchars($data['cliente_tel']       ?? '-');
    $matricula  = htmlspecialchars($data['matricula']          ?? '-');
    $vehiculo   = htmlspecialchars($data['vehiculo']           ?? '-');
    $vSalida    = htmlspecialchars($data['vuelo_salida']       ?? '-');
    $vLlegada   = htmlspecialchars($data['vuelo_llegada']      ?? '-');
    $personas   = htmlspecialchars($data['num_personas']       ?? '-');
    $empresa    = htmlspecialchars($data['empresa']            ?? '-');
    $descuento  = htmlspecialchars($data['codigo_descuento']   ?? '-');
    $comentarios= htmlspecialchars($data['comentarios']        ?? '-');
    $dias       = htmlspecialchars($data['dias']               ?? '-');
    $duracion   = htmlspecialchars($data['duracion']           ?? '-');
    $fEntrada   = htmlspecialchars($data['fecha_entrada']      ?? '-');
    $fSalida    = htmlspecialchars($data['fecha_salida']       ?? '-');
    $metodo     = htmlspecialchars($data['metodo_pago']        ?? '-');
    $total      = htmlspecialchars($data['total']              ?? '-');
    $comprobante= htmlspecialchars($data['comprobante_adjunto']?? 'No adjuntado');

    $attachment = null;
    if (!empty($data['comprobante_base64']) && !empty($data['comprobante_nombre'])) {
        $attachment = ['base64' => $data['comprobante_base64'], 'name' => $data['comprobante_nombre']];
    }

    $rows =
        fila('Referencia',     "<span style='color:#f0b429;'>{$ref}</span>") .
        fila('Nombre',         $nombre) .
        fila('Telefono',       $tel) .
        fila('Email',          $email) .
        fila('Matricula',      $matricula) .
        fila('Vehiculo',       $vehiculo) .
        fila('Vuelo salida',   $vSalida) .
        fila('Vuelo llegada',  $vLlegada) .
        fila('Personas',       $personas) .
        fila('Empresa',        $empresa) .
        fila('Descuento',      $descuento) .
        fila('Comentarios',    $comentarios) .
        fila('Dias',           $dias) .
        fila('Duracion',       $duracion) .
        fila('Entrada',        $fEntrada) .
        fila('Salida',         $fSalida) .
        fila('Metodo de pago', $metodo) .
        fila('Comprobante',    $comprobante);

    $totalBox = "<div style='margin-top:20px;padding:16px;background:#111;border-radius:8px;border:2px solid #f0b429;display:flex;justify-content:space-between;align-items:center;'>
<span style='color:#888;font-size:13px;text-transform:uppercase;'>TOTAL A COBRAR</span>
<span style='color:#f0b429;font-size:28px;font-weight:700;'>{$total}</span></div>";

    $htmlParking = emailWrap("NUEVA RESERVA - {$ref}", $rows . $totalBox);
    sendEmail($EMAIL_RESERVAS, "Nueva reserva {$ref} - {$matricula}", $htmlParking, $EMAIL_RESERVAS, $FROM_NAME, $attachment);

    $rowsCliente =
        fila('Referencia',     "<span style='color:#f0b429;'>{$ref}</span>") .
        fila('Entrada',        $fEntrada) .
        fila('Salida',         $fSalida) .
        fila('Duracion',       $duracion) .
        fila('Matricula',      $matricula) .
        fila('Metodo de pago', $metodo) .
        fila('Total',          "<span style='color:#f0b429;font-size:18px;font-weight:700;'>{$total}</span>");

    $infoCliente = "<div style='margin-top:16px;padding:14px;background:rgba(240,180,41,0.06);border-radius:8px;border:1px dashed rgba(240,180,41,0.3);'>
<div style='color:#888;font-size:13px;line-height:1.7;'>Hola <strong style='color:#f0ede8;'>{$nombre}</strong>, hemos recibido tu reserva.<br/>
Confirmaremos tu plaza en un maximo de <strong style='color:#f0b429;'>2 horas</strong>.<br/><br/>
Dudas? Llamanos al <strong style='color:#f0ede8;'>623 515 086</strong> o escribe a
<a href='mailto:reservas@parkingfacil05.es' style='color:#f0b429;'>reservas@parkingfacil05.es</a></div></div>";

    $htmlCliente = emailWrap("Reserva recibida - {$ref}", $rowsCliente . $infoCliente);
    sendEmail($email, "Reserva recibida {$ref} - Parking Facil 05", $htmlCliente, $EMAIL_RESERVAS, $FROM_NAME);

    echo json_encode(['ok' => true, 'ref' => $ref]);
    exit;
}

if ($type === 'contacto') {
    $nombre   = htmlspecialchars($data['cliente_nombre'] ?? '-');
    $rawEmail = $data['cliente_email'] ?? '';
    $email    = filter_var($rawEmail, FILTER_VALIDATE_EMAIL);
    if (!$email) { echo json_encode(['ok'=>false,'error'=>'Invalid email']); exit; }
    $tel      = htmlspecialchars($data['cliente_tel']    ?? '-');
    $tipo     = htmlspecialchars($data['tipo_consulta']  ?? '-');
    $mensaje  = htmlspecialchars($data['mensaje']        ?? '-');

    $rowsAdmin = fila('Nombre',$nombre).fila('Email',$email).fila('Telefono',$tel).fila('Tipo',$tipo).fila('Mensaje',$mensaje);
    $htmlAdmin = emailWrap("NUEVO MENSAJE DE CONTACTO", $rowsAdmin);
    sendEmail($EMAIL_ADMIN, "Mensaje de {$nombre} - Parking Facil 05", $htmlAdmin, $EMAIL_RESERVAS, $FROM_NAME);

    $rowsConfirm = fila('Tipo de consulta',$tipo).fila('Tu mensaje',$mensaje);
    $infoConfirm = "<div style='margin-top:16px;padding:14px;background:rgba(240,180,41,0.06);border-radius:8px;border:1px dashed rgba(240,180,41,0.3);'>
<div style='color:#888;font-size:13px;line-height:1.7;'>Hola <strong style='color:#f0ede8;'>{$nombre}</strong>, hemos recibido tu mensaje y te responderemos pronto.<br/><br/>
Contacto directo: <strong style='color:#f0ede8;'>623 515 086</strong></div></div>";
    $htmlConfirm = emailWrap("Hemos recibido tu mensaje", $rowsConfirm . $infoConfirm);
    sendEmail($email, "Hemos recibido tu mensaje - Parking Facil 05", $htmlConfirm, $EMAIL_RESERVAS, $FROM_NAME);

    echo json_encode(['ok' => true]);
    exit;
}

echo json_encode(['ok' => false, 'error' => 'Unknown type']);
