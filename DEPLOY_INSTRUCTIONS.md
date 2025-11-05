# 🚀 INSTRUCCIONES DE DESPLIEGUE - Valora.vip

## 📋 Checklist Pre-Despliegue

### ✅ 1. Verificación del Sistema Local
- [ ] Login funciona con cédula: `1125998052` y password: `Reylondres7373`
- [ ] Registro de usuarios opera correctamente
- [ ] Recuperación de contraseña muestra emails/teléfonos enmascarados
- [ ] Animación del botón "Enviar Enlace" funciona
- [ ] No hay warnings visibles en `password_reset.php`

### ✅ 2. Configuración de GitHub Secrets

Ve a: **GitHub.com > valoras > Settings > Secrets and variables > Actions**

Agrega estos 3 secretos:

```
FTP_HOST: ftp.tu-servidor.com
FTP_USERNAME: tu-usuario-ftp
FTP_PASSWORD: tu-password-ftp
```

### ✅ 3. Configuración de Email (Producción)

Edita `config/email-config.php` en el servidor:

```php
// CAMBIAR ESTAS LÍNEAS:
'smtp_username' => 'noreply@valora.vip', // Email real de Migadu
'smtp_password' => 'PASSWORD_REAL_MIGADU', // Password real
'development_mode' => false, // Cambiar a false
'debug' => false, // Cambiar a false para producción
```

## 🚀 Comando de Despliegue

```bash
# 1. Preparar cambios
git add .
git commit -m "deploy: sistema completo de autenticación y email"

# 2. Desplegar automáticamente
git push origin main
```

## 📊 Lo Que Se Desplegará

### ✅ **Archivos Incluidos:**
```
📁 Sistema MVC Completo:
├── config/          # Configuración DB y Email
├── controllers/     # AuthController, PasswordResetController  
├── models/         # Usuario.php
├── services/       # EmailService.php con PHPMailer
├── views/          # login.php, register.php, password_reset.php
├── assets/         # CSS, JS, logos
├── vendor/         # PHPMailer 6.12.0
└── index.php       # Página principal
```

### 🚫 **Archivos Excluidos:**
- `.git/` - Historial de versiones
- `README.md` - Documentación  
- `test_*.php` - Archivos de prueba
- `.github/` - Workflows de CI/CD
- `*.log` - Logs de desarrollo

## 🎯 Funcionalidades Desplegadas

### 🔐 **Sistema de Autenticación:**
- Login: `https://valora.vip/views/login.php`
- Registro: `https://valora.vip/views/register.php`
- Logout: Sesiones seguras con limpieza

### 📧 **Sistema de Email:**
- Templates HTML profesionales con logo Valora
- PHPMailer 6.12.0 con SMTP Migadu
- Recuperación de contraseña con tokens seguros
- Validación de emails y celulares colombianos

### 🗄️ **Base de Datos:**
- 9,321 usuarios con contraseñas originales
- Validación estricta de datos
- Mascarado de información sensible

## 📈 Monitoreo Post-Despliegue

### ✅ **Verificación Manual:**
1. `https://valora.vip` - Página principal carga
2. `https://valora.vip/views/login.php` - Sistema de login  
3. `https://valora.vip/views/password_reset.php` - Recuperación
4. Probar email de recuperación con cédula: `1125998052`

### 📊 **GitHub Actions:**
- Tab "Actions" muestra progreso en tiempo real
- Status badge indica éxito/fallo del deploy
- Logs detallados de cada paso del proceso

## 🔄 Actualizaciones Futuras

Para futuras actualizaciones:

```bash
# Hacer cambios en el código
# Commit y push - se despliega automáticamente
git add .
git commit -m "feat: nueva funcionalidad"  
git push origin main  # 🚀 Auto-deploy
```

## 🆘 Troubleshooting

### ❌ **Deploy falla:**
1. Verificar secretos FTP en GitHub
2. Comprobar sintaxis PHP en Actions
3. Revisar permisos del servidor

### 📧 **Emails no llegan:**
1. Verificar credenciales Migadu en `config/email-config.php`
2. Cambiar `development_mode` a `false`
3. Probar con email diferente

### 🗄️ **Base de datos:**
1. Importar `usuarios.sql` en servidor de producción
2. Actualizar credenciales en `config/database.php`
3. Verificar permisos MySQL

---

**🎉 ¡Sistema listo para desplegar! Ejecuta `git push origin main` cuando estés listo.** 🚀