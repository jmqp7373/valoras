# 🏢 Valora.vip - Sistema de Autenticación Completo

[![Deploy Status](https://github.com/jmqp7373/valoras/actions/workflows/deploy.yml/badge.svg)](https://github.com/jmqp7373/valoras/actions/workflows/deploy.yml)
[![PHP Version](https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![PHPMailer](https://img.shields.io/badge/PHPMailer-6.12.0-green)](https://github.com/PHPMailer/PHPMailer)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

Sistema web profesional con autenticación, recuperación de contraseña y envío de emails automatizado.

> 🚀 **Estado del Deploy:** Configuración FTP corregida - Testing credenciales actualizadas (Nov 5, 2025)

## 📁 Estructura del Proyecto

```
valoras/
├── .github/workflows/     # GitHub Actions para deployment
│   └── deploy.yml        # Configuración de deployment automático
├── .git/                 # Repositorio Git
├── .gitignore           # Archivos a ignorar en Git
├── .htaccess            # Configuración del servidor Apache
├── index.php            # Página principal "Hola Mundo"
└── README.md            # Este archivo
```

## 🔧 Configuración de Secretos en GitHub

Para que el deployment automático funcione, necesitas configurar estos **secretos** en tu repositorio de GitHub:

### Pasos para configurar secretos:

1. **Ve a tu repositorio en GitHub**: `https://github.com/jmqp7373/valoras`
2. **Settings** > **Secrets and variables** > **Actions**
3. **New repository secret** y añade:

#### Secretos requeridos:

| Nombre | Descripción | Ejemplo |
|--------|-------------|---------|
| `FTP_HOST` | Servidor FTP de GoDaddy | `ftp.tudominio.com` |
| `FTP_USERNAME` | Usuario FTP de tu hosting | `usuario@tudominio.com` |
| `FTP_PASSWORD` | Contraseña FTP | `tu_contraseña_segura` |

### 📋 Cómo obtener las credenciales FTP de GoDaddy:

1. **Login en GoDaddy** → Mi cuenta
2. **Web Hosting** → Administrar
3. **cPanel** → Cuentas FTP
4. **Crear cuenta FTP** o usar la principal

### 📁 Configuración del directorio:
- Los archivos se suben directamente a la **raíz** del servidor (`/`)
- NO se utiliza `/public_html/` como directorio de destino

## 🚀 Cómo Funciona el Deployment

### Automático:
1. Haces `git push` a la rama `main`
2. GitHub Actions se activa automáticamente
3. Valida la sintaxis PHP
4. Sube los archivos vía FTP a GoDaddy
5. ¡Tu sitio se actualiza automáticamente! 🎉

### Manual:
- También puedes ejecutar el workflow manualmente desde GitHub Actions

## 📱 Acceso al Sitio

Una vez configurado, tu sitio estará disponible en:
- `https://tudominio.com`
- `https://tudominio.com/index.php`

## 🛠️ Desarrollo Local

Para probar localmente necesitas un servidor PHP:

```bash
# Con PHP built-in server
php -S localhost:8000

# Con XAMPP/WAMP
# Copia los archivos a htdocs y ve a localhost
```

## � Configuración de GitHub Secrets

Para que el deployment automático funcione, configura estos secretos en GitHub:

### 📋 Secretos Requeridos

Ve a: **Settings > Secrets and variables > Actions** y agrega:

```
FTP_HOST=ftp.tu-proveedor.com
FTP_USERNAME=tu-usuario-ftp  
FTP_PASSWORD=tu-password-ftp
```

### 🌐 Proveedores Comunes

**GoDaddy:**
- Host: `ftp.secureserver.net`
- Puerto: `21`
- Directorio: `/public_html/`

**Hostinger:**
- Host: `files.000webhost.com` 
- Puerto: `21`
- Directorio: `/domains/tudominio.com/public_html/`

**cPanel (General):**
- Host: `ftp.tudominio.com`
- Puerto: `21` 
- Directorio: `/public_html/`

## 🚀 Despliegue Automático

### ✅ **Qué se despliega:**
- 📄 Todos los archivos PHP (MVC completo)
- 📦 Dependencias Composer optimizadas
- 🎨 Assets (CSS, JS, imágenes)
- ⚙️ Configuraciones de producción

### 🚫 **Qué se excluye:**
- `.git/` - Historial de Git
- `README.md` - Documentación
- `test_*.php` - Archivos de prueba
- `.github/` - Workflows de CI/CD
- `*.log` - Archivos de log

### 🔄 **Trigger del Deploy:**
```bash
git add .
git commit -m "feat: nueva funcionalidad"
git push origin main  # 🚀 Se despliega automáticamente
```

## 📊 Monitoreo y Logs

- **GitHub Actions**: Tab "Actions" para ver progreso
- **Status Badge**: Muestra estado del último deploy
- **Health Check**: Verifica que el sitio esté activo
- **Rollback**: Manual via FTP si es necesario

## 🎯 Configuración de Email (Producción)

### Actualizar `config/email-config.php`:

```php
// Cambiar a credenciales reales de Migadu
'smtp_username' => 'noreply@valora.vip',
'smtp_password' => 'password_real_migadu',

// Cambiar a modo producción  
'development_mode' => false,
'debug' => false,
```

---

**🎉 ¡Sistema listo para producción!** 🚀