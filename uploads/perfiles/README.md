# Directorio de Perfiles de Usuario

Este directorio almacena las fotografías y documentos subidos por los usuarios en su perfil:

- **Fotos de perfil**: Avatar principal del usuario
- **Fotos con cédula**: Imagen del usuario sosteniendo su cédula
- **Cédulas**: Frente y reverso del documento de identidad
- **Certificados médicos**: Documentos en PDF o imágenes

## Seguridad

**IMPORTANTE**: Este directorio debe tener permisos de escritura (755 o 775) pero NO debe ser accesible directamente desde el navegador sin autenticación.

## Estructura de Nombres

Los archivos se nombran automáticamente con el siguiente formato:
```
{id_usuario}_{tipo_foto}_{timestamp}.{extension}
```

Ejemplo:
```
123_foto_perfil_1699459200.jpg
123_foto_cedula_frente_1699459201.png
```

## Tipos de Archivo Permitidos

- **Imágenes**: JPG, JPEG, PNG, GIF
- **Documentos**: PDF (solo para cédulas y certificados)
- **Tamaño máximo**: 5MB por archivo
