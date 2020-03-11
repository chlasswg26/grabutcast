<?php
require_once('lib.function.php');

use simple_curl\curl;

$query = base64_decode($_GET['query']);

if (empty($query) || !isset($query))
{
    die();
} else {
    $snipe = URL . 'chapter/' . $query . '/';

    curl::prepare($snipe, NULL);
    curl::exec_get();
    $response = curl::get_response();

    $content = explode('<div id="content">', $response);
    $content = explode('<script>', $content[1]);

    $chapterList = explode('<div class="navig">', $content[0]);
    $chapterList = explode('<div id="readerarea">', $chapterList[1]);

    $imageList = explode('<div id="readerarea">', $content[0]);
    $imageList = explode('<div class="navig">', $imageList[1]);

    preg_match('/<h1 itemprop="name">(.*?)<\/h1>/', $content[0], $title);
    preg_match_all('/value="(\S*).+?"/', $chapterList[0], $chapterUrl);
    preg_match_all('/value="\S*.+?">(\S*.+?)<\/option>/', $chapterList[0], $chapter);
    preg_match_all('/<img src="(.*?)"/', $imageList[0], $image);
    preg_match_all('/width="(.*?)"/', $imageList[0], $width);
    preg_match_all('/height="(.*?)"/', $imageList[0], $height);

    $page = explode('<div class="nextprev">', $response);
    $page = explode('<div id="readerarea">', $page[1]);
    preg_match('/href="(.*?)"/', $page[0], $pagination);

    $array = [
        'isPaging' => (boolean)$pagination[1],
        'page' => [],
        'data' => [
            'title' => $title[1],
        ]
    ];
        if($pagination[1] == true)
        {
            preg_match('/<\/a><a href="(.*?)" rel="next"/', $page[0], $next);
            preg_match('/href="(.*?)" rel="prev"/', $page[0], $prev);

            $array['page'] = [
                    'next' => base64_encode(str_replace('/', '', str_replace(URL . 'chapter/', '', $next[1]))),
                    'prev' => base64_encode(str_replace('/', '', str_replace(URL . 'chapter/', '', $prev[1])))
            ];
        }

    for ($i = 0; $i < count($chapterUrl[1]); $i++)
    {
        $array['data']['chapterList'][] = [
            'code' => base64_encode(str_replace('/', '', str_replace(URL . 'chapter/', '', $chapterUrl[1][$i]))),
            'title' => $chapter[1][$i]
        ];
    }
    
    for ($i = 0; $i < count($image[1]); $i++)
    {
        $array['data']['image'][] = [
            'url' => $image[1][$i],
            'style' => [
                'width' => $width[1][$i],
                'height' => $height[1][$i]
            ]
        ];
    }

    echo json_encode($array, JSON_PRETTY_PRINT);
}