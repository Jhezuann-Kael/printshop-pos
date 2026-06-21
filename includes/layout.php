<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$user = getCurrentUser();
$pageTitle  = $pageTitle  ?? 'PrintShop';
$activeMenu = $activeMenu ?? '';
$isAdmin = $user['rol'] === 'admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — PrintShop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body class="<?= $isAdmin ? 'role-admin' : 'role-vendedor' ?>">

<!-- ──── SIDEBAR ──── -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-logo"><i class="fas fa-print"></i></div>
        <div>
            <div class="sidebar-title">PrintShop</div>
            <div class="sidebar-subtitle">Sistema de Ventas</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Principal</div>
        <a href="/dashboard.php" class="nav-item <?= $activeMenu==='dashboard'?'active':'' ?>">
            <span class="nav-icon"><i class="fas fa-chart-pie"></i></span>
            <span class="nav-label">Dashboard</span>
        </a>
        <a href="/ventas.php" class="nav-item <?= $activeMenu==='ventas'?'active':'' ?>">
            <span class="nav-icon"><i class="fas fa-cash-register"></i></span>
            <span class="nav-label">Nueva Venta</span>
        </a>
        <a href="/historial.php" class="nav-item <?= $activeMenu==='historial'?'active':'' ?>">
            <span class="nav-icon"><i class="fas fa-clock-rotate-left"></i></span>
            <span class="nav-label">Historial</span>
        </a>

        <a href="/clientes.php" class="nav-item <?= $activeMenu==='clientes'?'active':'' ?>">
            <span class="nav-icon"><i class="fas fa-address-book"></i></span>
            <span class="nav-label">Clientes</span>
        </a>

        <div class="nav-section-title">Cierres</div>
        <a href="/cierre.php" class="nav-item <?= $activeMenu==='cierre'?'active':'' ?>">
            <span class="nav-icon"><i class="fas fa-calendar-check"></i></span>
            <span class="nav-label">Cierre del Día</span>
        </a>
        <a href="/cierres.php" class="nav-item <?= $activeMenu==='cierres'?'active':'' ?>">
            <span class="nav-icon"><i class="fas fa-book"></i></span>
            <span class="nav-label">Historial Cierres</span>
        </a>

        <?php if ($isAdmin): ?>
        <div class="nav-section-title">Administración</div>
        <a href="/reportes.php" class="nav-item <?= $activeMenu==='reportes'?'active':'' ?>">
            <span class="nav-icon"><i class="fas fa-chart-line"></i></span>
            <span class="nav-label">Reportes</span>
            <span class="nav-badge">Admin</span>
        </a>
        <a href="/trabajadores.php" class="nav-item <?= $activeMenu==='trabajadores'?'active':'' ?>">
            <span class="nav-icon"><i class="fas fa-users"></i></span>
            <span class="nav-label">Trabajadores</span>
            <span class="nav-badge">Admin</span>
        </a>
        <a href="/configuracion.php" class="nav-item <?= $activeMenu==='configuracion'?'active':'' ?>">
            <span class="nav-icon"><i class="fas fa-gear"></i></span>
            <span class="nav-label">Configuración</span>
            <span class="nav-badge">Admin</span>
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr($user['nombre'], 0, 1)) ?></div>
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($user['nombre']) ?></div>
            <div class="user-role"><?= $isAdmin ? '<i class="fas fa-shield-halved" style="color:var(--primary-light);margin-right:3px"></i> Administrador' : '<i class="fas fa-user" style="margin-right:3px"></i> Vendedor' ?></div>
        </div>
        <button class="btn-logout" onclick="logout()" title="Cerrar sesión">
            <i class="fas fa-right-from-bracket"></i>
        </button>
    </div>
</aside>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- ──── MAIN ──── -->
<main class="main-content">
    <header class="topbar">
        <button class="btn-menu" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <div class="topbar-left">
            <div class="topbar-title"><?= htmlspecialchars($pageTitle) ?></div>
        </div>
        <div class="topbar-right">
            <div id="bcvBadge" style="display:none;align-items:center;gap:5px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);border-radius:8px;padding:4px 10px;font-size:11px;font-weight:700;color:#6ee7b7;cursor:default" title="Tasa BCV oficial">
                <i class="fas fa-dollar-sign" style="font-size:10px"></i>
                <span id="bcvRate">—</span>
                <span style="font-size:9px;opacity:0.7;font-weight:500">BCV</span>
            </div>
            <div class="topbar-datetime" id="currentDateTime"></div>
            <?php if ($isAdmin): ?>
            <a href="/ventas.php" class="topbar-cta"><i class="fas fa-plus"></i><span class="hide-sm"> Nueva Venta</span></a>
            <?php endif; ?>
        </div>
    </header>

    <div class="page-content">
