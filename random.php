<?php
require_once('lib.function.php');

use simple_curl\curl;

    $snipe = getRedirectTo(URL . 'random/');

    curl::prepare($snipe, NULL);
    curl::exec_get();
    $response = curl::get_response();

    $content = explode('<div id="content">', $response);
    $content = explode('<div class="bixbox">', $content[1]);

    $chapterList = explode('<div class="cl">', $content[1]);
    $chapterList = explode('</ul>', $chapterList[1]);

    $genreList = explode('Genres', $content[0]);
    $genreList = explode('Status', $genreList[1]);

    preg_match('/<h1 itemprop="headline">(.*?)<\/h1>/', $content[0], $title);
    preg_match('/<span class="alter">(.*?)<\/span>/', $content[0], $alt);
    preg_match('/src="(.*?)"/', $content[0], $image);
    preg_match('/Status:<\/b> (.*?)<\/span>/', $content[0], $status);
    preg_match('/Released:<\/b> (.*?)<\/span>/', $content[0], $released);
    preg_match('/Author:<\/b> (.*?)<\/span>/', $content[0], $author);
    preg_match('/Total Chapter:<\/b> (.*?)<\/span>/', $content[0], $totalChapter);
    preg_match('/Updated on:<\/b> <time itemprop=".*" datetime=".*">(.*?)<\/time>/', $content[0], $updated);
    preg_match('/<div class="rating">\s+<strong>(.*?)<\/strong>/', $content[0], $scoresOneToTen);
    preg_match('/data-current-rating="(.*?)"/', $content[0], $starsOneToFive);
    preg_match('/<div itemprop="articleBody">\s+(.+?)\s+<\/div>/s', $content[0], $synopsis);
    preg_match_all('/href=".*?\/type\/(.*?)\//', $content[0], $type);
    preg_match_all('/href="(.*?)"/', $chapterList[0], $chapterUrl);
    preg_match_all('/href=".*?">(.*?)<\/a>/', $chapterList[0], $chapter);
    preg_match_all('/<span class="rightoff">(.*?)<\/span>/', $chapterList[0], $chapterUpdated);
    preg_match_all('/rel="tag">(.*?)<\/a>/', $genreList[0], $genre);

    $array = [
        'title' => $title[1],
        'alt' => $alt[1],
        'image' => $image[1],
        'type' => [],
        'genre' => [],
        'status' => $status[1],
        'released' => $released[1],
        'author' => $author[1],
        'totalChapter' => $totalChapter[1],
        'updated' => $updated[1],
        'rating' => [
            'starsOTF' => $starsOneToFive[1],
            'scoresOTT' => preg_replace('/Rating /', '', $scoresOneToTen[1])
        ],
        'synopsis' => $synopsis[1],
        'chapterList' => []
    ];

    for ($i = 0; $i < count($type[1]); $i++)
    {
        $array['type'] = [
            'code' => base64_encode($type[1][$i]),
            'title' => ucwords($type[1][$i])
        ];
    }

    for ($i = 0; $i < count($chapterUrl[1]); $i++)
    {
        $array['chapterList'][] = [
            'code' => base64_encode(str_replace('/', '', str_replace(URL . 'chapter/', '', $chapterUrl[1][$i]))),
            'title' => $chapter[1][$i],
            'updated' => $chapterUpdated[1][$i]
        ];
    }

    for ($i = 0; $i < count($genre[1]); $i++)
    {
        $array['genre'][] = $genre[1][$i];
    }

    echo json_encode((object)$array, JSON_PRETTY_PRINT);