<?php
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PrintShop — Iniciar Sesión</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #7c3aed;
            --primary-light: #8b5cf6;
            --secondary: #06b6d4;
            --accent: #f59e0b;
            --dark: #0f0f1a;
            --dark2: #1a1a2e;
            --card: rgba(255,255,255,0.05);
            --border: rgba(255,255,255,0.1);
            --text: #e2e8f0;
            --text-muted: #94a3b8;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated background */
        .bg-animated {
            position: fixed;
            inset: 0;
            background: linear-gradient(135deg, #0f0f1a 0%, #1a0a2e 50%, #0a1628 100%);
            z-index: 0;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            animation: float 8s ease-in-out infinite;
        }
        .orb-1 { width: 500px; height: 500px; background: rgba(124,58,237,0.2); top: -100px; left: -100px; animation-delay: 0s; }
        .orb-2 { width: 400px; height: 400px; background: rgba(6,182,212,0.15); bottom: -100px; right: -100px; animation-delay: -3s; }
        .orb-3 { width: 300px; height: 300px; background: rgba(245,158,11,0.1); top: 50%; left: 50%; transform: translate(-50%,-50%); animation-delay: -6s; }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-30px) scale(1.05); }
        }

        /* Grid pattern */
        .grid-pattern {
            position: fixed;
            inset: 0;
            background-image: linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: 0;
        }

        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
            padding: 24px;
        }

        .login-card {
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            padding: 48px 40px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5), 0 0 0 1px rgba(124,58,237,0.1);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .brand {
            text-align: center;
            margin-bottom: 36px;
        }

        .brand-logo {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            margin: 0 auto 16px;
            box-shadow: 0 8px 32px rgba(124,58,237,0.4);
            animation: pulse-logo 3s ease-in-out infinite;
        }

        @keyframes pulse-logo {
            0%, 100% { box-shadow: 0 8px 32px rgba(124,58,237,0.4); }
            50% { box-shadow: 0 8px 48px rgba(124,58,237,0.7); }
        }

        .brand-name {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, #a78bfa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }

        .brand-tagline {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 4px;
            font-weight: 400;
        }

        .form-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 4px;
        }

        .form-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 28px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 15px;
            transition: color 0.2s;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 14px 16px 14px 44px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            color: var(--text);
            transition: all 0.2s;
            outline: none;
        }

        .form-input::placeholder { color: rgba(148,163,184,0.5); }

        .form-input:focus {
            border-color: var(--primary-light);
            background: rgba(124,58,237,0.08);
            box-shadow: 0 0 0 3px rgba(124,58,237,0.15);
        }

        .form-input:focus + .input-icon,
        .input-wrapper:focus-within .input-icon {
            color: var(--primary-light);
        }

        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, var(--primary), #6d28d9);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 8px;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }

        .btn-login:hover::before { left: 100%; }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(124,58,237,0.5);
        }

        .btn-login:active { transform: translateY(0); }

        .btn-login.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            margin-top: 16px;
            display: none;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.2);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(16,185,129,0.1);
            border: 1px solid rgba(16,185,129,0.2);
            color: #6ee7b7;
        }

        .alert.show { display: flex; animation: fadeIn 0.3s ease; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Category pills */
        .category-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 24px;
            justify-content: center;
        }

        .pill {
            font-size: 10px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 500;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            color: var(--text-muted);
            letter-spacing: 0.3px;
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 14px;
            padding: 4px;
            transition: color 0.2s;
        }
        .toggle-password:hover { color: var(--text); }
    </style>
</head>
<body>
    <div class="bg-animated">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>
    <div class="grid-pattern"></div>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="brand">
                <div class="brand-logo"><i class="fas fa-print"></i></div>
                <div class="brand-name">PrintShop</div>
                <div class="brand-tagline">Sistema de Gestión de Ventas</div>
            </div>

            <div class="form-title">Bienvenido de vuelta</div>
            <div class="form-subtitle">Ingresa tus credenciales para continuar</div>

            <form id="loginForm" autocomplete="off">
                <div class="form-group">
                    <label class="form-label">Usuario</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" class="form-input" id="usuario" placeholder="Tu nombre de usuario" autocomplete="username" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-input" id="password" placeholder="Tu contraseña" autocomplete="current-password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="btnLogin">
                    <span id="btnText"><i class="fas fa-arrow-right-to-bracket"></i> &nbsp;Ingresar</span>
                </button>

                <div class="alert alert-error" id="alertError">
                    <i class="fas fa-circle-exclamation"></i>
                    <span id="errorMsg">Error al iniciar sesión</span>
                </div>
            </form>

            <div class="category-pills">
                <span class="pill">Sublimación</span>
                <span class="pill">DTF</span>
                <span class="pill">Vinil Textil</span>
                <span class="pill">Vinil de Corte</span>
                <span class="pill">Impresiones</span>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnLogin');
            const btnText = document.getElementById('btnText');
            const alertError = document.getElementById('alertError');

            btn.classList.add('loading');
            btnText.innerHTML = '<i class="fas fa-spinner fa-spin"></i> &nbsp;Verificando...';
            alertError.classList.remove('show');

            try {
                const res = await fetch('/api/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        usuario: document.getElementById('usuario').value,
                        password: document.getElementById('password').value
                    })
                });
                const data = await res.json();

                if (data.success) {
                    btnText.innerHTML = '<i class="fas fa-check"></i> &nbsp;Entrando...';
                    setTimeout(() => window.location.href = '/dashboard.php', 600);
                } else {
                    document.getElementById('errorMsg').textContent = data.error || 'Usuario o contraseña incorrectos';
                    alertError.classList.add('show');
                    btn.classList.remove('loading');
                    btnText.innerHTML = '<i class="fas fa-arrow-right-to-bracket"></i> &nbsp;Ingresar';
                }
            } catch {
                document.getElementById('errorMsg').textContent = 'Error de conexión. Intenta de nuevo.';
                alertError.classList.add('show');
                btn.classList.remove('loading');
                btnText.innerHTML = '<i class="fas fa-arrow-right-to-bracket"></i> &nbsp;Ingresar';
            }
        });
    </script>
</body>
</html>
