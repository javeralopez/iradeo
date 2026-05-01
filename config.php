<?php
/*
   (c) iRadeo.com
*/




/*
   Your Player Title
*/

$title = 'Radionoticias';

/*
   Label Display Feature
   Display labels inside player.
   Set any label to '' to hide it.
*/

    $labels = array(
    'song' => 'Cancion/Programa',
    'artist' => 'Autor',
    'album' => 'Album'

);

/*
   Root directory to your mp3 folder.(eg: /root/mp3s/)
   Directory where your audio files are stored.
   If you need assistance, request this information from your web hosting provider.
*/

$mp3_dir = '/home/estudian/public_html/radio/mp3s/';

/*
   Public address for your audio folder.
   If you need assistance, request this information from your web hosting provider.
*/

$http_path = 'http://estudiantesdecomunicacion.com/radio/mp3s/';

/*
   Shuffle Mode Feature
   Enabled - Streams files randomly from specified directory.
   Disabled - Sorts files alphabetically by filename/pathname and play sequentially.
   Enter true to enable or false to disable feature.
*/

$shuffle = false;

/*
   Skip Feature
   Limits the number of skips before having to stream one whole audio file.
   Unlimited skips: -1
   No skipping: 0
   X Skips (then must listen to entire audio): 1+
*/

$skip_limit = 5;

/*
   File Type Supported
   DO NOT EDIT
   Only .wav and .mp3 will work.
*/

$playable = array('mp3', 'wav');

/*
   Auto Play Feature
   Enabled - Streams files automatically when web page loads.
   Disabled - Requires users to click on play button to start streaming.
   Enter true to enable or false to disable feature.
*/

$auto_play = true;

?>