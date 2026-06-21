<?php
function sendTelegram(string $token, string $chatId, string $text): bool {
    if (!$token || !$chatId) return false;
    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $payload = json_encode(['chat_id' => $chatId, 'text' => $text, 'parse_mode' => 'HTML']);
    $ctx = stream_context_create(['http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\nContent-Length: " . strlen($payload) . "\r\n",
        'content' => $payload,
        'timeout' => 6,
        'ignore_errors' => true,
    ]]);
    $res = @file_get_contents($url, false, $ctx);
    if (!$res) return false;
    $d = json_decode($res, true);
    return !empty($d['ok']);
}

function tgVenta(array $venta, array $items, float $tasa): void {
    $token  = getConfig('telegram_bot_token', '');
    $chatId = getConfig('telegram_chat_id', '');
    if (!$token || !$chatId || getConfig('telegram_notif_ventas', '1') !== '1') return;

    $payLabels = ['fisico_bs'=>'Físico (Bs)','fisico_usd'=>'Efectivo $','pago_movil'=>'Pago Móvil','mixto'=>'Mixto'];
    $metodo    = $payLabels[$venta['metodo_pago']] ?? $venta['metodo_pago'];
    $totalFinal = (float)$venta['total_final'];
    $totalBs    = $tasa > 0 ? $totalFinal * $tasa : 0;

    $lineas = '';
    foreach ($items as $it) {
        $sub = number_format((float)$it['subtotal'], 2);
        $lineas .= "\n  • {$it['descripcion']} x{$it['cantidad']} = \${$sub}";
    }

    $bsLine = $tasa > 0
        ? "\n<b>Total Bs:</b> " . number_format($totalBs, 2) . " (@ Bs " . number_format($tasa, 2) . "/$)"
        : '';

    $cliente = $venta['cliente'] ?: 'Sin cliente';
    $text = "🧾 <b>Nueva Venta</b> — {$venta['numero_venta']}\n"
          . "<b>Cliente:</b> {$cliente}\n"
          . "<b>Total:</b> \$" . number_format($totalFinal, 2) . $bsLine . "\n"
          . "<b>Pago:</b> {$metodo}\n"
          . "<b>Ítems:</b>{$lineas}";

    sendTelegram($token, $chatId, $text);
}

function tgCierre(string $fecha, array $totales, float $tasa): void {
    $token  = getConfig('telegram_bot_token', '');
    $chatId = getConfig('telegram_chat_id', '');
    if (!$token || !$chatId || getConfig('telegram_notif_cierres', '1') !== '1') return;

    $monto   = (float)$totales['monto_total'];
    $montoFs = $tasa > 0 ? number_format($monto * $tasa, 2) : null;
    $tasaLine = $montoFs ? " (Bs {$montoFs} @ Bs " . number_format($tasa, 2) . "/$)" : '';

    $metodos = [
        'fisico_bs'  => ['Físico (Bs)',   $totales['monto_fisico_bs']  ?? 0],
        'fisico_usd' => ['Efectivo $',    $totales['monto_fisico_usd'] ?? 0],
        'pago_movil' => ['Pago Móvil',    $totales['monto_pago_movil'] ?? 0],
        'mixto'      => ['Mixto',         $totales['monto_mixto']      ?? 0],
    ];
    $desglose = '';
    foreach ($metodos as [$lbl, $m]) {
        if ((float)$m > 0) $desglose .= "\n  • {$lbl}: \$" . number_format((float)$m, 2);
    }

    $text = "📊 <b>Cierre del día " . date('d/m/Y', strtotime($fecha)) . "</b>\n"
          . "<b>Ventas:</b> " . $totales['total_ventas'] . "\n"
          . "<b>Total:</b> \$" . number_format($monto, 2) . $tasaLine . "\n"
          . "<b>Por método:</b>" . $desglose;

    sendTelegram($token, $chatId, $text);
}
