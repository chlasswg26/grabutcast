<?php
require_once('lib.function.php');

use simple_curl\curl;

$page = $_GET['page'];

    if ((!empty($page) || isset($page)) && is_numeric($page))
    {
        $snipe = URL . 'page/' . $page . '/';
    } else {
        $snipe = URL;
    }

    curl::prepare($snipe, NULL);
    curl::exec_get();
    $response = curl::get_response();

    $content = explode('<div class="listupd">', $response);
    $content = explode('<div class="hpage">', $content[1]);

    preg_match_all('/<a class="series" href="(.*?)"/', $content[0], $channelUrl);
    preg_match_all('/<a class="series" href=".*?" title="(.*?)"/', $content[0], $channel);
    preg_match_all('/<ul class="(.*?)"/', $content[0], $type);
    preg_match_all('/<img .*? src="(.*?)"/', $content[0], $image);
    preg_match_all('/<ul class=".*?">\s+<li><a href="(.*?)"/', $content[0], $chapterUrl);
    preg_match_all('/<ul class=".*?">\s+<li><a href=".*?">(.*?)<\/a>/', $content[0], $chapter);
    preg_match_all('/<ul class=".*?">\s+.*?<i>(.*?)<\/i>/', $content[0], $chapterUpdated);

    $page = explode('<div class="pagination">', $response);
    $page = explode('</div>', $page[1]);
    preg_match('/class="page-numbers current">(\d)<\/span>/', $page[0], $pagination);

    $array = [];

    for ($i = 0; $i < count($type[1]); $i++)
    {
        $array[] = [
            'image' => $image[1][$i],
            'type' => [
                'code' => base64_encode(strtolower($type[1][$i])),
                'title' => $type[1][$i]
            ],
            'channel' => [
                'code' => base64_encode(str_replace('/', '', str_replace(URL . 'komik/', '', $channelUrl[1][$i]))),
                'title' => $channel[1][$i]
            ],
            'chapter' => [
                'code' => base64_encode(str_replace('/', '', str_replace(URL . 'chapter/', '', $chapterUrl[1][$i]))),
                'title' => $chapter[1][$i],
                'updated' => $chapterUpdated[1][$i]
            ]
        ];
    }

    echo json_encode($array, JSON_PRETTY_PRINT);