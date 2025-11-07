<?php
/**
 * Generador de nombres de usuario con IA - OpenAI GPT-4
 * Valora.vip - Sistema de sugerencias inteligentes
 */

// Verificar si existe el archivo de configuración de OpenAI
$configPath = __DIR__ . '/../../config/configOpenAiChatgpt.php';
if (!file_exists($configPath)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'error' => 'Configuración no encontrada. Por favor contacta al administrador.',
        'details' => 'El archivo config/configOpenAiChatgpt.php no existe. Copia config/configOpenAiChatgpt.example.php como config/configOpenAiChatgpt.php y configura tu API Key.'
    ]);
    exit;
}

require_once $configPath;

header('Content-Type: application/json; charset=utf-8');

$input = $_POST['prompt'] ?? '';

if (empty($input)) {
    echo json_encode(['error' => 'No se recibió texto para procesar.']);
    exit;
}

$data = [
    "model" => defined('AI_MODEL') ? AI_MODEL : "gpt-4",
    "messages" => [
        ["role" => "system", "content" => "Eres un experto generador de nombres de usuario para plataformas de webcam. Crea nombres compuestos ÚNICAMENTE por: un nombre femenino corto (3-5 letras) + un adjetivo sensual/atractivo. IMPORTANTE: Devuelve exactamente 10 nombres en formato numerado (1. nombre, 2. nombre, etc.). Ejemplos: MiaSiren, LunaFire, SofiaBold, etc. Máximo 14 caracteres cada nombre."],
        ["role" => "user", "content" => "Genera 10 nombres de usuario con estructura: [nombre femenino corto] + [adjetivo]. Basado en: " . $input]
    ],
    "temperature" => defined('AI_TEMPERATURE') ? AI_TEMPERATURE : 0.9,
    "max_tokens" => defined('AI_MAX_TOKENS') ? AI_MAX_TOKENS : 300
];

// Verificar que la API key esté configurada
if (!defined('OPENAI_API_KEY') || empty(OPENAI_API_KEY)) {
    echo json_encode(['error' => 'API Key de OpenAI no configurada.']);
    exit;
}

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer " . OPENAI_API_KEY
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Manejo de errores de cURL
if ($error) {
    echo json_encode(['error' => 'Error de conexión: ' . $error]);
    exit;
}

// Verificar código de respuesta HTTP
if ($httpCode !== 200) {
    echo json_encode(['error' => 'Error de API. Código: ' . $httpCode]);
    exit;
}

echo $response;
?>