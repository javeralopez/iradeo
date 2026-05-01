# Guía de Seguridad - iRadeo

## Cambios de Seguridad Implementados

### 1. **Variables de Entorno**
- Las rutas sensibles ya no están hardcodeadas en el código
- Utiliza `.env` para configuración local
- Ejemplo: `.env.example`

**Cómo usar:**
```bash
cp .env.example .env
# Edita .env con tus valores reales
```

### 2. **CSRF Protection (Cross-Site Request Forgery)**
- Se genera un token CSRF único por sesión
- Se valida en cada solicitud POST
- Previene ataques de falsificación de solicitudes entre sitios

### 3. **Validación de Entrada**
- Whitelist de acciones permitidas (`next_song`, `reset_skip`)
- Sanitización de datos de entrada
- Escapado de salida HTML

### 4. **Path Traversal Prevention**
- Validación que los archivos están dentro del directorio permitido
- Uso de `realpath()` para resolver rutas reales
- Previene acceso a archivos fuera del directorio de música

### 5. **Seguridad de Sesión**
- Timeout de sesión configurable
- Validación de tiempo de creación
- Límite de requests por sesión (rate limiting)

### 6. **Method Validation**
- Solo acepta solicitudes POST en `streamer.php`
- Retorna error 405 para otros métodos

### 7. **Consistencia en Metadatos ID3**
- Corrigió inconsistencia entre `comments` y `comments_html`
- Validación de datos antes de usar

### 8. **HTML5 Moderno**
- Actualizado a HTML5 válido
- Atributos ARIA para accesibilidad
- Meta viewport para responsive design

---

## Instalación y Configuración

### Paso 1: Configurar Variables de Entorno
```bash
cp .env.example .env
nano .env  # o tu editor favorito
```

Actualiza los valores según tu servidor:
- `MP3_DIR`: Ruta absoluta a tu directorio de MP3s
- `HTTP_PATH`: URL pública de acceso

### Paso 2: Permisos de Directorios
```bash
# Crear directorio de logs
mkdir -p logs
chmod 755 logs

# Permisos para directorio de MP3s (lectura)
chmod 755 /ruta/a/mp3s
```

### Paso 3: Configurar PHP
En tu `php.ini` o `.htaccess`:
```apache
# Limitar tamaño de upload
php_value upload_max_filesize 100M
php_value post_max_size 100M

# Seguridad de sesión
php_value session.cookie_httponly 1
php_value session.cookie_secure 1  # Si usas HTTPS
```

---

## Checklist de Seguridad

- [ ] `.env` creado y configurado (no commitear)
- [ ] `.env` está en `.gitignore`
- [ ] Directorio `logs/` tiene permisos de escritura
- [ ] `MP3_DIR` tiene permisos de lectura correctos
- [ ] HTTPS habilitado en producción
- [ ] PHP 7.4+ instalado
- [ ] `random_bytes()` disponible en PHP
- [ ] Headers de seguridad configurados en el servidor

---

## Headers de Seguridad Recomendados

Agrega a tu servidor web (nginx/Apache):

```nginx
# nginx
add_header X-Content-Type-Options "nosniff" always;
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
```

```apache
# Apache .htaccess
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
Header set Referrer-Policy "strict-origin-when-cross-origin"
```

---

## Mejoras Futuras

1. **Autenticación**: Agregar login para usuarios
2. **Rate Limiting Avanzado**: Implementar Memcached
3. **Logging**: Sistema de auditoría completo
4. **API REST**: Documentación de API con OpenAPI
5. **HTTPS Enforcement**: Redirección automática
6. **Content Security Policy**: Headers CSP más estrictos
7. **Two-Factor Authentication**: Para administración

---

## Reportar Vulnerabilidades

Si encuentras un problema de seguridad:
1. **NO** lo publiques públicamente
2. Contáctanos privadamente
3. Describe los pasos para reproducir
4. Da tiempo para que se corrija antes de divulgar

---

## Referencias

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [Content Security Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP)
