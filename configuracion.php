<?php
$pageTitle  = 'Configuración';
$activeMenu = 'configuracion';
require_once __DIR__ . '/includes/layout.php';
if ($user['rol'] !== 'admin') { header('Location: /dashboard.php'); exit; }
?>

<div style="display:grid;grid-template-columns:420px 1fr;gap:22px;align-items:start" id="cfgLayout">

    <!-- ══ Panel de configuración ══ -->
    <div style="display:flex;flex-direction:column;gap:14px">

        <!-- Datos del negocio -->
        <div class="card">
            <div class="cfg-section-head" onclick="toggleSection('sNegocio')">
                <div style="display:flex;align-items:center;gap:9px">
                    <div style="width:30px;height:30px;border-radius:8px;background:rgba(124,58,237,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-store" style="color:var(--primary-light);font-size:13px"></i>
                    </div>
                    <span style="font-size:14px;font-weight:700">Datos del Negocio</span>
                </div>
                <i class="fas fa-chevron-down cfg-chevron" id="chevron-sNegocio" style="color:var(--text-dim);font-size:12px;transition:transform 0.3s"></i>
            </div>
            <div id="sNegocio" class="cfg-section-body">
                <div class="form-group">
                    <label class="form-label">Nombre del Negocio</label>
                    <div class="input-icon-wrap"><i class="fas fa-store form-icon"></i>
                        <input type="text" class="form-control has-icon" id="cNombre" placeholder="PrintShop" oninput="livePreview()">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">RIF / Registro</label>
                    <div class="input-icon-wrap"><i class="fas fa-building form-icon"></i>
                        <input type="text" class="form-control has-icon" id="cRif" placeholder="J-000000000" oninput="livePreview()">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Dirección</label>
                    <textarea class="form-control" id="cDireccion" rows="2" placeholder="Dirección del negocio" oninput="livePreview()"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group mb-0">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="cTelefono" placeholder="+58 000-000-0000" oninput="livePreview()">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="cEmail" placeholder="negocio@mail.com" oninput="livePreview()">
                    </div>
                </div>
            </div>
        </div>

        <!-- Logo -->
        <div class="card">
            <div class="cfg-section-head" onclick="toggleSection('sLogo')">
                <div style="display:flex;align-items:center;gap:9px">
                    <div style="width:30px;height:30px;border-radius:8px;background:rgba(6,182,212,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-image" style="color:#06b6d4;font-size:13px"></i>
                    </div>
                    <span style="font-size:14px;font-weight:700">Logo</span>
                </div>
                <i class="fas fa-chevron-down cfg-chevron" id="chevron-sLogo" style="color:var(--text-dim);font-size:12px;transition:transform 0.3s"></i>
            </div>
            <div id="sLogo" class="cfg-section-body">
                <div style="display:flex;gap:14px;align-items:flex-start">
                    <div id="logoPreviewBox"
                         onclick="document.getElementById('logoFile').click()"
                         style="width:72px;height:72px;border-radius:12px;background:rgba(255,255,255,0.05);border:2px dashed var(--card-border);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;cursor:pointer;transition:all 0.2s"
                         onmouseover="this.style.borderColor='var(--primary-light)'" onmouseout="this.style.borderColor='var(--card-border)'">
                        <img id="logoPreviewImg" style="display:none;width:100%;height:100%;object-fit:contain;padding:6px" src="" alt="">
                        <i id="logoPlaceholder" class="fas fa-plus" style="font-size:18px;color:var(--text-dim)"></i>
                    </div>
                    <div style="flex:1">
                        <button class="btn btn-secondary btn-sm" onclick="document.getElementById('logoFile').click()"><i class="fas fa-upload"></i> Subir logo</button>
                        <input type="file" id="logoFile" accept="image/png,image/jpeg,image/webp,image/gif" style="display:none" onchange="uploadLogo(event)">
                        <div style="font-size:11px;color:var(--text-dim);margin-top:7px;line-height:1.5">PNG, JPG o WebP<br>Fondo transparente recomendado</div>
                        <label style="display:flex;align-items:center;gap:7px;margin-top:10px;cursor:pointer">
                            <input type="checkbox" id="cMostrarLogo" style="accent-color:var(--primary);width:14px;height:14px" onchange="livePreview()">
                            <span style="font-size:12px;color:var(--text-muted)">Mostrar logo en la factura</span>
                        </label>
                    </div>
                </div>
                <div id="logoStatus" style="display:none;margin-top:10px;padding:8px 12px;border-radius:8px;font-size:12px"></div>
            </div>
        </div>

        <!-- Colores -->
        <div class="card">
            <div class="cfg-section-head" onclick="toggleSection('sColores')">
                <div style="display:flex;align-items:center;gap:9px">
                    <div style="width:30px;height:30px;border-radius:8px;background:rgba(245,158,11,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-palette" style="color:#f59e0b;font-size:13px"></i>
                    </div>
                    <span style="font-size:14px;font-weight:700">Colores</span>
                </div>
                <i class="fas fa-chevron-down cfg-chevron" id="chevron-sColores" style="color:var(--text-dim);font-size:12px;transition:transform 0.3s"></i>
            </div>
            <div id="sColores" class="cfg-section-body">
                <!-- Color grid -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <?php
                    $colorFields = [
                        ['cColor',       'cColorPicker',       '#7c3aed', 'Encabezado (fondo)',   'fa-fill-drip'],
                        ['cHeaderTexto', 'cHeaderTextoPicker', '#ffffff', 'Encabezado (texto)',   'fa-font'],
                        ['cPieBg',       'cPieBgPicker',       '#f8fafc', 'Pie (fondo)',          'fa-shoe-prints'],
                        ['cPieTexto',    'cPieTextoPicker',    '#94a3b8', 'Pie (texto)',          'fa-align-left'],
                    ];
                    foreach ($colorFields as [$id, $pid, $def, $lbl, $ico]):
                    ?>
                    <div>
                        <label class="form-label" style="margin-bottom:6px"><i class="fas <?= $ico ?>" style="margin-right:4px;opacity:.7"></i><?= $lbl ?></label>
                        <div style="display:flex;gap:8px;align-items:center">
                            <input type="color" id="<?= $pid ?>" value="<?= $def ?>"
                                   style="width:38px;height:34px;border:none;background:none;cursor:pointer;border-radius:8px;padding:2px;flex-shrink:0"
                                   oninput="document.getElementById('<?= $id ?>').value=this.value;livePreview()">
                            <input type="text" class="form-control" id="<?= $id ?>" value="<?= $def ?>"
                                   style="font-family:monospace;font-size:12px"
                                   oninput="document.getElementById('<?= $pid ?>').value=this.value;livePreview()">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Palette -->
                <div style="margin-top:14px">
                    <div style="font-size:10px;text-transform:uppercase;letter-spacing:0.7px;color:var(--text-dim);font-weight:700;margin-bottom:8px">Paleta rápida (encabezado)</div>
                    <div style="display:flex;gap:7px;flex-wrap:wrap">
                        <?php foreach(['#7c3aed','#2563eb','#059669','#dc2626','#d97706','#0891b2','#be185d','#0f172a','#1a1a2e','#065f46','#7f1d1d','#1e3a5f'] as $c): ?>
                        <button onclick="setHeaderColor('<?= $c ?>')"
                                style="width:26px;height:26px;border-radius:6px;background:<?= $c ?>;border:2px solid transparent;cursor:pointer;transition:transform 0.15s,box-shadow 0.15s"
                                onmouseover="this.style.transform='scale(1.25)';this.style.boxShadow='0 0 8px <?= $c ?>88'"
                                onmouseout="this.style.transform='scale(1)';this.style.boxShadow='none'"></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla / Cuerpo -->
        <div class="card">
            <div class="cfg-section-head" onclick="toggleSection('sTabla')">
                <div style="display:flex;align-items:center;gap:9px">
                    <div style="width:30px;height:30px;border-radius:8px;background:rgba(239,68,68,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-table" style="color:#f87171;font-size:13px"></i>
                    </div>
                    <span style="font-size:14px;font-weight:700">Tabla de Ítems</span>
                </div>
                <i class="fas fa-chevron-down cfg-chevron" id="chevron-sTabla" style="color:var(--text-dim);font-size:12px;transition:transform 0.3s"></i>
            </div>
            <div id="sTabla" class="cfg-section-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <?php
                    $tablaFields = [
                        ['cFilaBg',    'cFilaBgPicker',    '#ffffff', 'Fondo filas',        'fa-fill'],
                        ['cFilaAlt',   'cFilaAltPicker',   '#f8fafc', 'Fondo filas alternas','fa-fill-drip'],
                        ['cFilaTexto', 'cFilaTextoPicker', '#334155', 'Texto de filas',     'fa-font'],
                        ['cFilaBorde', 'cFilaBordePicker', '#f1f5f9', 'Separadores',        'fa-minus'],
                        ['cTotalBg',   'cTotalBgPicker',   '#f8fafc', 'Fondo fila total',   'fa-rectangle-list'],
                        ['cTotalTexto','cTotalTextoPicker','#7c3aed', 'Texto fila total',   'fa-bold'],
                    ];
                    foreach ($tablaFields as [$id, $pid, $def, $lbl, $ico]):
                    ?>
                    <div>
                        <label class="form-label" style="margin-bottom:6px"><i class="fas <?= $ico ?>" style="margin-right:4px;opacity:.7"></i><?= $lbl ?></label>
                        <div style="display:flex;gap:8px;align-items:center">
                            <input type="color" id="<?= $pid ?>" value="<?= $def ?>"
                                   style="width:38px;height:34px;border:none;background:none;cursor:pointer;border-radius:8px;padding:2px;flex-shrink:0"
                                   oninput="document.getElementById('<?= $id ?>').value=this.value;livePreview()">
                            <input type="text" class="form-control" id="<?= $id ?>" value="<?= $def ?>"
                                   style="font-family:monospace;font-size:12px"
                                   oninput="document.getElementById('<?= $pid ?>').value=this.value;livePreview()">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Reset rápido -->
                <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
                    <button onclick="setTablaPreset('light')" class="btn btn-secondary btn-sm"><i class="fas fa-sun"></i> Claro</button>
                    <button onclick="setTablaPreset('dark')"  class="btn btn-secondary btn-sm"><i class="fas fa-moon"></i> Oscuro</button>
                    <button onclick="setTablaPreset('accent')" class="btn btn-secondary btn-sm"><i class="fas fa-palette"></i> Acento</button>
                </div>
            </div>
        </div>

        <!-- Textos -->
        <div class="card">
            <div class="cfg-section-head" onclick="toggleSection('sTextos')">
                <div style="display:flex;align-items:center;gap:9px">
                    <div style="width:30px;height:30px;border-radius:8px;background:rgba(16,185,129,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-align-left" style="color:#10b981;font-size:13px"></i>
                    </div>
                    <span style="font-size:14px;font-weight:700">Textos de la Factura</span>
                </div>
                <i class="fas fa-chevron-down cfg-chevron" id="chevron-sTextos" style="color:var(--text-dim);font-size:12px;transition:transform 0.3s"></i>
            </div>
            <div id="sTextos" class="cfg-section-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Título del documento</label>
                        <input type="text" class="form-control" id="cTitulo" placeholder="Nota de Entrega" oninput="livePreview()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subtítulo (opcional)</label>
                        <input type="text" class="form-control" id="cSubtitulo" placeholder="Servicios de impresión" oninput="livePreview()">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Pie de página</label>
                    <input type="text" class="form-control" id="cPie" placeholder="Gracias por su preferencia" oninput="livePreview()">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Nota en facturas</label>
                    <textarea class="form-control" id="cNota" rows="2" placeholder="Condiciones, información adicional..." oninput="livePreview()"></textarea>
                </div>
            </div>
        </div>

        <!-- Telegram -->
        <div class="card">
            <div class="cfg-section-head" onclick="toggleSection('sTelegram')">
                <div style="display:flex;align-items:center;gap:9px">
                    <div style="width:30px;height:30px;border-radius:8px;background:rgba(0,136,204,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fab fa-telegram" style="color:#29b6f6;font-size:14px"></i>
                    </div>
                    <span style="font-size:14px;font-weight:700">Bot de Telegram</span>
                </div>
                <i class="fas fa-chevron-down cfg-chevron" id="chevron-sTelegram" style="color:var(--text-dim);font-size:12px;transition:transform 0.3s"></i>
            </div>
            <div id="sTelegram" class="cfg-section-body">
                <div class="form-group">
                    <label class="form-label">Token del bot</label>
                    <input type="text" class="form-control" id="cTgToken" placeholder="123456:AABBccDDee..." style="font-family:monospace;font-size:12px">
                </div>
                <div class="form-group">
                    <label class="form-label">Chat ID (grupo o usuario)</label>
                    <div style="display:flex;gap:8px">
                        <input type="text" class="form-control" id="cTgChat" placeholder="Ej: -1001234567890" style="font-family:monospace;font-size:12px;flex:1">
                        <button class="btn btn-secondary btn-sm" onclick="detectChatId()" style="white-space:nowrap"><i class="fas fa-magnifying-glass"></i> Detectar</button>
                    </div>
                    <div style="font-size:11px;color:var(--text-dim);margin-top:5px">Envía un mensaje al bot en Telegram y pulsa Detectar para obtener el ID automáticamente</div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:4px">
                    <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:12px;color:var(--text-muted)">
                        <input type="checkbox" id="cTgVentas" style="accent-color:var(--primary);width:14px;height:14px">
                        <span>Notificar ventas</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:12px;color:var(--text-muted)">
                        <input type="checkbox" id="cTgCierres" style="accent-color:var(--primary);width:14px;height:14px">
                        <span>Notificar cierres</span>
                    </label>
                </div>
                <div id="tgStatus" style="display:none;margin-top:10px;padding:8px 12px;border-radius:8px;font-size:12px"></div>
            </div>
        </div>

        <button class="btn btn-success btn-lg w-full" onclick="saveAll()" id="btnSaveAll">
            <i class="fas fa-save"></i> Guardar Todo
        </button>
    </div>

    <!-- ══ Preview completo de la factura ══ -->
    <div style="position:sticky;top:76px">
        <div class="card" style="padding:0;overflow:hidden">
            <div style="padding:14px 18px;border-bottom:1px solid var(--card-border);display:flex;align-items:center;justify-content:space-between">
                <div style="font-size:13px;font-weight:700;display:flex;align-items:center;gap:7px">
                    <i class="fas fa-file-invoice" style="color:var(--primary-light)"></i> Vista Previa en Vivo
                </div>
                <span style="font-size:10px;color:var(--text-dim);background:rgba(16,185,129,0.1);color:#6ee7b7;border:1px solid rgba(16,185,129,0.2);padding:2px 8px;border-radius:10px;font-weight:600">TIEMPO REAL</span>
            </div>

            <!-- Invoice preview — mirrors factura.php structure -->
            <div id="invoicePreview" style="font-family:'Inter',sans-serif;font-size:12px;background:#f1f5f9;padding:16px">
                <div style="background:white;border-radius:8px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.12)">

                    <!-- Header -->
                    <div id="pv-header" style="background:#7c3aed;color:#ffffff;padding:24px 28px;display:flex;justify-content:space-between;align-items:flex-start;gap:20px">
                        <div style="flex:1">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
                                <div id="pv-logo-wrap" style="display:none">
                                    <img id="pv-logo" src="" alt="" style="height:42px;border-radius:5px;background:rgba(255,255,255,0.15);padding:3px">
                                </div>
                                <div>
                                    <div id="pv-nombre" style="font-size:18px;font-weight:800;letter-spacing:-0.3px">PrintShop</div>
                                    <div id="pv-subtitulo" style="font-size:10px;opacity:0.75;margin-top:2px"></div>
                                </div>
                            </div>
                            <div id="pv-info" style="font-size:11px;opacity:0.8;line-height:1.8">
                                <div id="pv-rif"></div>
                                <div id="pv-dir"></div>
                                <div id="pv-tel"></div>
                                <div id="pv-email"></div>
                            </div>
                        </div>
                        <div style="text-align:right;flex-shrink:0">
                            <div id="pv-titulo-label" style="font-size:9px;text-transform:uppercase;letter-spacing:1px;opacity:0.7;margin-bottom:3px">Nota de Entrega</div>
                            <div style="font-size:15px;font-weight:800;font-family:monospace">VTA-20240101-ABC</div>
                            <div style="font-size:10px;opacity:0.85;margin-top:3px">10/06/2024  14:30</div>
                            <div style="margin-top:6px">
                                <span style="background:rgba(255,255,255,0.2);padding:3px 10px;border-radius:12px;font-size:9px;font-weight:700;letter-spacing:0.5px">COMPLETADA</span>
                            </div>
                        </div>
                    </div>

                    <!-- Body -->
                    <div style="padding:22px 28px;background:white;color:#334155">

                        <!-- Client + payment boxes -->
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px">
                            <div style="background:#f8fafc;border-radius:7px;padding:13px;border:1px solid #e2e8f0">
                                <div style="font-size:9px;text-transform:uppercase;letter-spacing:0.7px;color:#64748b;font-weight:700;margin-bottom:8px"><i class="fas fa-user" style="margin-right:4px"></i>Cliente</div>
                                <div style="font-size:12px;font-weight:600;margin-bottom:3px">María González</div>
                                <div style="font-size:11px;color:#94a3b8"><i class="fas fa-id-card" style="margin-right:3px"></i>V-18765432</div>
                                <div style="font-size:11px;color:#94a3b8;margin-top:2px"><i class="fas fa-phone" style="margin-right:3px"></i>0414-5551234</div>
                            </div>
                            <div style="background:#f8fafc;border-radius:7px;padding:13px;border:1px solid #e2e8f0">
                                <div style="font-size:9px;text-transform:uppercase;letter-spacing:0.7px;color:#64748b;font-weight:700;margin-bottom:8px"><i class="fas fa-wallet" style="margin-right:4px"></i>Pago</div>
                                <div style="font-size:12px;font-weight:600;margin-bottom:3px">Pago Móvil</div>
                                <div style="font-size:11px;color:#94a3b8">Vendedor: Admin</div>
                                <div style="font-size:11px;color:#94a3b8;margin-top:2px">10/06/2024</div>
                            </div>
                        </div>

                        <!-- Items table -->
                        <table id="pv-items-table" style="width:100%;border-collapse:collapse;margin-bottom:18px">
                            <thead>
                                <tr id="pv-table-head" style="background:#7c3aed;color:white">
                                    <th style="padding:9px 12px;font-size:10px;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;text-align:left">Descripción</th>
                                    <th style="padding:9px 12px;font-size:10px;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;text-align:left">Categoría</th>
                                    <th style="padding:9px 12px;font-size:10px;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;text-align:center">Cant.</th>
                                    <th style="padding:9px 12px;font-size:10px;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;text-align:right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="pv-tbody">
                                <tr id="pv-row-0">
                                    <td class="pv-td" style="padding:11px 12px;font-size:12px">Sublimación camiseta blanca</td>
                                    <td class="pv-td" style="padding:11px 12px;font-size:12px"><span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#7c3aed;margin-right:5px"></span>Sublimación</td>
                                    <td class="pv-td" style="padding:11px 12px;font-size:12px;text-align:center">3</td>
                                    <td class="pv-td" style="padding:11px 12px;font-size:12px;font-weight:600;text-align:right">$18.00</td>
                                </tr>
                                <tr id="pv-row-1">
                                    <td class="pv-td" style="padding:11px 12px;font-size:12px">DTF en franela negra</td>
                                    <td class="pv-td" style="padding:11px 12px;font-size:12px"><span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#06b6d4;margin-right:5px"></span>DTF</td>
                                    <td class="pv-td" style="padding:11px 12px;font-size:12px;text-align:center">2</td>
                                    <td class="pv-td" style="padding:11px 12px;font-size:12px;font-weight:600;text-align:right">$14.00</td>
                                </tr>
                                <tr id="pv-row-2">
                                    <td class="pv-td" style="padding:11px 12px;font-size:12px">Rotulación vinil de corte</td>
                                    <td class="pv-td" style="padding:11px 12px;font-size:12px"><span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#f59e0b;margin-right:5px"></span>Rotulación</td>
                                    <td class="pv-td" style="padding:11px 12px;font-size:12px;text-align:center">1</td>
                                    <td class="pv-td" style="padding:11px 12px;font-size:12px;font-weight:600;text-align:right">$8.00</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr id="pv-subtotal-row">
                                    <td id="pv-sub-l" colspan="3" style="padding:8px 12px;font-size:11px;text-align:right;color:#64748b;border-top:1px solid #e2e8f0">Subtotal</td>
                                    <td id="pv-sub-r" style="padding:8px 12px;font-size:11px;text-align:right;border-top:1px solid #e2e8f0">$40.00</td>
                                </tr>
                                <tr id="pv-total-row" style="background:#f8fafc">
                                    <td colspan="3" style="padding:10px 12px;font-size:14px;font-weight:800;text-align:right;color:#7c3aed;border-top:2px solid #7c3aed">TOTAL</td>
                                    <td style="padding:10px 12px;font-size:14px;font-weight:800;text-align:right;color:#7c3aed;border-top:2px solid #7c3aed">$40.00</td>
                                </tr>
                            </tfoot>
                        </table>

                        <!-- Nota -->
                        <div id="pv-nota-wrap" style="display:none;background:#fff7ed;border:1px solid #fed7aa;border-radius:7px;padding:11px 14px;margin-bottom:4px">
                            <div style="font-size:9px;text-transform:uppercase;letter-spacing:0.7px;color:#92400e;font-weight:700;margin-bottom:4px"><i class="fas fa-info-circle"></i> Nota</div>
                            <div id="pv-nota-text" style="font-size:11px;color:#92400e"></div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div id="pv-footer" style="background:#f8fafc;border-top:2px solid #f1f5f9;padding:14px 28px;display:flex;justify-content:space-between;align-items:center;gap:16px">
                        <div id="pv-pie" style="font-size:11px;color:#94a3b8;font-style:italic">Gracias por su preferencia</div>
                        <div style="font-size:9px;color:#cbd5e1;text-align:right">Generado por PrintShop</div>
                    </div>
                </div>
            </div>
            <!-- End invoice preview -->
        </div>
    </div>
</div>

<style>
.cfg-section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    user-select: none;
    padding-bottom: 0;
    transition: opacity 0.2s;
}
.cfg-section-head:hover { opacity: 0.85; }
.cfg-section-body {
    margin-top: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
}
.cfg-section-body.collapsed {
    margin-top: 0;
    max-height: 0 !important;
    opacity: 0;
    pointer-events: none;
}
@media (max-width: 900px) {
    #cfgLayout { grid-template-columns: 1fr !important; }
    #cfgLayout > div:last-child { position: static !important; }
}
</style>

<?php require_once __DIR__ . '/includes/layout_end.php'; ?>
<script>
let logoPath = '';
let sections = { sNegocio: true, sLogo: true, sColores: true, sTabla: true, sTextos: true, sTelegram: false };

function toggleSection(id) {
    sections[id] = !sections[id];
    const el = document.getElementById(id);
    const ch = document.getElementById('chevron-' + id);
    if (sections[id]) {
        el.classList.remove('collapsed');
        ch.style.transform = 'rotate(0deg)';
    } else {
        el.classList.add('collapsed');
        ch.style.transform = 'rotate(-90deg)';
    }
}

function setHeaderColor(hex) {
    document.getElementById('cColor').value        = hex;
    document.getElementById('cColorPicker').value  = hex;
    livePreview();
}

function setTablaPreset(preset) {
    const presets = {
        light:  { bg:'#ffffff', alt:'#f8fafc', txt:'#334155', borde:'#e2e8f0', totalBg:'#f1f5f9', totalTxt:'#1e293b' },
        dark:   { bg:'#1e293b', alt:'#0f172a', txt:'#e2e8f0', borde:'#334155', totalBg:'#0f172a', totalTxt:'#a78bfa' },
        accent: { bg:'#faf5ff', alt:'#f3e8ff', txt:'#3b0764', borde:'#e9d5ff', totalBg:'#7c3aed', totalTxt:'#ffffff' },
    };
    const p = presets[preset]; if (!p) return;
    const apply = (inputId, pickerId, val) => {
        document.getElementById(inputId).value  = val;
        document.getElementById(pickerId).value = val;
    };
    apply('cFilaBg',    'cFilaBgPicker',    p.bg);
    apply('cFilaAlt',   'cFilaAltPicker',   p.alt);
    apply('cFilaTexto', 'cFilaTextoPicker', p.txt);
    apply('cFilaBorde', 'cFilaBordePicker', p.borde);
    apply('cTotalBg',   'cTotalBgPicker',   p.totalBg);
    apply('cTotalTexto','cTotalTextoPicker',p.totalTxt);
    livePreview();
}

function setLogoPreview(src) {
    document.getElementById('logoPreviewImg').src = src;
    document.getElementById('logoPreviewImg').style.display = 'block';
    document.getElementById('logoPlaceholder').style.display = 'none';
    document.getElementById('pv-logo').src = src;
    livePreview();
}

async function uploadLogo(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => setLogoPreview(ev.target.result);
    reader.readAsDataURL(file);

    const fd = new FormData();
    fd.append('logo', file);
    const st = document.getElementById('logoStatus');
    st.style.cssText = 'display:block;padding:8px 12px;border-radius:8px;font-size:12px;background:rgba(245,158,11,0.1);color:#fcd34d;border:1px solid rgba(245,158,11,0.2)';
    st.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subiendo logo…';
    try {
        const r = await fetch('/api/configuracion.php?logo=1', { method: 'POST', body: fd });
        const d = await r.json();
        if (d.success) {
            logoPath = d.path;
            st.style.cssText = 'display:block;padding:8px 12px;border-radius:8px;font-size:12px;background:rgba(16,185,129,0.1);color:#6ee7b7;border:1px solid rgba(16,185,129,0.2)';
            st.innerHTML = '<i class="fas fa-check-circle"></i> Logo guardado correctamente';
            setTimeout(() => st.style.display = 'none', 3000);
        } else {
            st.style.cssText = 'display:block;padding:8px 12px;border-radius:8px;font-size:12px;background:rgba(239,68,68,0.1);color:#fca5a5;border:1px solid rgba(239,68,68,0.2)';
            st.innerHTML = '<i class="fas fa-times-circle"></i> ' + (d.error || 'Error al subir');
        }
    } catch {
        st.innerHTML = '<i class="fas fa-times-circle"></i> Error de conexión';
    }
}

function livePreview() {
    const nombre    = document.getElementById('cNombre')?.value      || 'PrintShop';
    const rif       = document.getElementById('cRif')?.value          || '';
    const dir       = document.getElementById('cDireccion')?.value    || '';
    const tel       = document.getElementById('cTelefono')?.value     || '';
    const email     = document.getElementById('cEmail')?.value        || '';
    const color     = document.getElementById('cColor')?.value        || '#7c3aed';
    const hTxt      = document.getElementById('cHeaderTexto')?.value  || '#ffffff';
    const pieBg     = document.getElementById('cPieBg')?.value        || '#f8fafc';
    const pieTxt    = document.getElementById('cPieTexto')?.value     || '#94a3b8';
    const titulo    = document.getElementById('cTitulo')?.value       || 'Nota de Entrega';
    const subtit    = document.getElementById('cSubtitulo')?.value    || '';
    const pie       = document.getElementById('cPie')?.value          || 'Gracias por su preferencia';
    const nota      = document.getElementById('cNota')?.value         || '';
    const showLogo  = document.getElementById('cMostrarLogo')?.checked;
    // Table interior colors
    const filaBg    = document.getElementById('cFilaBg')?.value       || '#ffffff';
    const filaAlt   = document.getElementById('cFilaAlt')?.value      || '#f8fafc';
    const filaTxt   = document.getElementById('cFilaTexto')?.value    || '#334155';
    const filaBorde = document.getElementById('cFilaBorde')?.value    || '#f1f5f9';
    const totalBg   = document.getElementById('cTotalBg')?.value      || '#f8fafc';
    const totalTxt  = document.getElementById('cTotalTexto')?.value   || '#7c3aed';

    // Header
    const pvh = document.getElementById('pv-header');
    pvh.style.background = color;
    pvh.style.color      = hTxt;

    // Brand
    document.getElementById('pv-nombre').textContent       = nombre;
    document.getElementById('pv-subtitulo').textContent    = subtit;
    document.getElementById('pv-titulo-label').textContent = titulo.toUpperCase();

    // Info lines
    const pvRif   = document.getElementById('pv-rif');
    const pvDir   = document.getElementById('pv-dir');
    const pvTel   = document.getElementById('pv-tel');
    const pvEmail = document.getElementById('pv-email');
    pvRif.innerHTML   = rif   ? `<i class="fas fa-building" style="width:12px;margin-right:4px;opacity:.8"></i>${rif}`    : '';
    pvDir.innerHTML   = dir   ? `<i class="fas fa-location-dot" style="width:12px;margin-right:4px;opacity:.8"></i>${dir}` : '';
    pvTel.innerHTML   = tel   ? `<i class="fas fa-phone" style="width:12px;margin-right:4px;opacity:.8"></i>${tel}`        : '';
    pvEmail.innerHTML = email ? `<i class="fas fa-envelope" style="width:12px;margin-right:4px;opacity:.8"></i>${email}`   : '';

    // Logo
    document.getElementById('pv-logo-wrap').style.display = (showLogo && logoPath) ? '' : 'none';

    // Table head — always uses the primary header color
    document.getElementById('pv-table-head').style.background = color;

    // Table body rows
    const rows = document.querySelectorAll('#pv-tbody tr');
    rows.forEach((tr, i) => {
        tr.style.background = (i % 2 === 0) ? filaBg : filaAlt;
        tr.querySelectorAll('td.pv-td').forEach(td => {
            td.style.color       = filaTxt;
            td.style.borderColor = filaBorde;
        });
        tr.style.borderBottom = `1px solid ${filaBorde}`;
    });

    // Subtotal separator
    const pvSubL = document.getElementById('pv-sub-l');
    const pvSubR = document.getElementById('pv-sub-r');
    if (pvSubL) pvSubL.style.borderTopColor = filaBorde;
    if (pvSubR) pvSubR.style.borderTopColor = filaBorde;

    // Total row
    const totalRow = document.getElementById('pv-total-row');
    totalRow.style.background = totalBg;
    totalRow.querySelectorAll('td').forEach(td => {
        td.style.color          = totalTxt;
        td.style.borderTopColor = totalTxt;
    });

    // Footer
    const pvf = document.getElementById('pv-footer');
    pvf.style.background = pieBg;
    const pvPie = document.getElementById('pv-pie');
    pvPie.textContent = pie;
    pvPie.style.color = pieTxt;

    // Nota
    const noteWrap = document.getElementById('pv-nota-wrap');
    const noteText = document.getElementById('pv-nota-text');
    if (nota) {
        noteWrap.style.display = '';
        noteText.textContent   = nota;
    } else {
        noteWrap.style.display = 'none';
    }
}

async function detectChatId() {
    const token = document.getElementById('cTgToken').value.trim();
    const st    = document.getElementById('tgStatus');
    if (!token) {
        st.style.cssText = 'display:block;padding:8px 12px;border-radius:8px;font-size:12px;background:rgba(245,158,11,0.1);color:#fcd34d;border:1px solid rgba(245,158,11,0.2)';
        st.innerHTML = '<i class="fas fa-triangle-exclamation"></i> Ingresa el token del bot primero';
        return;
    }
    st.style.cssText = 'display:block;padding:8px 12px;border-radius:8px;font-size:12px;background:rgba(124,58,237,0.1);color:#a78bfa;border:1px solid rgba(124,58,237,0.2)';
    st.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando mensajes recientes…';
    try {
        const r = await fetch(`https://api.telegram.org/bot${token}/getUpdates?limit=5&offset=-5`);
        const d = await r.json();
        if (!d.ok) throw new Error(d.description || 'Token inválido');
        const upds = d.result;
        if (!upds.length) {
            st.style.cssText = 'display:block;padding:8px 12px;border-radius:8px;font-size:12px;background:rgba(245,158,11,0.1);color:#fcd34d;border:1px solid rgba(245,158,11,0.2)';
            st.innerHTML = '<i class="fas fa-info-circle"></i> Sin mensajes aún. Envía cualquier mensaje al bot en Telegram y vuelve a intentarlo.';
            return;
        }
        const last  = upds[upds.length - 1];
        const chat  = last.message?.chat || last.channel_post?.chat;
        const chatId = chat?.id;
        const title  = chat?.title || chat?.first_name || chat?.username || '';
        if (!chatId) throw new Error('No se pudo leer el chat');
        document.getElementById('cTgChat').value = String(chatId);
        st.style.cssText = 'display:block;padding:8px 12px;border-radius:8px;font-size:12px;background:rgba(16,185,129,0.1);color:#6ee7b7;border:1px solid rgba(16,185,129,0.2)';
        st.innerHTML = `<i class="fas fa-check-circle"></i> Chat detectado: <strong>${title || chatId}</strong> (ID: ${chatId})`;
    } catch(e) {
        st.style.cssText = 'display:block;padding:8px 12px;border-radius:8px;font-size:12px;background:rgba(239,68,68,0.1);color:#fca5a5;border:1px solid rgba(239,68,68,0.2)';
        st.innerHTML = '<i class="fas fa-times-circle"></i> ' + e.message;
    }
}

async function loadConfig() {
    const d = await apiGet('/api/configuracion.php');

    const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
    set('cNombre',    d.negocio_nombre);
    set('cRif',       d.negocio_rif);
    set('cDireccion', d.negocio_direccion);
    set('cTelefono',  d.negocio_telefono);
    set('cEmail',     d.negocio_email);
    set('cPie',       d.factura_pie);
    set('cNota',      d.factura_nota);
    set('cTitulo',    d.factura_titulo);
    set('cSubtitulo', d.factura_subtitulo);

    const setColor = (inputId, pickerId, val, def) => {
        const v = val || def;
        document.getElementById(inputId).value  = v;
        document.getElementById(pickerId).value = v;
    };
    setColor('cColor',       'cColorPicker',       d.factura_color_primario,       '#7c3aed');
    setColor('cHeaderTexto', 'cHeaderTextoPicker', d.factura_color_header_texto,   '#ffffff');
    setColor('cPieBg',       'cPieBgPicker',       d.factura_color_pie_bg,         '#f8fafc');
    setColor('cPieTexto',    'cPieTextoPicker',    d.factura_color_pie_texto,       '#94a3b8');
    setColor('cFilaBg',      'cFilaBgPicker',      d.factura_color_fila_bg,         '#ffffff');
    setColor('cFilaAlt',     'cFilaAltPicker',     d.factura_color_fila_alt,        '#f8fafc');
    setColor('cFilaTexto',   'cFilaTextoPicker',   d.factura_color_fila_texto,      '#334155');
    setColor('cFilaBorde',   'cFilaBordePicker',   d.factura_color_fila_borde,      '#f1f5f9');
    setColor('cTotalBg',     'cTotalBgPicker',     d.factura_color_total_bg,        '#f8fafc');
    setColor('cTotalTexto',  'cTotalTextoPicker',  d.factura_color_total_texto,     '#7c3aed');

    if (d.factura_mostrar_logo === '1') document.getElementById('cMostrarLogo').checked = true;

    // Telegram
    set('cTgToken',  d.telegram_bot_token);
    set('cTgChat',   d.telegram_chat_id);
    const cbVentas  = document.getElementById('cTgVentas');
    const cbCierres = document.getElementById('cTgCierres');
    if (cbVentas)  cbVentas.checked  = d.telegram_notif_ventas  !== '0';
    if (cbCierres) cbCierres.checked = d.telegram_notif_cierres !== '0';

    if (d.factura_logo) {
        logoPath = d.factura_logo;
        const src = '/' + d.factura_logo + '?t=' + Date.now();
        document.getElementById('pv-logo').src = src;
        setLogoPreview(src);
    }

    livePreview();
}

function setLogoPreview(src) {
    document.getElementById('logoPreviewImg').src = src;
    document.getElementById('logoPreviewImg').style.display = 'block';
    document.getElementById('logoPlaceholder').style.display = 'none';
    document.getElementById('pv-logo').src = src;
}

async function saveAll() {
    const btn = document.getElementById('btnSaveAll');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando…';

    const d = await apiPost('/api/configuracion.php', {
        negocio_nombre:              document.getElementById('cNombre').value,
        negocio_rif:                 document.getElementById('cRif').value,
        negocio_direccion:           document.getElementById('cDireccion').value,
        negocio_telefono:            document.getElementById('cTelefono').value,
        negocio_email:               document.getElementById('cEmail').value,
        factura_color_primario:      document.getElementById('cColor').value,
        factura_color_header_texto:  document.getElementById('cHeaderTexto').value,
        factura_color_pie_bg:        document.getElementById('cPieBg').value,
        factura_color_pie_texto:     document.getElementById('cPieTexto').value,
        factura_mostrar_logo:        document.getElementById('cMostrarLogo').checked ? '1' : '0',
        factura_color_fila_bg:       document.getElementById('cFilaBg').value,
        factura_color_fila_alt:      document.getElementById('cFilaAlt').value,
        factura_color_fila_texto:    document.getElementById('cFilaTexto').value,
        factura_color_fila_borde:    document.getElementById('cFilaBorde').value,
        factura_color_total_bg:      document.getElementById('cTotalBg').value,
        factura_color_total_texto:   document.getElementById('cTotalTexto').value,
        factura_pie:                 document.getElementById('cPie').value,
        factura_nota:                document.getElementById('cNota').value,
        factura_titulo:              document.getElementById('cTitulo').value,
        factura_subtitulo:           document.getElementById('cSubtitulo').value,
        telegram_bot_token:          document.getElementById('cTgToken').value,
        telegram_chat_id:            document.getElementById('cTgChat').value,
        telegram_notif_ventas:       document.getElementById('cTgVentas').checked  ? '1' : '0',
        telegram_notif_cierres:      document.getElementById('cTgCierres').checked ? '1' : '0',
    });

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-save"></i> Guardar Todo';

    if (d.success) {
        showToast('Configuración guardada correctamente', 'success');
        // Flash the preview border
        const prev = document.getElementById('invoicePreview');
        prev.style.transition = 'box-shadow 0.3s';
        prev.style.boxShadow  = '0 0 0 3px rgba(16,185,129,0.4)';
        setTimeout(() => prev.style.boxShadow = 'none', 1200);
    } else {
        showToast('Error al guardar', 'error');
    }
}

loadConfig();
</script>
