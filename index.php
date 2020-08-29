<?php

require_once 'src/database/DBOperations.php';
require_once 'src/ApiConsummer.php';

use Catlebot\src\ApiConsummer;
use Catlebot\src\database\DBOperations;

require_once 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

//Configurando e inicializando o banco
$dbPath = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'database.sqlite';
$db = new DBOperations('sqlite', $dbPath);

//Configurando e inicializando o CodeBird
$jsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'keys.json';
$json = file_get_contents('keys.json');
$keys = json_decode($json,true);
$cb = new ApiConsummer($keys['API key'], $keys['API secret key'], $keys['Access token'], $keys['Access token secret'], 'AlistarGadoBot');

//Pegando as menções
$mentions = $cb->getMentions($db);

echo "<pre>";
echo var_dump($mentions);
echo "</pre>";

if (!isset($mentions[0])) {
    exit();
}

//pegando os tweets
$tweets = $cb->getTweets($mentions);

echo "<pre>";
echo var_dump($tweets);
echo "</pre>";

//preparando mídia
$filePath = __DIR__ . DIRECTORY_SEPARATOR . 'gado.mp4';
$filetype = 'video/mp4';
$cb->prepareMedia($filePath, $filetype);

//Frases aleatórias
$frases = [];
$frases[] = 'CHAMANDO TODOS OS GADOS!!!';
$frases[] = 'GADOS UNIDOS JAMAIS SERÃO VENCIDOS!!';
$frases[] = 'MUUUUUUUUUUUU, O CURRAL TÁ ABERTO E OS GADOS CHEGANDO';
$frases[] = 'MUUUUU, O REBANHO CHEGOU';

//pegando frase aleatória
$frase = $frases[mt_rand(0, count($frases)-1)];

//respondendo
$cb->reply($tweets, $frase);

//persistindo última resposta
if (isset($tweets[0])) {
    $lastTweetId = $tweets[0]['id'];
    $db->setLastId($lastTweetId);
}

