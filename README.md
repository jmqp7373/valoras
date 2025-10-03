# Proyecto Valoras 🚀

Un simple proyecto "Hola Mundo" en PHP con deployment automático a GoDaddy.

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

## 📝 Logs y Monitoreo

- **GitHub Actions**: Ve el progreso del deployment en la pestaña "Actions"
- **Errores**: Revisa los logs si algo falla
- **Estado**: El badge muestra si el último deployment fue exitoso

---

**¡Happy Coding! 🎈**