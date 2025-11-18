<?php
/**
 * Script para actualizar todas las consultas que ahora necesitan JOIN con usuarios_info
 * Ejecutar después de la migración de usuarios a usuarios_info
 */

echo "<h1>📋 Archivos que necesitan actualización</h1>";
echo "<style>
    body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }
    h1, h2, h3 { color: #4ec9b0; }
    .file { background: #252526; padding: 15px; margin: 10px 0; border-left: 4px solid #4ec9b0; }
    .query { background: #2d2d30; padding: 10px; margin: 10px 0; border-radius: 5px; }
    .old { color: #f48771; }
    .new { color: #4ec9b0; }
    pre { overflow-x: auto; }
</style>";

$archivos_afectados = [
    [
        'archivo' => 'models/Usuario.php',
        'metodo' => 'obtenerPerfil()',
        'query_vieja' => 'SELECT * FROM usuarios WHERE id_usuario = :id_usuario',
        'query_nueva' => 'SELECT u.*, ui.* FROM usuarios u LEFT JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario WHERE u.id_usuario = :id_usuario',
        'descripcion' => 'Ahora necesita JOIN para obtener datos de usuarios_info'
    ],
    [
        'archivo' => 'models/Usuario.php',
        'metodo' => 'loginByIdentifier()',
        'query_vieja' => 'SELECT * FROM usuarios WHERE cedula/celular/usuario = :identificador',
        'query_nueva' => 'SELECT u.*, ui.* FROM usuarios u LEFT JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario WHERE ui.cedula/ui.celular/u.usuario = :identificador',
        'descripcion' => 'cedula y celular ahora están en usuarios_info'
    ],
    [
        'archivo' => 'models/Usuario.php',
        'metodo' => 'existsByCedula()',
        'query_vieja' => 'SELECT id_usuario FROM usuarios WHERE cedula = :cedula',
        'query_nueva' => 'SELECT u.id_usuario FROM usuarios u INNER JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario WHERE ui.cedula = :cedula',
        'descripcion' => 'cedula movida a usuarios_info'
    ],
    [
        'archivo' => 'models/Usuario.php',
        'metodo' => 'existsByEmail()',
        'query_vieja' => 'SELECT id_usuario FROM usuarios WHERE email = :email',
        'query_nueva' => 'SELECT u.id_usuario FROM usuarios u INNER JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario WHERE ui.email = :email',
        'descripcion' => 'email movido a usuarios_info'
    ],
    [
        'archivo' => 'models/Usuario.php',
        'metodo' => 'actualizarPerfil()',
        'query_vieja' => 'UPDATE usuarios SET ... WHERE id_usuario = :id_usuario',
        'query_nueva' => 'UPDATE usuarios_info SET ... WHERE id_usuario = :id_usuario (para campos de info)',
        'descripcion' => 'Los UPDATE deben ir a usuarios_info para la mayoría de campos'
    ],
    [
        'archivo' => 'controllers/PerfilController.php',
        'metodo' => 'actualizarPerfil()',
        'query_vieja' => 'Actualiza directamente en usuarios',
        'query_nueva' => 'Debe actualizar en usuarios_info para campos como celular, email, foto_perfil, etc.',
        'descripcion' => 'Separar UPDATE entre usuarios y usuarios_info'
    ],
    [
        'archivo' => 'controllers/login/AuthController.php',
        'metodo' => 'login()',
        'query_vieja' => 'Posiblemente usa SELECT * FROM usuarios',
        'query_nueva' => 'Necesita JOIN si accede a cedula o celular',
        'descripcion' => 'Verificar si necesita datos de usuarios_info para sesión'
    ],
    [
        'archivo' => 'views/ventas/ventasModelo.php',
        'linea' => '109, 127-129',
        'query_vieja' => 'Accede a cedula, email desde $usuarioInfo',
        'query_nueva' => 'Verificar que la consulta incluya JOIN con usuarios_info',
        'descripcion' => 'La vista muestra cedula y email'
    ],
    [
        'archivo' => 'controllers/VentasController.php',
        'metodo' => 'Consultas de usuarios/modelos',
        'query_vieja' => 'Posibles referencias a u.cedula, u.email',
        'query_nueva' => 'Debe usar ui.cedula, ui.email con JOIN',
        'descripcion' => 'Verificar todas las consultas que usen credenciales'
    ]
];

echo "<div class='file'>";
echo "<h2>🎯 PLAN DE ACTUALIZACIÓN</h2>";
echo "<p>Total de archivos a revisar: " . count($archivos_afectados) . "</p>";
echo "</div>";

foreach ($archivos_afectados as $i => $archivo) {
    echo "<div class='file'>";
    echo "<h3>" . ($i + 1) . ". " . $archivo['archivo'] . "</h3>";
    
    if (isset($archivo['metodo'])) {
        echo "<p><strong>Método:</strong> {$archivo['metodo']}</p>";
    }
    
    if (isset($archivo['linea'])) {
        echo "<p><strong>Líneas:</strong> {$archivo['linea']}</p>";
    }
    
    echo "<p><strong>Descripción:</strong> {$archivo['descripcion']}</p>";
    
    echo "<div class='query'>";
    echo "<p class='old'><strong>❌ Query anterior:</strong></p>";
    echo "<pre>{$archivo['query_vieja']}</pre>";
    echo "</div>";
    
    echo "<div class='query'>";
    echo "<p class='new'><strong>✅ Query nueva:</strong></p>";
    echo "<pre>{$archivo['query_nueva']}</pre>";
    echo "</div>";
    
    echo "</div>";
}

echo "<div class='file'>";
echo "<h2>📝 CAMPOS MIGRADOS A usuarios_info</h2>";
echo "<ul>";
$campos_migrados = [
    'disponibilidad', 'id_estudio', 'id_referente', 'codigo_pais',
    'celular', 'cedula', 'fecha_de_nacimiento', 'email', 'direccion',
    'ciudad', 'ref1_nombre', 'ref1_parentesco', 'ref1_celular',
    'info_medica', 'tipo_sangre', 'alergias', 'certificado_medico',
    'contacto_emergencia_nombre', 'contacto_emergencia_parentesco',
    'contacto_emergencia_telefono', 'banco_nombre', 'banco_tipo_cuenta',
    'banco_numero_cuenta', 'dias_descanso', 'url_entrevista',
    'inmune_asistencia', 'nivel_orden', 'foto_perfil', 'foto_con_cedula',
    'foto_cedula_frente', 'foto_cedula_reverso', 'notas', 'progreso_perfil'
];
foreach ($campos_migrados as $campo) {
    echo "<li><code>{$campo}</code></li>";
}
echo "</ul>";
echo "</div>";

echo "<div class='file'>";
echo "<h2>✅ CAMPOS QUE PERMANECEN EN usuarios</h2>";
echo "<ul>";
$campos_usuarios = ['id_usuario', 'fecha_creacion', 'usuario', 'nombres', 'apellidos', 'password', 'estado', 'id_rol'];
foreach ($campos_usuarios as $campo) {
    echo "<li><code>{$campo}</code></li>";
}
echo "</ul>";
echo "</div>";

echo "<div class='file'>";
echo "<h2>🔧 PRÓXIMOS PASOS</h2>";
echo "<ol>";
echo "<li>Actualizar <code>models/Usuario.php</code> para incluir JOINs</li>";
echo "<li>Actualizar <code>controllers/PerfilController.php</code> para separar UPDATEs</li>";
echo "<li>Revisar <code>controllers/login/AuthController.php</code></li>";
echo "<li>Actualizar consultas en <code>controllers/VentasController.php</code></li>";
echo "<li>Verificar vistas que muestren datos de usuarios</li>";
echo "<li>Probar login, registro y actualización de perfil</li>";
echo "<li>Probar visualización de ventas por modelo</li>";
echo "</ol>";
echo "</div>";

echo "<div class='file'>";
echo "<h2>⚠️ IMPORTANTE</h2>";
echo "<p>Ejecutar las correcciones en este orden para evitar errores:</p>";
echo "<ol>";
echo "<li>Primero: models/Usuario.php (capa de datos)</li>";
echo "<li>Segundo: controllers (lógica de negocio)</li>";
echo "<li>Tercero: views (presentación)</li>";
echo "</ol>";
echo "</div>";
?>
