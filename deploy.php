<?php
require 'vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$repo_dir = getenv('DIR');
$onbranch = getenv('BRANCH');

$update = false;
$payload = json_decode(file_get_contents('php://input'), true);

if (empty($payload)) {
    file_put_contents('deploy.log', date('m/d/Y h:i:s a')." File accessed with no data\n", FILE_APPEND) or die('log fail');
    die();
}

if (isset($payload['push'])) {
    $lastChange = $payload['push']['changes'][ count($payload['push']['changes']) - 1 ]['new'];
    $branch = isset($lastChange['name']) && !empty($lastChange['name']) ? $lastChange['name'] : '';

    if ($branch == $onbranch) {
        $update = true;
    }
}

if ($update) {
    exec('cd '.$repo_dir.' && '.$git_bin_path.' pull');
    $commit_hash = shell_exec('cd '.$repo_dir.' && '.$git_bin_path.' rev-parse --short HEAD');
    file_put_contents('deploy.log', date('m/d/Y h:i:s a').' Deployed branch: '.$branch.' Commit: '.$commit_hash."\n", FILE_APPEND);
}