<?php
/**
 * Google Vision Service
 * 
 * Servicio para interactuar con Google Cloud Vision API
 * Funcionalidades:
 * - OCR (Detección de texto en documentos)
 * - Detección de rostros
 * 
 * @author Valora.vip
 * @version 1.0.0
 */

class GoogleVisionService {
    
    private $apiKey;
    private $apiUrl = 'https://vision.googleapis.com/v1/images:annotate';
    
    /**
     * Constructor
     * Obtiene la API Key desde configGoogleVision.php
     */
    public function __construct() {
        if (!defined('GOOGLE_VISION_API_KEY')) {
            throw new Exception('GOOGLE_VISION_API_KEY no está definida en configGoogleVision.php');
        }
        $this->apiKey = GOOGLE_VISION_API_KEY;
    }
    
    /**
     * Analizar múltiples imágenes de documento
     * 
     * @param array $imagePaths Array de rutas a las imágenes
     * @return array Resultado del análisis combinado
     */
    public function analyzeMultipleImages($imagePaths) {
        try {
            // Preparar solicitud para múltiples imágenes
            $requests = [];
            
            foreach ($imagePaths as $imagePath) {
                $imageData = file_get_contents($imagePath);
                $base64Image = base64_encode($imageData);
                
                $requests[] = [
                    'image' => [
                        'content' => $base64Image
                    ],
                    'features' => [
                        [
                            'type' => 'DOCUMENT_TEXT_DETECTION',
                            'maxResults' => 1
                        ],
                        [
                            'type' => 'FACE_DETECTION',
                            'maxResults' => 10
                        ]
                    ]
                ];
            }
            
            $requestData = ['requests' => $requests];
            $jsonRequest = json_encode($requestData);
            
            // Enviar solicitud con cURL
            $ch = curl_init($this->apiUrl . '?key=' . $this->apiKey);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequest);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonRequest)
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            // Verificar errores de conexión
            if ($curlError) {
                return [
                    'success' => false,
                    'error' => 'Error de conexión: ' . $curlError
                ];
            }
            
            // Verificar código HTTP
            if ($httpCode !== 200) {
                return [
                    'success' => false,
                    'error' => 'Error HTTP ' . $httpCode . ': ' . $response
                ];
            }
            
            // Decodificar respuesta
            $responseData = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'error' => 'Error al decodificar respuesta: ' . json_last_error_msg()
                ];
            }
            
            // Combinar textos detectados de todas las imágenes
            $combinedText = '';
            $totalFaces = 0;
            
            foreach ($responseData['responses'] as $index => $response) {
                if (isset($response['fullTextAnnotation']['text'])) {
                    $combinedText .= "\n=== CARA " . ($index + 1) . " ===\n";
                    $combinedText .= $response['fullTextAnnotation']['text'] . "\n";
                }
                
                if (isset($response['faceAnnotations'])) {
                    $totalFaces += count($response['faceAnnotations']);
                }
            }
            
            // Extraer información del texto combinado
            $documentInfo = $this->extractDocumentInfo($combinedText);
            
            return [
                'success' => true,
                'text' => trim($combinedText),
                'faceCount' => $totalFaces,
                'documentInfo' => $documentInfo,
                'rawResponse' => $responseData
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Analizar documento de identidad
     * 
     * @param string $base64Image Imagen codificada en base64
     * @return array Resultado del análisis
     */
    public function analyzeDocument($base64Image) {
        try {
            // Preparar solicitud
            $requestData = [
                'requests' => [
                    [
                        'image' => [
                            'content' => $base64Image
                        ],
                        'features' => [
                            [
                                'type' => 'DOCUMENT_TEXT_DETECTION',
                                'maxResults' => 1
                            ],
                            [
                                'type' => 'FACE_DETECTION',
                                'maxResults' => 10
                            ]
                        ]
                    ]
                ]
            ];
            
            $jsonRequest = json_encode($requestData);
            
            // Enviar solicitud con cURL
            $ch = curl_init($this->apiUrl . '?key=' . $this->apiKey);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequest);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonRequest)
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            // Verificar errores de conexión
            if ($curlError) {
                return [
                    'success' => false,
                    'error' => 'Error de conexión: ' . $curlError
                ];
            }
            
            // Verificar código HTTP
            if ($httpCode !== 200) {
                return [
                    'success' => false,
                    'error' => 'Error HTTP ' . $httpCode . ': ' . $response
                ];
            }
            
            // Decodificar respuesta
            $responseData = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'error' => 'Error al decodificar respuesta: ' . json_last_error_msg()
                ];
            }
            
            // Extraer texto detectado
            $detectedText = isset($responseData['responses'][0]['fullTextAnnotation']['text']) 
                ? $responseData['responses'][0]['fullTextAnnotation']['text'] 
                : '';
            
            // Extraer rostros detectados
            $faces = isset($responseData['responses'][0]['faceAnnotations']) 
                ? $responseData['responses'][0]['faceAnnotations'] 
                : [];
            
            $faceCount = count($faces);
            
            // Extraer información del documento
            $documentInfo = $this->extractDocumentInfo($detectedText);
            
            return [
                'success' => true,
                'text' => $detectedText,
                'faceCount' => $faceCount,
                'documentInfo' => $documentInfo,
                'rawResponse' => $responseData
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Extraer información estructurada del texto del documento
     * 
     * @param string $text Texto detectado por OCR
     * @return array Información extraída
     */
    private function extractDocumentInfo($text) {
        $info = [
            'nombres' => '',
            'apellidos' => '',
            'cedula' => '',
            'fechaNacimiento' => '',
            'fechaExpedicion' => '',
            'tipoDocumento' => ''
        ];
        
        // Detectar tipo de documento
        if (stripos($text, 'REPÚBLICA DE COLOMBIA') !== false || 
            stripos($text, 'CEDULA DE CIUDADANIA') !== false) {
            $info['tipoDocumento'] = 'Cédula de Ciudadanía';
        } elseif (stripos($text, 'PASAPORTE') !== false) {
            $info['tipoDocumento'] = 'Pasaporte';
        }
        
        // ========================================
        // EXTRACCIÓN MEJORADA DEL NÚMERO DE CÉDULA
        // ========================================
        $cedula = $this->extractCedulaNumber($text);
        if ($cedula) {
            $info['cedula'] = $cedula;
        }
        
        // ========================================
        // EXTRACCIÓN MEJORADA DE NOMBRES Y APELLIDOS
        // ========================================
        $nombres = $this->extractNombres($text);
        if ($nombres) {
            $info['nombres'] = $nombres;
        }
        
        $apellidos = $this->extractApellidos($text);
        if ($apellidos) {
            $info['apellidos'] = $apellidos;
        }
        
        // Extraer fechas (formato DD/MM/YYYY o DD-MM-YYYY)
        if (preg_match_all('/\b(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4})\b/', $text, $matches)) {
            if (isset($matches[1][0])) {
                $info['fechaNacimiento'] = $matches[1][0];
            }
            if (isset($matches[1][1])) {
                $info['fechaExpedicion'] = $matches[1][1];
            }
        }
        
        return $info;
    }
    
    /**
     * Extrae el número de cédula con algoritmo inteligente
     * 
     * Estrategia:
     * 1. Buscar número después de palabra clave "NUMERO" o "NÚMERO"
     * 2. Validar que no empiece con ceros (descarta seriales como 00731551)
     * 3. Validar longitud de cédula colombiana (7-10 dígitos)
     * 4. Si hay múltiples candidatos, elegir el más probable
     * 
     * @param string $text Texto extraído del documento
     * @return string Número de cédula o cadena vacía
     */
    private function extractCedulaNumber($text) {
        $candidates = [];
        
        // PRIORIDAD 1: Número después de "NUMERO" o "NÚMERO" (con o sin acento)
        // Captura formatos: "NUMERO 1.125.998.052" o "NUMERO 1125998052"
        if (preg_match('/N[UÚ]MERO\s+([0-9.]+)/i', $text, $matches)) {
            $numero = preg_replace('/[^0-9]/', '', $matches[1]); // Eliminar puntos/separadores
            if (strlen($numero) >= 7 && strlen($numero) <= 11 && $numero[0] !== '0') {
                $candidates['keyword_match'] = [
                    'value' => $numero,
                    'priority' => 100, // Máxima prioridad
                    'source' => 'Encontrado después de palabra clave NUMERO'
                ];
            }
        }
        
        // PRIORIDAD 2: Buscar todos los números de 7-11 dígitos en el texto
        preg_match_all('/\b(\d{7,11})\b/', $text, $allMatches);
        
        if (!empty($allMatches[1])) {
            foreach ($allMatches[1] as $numero) {
                // Filtrar números que NO son cédulas válidas
                
                // 1. Descartar si empieza con cero (seriales internos)
                if ($numero[0] === '0') {
                    continue;
                }
                
                // 2. Descartar si tiene más de 10 dígitos (no es cédula colombiana estándar)
                if (strlen($numero) > 10) {
                    continue;
                }
                
                // 3. Descartar fechas (formato YYYYMMDD como 20150808)
                if (preg_match('/^(19|20)\d{6}$/', $numero)) {
                    continue;
                }
                
                // 4. Calcular prioridad basada en longitud (8-10 dígitos es lo más común)
                $length = strlen($numero);
                $priority = 50;
                
                if ($length >= 8 && $length <= 10) {
                    $priority = 70; // Alta prioridad para longitud típica
                } elseif ($length == 7) {
                    $priority = 40; // Menor prioridad para 7 dígitos
                }
                
                // Añadir a candidatos si no existe uno con mayor prioridad
                $key = 'number_' . $numero;
                if (!isset($candidates[$key]) || $candidates[$key]['priority'] < $priority) {
                    $candidates[$key] = [
                        'value' => $numero,
                        'priority' => $priority,
                        'source' => 'Número válido de ' . $length . ' dígitos'
                    ];
                }
            }
        }
        
        // Si no se encontraron candidatos, retornar vacío
        if (empty($candidates)) {
            return '';
        }
        
        // Ordenar candidatos por prioridad (mayor a menor)
        usort($candidates, function($a, $b) {
            return $b['priority'] - $a['priority'];
        });
        
        // Retornar el candidato con mayor prioridad
        return $candidates[0]['value'];
    }
    
    /**
     * Extrae los apellidos del documento de forma inteligente
     * 
     * Estrategia:
     * 1. Buscar línea con palabra "APELLIDOS"
     * 2. Capturar solo la siguiente línea (no todo lo que sigue)
     * 3. Limpiar palabras clave que no son apellidos
     * 
     * @param string $text Texto extraído del documento
     * @return string Apellidos o cadena vacía
     */
    private function extractApellidos($text) {
        // Dividir el texto en líneas
        $lines = preg_split('/\r\n|\r|\n/', $text);
        
        $apellidos = '';
        $foundApellidosLabel = false;
        
        foreach ($lines as $index => $line) {
            $line = trim($line);
            
            // Buscar la línea que contiene "APELLIDOS"
            if (preg_match('/^APELLIDOS?\s*:?\s*$/i', $line)) {
                // Si "APELLIDOS" está solo en una línea, tomar la siguiente
                if (isset($lines[$index + 1])) {
                    $apellidos = trim($lines[$index + 1]);
                    $foundApellidosLabel = true;
                    break;
                }
            } elseif (preg_match('/^APELLIDOS?\s*:?\s+(.+)$/i', $line, $matches)) {
                // Si "APELLIDOS" está en la misma línea que el contenido
                $apellidos = trim($matches[1]);
                $foundApellidosLabel = true;
                break;
            }
        }
        
        if (!$foundApellidosLabel) {
            return '';
        }
        
        // Limpiar el contenido extraído
        $apellidos = $this->cleanNameField($apellidos);
        
        return $apellidos;
    }
    
    /**
     * Extrae los nombres del documento de forma inteligente
     * 
     * Estrategia similar a extractApellidos
     * 
     * @param string $text Texto extraído del documento
     * @return string Nombres o cadena vacía
     */
    private function extractNombres($text) {
        // Dividir el texto en líneas
        $lines = preg_split('/\r\n|\r|\n/', $text);
        
        $nombres = '';
        $foundNombresLabel = false;
        
        foreach ($lines as $index => $line) {
            $line = trim($line);
            
            // Buscar la línea que contiene "NOMBRES"
            if (preg_match('/^NOMBRES?\s*:?\s*$/i', $line)) {
                // Si "NOMBRES" está solo en una línea, tomar la siguiente
                if (isset($lines[$index + 1])) {
                    $nombres = trim($lines[$index + 1]);
                    $foundNombresLabel = true;
                    break;
                }
            } elseif (preg_match('/^NOMBRES?\s*:?\s+(.+)$/i', $line, $matches)) {
                // Si "NOMBRES" está en la misma línea que el contenido
                $nombres = trim($matches[1]);
                $foundNombresLabel = true;
                break;
            }
        }
        
        if (!$foundNombresLabel) {
            return '';
        }
        
        // Limpiar el contenido extraído
        $nombres = $this->cleanNameField($nombres);
        
        return $nombres;
    }
    
    /**
     * Limpia campos de nombre/apellido eliminando texto basura
     * 
     * @param string $field Campo a limpiar
     * @return string Campo limpio
     */
    private function cleanNameField($field) {
        // Lista de palabras/frases que NO son parte del nombre
        $stopWords = [
            'REPUBLICA DE COLOMBIA',
            'REPUBLICA DE',
            'REPÚBLICA DE COLOMBIA',
            'REPÚBLICA DE',
            'CEDULA DE CIUDADANIA',
            'CÉDULA DE CIUDADANÍA',
            'FIRMA',
            'COLOR BIA',
            'RESLIBLICA',
            'UBLICA',
            'BIA',
            'COLOMBIA S',
            'NOMBRES',
            'APELLIDOS'
        ];
        
        // Eliminar palabras basura
        foreach ($stopWords as $word) {
            $field = str_ireplace($word, '', $field);
        }
        
        // Limpiar espacios múltiples y caracteres especiales
        $field = preg_replace('/\s+/', ' ', $field);
        $field = trim($field);
        
        // Si el campo resultante solo tiene 1-2 caracteres, probablemente sea basura
        if (strlen($field) <= 2) {
            return '';
        }
        
        return $field;
    }
    
    /**
     * Validar documento
     * 
     * @param array $analysisResult Resultado del análisis
     * @return array Resultado de validación
     */
    public function validateDocument($analysisResult) {
        $errors = [];
        $warnings = [];
        
        // Validar que se detectó texto
        if (empty($analysisResult['text'])) {
            $errors[] = 'No se pudo leer texto del documento. Asegúrate de que la imagen sea clara.';
        }
        
        // Validar que se detectó al menos un rostro
        if ($analysisResult['faceCount'] === 0) {
            $warnings[] = 'No se detectó rostro en el documento.';
        } elseif ($analysisResult['faceCount'] > 1) {
            $warnings[] = 'Se detectaron múltiples rostros en la imagen.';
        }
        
        // Validar información del documento
        $docInfo = $analysisResult['documentInfo'];
        
        if (empty($docInfo['cedula'])) {
            $errors[] = 'No se pudo detectar el número de documento.';
        }
        
        if (empty($docInfo['tipoDocumento'])) {
            $warnings[] = 'No se pudo identificar el tipo de documento.';
        }
        
        $isValid = empty($errors);
        
        return [
            'valid' => $isValid,
            'errors' => $errors,
            'warnings' => $warnings,
            'message' => $isValid 
                ? 'Documento validado correctamente' 
                : 'El documento no pudo ser validado completamente'
        ];
    }
}
?>
