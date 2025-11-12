# 📤 Deployment Manual - Valora.vip

## Archivos modificados que necesitas subir:

### 1️⃣ models/Permisos.php
**Ruta en servidor:** `/public_html/models/Permisos.php`

### 2️⃣ views/admin/permissionsPanel.php  
**Ruta en servidor:** `/public_html/views/admin/permissionsPanel.php`

## 🔧 Pasos con FileZilla:

1. **Conectar al servidor:**
   - Host: `valora.vip` (o la IP que uses)
   - Usuario: Tu usuario FTP/SFTP
   - Contraseña: Tu contraseña
   - Puerto: `21` (FTP) o `22` (SFTP)

2. **Navegar a la carpeta remota:**
   - Ir a `/public_html/`

3. **Subir archivos:**
   - Arrastra `models/Permisos.php` → `/public_html/models/`
   - Arrastra `views/admin/permissionsPanel.php` → `/public_html/views/admin/`

4. **Verificar:**
   - Visita: https://valora.vip/views/admin/permissionsPanel.php

## ⚡ Atajo rápido desde terminal local:

Si tienes WinSCP o FileZilla CLI:

```powershell
# Con WinSCP
winscp.com /command ^
    "open sftp://usuario:password@valora.vip" ^
    "put models/Permisos.php /public_html/models/" ^
    "put views/admin/permissionsPanel.php /public_html/views/admin/" ^
    "exit"
```

## 🔍 Verificar después:

Abre: https://valora.vip/views/admin/permissionsPanel.php

Si aún muestra error, los mensajes de debug ahora serán visibles.
