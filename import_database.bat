@echo off
echo Importando base de datos usuarios.sql...
echo.

REM Crear base de datos si no existe
"C:\xampp\mysql\bin\mysql.exe" -u root -p -e "CREATE DATABASE IF NOT EXISTS valora_db;"

REM Importar el archivo SQL
"C:\xampp\mysql\bin\mysql.exe" -u root -p valora_db < "C:\Users\jmqp7\OneDrive\Desktop\usuarios.sql"

echo.
echo Base de datos importada exitosamente!
pause