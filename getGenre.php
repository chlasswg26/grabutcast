<?php
require_once('lib.function.php');

use simple_curl\curl;

    $snipe = URL;

    curl::prepare($snipe, NULL);
    curl::exec_get();
    $response = curl::get_response();

    $content = explode('<ul class="genre">', $response);
    $content = explode('</ul>', $content[1]);

    preg_match_all('/genres\/(.*?)\//', $content[0], $genreUrl);
    preg_match_all('/title=.*?>(.*?)<\/a>/', $content[0], $genre);

    $array = [];

    for ($i = 0; $i < count($genreUrl[1]); $i++)
    {
        $array[] = [
            'code' => base64_encode($genreUrl[1][$i]),
            'title' => $genre[1][$i]
        ];
    }

    echo json_encode((object)$array, JSON_PRETTY_PRINT);