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
            background-attachment: fixed; /* Fondo fijo para evitar distorsión */
            min-height: 100vh;
            height: auto; /* Permitir que el body crezca según el contenido */
            margin: 0;
            padding: 60px 0 60px 0; /* Padding reducido pero suficiente para no ocultarse */
            font-family: 'Poppins', sans-serif;
        }
        
        .wizard-container {
            max-width: 900px;
            margin: 0 auto; /* Sin margen superior adicional ya que el body tiene padding */
            padding: 20px 20px 60px 20px; /* Padding superior reducido para acercar el título */
            position: relative;
            z-index: 1;
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

        /* Estilos para análisis detallado de nombres */
        .characteristic-tag {
            display: inline-block;
            background: #882A57;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            margin: 2px;
        }

        .trait-connection {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 8px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .trait-connection:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        .matched-trait {
            background: #007bff;
            color: white;
            padding: 3px 8px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .connection-text {
            color: #495057;
            font-size: 14px;
            flex: 1;
            line-height: 1.4;
        }

        /* Animaciones para el análisis */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .name-analysis-card {
            animation: fadeInUp 0.5s ease-out;
        }

        /* Estilo específico para el contenedor de análisis */
        #nameAnalysisContainer {
            scroll-margin-top: 180px; /* Espacio masivo para evitar ocultarse */
            margin-top: 40px !important;
            margin-bottom: 40px !important;
            position: relative;
            z-index: 10;
        }
        
        /* Prevenir scroll automático no deseado */
        html {
            scroll-behavior: auto; /* Evitar scroll suave automático */
            height: 100%;
            background: linear-gradient(135deg, #ee6f92 0%, #8b5a83 100%);
            background-attachment: fixed;
        }
        
        /* Asegurar espacio suficiente en toda la página */
        .wizard-container * {
            scroll-margin-top: 150px;
        }
        
        /* Fondo adicional para páginas largas */
        html::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #ee6f92 0%, #8b5a83 100%);
            z-index: -1;
        }

        /* Responsive para análisis detallado */
        @media (max-width: 768px) {
            .trait-connection {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .matched-trait {
                margin-bottom: 5px;
            }
            
            .characteristic-tag {
                font-size: 11px;
                padding: 3px 10px;
            }
            
            #nameAnalysisContainer {
                scroll-margin-top: 120px;
            }
            
            body {
                padding-top: 50px; /* Padding reducido en móviles */
            }
            
            .wizard-container {
                padding: 15px 15px 40px 15px; /* Padding superior reducido en móviles */
            }
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
                'gem': { spanish: 'Gema', meaning: 'piedra preciosa', origin: 'latino' },
                'kay': { spanish: 'Kay', meaning: 'pura', origin: 'inglés' },
                'mae': { spanish: 'Mae', meaning: 'perla', origin: 'inglés' },
                'sue': { spanish: 'Sue', meaning: 'lirio', origin: 'inglés' },
                'ann': { spanish: 'Ana', meaning: 'gracia', origin: 'hebreo' },
                'amy': { spanish: 'Amy', meaning: 'amada', origin: 'francés' },
                'kim': { spanish: 'Kim', meaning: 'oro', origin: 'inglés' },
                'jen': { spanish: 'Jen', meaning: 'justa', origin: 'inglés' },
                'sam': { spanish: 'Sam', meaning: 'escuchada', origin: 'hebreo' },
                'max': { spanish: 'Max', meaning: 'la más grande', origin: 'latino' },
                'rio': { spanish: 'Río', meaning: 'río', origin: 'español' },
                'ava': { spanish: 'Ava', meaning: 'vida', origin: 'latino' },
                'ada': { spanish: 'Ada', meaning: 'noble', origin: 'germánico' },
                'ara': { spanish: 'Ara', meaning: 'altar', origin: 'latino' },
                'ari': { spanish: 'Ari', meaning: 'león', origin: 'hebreo' },
                'ash': { spanish: 'Ash', meaning: 'fresno', origin: 'inglés' },
                'bea': { spanish: 'Bea', meaning: 'felicidad', origin: 'latino' },
                'cam': { spanish: 'Cam', meaning: 'torcida', origin: 'escocés' },
                'dex': { spanish: 'Dex', meaning: 'diestra', origin: 'latino' },
                'eli': { spanish: 'Eli', meaning: 'ascensión', origin: 'hebreo' },
                'fox': { spanish: 'Fox', meaning: 'zorro', origin: 'inglés' },
                'gia': { spanish: 'Gia', meaning: 'gracia de Dios', origin: 'italiano' },
                'iris': { spanish: 'Iris', meaning: 'arcoíris', origin: 'griego' },
                'jade': { spanish: 'Jade', meaning: 'piedra de jade', origin: 'español' },
                'kira': { spanish: 'Kira', meaning: 'asesina', origin: 'japonés' },
                'luna': { spanish: 'Luna', meaning: 'luna', origin: 'latino' },
                'nova': { spanish: 'Nova', meaning: 'nueva', origin: 'latino' },
                'rain': { spanish: 'Rain', meaning: 'lluvia', origin: 'inglés' },
                'sage': { spanish: 'Sage', meaning: 'sabia', origin: 'latino' },
                'vera': { spanish: 'Vera', meaning: 'verdad', origin: 'latino' },
                'wren': { spanish: 'Wren', meaning: 'reyezuelo', origin: 'inglés' },
                'zara': { spanish: 'Zara', meaning: 'flor', origin: 'árabe' },
                'blue': { spanish: 'Blue', meaning: 'azul', origin: 'inglés' },
                'dawn': { spanish: 'Dawn', meaning: 'amanecer', origin: 'inglés' },
                'faye': { spanish: 'Faye', meaning: 'hada', origin: 'inglés' },
                'hope': { spanish: 'Hope', meaning: 'esperanza', origin: 'inglés' },
                'june': { spanish: 'June', meaning: 'junio', origin: 'latino' },
                'lake': { spanish: 'Lake', meaning: 'lago', origin: 'inglés' }
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
                'sage': { spanish: 'sabia', meaning: 'inteligente y reflexiva' },
                'sultry': { spanish: 'seductora', meaning: 'sensual y provocativa' },
                'velvet': { spanish: 'aterciopelada', meaning: 'suave y elegante' },
                'diamond': { spanish: 'diamante', meaning: 'brillante y valiosa' },
                'silk': { spanish: 'sedosa', meaning: 'suave y refinada' },
                'pearl': { spanish: 'perla', meaning: 'preciosa y elegante' },
                'golden': { spanish: 'dorada', meaning: 'radiante y valiosa' },
                'crystal': { spanish: 'cristalina', meaning: 'clara y transparente' },
                'crimson': { spanish: 'carmesí', meaning: 'intensa y apasionada' },
                'azure': { spanish: 'azul celeste', meaning: 'serena y celestial' },
                'emerald': { spanish: 'esmeralda', meaning: 'preciosa y natural' },
                'scarlet': { spanish: 'escarlata', meaning: 'vibrante y llamativa' },
                'amber': { spanish: 'ámbar', meaning: 'cálida y misteriosa' },
                'jade': { spanish: 'jade', meaning: 'serena y equilibrada' },
                'coral': { spanish: 'coral', meaning: 'vibrante y marina' },
                'violet': { spanish: 'violeta', meaning: 'mística y elegante' },
                'hotness': { spanish: 'calentura', meaning: 'atractivo y sensual' },
                'beauty': { spanish: 'belleza', meaning: 'hermosura y elegancia' },
                'magic': { spanish: 'mágica', meaning: 'encantadora y misteriosa' },
                'angel': { spanish: 'ángel', meaning: 'pura y celestial' },
                'goddess': { spanish: 'diosa', meaning: 'divina y poderosa' },
                'queen': { spanish: 'reina', meaning: 'majestuosa y dominante' },
                'princess': { spanish: 'princesa', meaning: 'elegante y real' },
                'fantasy': { spanish: 'fantasía', meaning: 'imaginativa y soñadora' },
                'dream': { spanish: 'sueño', meaning: 'aspiracional y deseada' },
                'love': { spanish: 'amor', meaning: 'amorosa y cariñosa' },
                'passion': { spanish: 'pasión', meaning: 'intensa y apasionada' },
                'desire': { spanish: 'deseo', meaning: 'deseada y atractiva' },
                'charm': { spanish: 'encanto', meaning: 'encantadora y cautivadora' },
                'grace': { spanish: 'gracia', meaning: 'elegante y refinada' },
                'elegance': { spanish: 'elegancia', meaning: 'sofisticada y distinguida' },
                'mystery': { spanish: 'misterio', meaning: 'enigmática y fascinante' },
                'seduction': { spanish: 'seducción', meaning: 'seductora y cautivante' },
                'temptation': { spanish: 'tentación', meaning: 'irresistible y provocativa' },
                'allure': { spanish: 'atractivo', meaning: 'magnética y fascinante' },
                'enchant': { spanish: 'encanto', meaning: 'hechizante y mágica' },
                'divine': { spanish: 'divina', meaning: 'celestial y perfecta' },
                'celestial': { spanish: 'celestial', meaning: 'angelical y etérea' },
                'radiant': { spanish: 'radiante', meaning: 'brillante y luminosa' },
                'luminous': { spanish: 'luminosa', meaning: 'brillante y resplandeciente' },
                'brilliant': { spanish: 'brillante', meaning: 'inteligente y deslumbrante' },
                'dazzling': { spanish: 'deslumbrante', meaning: 'impactante y brillante' },
                'stunning': { spanish: 'impresionante', meaning: 'espectacular y hermosa' },
                'gorgeous': { spanish: 'preciosa', meaning: 'hermosa y atractiva' },
                'beautiful': { spanish: 'hermosa', meaning: 'bella y atractiva' },
                'lovely': { spanish: 'encantadora', meaning: 'adorable y querida' },
                'fierce': { spanish: 'feroz', meaning: 'intensa y determinada' },
                'powerful': { spanish: 'poderosa', meaning: 'fuerte y dominante' },
                'strong': { spanish: 'fuerte', meaning: 'resistente y valiente' },
                'brave': { spanish: 'valiente', meaning: 'audaz y corajuda' },
                'fearless': { spanish: 'intrépida', meaning: 'sin miedo y audaz' },
                'confident': { spanish: 'confiada', meaning: 'segura de sí misma' },
                'daring': { spanish: 'atrevida', meaning: 'audaz y arriesgada' },
                'adventurous': { spanish: 'aventurera', meaning: 'exploradora y libre' },
                'rebel': { spanish: 'rebelde', meaning: 'independiente y libre' },
                'gentle': { spanish: 'gentil', meaning: 'dulce y cariñosa' },
                'tender': { spanish: 'tierna', meaning: 'delicada y amorosa' },
                'soft': { spanish: 'suave', meaning: 'delicada y gentil' },
                'delicate': { spanish: 'delicada', meaning: 'fina y elegante' },
                'precious': { spanish: 'preciosa', meaning: 'valiosa y querida' },
                'innocent': { spanish: 'inocente', meaning: 'pura y sincera' },
                'fresh': { spanish: 'fresca', meaning: 'natural y juvenil' },
                'natural': { spanish: 'natural', meaning: 'auténtica y genuina' }
            };

            const feminineInfo = feminineNames[feminineName.toLowerCase()] || 
                { spanish: feminineName, meaning: 'nombre único', origin: 'moderno' };
            const adjectiveInfo = adjectives[adjective.toLowerCase()] || 
                { spanish: adjective, meaning: 'característica especial' };

            // Obtener explicación detallada de la relación personal
            const relationExplanation = explainNameRelation(feminineName, adjective, userCharacteristics);

            return {
                feminine: feminineInfo,
                adjective: adjectiveInfo,
                relation: relationExplanation,
                fullName: username
            };
        }

        // Función inteligente para dividir el nombre en partes
        function analyzeName(username) {
            // Array de nombres cortos en inglés (nombres propios)
            const englishFeminineNames = [
                'zoe', 'eve', 'mia', 'sky', 'lea', 'ivy', 'ray', 'joy', 'lux', 'gem',
                'kay', 'mae', 'sue', 'ann', 'amy', 'kim', 'jen', 'sam', 'max', 'rio',
                'ava', 'ada', 'ara', 'ari', 'ash', 'bea', 'cam', 'dex', 'eli', 'fox',
                'gia', 'halo', 'iris', 'jade', 'kira', 'luna', 'nova', 'paige', 'rain', 'sage',
                'tara', 'vera', 'wren', 'zara', 'blue', 'dawn', 'faye', 'hope', 'june', 'lake'
            ];
            
            // Array de adjetivos/palabras descriptivas en inglés
            const englishAdjectives = [
                'fire', 'star', 'moon', 'wild', 'sweet', 'bold', 'pure', 'storm', 'rose', 'sage',
                'sultry', 'velvet', 'diamond', 'silk', 'pearl', 'golden', 'crystal', 'crimson', 'azure', 'emerald',
                'hotness', 'beauty', 'magic', 'angel', 'goddess', 'queen', 'princess', 'fantasy', 'dream', 'love',
                'passion', 'desire', 'charm', 'grace', 'elegance', 'mystery', 'seduction', 'temptation', 'allure', 'enchant',
                'divine', 'celestial', 'radiant', 'luminous', 'brilliant', 'dazzling', 'stunning', 'gorgeous', 'beautiful', 'lovely',
                'fierce', 'powerful', 'strong', 'brave', 'fearless', 'confident', 'bold', 'daring', 'adventurous', 'rebel',
                'gentle', 'tender', 'soft', 'delicate', 'precious', 'sweet', 'innocent', 'pure', 'fresh', 'natural'
            ];
            
            let feminineName = '';
            let adjective = '';
            
            const lowerUsername = username.toLowerCase();
            
            // Buscar coincidencia inteligente: nombre + adjetivo
            for (const name of englishFeminineNames) {
                if (lowerUsername.startsWith(name)) {
                    const remainingPart = lowerUsername.slice(name.length);
                    
                    // Verificar si la parte restante es un adjetivo conocido
                    for (const adj of englishAdjectives) {
                        if (remainingPart === adj) {
                            feminineName = name;
                            adjective = adj;
                            break;
                        }
                    }
                    
                    // Si encontramos una coincidencia exacta, salir del bucle
                    if (feminineName && adjective) {
                        break;
                    }
                    
                    // Si la parte restante no es un adjetivo conocido pero el nombre sí coincide
                    // usar la parte restante como adjetivo
                    if (remainingPart.length > 2) {
                        feminineName = name;
                        adjective = remainingPart;
                        break;
                    }
                }
            }
            
            // Fallback: buscar por adjetivos conocidos al final
            if (!feminineName || !adjective) {
                for (const adj of englishAdjectives) {
                    if (lowerUsername.endsWith(adj)) {
                        adjective = adj;
                        feminineName = lowerUsername.slice(0, lowerUsername.length - adj.length);
                        
                        // Verificar si la parte del nombre está en nuestra lista
                        if (englishFeminineNames.includes(feminineName)) {
                            break;
                        } else if (feminineName.length >= 2 && feminineName.length <= 6) {
                            // Aceptar nombres cortos aunque no estén en la lista
                            break;
                        }
                    }
                }
            }
            
            // Último fallback: división inteligente por posición
            if (!feminineName || !adjective) {
                // Buscar una división natural (mayúsculas en el medio pueden indicar división)
                const capitalMatch = username.match(/([a-z]+)([A-Z][a-z]+)/);
                if (capitalMatch) {
                    feminineName = capitalMatch[1].toLowerCase();
                    adjective = capitalMatch[2].toLowerCase();
                } else {
                    // División por mitad como último recurso
                    const midPoint = Math.ceil(username.length / 2);
                    feminineName = username.slice(0, midPoint).toLowerCase();
                    adjective = username.slice(midPoint).toLowerCase();
                }
            }
            
            return { feminine: feminineName, adjective: adjective };
        }

        // Función para explicar la relación detallada con las características del usuario
        function explainNameRelation(feminineName, adjective, userCharacteristics) {
            // Mapeo detallado de adjetivos con características y explicaciones
            const adjectiveConnections = {
                'fire': {
                    traits: ['atrevida', 'energetica', 'pasional', 'radiante', 'dramatica', 'intensa'],
                    explanation: 'Fire (Ardiente) representa pasión, energía y determinación',
                    connections: {
                        'atrevida': 'tu espíritu atrevido se refleja en el fuego interior',
                        'energetica': 'tu energía vibrante coincide con la intensidad del fuego',
                        'pasional': 'tu naturaleza pasional resuena con la llama ardiente',
                        'radiante': 'tu brillo natural se amplifica con la fuerza del fuego',
                        'dramatica': 'tu personalidad dramática encuentra su expresión en lo ardiente',
                        'intensa': 'tu intensidad emocional se materializa en el concepto de fuego'
                    }
                },
                'star': {
                    traits: ['brillante', 'radiante', 'glamourosa', 'elegante', 'unica', 'magnetica'],
                    explanation: 'Star (Estrella) simboliza brillo, unicidad y capacidad de destacar',
                    connections: {
                        'brillante': 'tu inteligencia brillante se refleja como una estrella en el cielo',
                        'radiante': 'tu energía radiante natural coincide con el brillo estelar',
                        'glamourosa': 'tu glamour natural te hace brillar como una estrella',
                        'elegante': 'tu elegancia innata te distingue como una estrella única',
                        'unica': 'tu individualidad especial te hace brillar entre las demás',
                        'magnetica': 'tu carisma magnético atrae como una estrella brillante'
                    }
                },
                'bold': {
                    traits: ['decidida', 'atrevida', 'valiente', 'aventurera', 'decidida', 'energetica'],
                    explanation: 'Bold (Audaz) representa coraje, determinación y valentía',
                    connections: {
                        'decidida': 'tu carácter decidido se expresa perfectamente en la audacia',
                        'atrevida': 'tu espíritu atrevido encuentra su voz en lo audaz',
                        'valiente': 'tu valentía natural se amplifica con la audacia',
                        'aventurera': 'tu alma aventurera resuena con el espíritu audaz',
                        'energetica': 'tu energía desbordante se canaliza en acciones audaces'
                    }
                },
                'sweet': {
                    traits: ['dulce', 'tierna', 'delicada', 'coqueta', 'amorosa', 'inocente'],
                    explanation: 'Sweet (Dulce) evoca ternura, delicadeza y encanto natural',
                    connections: {
                        'dulce': 'tu naturaleza dulce se refleja perfectamente en este adjetivo',
                        'tierna': 'tu ternura natural encuentra expresión en la dulzura',
                        'delicada': 'tu delicadeza se complementa con la suavidad de lo dulce',
                        'coqueta': 'tu coquetería encantadora se expresa en la dulzura',
                        'amorosa': 'tu capacidad de amar se materializa en la dulzura',
                        'inocente': 'tu inocencia natural se refleja en la pureza de lo dulce'
                    }
                },
                'moon': {
                    traits: ['misteriosa', 'seductora', 'nocturna', 'magnetica', 'mistica', 'elegante'],
                    explanation: 'Moon (Lunar) simboliza misterio, seducción y magnetismo nocturno',
                    connections: {
                        'misteriosa': 'tu aura misteriosa se amplifica con la magia lunar',
                        'seductora': 'tu poder de seducción resuena con el encanto de la luna',
                        'nocturna': 'tu espíritu nocturno encuentra su hogar en lo lunar',
                        'magnetica': 'tu magnetismo natural se potencia con la atracción lunar',
                        'mistica': 'tu esencia mística se conecta con los ciclos lunares',
                        'elegante': 'tu elegancia se sublima con la gracia lunar'
                    }
                },
                'sultry': {
                    traits: ['seductora', 'sensual', 'magnetica', 'misteriosa', 'pasional', 'intensa'],
                    explanation: 'Sultry (Seductora) representa sensualidad, magnetismo y poder de atracción',
                    connections: {
                        'seductora': 'tu poder de seducción natural se intensifica con lo seductor',
                        'sensual': 'tu sensualidad innata encuentra expresión en lo seductor',
                        'magnetica': 'tu magnetismo personal se amplifica con la seducción',
                        'misteriosa': 'tu misterio natural se potencia con el encanto seductor',
                        'pasional': 'tu naturaleza pasional se manifiesta en la seducción',
                        'intensa': 'tu intensidad emocional se canaliza a través de lo seductor'
                    }
                },
                'velvet': {
                    traits: ['elegante', 'sofisticada', 'delicada', 'refinada', 'sensual', 'lujosa'],
                    explanation: 'Velvet (Aterciopelada) evoca elegancia, suavidad y refinamiento',
                    connections: {
                        'elegante': 'tu elegancia natural se refleja en la suavidad del terciopelo',
                        'sofisticada': 'tu sofisticación encuentra expresión en lo aterciopelado',
                        'delicada': 'tu delicadeza se complementa con la textura suave',
                        'refinada': 'tu refinamiento se materializa en la calidad aterciopelada',
                        'sensual': 'tu sensualidad se expresa a través de la suavidad',
                        'lujosa': 'tu gusto por lo lujoso resuena con el terciopelo'
                    }
                },
                'diamond': {
                    traits: ['brillante', 'valiosa', 'fuerte', 'unica', 'radiante', 'preciosa'],
                    explanation: 'Diamond (Diamante) simboliza valor, brillantez y fortaleza',
                    connections: {
                        'brillante': 'tu inteligencia brillante resplandece como un diamante',
                        'valiosa': 'tu valor como persona se refleja en lo precioso del diamante',
                        'fuerte': 'tu fortaleza interior es tan sólida como un diamante',
                        'unica': 'tu unicidad es tan rara y preciosa como un diamante',
                        'radiante': 'tu energía radiante brilla como las facetas de un diamante',
                        'preciosa': 'tu naturaleza preciosa se materializa en el diamante'
                    }
                },
                'hotness': {
                    traits: ['sensual', 'seductora', 'atractiva', 'magnetica', 'pasional', 'intensa', 'ardiente'],
                    explanation: 'Hotness (Calentura) representa atractivo sensual, magnetismo y poder de seducción',
                    connections: {
                        'sensual': 'tu sensualidad natural se intensifica con la calentura',
                        'seductora': 'tu poder seductor se amplifica con lo ardiente',
                        'atractiva': 'tu atractivo natural se potencia con la calentura',
                        'magnetica': 'tu magnetismo personal se intensifica con lo ardiente',
                        'pasional': 'tu naturaleza pasional encuentra expresión en la calentura',
                        'intensa': 'tu intensidad emocional se manifiesta como calentura',
                        'ardiente': 'tu espíritu ardiente se refleja en la calentura'
                    }
                },
                'beauty': {
                    traits: ['hermosa', 'elegante', 'radiante', 'encantadora', 'preciosa', 'angelical'],
                    explanation: 'Beauty (Belleza) simboliza hermosura, elegancia y encanto natural',
                    connections: {
                        'hermosa': 'tu hermosura natural se refleja en la belleza pura',
                        'elegante': 'tu elegancia innata se materializa en la belleza',
                        'radiante': 'tu energía radiante forma parte de tu belleza',
                        'encantadora': 'tu encanto natural se expresa a través de la belleza',
                        'preciosa': 'tu naturaleza preciosa se manifiesta como belleza',
                        'angelical': 'tu esencia angelical se refleja en la belleza celestial'
                    }
                },
                'goddess': {
                    traits: ['poderosa', 'divina', 'majestuosa', 'dominante', 'regia', 'suprema'],
                    explanation: 'Goddess (Diosa) representa poder divino, majestuosidad y supremacía',
                    connections: {
                        'poderosa': 'tu poder natural se eleva al nivel de una diosa',
                        'divina': 'tu esencia divina se manifiesta como una diosa',
                        'majestuosa': 'tu majestuosidad natural te convierte en una diosa',
                        'dominante': 'tu presencia dominante refleja el poder de una diosa',
                        'regia': 'tu porte regio te posiciona como una diosa',
                        'suprema': 'tu naturaleza suprema se expresa como divinidad'
                    }
                }
            };

            // Obtener información del adjetivo
            const adjectiveInfo = adjectiveConnections[adjective.toLowerCase()] || {
                traits: [],
                explanation: `${adjective} representa una cualidad especial`,
                connections: {}
            };

            // Encontrar características que coinciden
            const matchingTraits = userCharacteristics.filter(userTrait => 
                adjectiveInfo.traits.some(adjectiveTrait => 
                    userTrait.toLowerCase().includes(adjectiveTrait) || 
                    adjectiveTrait.includes(userTrait.toLowerCase())
                )
            );

            return {
                selectedTraits: userCharacteristics,
                matchingTraits: matchingTraits,
                adjectiveExplanation: adjectiveInfo.explanation,
                connections: matchingTraits.map(trait => {
                    const connectionKey = adjectiveInfo.traits.find(adjTrait => 
                        trait.toLowerCase().includes(adjTrait) || adjTrait.includes(trait.toLowerCase())
                    );
                    return {
                        trait: trait,
                        connection: adjectiveInfo.connections[connectionKey] || `tu naturaleza ${trait} se complementa perfectamente con ${adjective.toLowerCase()}`
                    };
                }),
                fullExplanation: matchingTraits.length > 0 ? 
                    `Basándonos en tus características seleccionadas, ${adjective.toLowerCase()} es perfecto para ti` :
                    `Este adjetivo complementa tu personalidad única de manera especial`
            };
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

        // Función para mostrar el análisis detallado del nombre
        function showNameAnalysis(username) {
            const explanation = generateNameExplanation(username, selectedCharacteristics);
            const container = document.getElementById('nameAnalysisContainer');
            const content = document.getElementById('nameAnalysisContent');
            
            // Obtener las características del usuario
            const userCharacteristics = selectedCharacteristics || [];
            
            content.innerHTML = `
                <div style="text-align: center; margin-bottom: 20px;">
                    <h4 style="color: #882A57; margin: 0 0 15px 0; font-size: 20px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <span>✨</span> ¿Por qué <strong>${explanation.fullName}</strong> es perfecto para ti?
                    </h4>
                </div>
                
                <!-- Características seleccionadas por el usuario -->
                <div style="background: linear-gradient(135deg, #fff5f8, #f0f8ff); padding: 15px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #d4c5d9;">
                    <h5 style="color: #882A57; margin: 0 0 10px 0; display: flex; align-items: center; gap: 6px;">
                        <span>👤</span> Tus Características Seleccionadas
                    </h5>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        ${userCharacteristics.map(trait => `
                            <span style="background: #882A57; color: white; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 500;">
                                ${trait}
                            </span>
                        `).join('')}
                    </div>
                </div>
                
                <!-- Análisis del nombre -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div style="background: #f0f8ff; padding: 15px; border-radius: 10px; border-left: 4px solid #4A90E2;">
                        <strong style="color: #4A90E2; display: block; margin-bottom: 8px;">🌸 Nombre Base</strong>
                        <span style="font-size: 18px; font-weight: 700; color: #2c3e50; display: block;">${explanation.feminine.spanish}</span>
                        <small style="color: #666; display: block; margin-top: 5px;">
                            <strong>Significado:</strong> "${explanation.feminine.meaning}"<br>
                            <strong>Origen:</strong> ${explanation.feminine.origin}
                        </small>
                    </div>
                    
                    <div style="background: #fff0f5; padding: 15px; border-radius: 10px; border-left: 4px solid #ee6f92;">
                        <strong style="color: #ee6f92; display: block; margin-bottom: 8px;">💫 Adjetivo Personalizado</strong>
                        <span style="font-size: 18px; font-weight: 700; color: #2c3e50; display: block;">${explanation.adjective.spanish}</span>
                        <small style="color: #666; display: block; margin-top: 5px;">
                            <strong>Representa:</strong> ${explanation.adjective.meaning}
                        </small>
                    </div>
                </div>
                
                <!-- Conexión personalizada detallada -->
                <div style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); padding: 20px; border-radius: 12px; border: 1px solid #dee2e6;">
                    <h5 style="color: #495057; display: flex; align-items: center; gap: 8px; margin: 0 0 15px 0; font-size: 16px;">
                        <span>🔗</span> Conexión Personalizada
                    </h5>
                    
                    <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #28a745;">
                        <strong style="color: #28a745; display: block; margin-bottom: 8px;">
                            📊 ${explanation.relation.adjectiveExplanation}
                        </strong>
                    </div>
                    
                    ${explanation.relation.matchingTraits.length > 0 ? `
                        <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                            <h6 style="color: #28a745; margin: 0 0 10px 0; display: flex; align-items: center; gap: 6px;">
                                <span>✅</span> Características que Coinciden Perfectamente:
                            </h6>
                            ${explanation.relation.connections.map(connection => `
                                <div style="display: flex; align-items: center; gap: 10px; margin: 8px 0; padding: 10px; background: #f8f9fa; border-radius: 6px;">
                                    <span style="background: #007bff; color: white; padding: 3px 8px; border-radius: 15px; font-size: 12px; font-weight: 600;">
                                        ${connection.trait}
                                    </span>
                                    <span style="color: #6c757d;">→</span>
                                    <span style="color: #495057; font-size: 14px; flex: 1;">
                                        ${connection.connection}
                                    </span>
                                </div>
                            `).join('')}
                        </div>
                    ` : `
                        <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                            <p style="margin: 0; color: #6c757d; font-style: italic;">
                                ⭐ ${explanation.relation.fullExplanation}
                            </p>
                        </div>
                    `}
                    
                    <!-- Resumen final -->
                    <div style="background: linear-gradient(135deg, #882A57, #a64b73); color: white; padding: 15px; border-radius: 10px; text-align: center;">
                        <strong style="display: block; margin-bottom: 8px; font-size: 16px;">💖 Resumen Personal</strong>
                        <p style="margin: 0; font-size: 14px; line-height: 1.6;">
                            <strong>${explanation.fullName}</strong> combina la esencia de "${explanation.feminine.meaning}" con la fuerza de ser "${explanation.adjective.spanish}", 
                            creando una identidad digital que refleja auténticamente quien eres y las cualidades que más te representan.
                        </p>
                    </div>
                </div>
            `;
            
            // Actualizar el texto explicativo principal
            document.getElementById('usernameExplanation').innerHTML = `
                🎉 <strong>¡Perfecto!</strong> Has seleccionado <strong>${username}</strong>. Descubre por qué es ideal para ti:
            `;
            
            // Guardar la posición de scroll actual ANTES de mostrar el contenedor
            const currentScrollY = window.scrollY;
            
            // Mostrar el contenedor con animación
            container.style.display = 'block';
            container.style.opacity = '0';
            container.style.transform = 'translateY(-10px)';
            
            setTimeout(() => {
                container.style.transition = 'all 0.3s ease';
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
                
                // MANTENER la posición de scroll exactamente donde estaba
                // Esto previene que la página "salte" o se oculte
                setTimeout(() => {
                    window.scrollTo(0, currentScrollY);
                }, 50);
                
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
                        
                        div.addEventListener('click', function(e) {
                            // Prevenir cualquier comportamiento de scroll por defecto
                            e.preventDefault();
                            e.stopPropagation();
                            
                            document.querySelectorAll('.username-item').forEach(item => {
                                item.classList.remove('selected');
                            });
                            this.classList.add('selected');
                            selectedUsername = cleanName;
                            
                            // Mostrar explicación personalizada del nombre SIN cambiar scroll
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