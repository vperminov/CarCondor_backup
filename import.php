<?php

$shortOpts = 'u:';
$shortOpts .= 'p:';
$shortOpts .= 'd:';
$shortOpts .= 'f:';
$shortOpts .= 'h::';
$shortOpts .= 'o::';

$longOpts = [
    'user:',
    'password:',
    'database:',
    'file:',
    'host::',
    'port::'
];

$options = getopt($shortOpts, $longOpts);


$dbUser = $options['u'] ?? $options['user'] ?? false;
$dbPass = $options['p'] ?? $options['password'] ?? false;
$dbName = $options['d'] ?? $options['database'] ?? false;
$file = $options['f'] ?? $options['file'] ?? false;
$dbHost = $options['h'] ?? $options['host'] ?? '127.0.0.1';
$dbPort = $options['o'] ?? $options['port'] ?? '3306';

if (!$dbUser || !$dbPass || !$dbName || !$file) {
    echo "Not all params provided \n";
    exit;
}
$host = $dbHost . ':' . $dbPort;

$ext = substr(strrchr($file, '.'), 1);;

if ($ext == 'gz') {
    $phar = new PharData($file);
    $fileName = $phar->getFilename();
    $tmp = array_slice(explode('/', $file), 1, -1);
    $path = implode('/', $tmp);

    $phar->extractTo('/' . $path, $fileName, true);
    $file = '/' . $path . '/' . $fileName;
}

$sql = file_get_contents($file);


$mysqli = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    throw new RuntimeException('mysqli connection error: ' . $mysqli->connect_error);
}
$mysqli->multi_query($sql);

echo "\nBackup imported\n";