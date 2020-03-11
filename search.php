<?php
require_once('lib.function.php');

use simple_curl\curl;

$query = strtolower(str_replace(' ', '+', $_GET['query']));
$page = $_GET['page'];

if (empty($query) || !isset($query))
{
    die();
} else {
    if ((!empty($page) || isset($page)) && is_numeric($page))
    {
        $snipe = URL . 'page/' . $page . '/?s=' . $query . '&post_type=post';
    } else {
        $snipe = URL . '?s=' . $query . '&post_type=post';
    }

    curl::prepare($snipe, NULL);
    curl::exec_get();
    $response = curl::get_response();

    $content = explode('<main id="main" class="site-main" role="main">', $response);
    $content = explode('</main>', $content[1]);

    preg_match_all('/<div class="bsx">\s+<a href="(.*?)"/', $content[0], $channelUrl);
    preg_match_all('/<div class="bsx">\s+.*? title="(.*?)"/', $content[0], $channel);
    preg_match_all('/<span class="type (.*?)"/', $content[0], $type);
    preg_match_all('/data-cfsrc="(.*?)"/', $content[0], $image);
    preg_match_all('/<div class="adds">\s+.*? href="(.*?)"/', $content[0], $chapterUrl);
    preg_match_all('/<div class="adds">\s+.*? href=".*?">(.*?)<\/a>/', $content[0], $chapter);
    preg_match_all('/data-current-rating="(.*?)"/', $content[0], $starsOneToFive);
    preg_match_all('/<\/select>\s+.*?<i>(.*?)<\/i>/', $content[0], $scoresOneToTen);

    $page = explode('<div class="pagination">', $response);
    $page = explode('</div>', $page[1]);
    preg_match('/class="page-numbers current">(\d)<\/span>/', $page[0], $pagination);

    $array = [
        'isPaging' => (boolean)$pagination[1],
        'page' => [],
        'data' => []
    ];
        if($pagination[1] == true)
        {
            preg_match('/<a class="next page-numbers" href=".*?page\/(\d)\//', $page[0], $next);
            preg_match('/<a class="prev page-numbers" href=".*?page\/(\d)\//', $page[0], $prev);
            $array['page'] = [
                    'next' => $next[1],
                    'prev' => $prev[1]
            ];
        }

    for ($i = 0; $i < count($type[1]); $i++)
    {
        $array['data'][] = [
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
                'title' => $chapter[1][$i]
            ],
            'rating' => [
                'starsOTF' => $starsOneToFive[1][$i],
                'scoresOTT' => $scoresOneToTen[1][$i]
            ]
        ];
    }

    echo json_encode($array, JSON_PRETTY_PRINT);
}