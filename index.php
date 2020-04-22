<?php

use Codebird\Codebird;

require 'vendor\autoload.php';

$databasePath = __DIR__ . '/database.sqlite';
$db = new PDO('sqlite:' . $databasePath);

//Configurando COdeBird
Codebird::setConsumerKey('fVp0hmbI8rx3PM2pcnWFAQ2z4', 'tGxpUTi6Bp1Bq9bY7IiEcNnZykjpfE6ZaqZm1BpyOv1QGoMKD3');
$cb = Codebird::getInstance();
$cb->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);
$cb->setToken('1252716377686847494-6YmrAnzkl92KicTh0ruiwukP19yGj1', 'NfvGQnZx7fGj2cBlZpgvOTgBccNPnrhhcHsxEJ8qCf9vp');

$lastId = $db->query('
    SELECT * FROM last_tweet
    ORDER BY id
    DESC
    LIMIT 1
')->fetch(PDO::FETCH_ASSOC);

//Pegando as menções
$mentions = $cb->statuses_mentionsTimeline($lastId ? 'since_id=' . $lastId['id'] : '');
if (!isset($mentions[0])) {
    return;
}
$tweets = [];
foreach ($mentions as $index => $mention) {
    if (isset($mention['id'])) {
        $tweets[] = [
            'id' => $mention['id'],
            'user_screen-name' => $mention['user']['screen_name'],
        ];
    }
}

//Arquivo de vídeo
$file = __DIR__ . '/gado.mp4';
$sizeBytes = filesize($file);
$fp = fopen($file, 'r');

$reply = $cb->media_upload([
    'command'     => 'INIT',
    'media_type'  => 'video/mp4',
    'total_bytes' => $sizeBytes
]);

$media_id = $reply['media_id_string'];
$segment_id = 0;

while (! feof($fp)) {
    $chunk = fread($fp, 1048576); // 1MB por chunk

    $reply = $cb->media_upload([
        'command'       => 'APPEND',
        'media_id'      => $media_id,
        'segment_index' => $segment_id,
        'media'         => $chunk
    ]);

    $segment_id++;
}

fclose($fp);

$reply = $cb->media_upload([
    'command'       => 'FINALIZE',
    'media_id'      => $media_id
]);
if ($reply['httpstatus'] < 200 || $reply['httpstatus'] > 299) {
    die();
}

//Frases aleatórias
$frases = [];
$frases[] = 'CHAMANDO TODOS OS GADOS!!!';
$frases[] = 'GADOS UNIDOS JAMAIS SERÃO VENCIDOS!!';
$frases[] = 'MUUUUUUUUUUUU, O CURRAL TÁ ABERTO E OS GADOS CHEGANDO';
$frases[] = 'MUUUUU, O REBANHO CHEGOU';

foreach ($tweets as $index => $tweet) {

    $reply = $cb->statuses_update([
        'status'    => '@' . $tweet['user_screen-name'] . ' ' . $frases[mt_rand(0,2)],
        'in_reply_to_status_id' => $tweet['id'],
        'media_ids' => $media_id
    ]);

}

$stmt = $db->prepare('INSERT INTO last_tweet (id) VALUES (:tweetId);');
$stmt->execute([
   'tweetId' => $tweets[0]['id'],
]);

