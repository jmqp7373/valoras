<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restricción de Edad - Valora</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        body {
            background: linear-gradient(135deg, #ee6f92 0%, #8b5a83 100%);
            min-height: 100vh;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .restriction-container {
            max-width: 500px;
            margin: 20px;
            padding: 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .restriction-icon {
            font-size: 80px;
            margin-bottom: 20px;
            color: #ee6f92;
        }
        
        .restriction-title {
            font-size: 24px;
            font-weight: 600;
            color: #882A57;
            margin-bottom: 15px;
        }
        
        .restriction-message {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .restriction-highlight {
            background: #fff5f7;
            border: 2px solid #ee6f92;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
        }
        
        .restriction-highlight strong {
            color: #882A57;
            font-size: 18px;
        }
        
        .navigation-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 15px 30px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #ee6f92, #882A57);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(238, 111, 146, 0.3);
        }
        
        .btn-secondary {
            background: transparent;
            color: #882A57;
            border-color: #ee6f92;
        }
        
        .btn-secondary:hover {
            background: #ee6f92;
            color: white;
        }
        
        .legal-notice {
            font-size: 12px;
            color: #999;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            line-height: 1.5;
        }
        
        @media (max-width: 768px) {
            .restriction-container {
                margin: 10px;
                padding: 30px 20px;
            }
            
            .restriction-icon {
                font-size: 60px;
            }
            
            .restriction-title {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="restriction-container">
        <div class="restriction-icon">🔞</div>
        
        <h1 class="restriction-title">Contenido para Mayores de Edad</h1>
        
        <div class="restriction-message">
            Lo sentimos, pero <strong>Valora.vip</strong> es una plataforma exclusivamente para adultos mayores de edad.
        </div>
        
        <div class="restriction-highlight">
            <strong>⚠️ Debes tener 18 años o más para acceder</strong>
            <p style="margin: 10px 0 0; font-size: 14px; color: #666;">
                Esta restricción es obligatoria según las leyes vigentes y nuestros términos de servicio.
            </p>
        </div>
        
        <div class="navigation-buttons">
            <a href="login.php" class="btn btn-primary">
                🔑 Ir al Login (Mayores de 18)
            </a>
            
            <a href="javascript:history.back()" class="btn btn-secondary">
                ← Volver Atrás
            </a>
        </div>
        
        <div class="legal-notice">
            <strong>Aviso Legal:</strong> Al acceder a Valora.vip, confirmas que eres mayor de edad en tu jurisdicción 
            y que comprendes que el sitio contiene material destinado únicamente para adultos. 
            El acceso está restringido a personas menores de 18 años de acuerdo con las leyes aplicables.
        </div>
    </div>
    
    <script>
        // Limpiar cualquier dato sensible del historial
        if (window.history && window.history.replaceState) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        
        // Desactivar botón atrás en navegadores que lo permiten
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</body>
</html>