<?php
require_once __DIR__ . '/src/alfred.php';
require_once __DIR__ . '/src/db.php';

$config = array_merge(
    require_once __DIR__ . '/src/defaults.php',
    require_once __DIR__ . '/_config.php'
);

try {
    $db = db_connect();
    [$originalQuery, $sqliteFtsQuery] = alfred_query_to_sqlite($argv);

    $result = db_query_fts($db, $sqliteFtsQuery, $config);    

    $items = [];
    while($r = $result->fetchArray(SQLITE3_ASSOC)) {
        $snip = str_replace("\n", '↩', $r['snippet']); // replace newlines with ↩

        $item = [
            'title' => $r['title'],
            'subtitle' => $r['path']
                ? "{$r['path']} • {$snip}"
                : $snip,
            'arg' => $r['callback'] . '&useExistingSubWindow=yes',
            'icon' => [
                'path' => __DIR__ . "/icons/noteplan-{$r['type']}.png",
            ],
            "mods" => [
                "cmd" => [
                    "arg" => $r['callback'] . '&subWindow=yes',
                    "subtitle" => "Open in a new window"
                ]
            ]
        ];

        $items []= $item;
    }

    echo alfred_itemize($items);
} catch (\Throwable $th) {
    echo alfred_return_error($th);
}