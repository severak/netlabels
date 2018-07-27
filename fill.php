<?php
$endpoint = 'https://archive.org/services/search/v1/scrape';

$params['q'] = 'collection:netlabels';
$params['fields'] = 'creator,title,licenseurl';
$params['count'] = 500;

error_reporting(E_ALL & ~E_NOTICE);

$pdo = new PDO('sqlite:' . __DIR__ . '/items.db3');

$insert = $pdo->prepare('INSERT INTO items (identifier, creator, title, licenseurl) VALUES (?, ?, ?, ?)');

$pages = 0;
while ($pages < 100) {
    echo 'fetching page ' . $pages . PHP_EOL;
    $response = file_get_contents($endpoint . '?' . http_build_query($params));
    if ($response && $returned = json_decode($response, true)) {
        foreach ($returned['items'] as $item) {
            $ok = $insert->execute([ $item['identifier'], $item['creator'], $item['title'], $item['licenseurl'] ]);
            echo 'insert ' . $item['identifier'] . ($ok ? ' ok' : ' ERROR') . PHP_EOL;
        }
        $params['cursor'] = $returned['cursor'];
        $pages++;
        echo 'sleeping...' . PHP_EOL;
        sleep(3);
    } else {
        break;
    }
}

echo 'konet' . PHP_EOL;