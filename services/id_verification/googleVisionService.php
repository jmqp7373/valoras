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
        
        // Extraer número de cédula (patrón común: 8-10 dígitos)
        if (preg_match('/\b(\d{8,10})\b/', $text, $matches)) {
            $info['cedula'] = $matches[1];
        }
        
        // Extraer nombres (patrón: línea después de "NOMBRES" o "APELLIDOS")
        if (preg_match('/NOMBRES?\s*[:\n]?\s*([A-ZÁÉÍÓÚÑ\s]+)/i', $text, $matches)) {
            $info['nombres'] = trim($matches[1]);
        }
        
        if (preg_match('/APELLIDOS?\s*[:\n]?\s*([A-ZÁÉÍÓÚÑ\s]+)/i', $text, $matches)) {
            $info['apellidos'] = trim($matches[1]);
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
