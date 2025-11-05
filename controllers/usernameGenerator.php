<?php
require_once __DIR__ . '/../config.php'; // La API Key se guarda en config.php

header('Content-Type: application/json; charset=utf-8');

$input = $_POST['prompt'] ?? '';

if (empty($input)) {
    echo json_encode(['error' => 'No se recibió texto para procesar.']);
    exit;
}

$data = [
    "model" => "gpt-4",
    "messages" => [
        ["role" => "system", "content" => "Eres un generador de nombres de usuario elegantes, originales y con un toque de sensualidad profesional. Devuelve exactamente cinco nombres únicos, fáciles de pronunciar y de recordar. Formato: cada nombre en una línea numerada (1. nombre, 2. nombre, etc.)"],
        ["role" => "user", "content" => "Genera 5 nombres de usuario basados en: " . $input]
    ],
    "temperature" => 0.85,
    "max_tokens" => 200
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