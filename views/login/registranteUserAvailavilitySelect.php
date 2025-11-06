<?php
session_start();

// Pool completo de características disponibles
$caracteristicasPool = [
    ['emoji' => '💋', 'nombre' => 'Sexy', 'trait' => 'sexy'],
    ['emoji' => '😄', 'nombre' => 'Divertida', 'trait' => 'divertida'],
    ['emoji' => '💎', 'nombre' => 'Elegante', 'trait' => 'elegante'],
    ['emoji' => '🌙', 'nombre' => 'Misteriosa', 'trait' => 'misteriosa'],
    ['emoji' => '🍯', 'nombre' => 'Dulce', 'trait' => 'dulce'],
    ['emoji' => '🔥', 'nombre' => 'Atrevida', 'trait' => 'atrevida'],
    ['emoji' => '🌿', 'nombre' => 'Natural', 'trait' => 'natural'],
    ['emoji' => '✨', 'nombre' => 'Glamourosa', 'trait' => 'glamourosa'],
    ['emoji' => '📚', 'nombre' => 'Intelectual', 'trait' => 'intelectual'],
    ['emoji' => '🗺️', 'nombre' => 'Aventurera', 'trait' => 'aventurera'],
    ['emoji' => '🎨', 'nombre' => 'Artística', 'trait' => 'artistica'],
    ['emoji' => '💪', 'nombre' => 'Deportiva', 'trait' => 'deportiva'],
    ['emoji' => '🎭', 'nombre' => 'Dramática', 'trait' => 'dramatica'],
    ['emoji' => '🌺', 'nombre' => 'Exótica', 'trait' => 'exotica'],
    ['emoji' => '⚡', 'nombre' => 'Energética', 'trait' => 'energetica'],
    ['emoji' => '🦋', 'nombre' => 'Delicada', 'trait' => 'delicada'],
    ['emoji' => '🔮', 'nombre' => 'Mística', 'trait' => 'mistica'],
    ['emoji' => '👑', 'nombre' => 'Regia', 'trait' => 'regia'],
    ['emoji' => '🌟', 'nombre' => 'Radiante', 'trait' => 'radiante'],
    ['emoji' => '🌹', 'nombre' => 'Romántica', 'trait' => 'romantica'],
    ['emoji' => '🎪', 'nombre' => 'Extrovertida', 'trait' => 'extrovertida'],
    ['emoji' => '🏖️', 'nombre' => 'Relajada', 'trait' => 'relajada'],
    ['emoji' => '🎯', 'nombre' => 'Decidida', 'trait' => 'decidida'],
    ['emoji' => '🌈', 'nombre' => 'Colorida', 'trait' => 'colorida'],
    ['emoji' => '🦄', 'nombre' => 'Única', 'trait' => 'unica'],
    ['emoji' => '🍓', 'nombre' => 'Fresca', 'trait' => 'fresca'],
    ['emoji' => '�', 'nombre' => 'Pasional', 'trait' => 'pasional'],
    ['emoji' => '🌊', 'nombre' => 'Fluida', 'trait' => 'fluida'],
    ['emoji' => '💫', 'nombre' => 'Magnética', 'trait' => 'magnetica'],
    ['emoji' => '🎵', 'nombre' => 'Musical', 'trait' => 'musical'],
    ['emoji' => '🍑', 'nombre' => 'Sensual', 'trait' => 'sensual'],
    ['emoji' => '🌸', 'nombre' => 'Tierna', 'trait' => 'tierna'],
    ['emoji' => '🍷', 'nombre' => 'Sofisticada', 'trait' => 'sofisticada'],
    ['emoji' => '🎀', 'nombre' => 'Coqueta', 'trait' => 'coqueta'],
    ['emoji' => '🌻', 'nombre' => 'Alegre', 'trait' => 'alegre'],
    ['emoji' => '🍀', 'nombre' => 'Afortunada', 'trait' => 'afortunada'],
    ['emoji' => '🎈', 'nombre' => 'Juguetona', 'trait' => 'juguetona'],
    ['emoji' => '🌙', 'nombre' => 'Nocturna', 'trait' => 'nocturna'],
    ['emoji' => '☀️', 'nombre' => 'Radiosa', 'trait' => 'radiosa'],
    ['emoji' => '🍃', 'nombre' => 'Libre', 'trait' => 'libre'],
    ['emoji' => '💃', 'nombre' => 'Bailarina', 'trait' => 'bailarina'],
    ['emoji' => '🎤', 'nombre' => 'Cantante', 'trait' => 'cantante'],
    ['emoji' => '📸', 'nombre' => 'Fotogénica', 'trait' => 'fotogenica'],
    ['emoji' => '🍒', 'nombre' => 'Dulce como cereza', 'trait' => 'dulcecereza'],
    ['emoji' => '🌷', 'nombre' => 'Primaveral', 'trait' => 'primaveral'],
    ['emoji' => '🎊', 'nombre' => 'Festiva', 'trait' => 'festiva'],
    ['emoji' => '💝', 'nombre' => 'Amorosa', 'trait' => 'amorosa'],
    ['emoji' => '🍯', 'nombre' => 'Melosa', 'trait' => 'melosa'],
    ['emoji' => '🌺', 'nombre' => 'Tropical', 'trait' => 'tropical'],
    ['emoji' => '🎨', 'nombre' => 'Creativa', 'trait' => 'creativa'],
    ['emoji' => '🦢', 'nombre' => 'Elegante como cisne', 'trait' => 'elegantecisne'],
    ['emoji' => '🍊', 'nombre' => 'Vibrante', 'trait' => 'vibrante'],
    ['emoji' => '🌿', 'nombre' => 'Ecológica', 'trait' => 'ecologica'],
    ['emoji' => '🎹', 'nombre' => 'Melódica', 'trait' => 'melodica'],
    ['emoji' => '🍰', 'nombre' => 'Golosa', 'trait' => 'golosa'],
    ['emoji' => '🌼', 'nombre' => 'Inocente', 'trait' => 'inocente'],
    ['emoji' => '🎭', 'nombre' => 'Actriz', 'trait' => 'actriz'],
    ['emoji' => '🏵️', 'nombre' => 'Premiada', 'trait' => 'premiada'],
    ['emoji' => '🌟', 'nombre' => 'Estrella', 'trait' => 'estrella'],
    ['emoji' => '🍎', 'nombre' => 'Tentadora', 'trait' => 'tentadora'],
    ['emoji' => '🎪', 'nombre' => 'Circense', 'trait' => 'circense'],
    ['emoji' => '🌋', 'nombre' => 'Volcánica', 'trait' => 'volcanica'],
    ['emoji' => '🍾', 'nombre' => 'Celebradora', 'trait' => 'celebradora'],
    ['emoji' => '🎯', 'nombre' => 'Precisa', 'trait' => 'precisa'],
    ['emoji' => '🌅', 'nombre' => 'Matutina', 'trait' => 'matutina'],
    ['emoji' => '🍭', 'nombre' => 'Dulce como caramelo', 'trait' => 'dulcecaramelo'],
    ['emoji' => '🎨', 'nombre' => 'Bohemia', 'trait' => 'bohemia'],
    ['emoji' => '🌪️', 'nombre' => 'Torbellino', 'trait' => 'torbellino'],
    ['emoji' => '🍀', 'nombre' => 'Suertuda', 'trait' => 'suertuda'],
    ['emoji' => '🎈', 'nombre' => 'Espontánea', 'trait' => 'espontanea'],
    ['emoji' => '🌺', 'nombre' => 'Hawaiana', 'trait' => 'hawaiana'],
    ['emoji' => '🍑', 'nombre' => 'Provocativa', 'trait' => 'provocativa'],
    ['emoji' => '🎪', 'nombre' => 'Entretenida', 'trait' => 'entretenida'],
    ['emoji' => '🌙', 'nombre' => 'Soñadora', 'trait' => 'sonadora'],
    ['emoji' => '💎', 'nombre' => 'Valiosa', 'trait' => 'valiosa'],
    ['emoji' => '🍓', 'nombre' => 'Deliciosa', 'trait' => 'deliciosa'],
    ['emoji' => '🎭', 'nombre' => 'Versátil', 'trait' => 'versatil'],
    ['emoji' => '🌻', 'nombre' => 'Luminosa', 'trait' => 'luminosa'],
    ['emoji' => '🍒', 'nombre' => 'Irresistible', 'trait' => 'irresistible'],
    ['emoji' => '🎨', 'nombre' => 'Inspiradora', 'trait' => 'inspiradora'],
    ['emoji' => '🌈', 'nombre' => 'Multicolor', 'trait' => 'multicolor'],
    ['emoji' => '🍯', 'nombre' => 'Adictiva', 'trait' => 'adictiva'],
    ['emoji' => '🎪', 'nombre' => 'Espectacular', 'trait' => 'espectacular'],
    ['emoji' => '🌺', 'nombre' => 'Paradisíaca', 'trait' => 'paradisiaca'],
    ['emoji' => '💫', 'nombre' => 'Cósmica', 'trait' => 'cosmica'],
    ['emoji' => '🍑', 'nombre' => 'Apetecible', 'trait' => 'apetecible'],
    ['emoji' => '🎭', 'nombre' => 'Teatral', 'trait' => 'teatral'],
    ['emoji' => '🌙', 'nombre' => 'Seductora', 'trait' => 'seductora'],
    ['emoji' => '💎', 'nombre' => 'Brillante', 'trait' => 'brillante'],
    ['emoji' => '🍓', 'nombre' => 'Tentación', 'trait' => 'tentacion'],
    ['emoji' => '🎨', 'nombre' => 'Conceptual', 'trait' => 'conceptual'],
    ['emoji' => '🌻', 'nombre' => 'Soleada', 'trait' => 'soleada'],
    ['emoji' => '🍒', 'nombre' => 'Pecaminosa', 'trait' => 'pecaminosa'],
    ['emoji' => '🎭', 'nombre' => 'Camaleón', 'trait' => 'camaleon'],
    ['emoji' => '🌈', 'nombre' => 'Fantástica', 'trait' => 'fantastica'],
    ['emoji' => '🍯', 'nombre' => 'Embriagadora', 'trait' => 'embriagadora'],
    ['emoji' => '🎪', 'nombre' => 'Circunstancial', 'trait' => 'circunstancial'],
    ['emoji' => '🌺', 'nombre' => 'Floral', 'trait' => 'floral'],
    ['emoji' => '💫', 'nombre' => 'Galáctica', 'trait' => 'galactica'],
    ['emoji' => '🍑', 'nombre' => 'Jugosa', 'trait' => 'jugosa'],
    ['emoji' => '🎭', 'nombre' => 'Performática', 'trait' => 'performatica'],
    ['emoji' => '🌙', 'nombre' => 'Lunar', 'trait' => 'lunar'],
    ['emoji' => '💎', 'nombre' => 'Preciosa', 'trait' => 'preciosa'],
    ['emoji' => '🍓', 'nombre' => 'Sabrosura', 'trait' => 'sabrosura']
];

// Función para generar 12 características aleatorias
function generarCaracteristicas($pool) {
    $caracteristicasAleatorias = $pool;
    shuffle($caracteristicasAleatorias);
    return array_slice($caracteristicasAleatorias, 0, 12);
}

// Verificar si es un refresh o usuario nuevo
if (isset($_GET['refresh']) || !isset($_SESSION['caracteristicas_usuario'])) {
    $_SESSION['caracteristicas_usuario'] = generarCaracteristicas($caracteristicasPool);
    $_SESSION['session_id'] = session_id();
}

$caracteristicasActuales = $_SESSION['caracteristicas_usuario'];

// Validación: Asegurar que hay características disponibles
if (empty($caracteristicasActuales) || !is_array($caracteristicasActuales)) {
    $_SESSION['caracteristicas_usuario'] = generarCaracteristicas($caracteristicasPool);
    $caracteristicasActuales = $_SESSION['caracteristicas_usuario'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Tu Perfil - Valora</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        body {
            background: linear-gradient(135deg, #ee6f92 0%, #8b5a83 100%);
            min-height: 100vh;
            margin: 0;
            padding-top: 20px;
            font-family: 'Poppins', sans-serif;
        }
        
        .wizard-container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .wizard-header {
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }
        
        .wizard-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .wizard-header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .wizard-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
        }
        
        .step {
            display: flex;
            align-items: center;
            color: white;
            font-size: 14px;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            font-weight: 600;
        }
        
        .step.active .step-number {
            background: white;
            color: #882A57;
        }
        
        .step:not(:last-child)::after {
            content: '→';
            margin: 0 20px;
            opacity: 0.7;
        }
        
        .block {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        
        .block-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .block-icon {
            font-size: 24px;
            margin-right: 12px;
        }
        
        .block-title {
            font-size: 18px;
            font-weight: 600;
            color: #882A57;
            margin: 0;
        }
        
        .characteristics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-top: 15px;
        }
        
        .characteristic-item {
            padding: 12px 15px;
            border: 2px solid #f0f0f0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            font-size: 14px;
        }
        
        .characteristic-item:hover {
            border-color: #ee6f92;
            background: #fdf7f9;
        }
        
        .characteristic-item.selected {
            border-color: #882A57;
            background: #882A57;
            color: white;
        }
        
        .username-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 15px;
        }
        
        /* Responsive grid para 10 elementos */
        @media (min-width: 480px) {
            .username-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (min-width: 768px) {
            .username-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 15px;
            }
        }
        
        @media (min-width: 1024px) {
            .username-grid {
                grid-template-columns: repeat(5, 1fr);
                max-width: 100%;
            }
        }
        
        .username-item {
            padding: 15px 10px;
            border: 2px solid #f0f0f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            position: relative;
            min-height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            word-wrap: break-word;
            word-break: break-word;
        }
        
        .username-item:hover {
            border-color: #ee6f92;
            transform: translateY(-2px);
        }
        
        .username-item.selected {
            border-color: #882A57;
            background: #882A57;
            color: white;
        }
        
        .username-text {
            font-weight: 600;
            font-size: 14px;
            line-height: 1.2;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Responsive para nombres más largos */
        @media (max-width: 768px) {
            .username-text {
                font-size: 13px;
            }
        }
        
        .availability-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 15px;
        }
        
        .platform-check {
            padding: 20px;
            border: 2px solid #f0f0f0;
            border-radius: 12px;
            text-align: center;
        }
        
        .platform-check.available {
            border-color: #28a745;
            background: #f8fff9;
        }
        
        .platform-check.unavailable {
            border-color: #dc3545;
            background: #fff8f8;
        }
        
        .platform-name {
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .platform-status {
            font-size: 14px;
        }
        
        .continue-btn {
            background: linear-gradient(135deg, #ee6f92 0%, #8b5a83 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            margin: 30px auto 0;
            min-width: 200px;
        }
        
        .continue-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .continue-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .refresh-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 15px auto;
            min-width: 180px;
            justify-content: center;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .refresh-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .refresh-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(118, 75, 162, 0.4);
            background: linear-gradient(135deg, #764ba2 0%, #667eea 50%, #f093fb 100%);
        }
        
        .refresh-btn:hover::before {
            left: 100%;
        }
        
        .refresh-btn:hover .icon {
            transform: rotate(90deg);
        }
        
        .refresh-btn .icon {
            font-size: 16px;
            transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            display: inline-block;
        }
        
        .refresh-btn:active {
            transform: translateY(-1px) scale(0.98);
            box-shadow: 0 6px 20px rgba(118, 75, 162, 0.3);
        }
        
        .refresh-btn.loading {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            animation: pulseGlow 2s ease-in-out infinite;
        }
        
        .refresh-btn.loading .icon {
            animation: elegantRotate 1.2s ease-in-out infinite;
        }
        
        @keyframes elegantRotate {
            0% { transform: rotate(0deg); }
            50% { transform: rotate(180deg) scale(1.1); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes pulseGlow {
            0%, 100% { 
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            }
            50% { 
                box-shadow: 0 8px 25px rgba(118, 75, 162, 0.5), 0 0 20px rgba(240, 147, 251, 0.3);
            }
        }
        
        .characteristics-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0 10px;
        }
        
        .characteristics-title {
            color: #882A57;
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }
        
        .session-info {
            background: rgba(255,255,255,0.8);
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 12px;
            color: #666;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .input-group {
            display: flex;
            gap: 12px;
            margin-bottom: 15px;
        }
        
        .input-group input {
            flex: 1;
            padding: 14px 16px;
            border: 2px solid #ee6f92;
            border-radius: 12px;
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
            background-color: #fafafa;
            transition: all 0.3s ease;
        }
        
        .input-group input:focus {
            outline: none;
            border-color: #882A57;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(136, 42, 87, 0.1);
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #882A57;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .navigation-menu {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 15px 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .nav-btn {
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 2px solid transparent;
        }
        
        .nav-btn.primary {
            background: linear-gradient(135deg, #ee6f92, #882A57);
            color: white;
        }
        
        .nav-btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(238, 111, 146, 0.3);
        }
        
        .nav-btn.secondary {
            background: white;
            color: #882A57;
            border-color: #ee6f92;
        }
        
        .nav-btn.secondary:hover {
            background: #ee6f92;
            color: white;
        }
        
        .nav-btn.tertiary {
            background: transparent;
            color: #666;
            border-color: #ddd;
        }
        
        .nav-btn.tertiary:hover {
            background: #f5f5f5;
            color: #333;
        }
        
        @media (max-width: 768px) {
            .navigation-menu {
                flex-direction: column;
                align-items: center;
            }
            
            .nav-btn {
                width: 100%;
                justify-content: center;
                max-width: 250px;
            }
        }
        

        
        .help-menu.show {
            display: block;
            animation: slideUp 0.3s ease;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

    </style>
</head>
<body>
    <div class="wizard-container">
        <div class="wizard-header">
            <h1>🌟 Crear Tu Perfil Perfecto</h1>
            <p>Descubre el nombre de usuario ideal para ti en 3 simples pasos</p>
            
            <!-- Información explicativa sobre nombres únicos -->
            <div class="info-box" style="background: linear-gradient(135deg, #fff9fc, #fef5f8); border: 2px solid #ee6f92; border-radius: 15px; padding: 15px; margin: 15px 0; text-align: left;">
                <h3 style="color: #882A57; margin: 0 0 10px 0; font-size: 16px; display: flex; align-items: center;">
                    <span style="margin-right: 8px;">🎯</span>
                    ¿Por qué necesitas un nombre único?
                </h3>
                <div style="color: #666; font-size: 14px; line-height: 1.5;">
                    <p style="margin: 0 0 8px 0;">
                        <strong>🔐 Para registrarte en Valora.vip</strong> necesitas un nombre <strong>completamente único</strong> y no usado por otra persona.
                    </p>
                    <p style="margin: 0 0 8px 0;">
                        <strong style="color: #882A57;">✨ Nuestro sistema IA</strong> genera nombres únicos, verifica disponibilidad en tiempo real en <strong>Chaturbate.com</strong> y <strong>Stripchat.com</strong>, y combina tu personalidad con nombres atractivos.
                    </p>
                    <div style="background: #f0f8ff; border-left: 4px solid #17a2b8; padding: 8px 12px; margin-top: 10px; border-radius: 0 8px 8px 0;">
                        <strong style="color: #17a2b8;">💡 Tip:</strong> Si un nombre no está disponible, puedes refrescar las opciones para obtener nuevas sugerencias.
                    </div>
                </div>
            </div>
        </div>
        
        <div class="wizard-steps">
            <div class="step active" id="step1">
                <div class="step-number">1</div>
                <span>Cuéntanos sobre ti</span>
            </div>
            <div class="step" id="step2">
                <div class="step-number">2</div>
                <span>IA crea sugerencias</span>
            </div>
            <div class="step" id="step3">
                <div class="step-number">3</div>
                <span>Verificar disponibilidad</span>
            </div>
        </div>

        <!-- Bloque 1: Características del Usuario -->
        <div class="block" id="block1">
            <div class="block-header">
                <div class="block-icon">👤</div>
                <h3 class="block-title">Cuéntanos sobre ti</h3>
            </div>
            
            <form id="characteristicsForm">
                <div class="input-group">
                    <input type="text" name="edad" placeholder="Tu edad (ej: 25)" required>
                </div>
                
                <!-- Información de sesión -->
                <div class="session-info">
                    ✨ Sesión activa: <?php echo substr($_SESSION['session_id'], 0, 8); ?>... | Características únicas generadas para ti
                </div>
                
                <div class="characteristics-header">
                    <h4 class="characteristics-title">Selecciona tus características:</h4>
                    <button type="button" class="refresh-btn" onclick="refreshCharacteristics()">
                        <span class="icon">🔄</span>
                        <span>Nuevas opciones</span>
                    </button>
                </div>
                
                <div class="characteristics-grid" id="characteristicsGrid">
                    <?php foreach ($caracteristicasActuales as $caracteristica): ?>
                        <div class="characteristic-item" data-trait="<?php echo htmlspecialchars($caracteristica['trait']); ?>">
                            <?php echo $caracteristica['emoji']; ?> <?php echo htmlspecialchars($caracteristica['nombre']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="submit" class="continue-btn" id="generateBtn">
                    🤖 Generar Nombres con IA
                </button>
                
                <div style="text-align: center; margin-top: 25px; color: #666; font-size: 14px;">
                    <div style="margin-bottom: 12px;">
                        ¿Ya tienes una cuenta? <a href="login.php" style="color: #882A57; text-decoration: none; font-weight: 500;">Inicia sesión</a>
                    </div>
                    <div>
                        <a href="password_reset.php" style="color: #882A57; text-decoration: none; font-weight: 500;">¿Olvidaste tu contraseña?</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Bloque 2: Sugerencias de IA -->
        <div class="block" id="block2" style="display: none;">
            <div class="block-header">
                <div class="block-icon">🤖</div>
                <h3 class="block-title">10 Sugerencias Personalizadas de IA</h3>
            </div>
            
            <div class="loading" id="loading" style="text-align: center; margin: 20px 0; display: none;">
                <div class="spinner"></div>
                <p>🤖 IA creando 10 nombres únicos: [Nombre femenino] + [Adjetivo atractivo]...</p>
            </div>
            
            <div id="suggestionsContainer" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                    <p id="usernameExplanation" style="color: #666; margin: 0; flex: 1; min-width: 200px;">
                        ✨ Nuestra IA ha creado <strong>10 nombres únicos</strong> combinando nombres femeninos cortos + adjetivos atractivos:
                    </p>
                    <button type="button" class="refresh-btn" onclick="refreshUsernames()" id="refreshUsernamesBtn">
                        <span class="icon">🔄</span>
                        <span>Nuevas opciones</span>
                    </button>
                </div>
                
                <!-- Área para mostrar la explicación personalizada del nombre -->
                <div id="nameAnalysisContainer" style="display: none; background: linear-gradient(135deg, #fff9fc, #fef5f8); border: 2px solid #ee6f92; border-radius: 15px; padding: 20px; margin-bottom: 20px;">
                    <div id="nameAnalysisContent"></div>
                </div>
                <div class="username-grid" id="usernameGrid">
                    <!-- Las sugerencias aparecerán aquí -->
                </div>
                
                <button type="button" class="continue-btn" id="checkAvailabilityBtn" style="display: none;">
                    🔍 Verificar Disponibilidad
                </button>
                
                <button type="button" class="continue-btn" id="backToStep1Btn" style="background: #6c757d; margin-top: 15px;">
                    ← Volver a Características
                </button>
                
                <div style="text-align: center; margin-top: 25px; color: #666; font-size: 14px;">
                    <div style="margin-bottom: 12px;">
                        ¿Ya tienes una cuenta? <a href="login.php" style="color: #882A57; text-decoration: none; font-weight: 500;">Inicia sesión</a>
                    </div>
                    <div>
                        <a href="password_reset.php" style="color: #882A57; text-decoration: none; font-weight: 500;">¿Olvidaste tu contraseña?</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bloque 3: Verificación de Disponibilidad -->
        <div class="block" id="block3" style="display: none;">
            <div class="block-header">
                <div class="block-icon">🔍</div>
                <h3 class="block-title">Disponibilidad en Plataformas</h3>
            </div>
            
            <p style="text-align: center; color: #666; margin-bottom: 20px;">
                Verificando disponibilidad de "<strong id="selectedUsername"></strong>" en las plataformas:
            </p>
            
            <div class="availability-grid" id="availabilityGrid">
                <div class="platform-check" id="valoraCheck">
                    <div class="platform-name">Valora.vip</div>
                    <div class="platform-status">Verificando...</div>
                </div>
                <div class="platform-check" id="chaturbateCheck">
                    <div class="platform-name">Chaturbate</div>
                    <div class="platform-status">Verificando...</div>
                </div>
                <div class="platform-check" id="stripchatCheck">
                    <div class="platform-name">Stripchat</div>
                    <div class="platform-status">Verificando...</div>
                </div>
            </div>
            
            <?php include_once '../../components/botonContinuar.php'; ?>
            <?php continueRegistrationButton(); ?>
            
            <button type="button" class="continue-btn" id="backToStep2Btn" style="background: #6c757d; margin-top: 15px;">
                ← Volver a Sugerencias
            </button>
            
            <div style="text-align: center; margin-top: 25px; color: #666; font-size: 14px;">
                <div style="margin-bottom: 12px;">
                    ¿Ya tienes una cuenta? <a href="login.php" style="color: #882A57; text-decoration: none; font-weight: 500;">Inicia sesión</a>
                </div>
                <div>
                    <a href="password_reset.php" style="color: #882A57; text-decoration: none; font-weight: 500;">¿Olvidaste tu contraseña?</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let selectedUsername = '';
        let selectedCharacteristics = [];

        // Función para analizar y explicar el nombre seleccionado
        function generateNameExplanation(username, userCharacteristics) {
            const nameParts = analyzeName(username);
            const feminineName = nameParts.feminine;
            const adjective = nameParts.adjective;
            
            // Traducciones y significados de nombres femeninos comunes
            const feminineNames = {
                'zoe': { spanish: 'Zoé', meaning: 'vida', origin: 'griego' },
                'eve': { spanish: 'Eva', meaning: 'viviente', origin: 'hebreo' },
                'mia': { spanish: 'Mía', meaning: 'mía/amada', origin: 'latino' },
                'sky': { spanish: 'Cielo', meaning: 'cielo', origin: 'inglés' },
                'lea': { spanish: 'Lea', meaning: 'pradera', origin: 'hebreo' },
                'ivy': { spanish: 'Hiedra', meaning: 'hiedra', origin: 'inglés' },
                'ray': { spanish: 'Rayo', meaning: 'rayo de luz', origin: 'inglés' },
                'joy': { spanish: 'Alegría', meaning: 'gozo', origin: 'inglés' },
                'lux': { spanish: 'Luz', meaning: 'luz', origin: 'latino' },
                'gem': { spanish: 'Gema', meaning: 'piedra preciosa', origin: 'latino' }
            };

            // Traducciones de adjetivos comunes
            const adjectives = {
                'fire': { spanish: 'ardiente', meaning: 'llena de pasión y energía' },
                'star': { spanish: 'estrella', meaning: 'brillante y destacada' },
                'moon': { spanish: 'lunar', meaning: 'misteriosa y seductora' },
                'wild': { spanish: 'salvaje', meaning: 'libre y aventurera' },
                'sweet': { spanish: 'dulce', meaning: 'tierna y encantadora' },
                'bold': { spanish: 'audaz', meaning: 'valiente y decidida' },
                'pure': { spanish: 'pura', meaning: 'natural y genuina' },
                'storm': { spanish: 'tormenta', meaning: 'intensa y poderosa' },
                'rose': { spanish: 'rosa', meaning: 'delicada y hermosa' },
                'sage': { spanish: 'sabia', meaning: 'inteligente y reflexiva' }
            };

            const feminineInfo = feminineNames[feminineName.toLowerCase()] || 
                { spanish: feminineName, meaning: 'nombre único', origin: 'moderno' };
            const adjectiveInfo = adjectives[adjective.toLowerCase()] || 
                { spanish: adjective, meaning: 'característica especial' };

            // Relacionar con características del usuario
            const relationExplanation = explainNameRelation(feminineName, adjective, userCharacteristics);

            return {
                feminine: feminineInfo,
                adjective: adjectiveInfo,
                relation: relationExplanation,
                fullName: username
            };
        }

        // Función para dividir el nombre en partes
        function analyzeName(username) {
            // Lógica simple para dividir - puede mejorarse
            const commonFeminineNames = ['zoe', 'eve', 'mia', 'sky', 'lea', 'ivy', 'ray', 'joy', 'lux', 'gem'];
            
            let feminineName = '';
            let adjective = '';
            
            for (const name of commonFeminineNames) {
                if (username.toLowerCase().startsWith(name)) {
                    feminineName = name;
                    adjective = username.slice(name.length);
                    break;
                }
            }
            
            // Fallback si no encuentra coincidencia
            if (!feminineName) {
                const midPoint = Math.ceil(username.length / 2);
                feminineName = username.slice(0, midPoint);
                adjective = username.slice(midPoint);
            }
            
            return { feminine: feminineName, adjective: adjective };
        }

        // Función para explicar la relación con las características del usuario
        function explainNameRelation(feminineName, adjective, characteristics) {
            const relations = {
                'fire': ['atrevida', 'energetica', 'pasional', 'radiante'],
                'star': ['brillante', 'radiante', 'glamourosa', 'elegante'],
                'moon': ['misteriosa', 'seductora', 'nocturna', 'magnetica'],
                'wild': ['aventurera', 'libre', 'natural', 'decidida'],
                'sweet': ['dulce', 'tierna', 'delicada', 'coqueta'],
                'bold': ['decidida', 'atrevida', 'valiente', 'dramatica'],
                'pure': ['natural', 'inocente', 'delicada', 'libre'],
                'storm': ['intensa', 'dramatica', 'poderosa', 'energetica'],
                'rose': ['romantica', 'elegante', 'delicada', 'sensual'],
                'sage': ['intelectual', 'reflexiva', 'sofisticada', 'mistica']
            };

            const relatedTraits = relations[adjective.toLowerCase()] || [];
            const matchingTraits = characteristics.filter(trait => 
                relatedTraits.some(related => trait.includes(related))
            );

            if (matchingTraits.length > 0) {
                const femInfo = feminineNames[feminineName.toLowerCase()] || { meaning: 'belleza' };
                return `Este nombre refleja perfectamente tu personalidad ${matchingTraits.join(', ')}, creando una identidad única que combina ${feminineName} (${femInfo.meaning}) con la esencia ${adjective.toLowerCase()} que seleccionaste.`;
            } else {
                return `Este nombre único combina ${feminineName} con ${adjective}, creando una identidad especial que refleja las características que seleccionaste en el paso anterior.`;
            }
        }

        // Asegurar que el DOM esté completamente cargado
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing event listeners...');
            initializeEventListeners();
        });

        function initializeEventListeners() {
            try {
                // Manejar selección de características
                const characteristicItems = document.querySelectorAll('.characteristic-item');
                console.log('Found', characteristicItems.length, 'characteristic items');
                
                characteristicItems.forEach((item, index) => {
                    item.addEventListener('click', function() {
                        console.log('Characteristic clicked:', this.dataset.trait);
                        this.classList.toggle('selected');
                        const trait = this.dataset.trait;
                        
                        if (this.classList.contains('selected')) {
                            selectedCharacteristics.push(trait);
                        } else {
                            selectedCharacteristics = selectedCharacteristics.filter(t => t !== trait);
                        }
                        console.log('Selected characteristics:', selectedCharacteristics);
                    });
                });

                // Resto de event listeners
                setupFormHandlers();
                
            } catch (error) {
                console.error('Error initializing event listeners:', error);
            }
        }

        function setupFormHandlers() {
            // Manejar validación de edad
            const edadInput = document.querySelector('input[name="edad"]');
            if (edadInput) {
                edadInput.addEventListener('input', function() {
                    const edad = parseInt(this.value);
                    
                    if (!isNaN(edad) && edad < 18) {
                        this.style.borderColor = '#dc3545';
                        this.style.backgroundColor = '#fff5f5';
                        
                        // Mostrar advertencia visual
                        let warning = document.getElementById('age-warning');
                        if (!warning) {
                            warning = document.createElement('div');
                            warning.id = 'age-warning';
                            warning.style.cssText = `
                                color: #dc3545;
                                font-size: 12px;
                                margin-top: 5px;
                                padding: 8px 12px;
                                background: #fff5f5;
                                border: 1px solid #dc3545;
                                border-radius: 6px;
                                font-weight: 500;
                            `;
                            warning.innerHTML = '⚠️ Debes tener 18 años o más para usar Valora.vip';
                            this.parentNode.appendChild(warning);
                        }
                    } else {
                        this.style.borderColor = '#ee6f92';
                        this.style.backgroundColor = '#fafafa';
                        
                        // Remover advertencia si existe
                        const warning = document.getElementById('age-warning');
                        if (warning) {
                            warning.remove();
                        }
                    }
                });
            }

        // Función para refrescar características con animación elegante
        function refreshCharacteristics() {
            const refreshBtn = document.querySelector('.refresh-btn');
            const icon = refreshBtn.querySelector('.icon');
            const text = refreshBtn.querySelector('span:not(.icon)');
            
            // Prevenir múltiples clics
            if (refreshBtn.disabled) return;
            
            // Estado de carga elegante
            refreshBtn.disabled = true;
            refreshBtn.classList.add('loading');
            
            // Cambiar texto y estilo
            text.textContent = 'Generando...';
            refreshBtn.style.background = 'linear-gradient(135deg, #17a2b8, #138496)';
            refreshBtn.style.transform = 'scale(0.98)';
            
            // Animación de las características actuales
            const currentItems = document.querySelectorAll('.characteristic-item');
            currentItems.forEach((item, index) => {
                setTimeout(() => {
                    item.style.transition = 'all 0.3s ease';
                    item.style.transform = 'scale(0.95)';
                    item.style.opacity = '0.6';
                }, index * 50);
            });
            
            // Limpiar selecciones actuales
            selectedCharacteristics = [];
            
            // Feedback visual adicional
            setTimeout(() => {
                refreshBtn.style.animation = 'pulseGlow 0.6s ease-in-out';
            }, 300);
            
            // Recargar página con parámetro refresh
            setTimeout(() => {
                window.location.href = window.location.pathname + '?refresh=1';
            }, 800);
        }

        // Validación de edad en tiempo real
        } // Fin de setupFormHandlers

        // Función para mostrar el análisis personalizado del nombre
        function showNameAnalysis(username) {
            const explanation = generateNameExplanation(username, selectedCharacteristics);
            const container = document.getElementById('nameAnalysisContainer');
            const content = document.getElementById('nameAnalysisContent');
            
            content.innerHTML = `
                <div style="text-align: center; margin-bottom: 15px;">
                    <h4 style="color: #882A57; margin: 0 0 10px 0; font-size: 18px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <span>✨</span> Análisis de tu nombre: <strong>${explanation.fullName}</strong>
                    </h4>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div style="background: #f0f8ff; padding: 12px; border-radius: 8px; border-left: 4px solid #4A90E2;">
                        <strong style="color: #4A90E2;">🌸 Nombre Femenino:</strong><br>
                        <span style="font-size: 16px; font-weight: 600;">${explanation.feminine.spanish}</span><br>
                        <small style="color: #666;">Significa: "${explanation.feminine.meaning}" (${explanation.feminine.origin})</small>
                    </div>
                    
                    <div style="background: #fff0f5; padding: 12px; border-radius: 8px; border-left: 4px solid #ee6f92;">
                        <strong style="color: #ee6f92;">💫 Adjetivo:</strong><br>
                        <span style="font-size: 16px; font-weight: 600;">${explanation.adjective.spanish}</span><br>
                        <small style="color: #666;">${explanation.adjective.meaning}</small>
                    </div>
                </div>
                
                <div style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); padding: 15px; border-radius: 10px; border: 1px solid #dee2e6;">
                    <strong style="color: #495057; display: flex; align-items: center; gap: 6px; margin-bottom: 8px;">
                        <span>🎯</span> ¿Por qué este nombre es perfecto para ti?
                    </strong>
                    <p style="margin: 0; color: #6c757d; font-size: 14px; line-height: 1.5;">
                        ${explanation.relation}
                    </p>
                </div>
            `;
            
            // Actualizar el texto explicativo principal
            document.getElementById('usernameExplanation').innerHTML = `
                🎉 <strong>¡Perfecto!</strong> Has seleccionado <strong>${username}</strong>. Aquí está el significado personalizado:
            `;
            
            // Mostrar el contenedor con animación
            container.style.display = 'block';
            container.style.opacity = '0';
            container.style.transform = 'translateY(-10px)';
            
            setTimeout(() => {
                container.style.transition = 'all 0.3s ease';
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            }, 10);
        }

        // Código movido a initializeEventListeners()

        // Manejar envío del formulario de características
        document.getElementById('characteristicsForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const edad = formData.get('edad');
            
            if (!edad || selectedCharacteristics.length === 0) {
                alert('Por favor completa tu edad y selecciona al menos una característica.');
                return;
            }
            
            // Validación de edad mínima (18 años)
            const edadNumerica = parseInt(edad);
            if (isNaN(edadNumerica) || edadNumerica < 18) {
                window.location.href = 'age_restriction.php';
                return;
            }

            // Mostrar paso 2 y ocultar paso 1
            document.getElementById('block1').style.display = 'none';
            document.getElementById('block2').style.display = 'block';
            document.getElementById('step1').classList.remove('active');
            document.getElementById('step2').classList.add('active');
            document.getElementById('loading').style.display = 'block';

            // Crear prompt específico para la IA
            const prompt = `Mujer de ${edad} años con características: ${selectedCharacteristics.join(', ')}. Necesito 10 nombres de usuario para webcam con formato: [nombre femenino corto de 3-5 letras] + [adjetivo sensual/atractivo]. Ejemplos: MiaFire, LunaWild, SofiaBold, AnaSiren, etc. Máximo 14 caracteres cada uno.`;

            try {
                const response = await fetch('../../controllers/login/usernameGenerator.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'prompt=' + encodeURIComponent(prompt)
                });

                const data = await response.json();

                if (data.error) {
                    throw new Error(data.error);
                }

                // Procesar respuesta de OpenAI - ahora esperamos 10 nombres
                const content = data.choices[0].message.content;
                const suggestions = content.split(/\d+\.\s*/).filter(name => name.trim()).slice(0, 10);

                // Ocultar loading y mostrar sugerencias
                document.getElementById('loading').style.display = 'none';
                document.getElementById('suggestionsContainer').style.display = 'block';

                // Mostrar sugerencias - grid para 10 nombres
                const usernameGrid = document.getElementById('usernameGrid');
                usernameGrid.innerHTML = '';

                suggestions.forEach((name) => {
                    let cleanName = name.trim().replace(/[^\w]/g, '');
                    
                    // Limitar a máximo 14 caracteres
                    if (cleanName.length > 14) {
                        cleanName = cleanName.substring(0, 14);
                    }
                    
                    if (cleanName && cleanName.length > 2) {
                        const div = document.createElement('div');
                        div.className = 'username-item';
                        div.innerHTML = `<div class="username-text">${cleanName}</div>`;
                        
                        div.addEventListener('click', function() {
                            document.querySelectorAll('.username-item').forEach(item => {
                                item.classList.remove('selected');
                            });
                            this.classList.add('selected');
                            selectedUsername = cleanName;
                            
                            // Mostrar explicación personalizada del nombre
                            showNameAnalysis(cleanName);
                            
                            document.getElementById('checkAvailabilityBtn').style.display = 'block';
                        });
                        
                        usernameGrid.appendChild(div);
                    }
                });

            } catch (error) {
                console.error('Error:', error);
                document.getElementById('loading').innerHTML = `
                    <p style="color: #dc3545;">❌ Error al generar sugerencias: ${error.message}</p>
                    <button type="button" onclick="location.reload()" class="continue-btn">Intentar Nuevamente</button>
                `;
            }
        });

        // Manejar verificación de disponibilidad
        document.getElementById('checkAvailabilityBtn').addEventListener('click', function() {
            if (!selectedUsername) return;

            // Mostrar paso 3 y ocultar paso 2
            document.getElementById('block2').style.display = 'none';
            document.getElementById('block3').style.display = 'block';
            document.getElementById('step2').classList.remove('active');
            document.getElementById('step3').classList.add('active');
            document.getElementById('selectedUsername').textContent = selectedUsername;

            // Verificar disponibilidad en todas las plataformas
            checkAllPlatformsAvailability(selectedUsername);
        });

        // Función para refrescar nombres de usuario (Paso 2)
        async function refreshUsernames() {
            const refreshBtn = document.getElementById('refreshUsernamesBtn');
            const icon = refreshBtn.querySelector('.icon');
            const text = refreshBtn.querySelector('span:not(.icon)');
            
            // Prevenir múltiples clicks
            if (refreshBtn.disabled) return;
            
            // Ocultar análisis de nombre anterior
            const analysisContainer = document.getElementById('nameAnalysisContainer');
            if (analysisContainer) {
                analysisContainer.style.display = 'none';
            }
            
            // Restaurar texto explicativo original
            document.getElementById('usernameExplanation').innerHTML = `
                ✨ Nuestra IA ha creado <strong>10 nombres únicos</strong> combinando nombres femeninos cortos + adjetivos atractivos:
            `;
            
            // Animación de inicio
            refreshBtn.disabled = true;
            refreshBtn.classList.add('loading');
            icon.textContent = '⏳';
            text.textContent = 'Generando...';
            refreshBtn.style.background = 'linear-gradient(135deg, #17a2b8, #138496)';
            refreshBtn.style.transform = 'scale(0.98)';
            
            // Animar los nombres existentes (fade out)
            const currentItems = document.querySelectorAll('.username-item');
            currentItems.forEach((item, index) => {
                setTimeout(() => {
                    item.style.transition = 'all 0.3s ease';
                    item.style.transform = 'scale(0.95)';
                    item.style.opacity = '0.6';
                }, index * 30);
            });
            
            // Limpiar selección actual
            selectedUsername = '';
            document.getElementById('checkAvailabilityBtn').style.display = 'none';
            
            // Obtener datos del formulario anterior
            const formData = new FormData(document.getElementById('characteristicsForm'));
            const edad = formData.get('edad');
            
            // Crear nuevo prompt con variación para obtener nombres diferentes
            const timestamp = Date.now();
            const prompt = `Mujer de ${edad} años con características: ${selectedCharacteristics.join(', ')}. IMPORTANTE: Genera 10 nombres COMPLETAMENTE DIFERENTES a los anteriores. Formato: [nombre femenino corto 3-5 letras] + [adjetivo sensual/atractivo]. Variación ${timestamp}. Ejemplos: EmmaFire, ZoeSiren, AvaBold, etc. Máximo 14 caracteres.`;

            try {
                await new Promise(resolve => setTimeout(resolve, 800)); // Pequeña pausa para la animación
                
                const response = await fetch('../../controllers/login/usernameGenerator.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'prompt=' + encodeURIComponent(prompt)
                });

                const data = await response.json();

                if (data.error) {
                    throw new Error(data.error);
                }

                // Procesar nueva respuesta de la IA
                const content = data.choices[0].message.content;
                const suggestions = content.split(/\d+\.\s*/).filter(name => name.trim()).slice(0, 10);

                // Actualizar grid con nuevos nombres (animación de entrada)
                const usernameGrid = document.getElementById('usernameGrid');
                usernameGrid.innerHTML = '';

                suggestions.forEach((name, index) => {
                    let cleanName = name.trim().replace(/[^\w]/g, '');
                    
                    // Limitar a máximo 14 caracteres
                    if (cleanName.length > 14) {
                        cleanName = cleanName.substring(0, 14);
                    }
                    
                    if (cleanName && cleanName.length > 2) {
                        const div = document.createElement('div');
                        div.className = 'username-item';
                        div.innerHTML = `<div class="username-text">${cleanName}</div>`;
                        div.style.opacity = '0';
                        div.style.transform = 'translateY(20px)';
                        
                        div.addEventListener('click', function() {
                            document.querySelectorAll('.username-item').forEach(item => {
                                item.classList.remove('selected');
                            });
                            this.classList.add('selected');
                            selectedUsername = cleanName;
                            document.getElementById('checkAvailabilityBtn').style.display = 'block';
                        });
                        
                        usernameGrid.appendChild(div);
                        
                        // Animación de entrada escalonada
                        setTimeout(() => {
                            div.style.transition = 'all 0.4s ease';
                            div.style.opacity = '1';
                            div.style.transform = 'translateY(0)';
                        }, index * 100);
                    }
                });

                // Restablecer botón
                setTimeout(() => {
                    refreshBtn.disabled = false;
                    refreshBtn.classList.remove('loading');
                    icon.textContent = '🔄';
                    text.textContent = 'Nuevas opciones';
                    refreshBtn.style.background = '';
                    refreshBtn.style.transform = '';
                    
                    // Efecto de éxito
                    refreshBtn.style.animation = 'pulseGlow 0.6s ease-in-out';
                }, 1000);

            } catch (error) {
                console.error('Error:', error);
                
                // Mostrar error y restaurar botón
                refreshBtn.disabled = false;
                refreshBtn.classList.remove('loading');
                icon.textContent = '❌';
                text.textContent = 'Error - Intentar de nuevo';
                refreshBtn.style.background = 'linear-gradient(135deg, #dc3545, #c82333)';
                
                // Restaurar después de 3 segundos
                setTimeout(() => {
                    icon.textContent = '🔄';
                    text.textContent = 'Nuevas opciones';
                    refreshBtn.style.background = '';
                    refreshBtn.style.transform = '';
                }, 3000);
            }
        }

        // Verificar disponibilidad en Valora
        async function checkValoraAvailability(username) {
            try {
                const response = await fetch('../../controllers/login/AuthController.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=check_username&username=' + encodeURIComponent(username)
                });
                
                const available = await response.text() === 'available';
                
                const valoraCheck = document.getElementById('valoraCheck');
                valoraCheck.className = available ? 'platform-check available' : 'platform-check unavailable';
                valoraCheck.querySelector('.platform-status').textContent = available ? '✅ Disponible' : '❌ No disponible';
                
            } catch (error) {
                const valoraCheck = document.getElementById('valoraCheck');
                valoraCheck.className = 'platform-check unavailable';
                valoraCheck.querySelector('.platform-status').textContent = '❌ Error verificando';
            }
        }

        // Función para verificar disponibilidad en todas las plataformas
        async function checkAllPlatformsAvailability(username) {
            let availableCount = 0;
            let totalPlatforms = 3;
            
            // Verificar Valora.vip (real)
            await checkValoraAvailability(username);
            
            // Simular verificación en Chaturbate (siempre disponible para demo)
            setTimeout(() => {
                const chaturbateCheck = document.getElementById('chaturbateCheck');
                chaturbateCheck.className = 'platform-check available';
                chaturbateCheck.querySelector('.platform-status').textContent = '✅ Disponible';
                
                checkIfCanContinue();
            }, 1000);
            
            // Simular verificación en Stripchat (siempre disponible para demo)  
            setTimeout(() => {
                const stripchatCheck = document.getElementById('stripchatCheck');
                stripchatCheck.className = 'platform-check available';
                stripchatCheck.querySelector('.platform-status').textContent = '✅ Disponible';
                
                checkIfCanContinue();
            }, 1500);
            
            // Función para verificar si se puede continuar
            function checkIfCanContinue() {
                const allChecks = document.querySelectorAll('.platform-check');
                const availableChecks = document.querySelectorAll('.platform-check.available');
                const unavailableChecks = document.querySelectorAll('.platform-check.unavailable');
                
                // Solo mostrar el botón si todas las verificaciones están completas
                if (availableChecks.length + unavailableChecks.length === totalPlatforms) {
                    const continueBtn = document.getElementById('continueBtn');
                    
                    if (availableChecks.length === totalPlatforms) {
                        // Todas las plataformas disponibles
                        continueBtn.style.display = 'block';
                        continueBtn.style.background = 'linear-gradient(135deg, #28a745, #20c997)';
                        continueBtn.innerHTML = '✅ Continuar con el Registro';
                        continueBtn.disabled = false;
                        continueBtn.style.cursor = 'pointer';
                        continueBtn.style.opacity = '1';
                    } else {
                        // Al menos una plataforma no disponible
                        continueBtn.style.display = 'block';
                        continueBtn.style.background = 'linear-gradient(135deg, #dc3545, #c82333)';
                        continueBtn.innerHTML = '❌ No se puede continuar - Username no disponible';
                        continueBtn.disabled = true;
                        continueBtn.style.cursor = 'not-allowed';
                        continueBtn.style.opacity = '0.7';
                        
                        // Agregar mensaje explicativo
                        let warningMsg = document.getElementById('availability-warning');
                        if (!warningMsg) {
                            warningMsg = document.createElement('div');
                            warningMsg.id = 'availability-warning';
                            warningMsg.style.cssText = `
                                background: #fff3cd;
                                border: 1px solid #ffeaa7;
                                border-radius: 8px;
                                padding: 15px;
                                margin-top: 20px;
                                color: #856404;
                                text-align: center;
                                font-size: 14px;
                            `;
                            warningMsg.innerHTML = `
                                <strong>⚠️ El nombre de usuario no está disponible en todas las plataformas.</strong><br>
                                Por favor, regresa y selecciona otro nombre de usuario.
                            `;
                            continueBtn.parentNode.insertBefore(warningMsg, continueBtn.nextSibling);
                        }
                    }
                }
            }
        }

        // Continuar al registro
        document.getElementById('continueBtn').addEventListener('click', function() {
            if (selectedUsername) {
                window.location.href = `register.php?suggested_username=${encodeURIComponent(selectedUsername)}`;
            }
        });

        // Botón volver del Paso 2 al Paso 1
        document.getElementById('backToStep1Btn').addEventListener('click', function() {
            // Ocultar paso 2 y mostrar paso 1
            document.getElementById('block2').style.display = 'none';
            document.getElementById('block1').style.display = 'block';
            
            // Actualizar indicadores de pasos
            document.getElementById('step2').classList.remove('active');
            document.getElementById('step1').classList.add('active');
            
            // Limpiar selección de username si existe
            selectedUsername = '';
            document.querySelectorAll('.username-item').forEach(item => {
                item.classList.remove('selected');
            });
            document.getElementById('checkAvailabilityBtn').style.display = 'none';
        });

        // Botón volver del Paso 3 al Paso 2
        document.getElementById('backToStep2Btn').addEventListener('click', function() {
            // Ocultar paso 3 y mostrar paso 2
            document.getElementById('block3').style.display = 'none';
            document.getElementById('block2').style.display = 'block';
            
            // Actualizar indicadores de pasos
            document.getElementById('step3').classList.remove('active');
            document.getElementById('step2').classList.add('active');
            
            // Ocultar el botón de continuar final
            document.getElementById('continueBtn').style.display = 'none';
        });


    </script>


</body>
</html>