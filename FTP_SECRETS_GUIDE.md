# 🔐 Verificar GitHub Secrets para FTP/SFTP

## Secrets que debes verificar en GitHub:

Ve a: https://github.com/jmqp7373/valoras/settings/secrets/actions

### Secrets necesarios:

1. **FTP_HOST**
   - Valor: `valora.vip` o la IP del servidor
   - Ejemplo: `198.54.xxx.xxx` o `valora.vip`

2. **FTP_USERNAME**  
   - Tu nombre de usuario FTP/SFTP del hosting
   - Ejemplo: `u179023609` o similar

3. **FTP_PASSWORD**
   - Tu contraseña FTP/SFTP
   - ⚠️ Debe ser exacta, sin espacios extras

4. **FTP_PORT**
   - Para FTP: `21`
   - Para SFTP: `22`
   - ⚠️ Si el servidor solo acepta SFTP, debe ser `22`

## 🔍 Cómo encontrar esta información:

### En cPanel:
1. Ir a **Archivos** → **Cuentas FTP**
2. Buscar tu cuenta FTP
3. Ver: Usuario, Servidor, Puerto

### En Hostinger:
1. Panel de control → **Archivos** → **Administrador de archivos**
2. O buscar sección **FTP/SFTP**

### Test manual:
```powershell
# Probar conexión FTP
Test-NetConnection -ComputerName valora.vip -Port 21

# Probar conexión SFTP  
Test-NetConnection -ComputerName valora.vip -Port 22
```

## 📝 Actualizar secrets:

1. Ve a: https://github.com/jmqp7373/valoras/settings/secrets/actions
2. Haz clic en cada secret
3. Click en "Update"
4. Ingresa el valor correcto
5. Click "Update secret"

Luego vuelve a ejecutar el workflow.
