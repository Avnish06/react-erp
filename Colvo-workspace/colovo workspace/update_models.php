<?php

$files = glob(__DIR__ . '/app/Models/*.php');
foreach($files as $f) {
    $c = file_get_contents($f);
    if (preg_match('/protected\s+\$fillable\s*=\s*\[(.*?)\];/s', $c, $m)) {
        if (strpos($m[1], 'company_id') === false) {
            $c = str_replace($m[0], 'protected $fillable = [' . $m[1] . ", 'company_id'];", $c);
            file_put_contents($f, $c);
            echo "Updated " . basename($f) . "\n";
        }
    }
}
