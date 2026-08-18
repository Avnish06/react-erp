<?php

$files = glob(__DIR__ . '/app/Models/*.php');
foreach($files as $f) {
    $c = file_get_contents($f);
    // Replace any occurrence of empty array element commas created by the previous script
    // E.g., `,\r\n    , 'company_id']` or `, , 'company_id']`
    // More robustly: find `,\s*,\s*'company_id'` and replace with `,\n        'company_id'`
    
    $c = preg_replace('/,\s*,\s*\'company_id\'\s*\]/', ",\n        'company_id'\n    ]", $c);
    
    file_put_contents($f, $c);
}
echo "Models fixed.\n";
