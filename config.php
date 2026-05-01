<?php
/*
   (c) iRadeo.com
   Configuración mejorada con soporte para variables de entorno
*/

// Cargar variables de entorno (si usas un archivo .env)
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

/**
 * Player Title
 */
$title = getenv('PLAYER_TITLE') ?: 'Radionoticias';

/**
 * Label Display Feature
 * Display labels inside player.
 * Set any label to '' to hide it.
 */
$labels = array(
    'song'   => 'Cancion/Programa',
    'artist' => 'Autor',
    'album'  => 'Album'
);

/**
 * Root directory to your mp3 folder
 * Use absolute paths for better security
 */
$mp3_dir = getenv('MP3_DIR') ?: '/home/estudian/public_html/radio/mp3s/';

// Validar que el directorio existe
if (!is_dir($mp3_dir)) {
    die('Error: El directorio de MP3s no existe: ' . htmlspecialchars($mp3_dir));
}

// Asegurar que termina con /
if (substr($mp3_dir, -1) !== '/') {
    $mp3_dir .= '/';
}

/**
 * Public address for your audio folder
 */
$http_path = getenv('HTTP_PATH') ?: 'http://estudiantesdecomunicacion.com/radio/mp3s/';

// Asegurar que termina con /
if (substr($http_path, -1) !== '/') {
    $http_path .= '/';
}

/**
 * Shuffle Mode Feature
 * Enabled - Streams files randomly from specified directory.
 * Disabled - Sorts files alphabetically by filename/pathname and play sequentially.
 * Enter true to enable or false to disable feature.
 */
$shuffle = (getenv('SHUFFLE_MODE') === 'true') ? true : false;

/**
 * Skip Feature
 * Limits the number of skips before having to stream one whole audio file.
 * Unlimited skips: -1
 * No skipping: 0
 * X Skips (then must listen to entire audio): 1+
 */
$skip_limit = (int) (getenv('SKIP_LIMIT') ?: 5);

/**
 * File Type Supported
 * Only .wav and .mp3 will work.
 */
$playable = array('mp3', 'wav');

/**
 * Auto Play Feature
 * Enabled - Streams files automatically when web page loads.
 * Disabled - Requires users to click on play button to start streaming.
 * Enter true to enable or false to disable feature.
 */
$auto_play = (getenv('AUTO_PLAY') === 'true') ? true : false;

/**
 * Configuración de seguridad
 */
define('SESSION_TIMEOUT', getenv('SESSION_TIMEOUT') ?: 3600); // 1 hora
define('MAX_FILE_SIZE', getenv('MAX_FILE_SIZE') ?: 104857600); // 100MB
define('DEBUG_MODE', getenv('DEBUG_MODE') === 'true' ? true : false);

?>
