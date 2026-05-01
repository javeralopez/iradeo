<?php
// (c) iRadeo.com
// Versión mejorada con validaciones de seguridad

session_start();

require('config.php');
require('classes/security.php');

include('classes/getid3/getid3.php');
include('classes/json.php');

class Streamer {
    private $security;
    
    function __construct($action) {
        $this->json = new JSON_obj;
        $this->id3  = new getID3;
        $this->security = new Security();
        
        // Validar CSRF token
        if (!$this->security->validateCSRFToken($_POST['csrf_token'] ?? '')) {
            echo $this->json->encode(array('error' => 'Invalid CSRF token'));
            exit;
        }
        
        // Inicializar sesión
        if(!isset($_SESSION['current_song_id'])) {
            $_SESSION = array(
                'current_song_id' => 0,
                'current_song_filename' => '',
                'current_artist' => '',
                'current_album' => '',
                'skipped' => 0,
                'created_at' => time()
            );
        }
        
        // Validar timeout de sesión
        if (time() - $_SESSION['created_at'] > SESSION_TIMEOUT) {
            session_destroy();
            echo $this->json->encode(array('error' => 'Session expired'));
            exit;
        }
        
        // Whitelist de acciones permitidas
        $allowed_actions = array('next_song', 'reset_skip');
        
        if (in_array($action, $allowed_actions)) {
            echo $this->executeAction($action);
        } else {
            echo $this->json->encode(array('error' => 'Invalid action'));
        }
    }
    
    private function executeAction($action) {
        switch($action) {
            case 'next_song':
                return $this->nextSong();
            case 'reset_skip':
                return $this->resetSkip();
            default:
                return $this->json->encode(array('error' => 'Unknown action'));
        }
    }
    
    function nextSong() {
        global $mp3_dir, $http_path, $shuffle, $skip_limit;
        
        $error = '';
        
        // Validar skip limit
        if(isset($_POST['skip']) && $skip_limit > -1) {
            if($_SESSION['skipped'] >= $skip_limit) {
                $error = 'skip_limit_exceeded';
            } else {
                $_SESSION['skipped']++;
            }
        }
        
        $dir_parse = new DirectoryParser($mp3_dir);
        
        if (empty($dir_parse->files)) {
            return $this->json->encode(array('error' => 'No playable files found'));
        }
        
        if($shuffle == true) {
            $song = $this->getRandomSong($dir_parse);
        } else {
            $song = $this->getNextSequentialSong($dir_parse);
        }
        
        if (!$song) {
            return $this->json->encode(array('error' => 'Could not load song'));
        }
        
        // Extraer metadatos con validación consistente
        $metadata = $this->extractMetadata($song);
        
        if (!$metadata) {
            return $this->json->encode(array('error' => 'Could not read song metadata'));
        }
        
        // Actualizar sesión
        $_SESSION['current_song_filename'] = $song;
        $_SESSION['current_song'] = $metadata['title'];
        $_SESSION['current_album'] = $metadata['album'];
        $_SESSION['current_artist'] = $metadata['artist'];
        $_SESSION['last_updated'] = time();
        
        // Construir respuesta segura
        $info = array(
            'error'    => $error,
            'title'    => $metadata['title'],
            'artist'   => $metadata['artist'],
            'album'    => $metadata['album'],
            'filepath' => $this->buildSafePath($song, $mp3_dir, $http_path),
            'duration' => $metadata['duration']
        );
        
        return $this->json->encode($info);
    }
    
    private function getRandomSong($dir_parse) {
        $count = 0;
        $rand = array();
        
        do {
            $song_num = rand(0, count($dir_parse->files) - 1);
            $song = $dir_parse->files[$song_num];
            
            $metadata = $this->extractMetadata($song);
            
            if (!in_array($song, $rand)) {
                $rand[] = $song;
                $count++;
            }
        } while(
            ($song == $_SESSION['current_song_filename'] ||
             $metadata['artist'] == $_SESSION['current_artist'] ||
             $metadata['album'] == $_SESSION['current_album']) &&
            $count < count($dir_parse->files)
        );
        
        return $song;
    }
    
    private function getNextSequentialSong($dir_parse) {
        $_SESSION['current_song_id']++;
        
        if (!isset($dir_parse->files[$_SESSION['current_song_id']])) {
            $_SESSION['current_song_id'] = 0;
        }
        
        return $dir_parse->files[$_SESSION['current_song_id']];
    }
    
    private function extractMetadata($song) {
        $song_id3 = $this->id3->analyze($song);
        
        if (!is_array($song_id3)) {
            return false;
        }
        
        getid3_lib::CopyTagsToComments($song_id3);
        
        // Usar key consistente
        $source = isset($song_id3['comments_html']) ? 'comments_html' : 'comments';
        
        $title = $this->safeGetMetadata($song_id3, $source, 'title', 'Unknown');
        $artist = $this->safeGetMetadata($song_id3, $source, 'artist', 'Unknown');
        $album = $this->safeGetMetadata($song_id3, $source, 'album', 'Unknown');
        $duration = $song_id3['playtime_string'] ?? '0:00';
        
        return array(
            'title' => $title,
            'artist' => $artist,
            'album' => $album,
            'duration' => $duration
        );
    }
    
    private function safeGetMetadata($song_id3, $source, $key, $default) {
        if (isset($song_id3[$source][$key][0])) {
            return htmlspecialchars($song_id3[$source][$key][0], ENT_QUOTES, 'UTF-8');
        }
        return $default;
    }
    
    private function buildSafePath($file, $mp3_dir, $http_path) {
        // Validar que el archivo está dentro del directorio permitido
        $real_file = realpath($file);
        $real_dir = realpath($mp3_dir);
        
        if ($real_file === false || $real_dir === false || strpos($real_file, $real_dir) !== 0) {
            return ''; // Path traversal attempt detected
        }
        
        $relative_path = str_replace($mp3_dir, '', $file);
        $relative_path = str_replace('\\', '/', $relative_path);
        
        return htmlentities($http_path . $relative_path, ENT_QUOTES, 'UTF-8');
    }
    
    function resetSkip() {
        $_SESSION['skipped'] = 0;
        
        return $this->json->encode(array('reset' => true));
    }
}

class DirectoryParser {
    var $files;
    private $max_depth;
    private $current_depth;
    
    function __construct($dir) {
        $this->files = array();
        $this->max_depth = 5; // Limitar profundidad para evitar recursión infinita
        $this->current_depth = 0;
        
        $this->parseDir($dir);
        sort($this->files);
    }
    
    function parseDir($dir) {
        global $playable;
        
        // Prevenir recursión infinita
        if ($this->current_depth >= $this->max_depth) {
            return;
        }
        
        $h = @opendir($dir);
        if ($h === false) {
            return;
        }
        
        $this->current_depth++;
        
        while(($file = readdir($h)) !== false) {
            if($file == '.' || $file == '..') continue;
            
            $full_path = $dir . $file;
            
            // Validar que es un archivo/directorio legítimo
            if (!is_readable($full_path)) continue;
            
            if(is_dir($full_path)) {
                $this->parseDir($full_path . (strpos($dir, '\\') !== false ? '\\' : '/'));
            } else if(is_file($full_path)) {
                $ext = strtolower(substr($file, strrpos($file, ".") + 1));
                if (in_array($ext, $playable)) {
                    $this->files[] = $full_path;
                }
            }
        }
        
        $this->current_depth--;
        closedir($h);
    }
}

// Ejecutar solo si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    if ($action) {
        $s = new Streamer($action);
    } else {
        echo json_encode(array('error' => 'No action specified'));
    }
} else {
    http_response_code(405);
    echo json_encode(array('error' => 'Method not allowed'));
}

?>
