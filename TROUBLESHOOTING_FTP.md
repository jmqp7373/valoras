# 🔧 TROUBLESHOOTING - Error FTP Deploy

## ❌ Error Identificado: `530 Login incorrect`

### 🔍 **Diagnóstico del Problema:**

El error `530 Login incorrect` en GitHub Actions indica que:

1. **❌ Credenciales FTP incorrectas** en GitHub Secrets
2. **❌ Usuario/Password inválidos** para el servidor FTP  
3. **❌ Servidor no soporta FTP** (solo SFTP)
4. **❌ IP bloqueada** por el proveedor de hosting

## 🛠️ **Soluciones Paso a Paso:**

### ✅ **1. Verificar GitHub Secrets**

Ve a: `GitHub.com > valoras > Settings > Secrets and variables > Actions`

Verifica que estos 3 secretos existan y sean correctos:

```
FTP_HOST=ftp.tuservidor.com
FTP_USERNAME=tu-usuario-ftp
FTP_PASSWORD=tu-password-ftp
```

### ✅ **2. Identificar Proveedor de Hosting**

**¿Cuál es tu proveedor de hosting?**

#### **GoDaddy:**
```
FTP_HOST: ftp.secureserver.net
Puerto: 21 (FTP) o 22 (SFTP)
Directorio: /public_html/
```

#### **Hostinger:**
```  
FTP_HOST: files.000webhost.com
Puerto: 21 (FTP) o 22 (SFTP)
Directorio: /domains/tudominio.com/public_html/
```

#### **cPanel (General):**
```
FTP_HOST: ftp.tudominio.com
Puerto: 21 (FTP) o 22 (SFTP) 
Directorio: /public_html/
```

#### **Namecheap:**
```
FTP_HOST: ftp.namecheap.com
Puerto: 21
Directorio: /public_html/
```

### ✅ **3. Probar Conexión FTP Local**

Prueba las credenciales desde tu PC:

#### **Windows (PowerShell):**
```powershell
# Instalar WinSCP o usar FileZilla para probar
# O usar PowerShell nativo:
$ftp = [System.Net.FtpWebRequest]::Create("ftp://ftp.tuservidor.com/")
$ftp.Credentials = New-Object System.Net.NetworkCredential("usuario","password")
```

#### **Alternativa - FileZilla:**
1. Descargar FileZilla Client
2. Conectar con las mismas credenciales
3. Verificar que funcione

### ✅ **4. Alternativas de Deploy**

Si FTP no funciona, podemos usar:

#### **A) SFTP (más seguro):**
```yaml
- name: Deploy via SFTP  
  uses: wlixcc/SFTP-Deploy-Action@v1.2.4
  with:
    server: ${{ secrets.FTP_HOST }}
    username: ${{ secrets.FTP_USERNAME }}
    password: ${{ secrets.FTP_PASSWORD }}
    port: 22
```

#### **B) Git Deploy (si el hosting lo soporta):**
```yaml
- name: Deploy via Git
  run: |
    git remote add production usuario@servidor:/path/to/site
    git push production main
```

#### **C) rsync (Linux servers):**
```yaml  
- name: Deploy via rsync
  uses: burnett01/rsync-deployments@5.2
  with:
    switches: -avzr --delete
    path: ./
    remote_path: /public_html/
    remote_host: ${{ secrets.FTP_HOST }}
    remote_user: ${{ secrets.FTP_USERNAME }}
    remote_key: ${{ secrets.SSH_KEY }}
```

## 🔧 **Siguiente Paso Recomendado:**

### **Opción 1: Arreglar credenciales FTP**
1. Verifica credenciales en tu panel de hosting
2. Actualiza secretos en GitHub 
3. Vuelve a hacer push

### **Opción 2: Cambiar a SFTP** 
1. Confirma si tu servidor soporta SFTP
2. Actualizo el workflow para usar SFTP
3. Reintento deploy

### **Opción 3: Deploy manual temporal**
1. Descargar archivos del repositorio como ZIP
2. Subir manualmente vía cPanel/FileZilla
3. Configurar FTP correctamente para futuros deploys

## 📞 **¿Cuál prefieres?**

**Dime:**
1. **¿Cuál es tu proveedor de hosting?** (GoDaddy, Hostinger, etc.)
2. **¿Tienes acceso al panel de control?** (cPanel, Plesk, etc.)  
3. **¿Prefieres arreglar FTP o cambiar a SFTP?**

Con esta información puedo ayudarte a solucionar el deploy rápidamente.