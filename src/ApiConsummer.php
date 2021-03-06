<?php

namespace Catlebot\src;

use Codebird\Codebird;

class ApiConsummer
{
    private string $apiKey;
    private string $apiSecretKey;
    private string $accessToken;
    private string $accessSecretToken;
    private ?Codebird $cb;
    private string $mediaId;
    private string $userName;

    public function __construct(string $apiKey, string $apiSecretKey, string $accessToken, string $accessSecretToken, string $userName)
    {
        $this->apiKey = $apiKey;
        $this->apiSecretKey = $apiSecretKey;
        $this->accessToken = $accessToken;
        $this->accessSecretToken = $accessSecretToken;
        $this->initializeCB();
        $this->userName = '@' . $userName;
    }

    private function initializeCB()
    {
        Codebird::setConsumerKey($this->apiKey, $this->apiSecretKey);
        $this->cb = Codebird::getInstance();
        $this->cb->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);
        $this->cb->setToken($this->accessToken, $this->accessSecretToken);
    }

    public function getMentions($db)
    {
        $lastId = $db->getLastId();
        return $this->cb->statuses_mentionsTimeline($lastId ? 'since_id=' . $lastId['id'] : '');
    }

    public function getTweets($mentions)
    {
        $tweets = [];
        foreach ($mentions as $index => $mention) {
            if (isset($mention['id']) && $mention['in_reply_to_status_id'] === null) {
                $tweets[] = [
                    'id' => $mention['id'],
                    'user_screen-name' => $mention['user']['screen_name'],
                ];
            }
        }

        return $tweets;
    }

    public function prepareMedia($filePath, $filetype)
    {
        $sizeBytes = filesize($filePath);
        $fp = fopen($filePath, 'r');

        //filetype {vídeo = video/mp4}

        $reply = $this->cb->media_upload([
            'command'     => 'INIT',
            'media_type'  => $filetype,
            'total_bytes' => $sizeBytes
        ]);

        $this->mediaId = $reply['media_id_string'];
        $segment_id = 0;

        while (! feof($fp)) {
            $chunk = fread($fp, 1048576); // 1MB por chunk

            $this->cb->media_upload([
                'command'       => 'APPEND',
                'media_id'      => $this->mediaId,
                'segment_index' => $segment_id,
                'media'         => $chunk
            ]);

            $segment_id++;
        }

        fclose($fp);

        $reply = $this->cb->media_upload([
            'command'       => 'FINALIZE',
            'media_id'      => $this->mediaId
        ]);

        if ($reply['httpstatus'] < 200 || $reply['httpstatus'] > 299) {
            die();
        }
    }

    public function reply($tweets, $frase)
    {
        foreach ($tweets as $index => $tweet) {
            $this->cb->statuses_update([
                'status'    => '@' . $tweet['user_screen-name'] . ' ' . $frase,
                'in_reply_to_status_id' => $tweet['id'],
                'media_ids' => $this->mediaId
            ]);
        }
    }
}