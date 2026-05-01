<!-- iRadeo.com -->
<?php 
require('config.php');
require('classes/security.php');

$security = new Security();
$csrf_token = $security->generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="Reproductor de radio web iRadeo">
    
    <script type="text/javascript" src="js/soundmanager2.js"></script>
    <script type="text/javascript" src="js/excanvas.js"></script>
    <script type="text/javascript" src="js/xhconn.js"></script>
    <script type="text/javascript" src="js/mootools.js"></script>
    <script type="text/javascript" src="js/player.js"></script>
    
    <?php if($auto_play == true) { ?>
    <script type="text/javascript">
        Player.autoPlay = true;
        Player.csrfToken = '<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>';
    </script>
    <?php } else { ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            Player.csrfToken = '<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>';
        });
    </script>
    <?php } ?>

    <link rel="stylesheet" type="text/css" href="css/player.css">
    <link rel="stylesheet" type="text/css" href="css/responsive.css">
</head>

<body>
    <div class="container">
        <header>
            <h1><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
            <h2>Now Playing</h2>
        </header>

        <main>
            <section id="stream-info" class="stream-info">
                <?php if(strlen($labels['song']) > 0) { ?>
                <div class="info-item">
                    <span class="label" id="song-label"><?php echo htmlspecialchars($labels['song'], ENT_QUOTES, 'UTF-8'); ?>:</span>
                    <span class="info" id="song">Conectando...</span>
                </div>
                <?php } ?>
                
                <?php if(strlen($labels['artist']) > 0) { ?>
                <div class="info-item">
                    <span class="label" id="artist-label"><?php echo htmlspecialchars($labels['artist'], ENT_QUOTES, 'UTF-8'); ?>:</span>
                    <span class="info" id="artist">Conectando...</span>
                </div>
                <?php } ?>
                
                <?php if(strlen($labels['album']) > 0) { ?>
                <div class="info-item">
                    <span class="label" id="album-label"><?php echo htmlspecialchars($labels['album'], ENT_QUOTES, 'UTF-8'); ?>:</span>
                    <span class="info" id="album">Conectando...</span>
                </div>
                <?php } ?>
            </section>

            <div id="message" class="message" role="alert"></div>

            <section id="controls" class="controls" role="region" aria-label="Controles del reproductor">
                <div class="button-group">
                    <div id="button" class="button"></div>
                    <div id="button-down" class="button-down"></div>
                    <div id="button-disabled" class="button-disabled"></div>
                    <button id="playpause" type="button" title="Reproducir/Pausar" aria-label="Reproducir/Pausar" class="control-button"></button>
                    <button id="skip" type="button" title="Siguiente" aria-label="Siguiente canción" class="control-button"></button>
                </div>

                <div id="volume-control" class="volume-control" role="region" aria-label="Control de volumen">
                    <canvas id="audio-icon" width="10" height="20" aria-hidden="true"></canvas>
                    <div class="volume-bars">
                        <button class="volume-bar volume-active" id="vol-1" data-volume="10" title="10% volume"></button>
                        <button class="volume-bar volume-active" id="vol-2" data-volume="20" title="20% volume"></button>
                        <button class="volume-bar volume-active" id="vol-3" data-volume="30" title="30% volume"></button>
                        <button class="volume-bar volume-active" id="vol-4" data-volume="40" title="40% volume"></button>
                        <button class="volume-bar volume-active" id="vol-5" data-volume="50" title="50% volume"></button>
                        <button class="volume-bar volume-active" id="vol-6" data-volume="60" title="60% volume"></button>
                        <button class="volume-bar volume-active" id="vol-7" data-volume="70" title="70% volume"></button>
                        <button class="volume-bar volume-active" id="vol-8" data-volume="80" title="80% volume"></button>
                        <button class="volume-bar volume-active" id="vol-9" data-volume="90" title="90% volume"></button>
                        <button class="volume-bar volume-active" id="vol-10" data-volume="100" title="100% volume"></button>
                    </div>
                </div>

                <div id="song-status" class="song-status">
                    <span id="play-status" aria-live="polite">Detenido</span>
                    <span id="current-position" aria-live="off">0:00</span>
                    <span aria-hidden="true">/</span>
                    <span id="duration" aria-live="off">0:00</span>
                </div>
            </section>
        </main>

        <footer>
            <p>&copy; 2026 iRadeo.com - Reproductor de Radio Web</p>
        </footer>
    </div>
</body>
</html>
