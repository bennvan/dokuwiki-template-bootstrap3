<?php
/** Namespace actions for the full-screen media manager. */
if (file_exists(__DIR__ . '/doku_inc.php')) require_once __DIR__ . '/doku_inc.php';
if (!defined('DOKU_INC')) define('DOKU_INC', dirname(__DIR__, 3) . '/');
// Core base-URL detection handles lib/exe and lib/plugins, but not lib/tpl.
// Use the wiki entry point's path so authentication shares the normal login cookie.
$_SERVER['SCRIPT_NAME'] = preg_replace(
    '~/lib/tpl/[^/]+/media-actions\.php$~D',
    '/doku.php',
    $_SERVER['SCRIPT_NAME']
);
require_once DOKU_INC . 'inc/init.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function bootstrap3_media_response($data, $status = 200)
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function bootstrap3_media_namespace_empty($path)
{
    // Count hidden files and child directories too. Never recurse into them.
    return !(new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS))->valid();
}

if ($conf['template'] !== 'bootstrap3') bootstrap3_media_response(['error' => 'denied'], 403);
$method = $INPUT->server->str('REQUEST_METHOD');
if (!in_array($method, ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    bootstrap3_media_response(['error' => 'failed'], 405);
}
$input = $method === 'POST' ? $INPUT->post : $INPUT->get;
$ns = cleanID($input->str('ns'));
$parent = $ns === '' ? $conf['mediadir'] : mediaFN($ns);
if (!is_dir($parent)) bootstrap3_media_response(['error' => 'invalid'], 400);
$canCreate = auth_quickaclcheck($ns === '' ? '*' : $ns . ':*') >= AUTH_UPLOAD;
$canRemove = $ns !== '' && !is_link($parent) && auth_quickaclcheck($ns . ':*') >= AUTH_DELETE;
if ($method === 'GET') {
    bootstrap3_media_response([
        'ns' => $ns, 'canCreate' => $canCreate, 'canRemove' => $canRemove,
        'empty' => $canRemove && bootstrap3_media_namespace_empty($parent),
        'sectok' => getSecurityToken()
    ]);
}
if (!checkSecurityToken($INPUT->post->str('sectok'))) {
    bootstrap3_media_response(['error' => 'token'], 403);
}
$action = $INPUT->post->str('action', 'create');
if ($action === 'remove') {
    if (!$canRemove) bootstrap3_media_response(['error' => 'removeDenied'], 403);
    if (!bootstrap3_media_namespace_empty($parent)) {
        bootstrap3_media_response(['error' => 'notEmpty'], 409);
    }
    // rmdir itself also rejects a directory populated since the check above.
    // Remove exactly this namespace, never files, children, or empty ancestors.
    if (!@rmdir($parent)) bootstrap3_media_response(['error' => 'failed'], 409);
    $eventData = [$ns, 'media'];
    \dokuwiki\Extension\Event::createAndTrigger('IO_NAMESPACE_DELETED', $eventData);
    bootstrap3_media_response(['ns' => (string) getNS($ns)]);
}
if ($action !== 'create') bootstrap3_media_response(['error' => 'invalid'], 400);
if (!$canCreate) bootstrap3_media_response(['error' => 'denied'], 403);

// Create one child at a time. Reject path syntax before cleanID can silently rewrite it.
$name = trim($INPUT->post->str('name'));
if ($name === '' || preg_match('~[:/\\\\\x00-\x1f]~u', $name) || $name === '.' || $name === '..') {
    bootstrap3_media_response(['error' => 'invalid'], 400);
}
$name = cleanID($name);
if ($name === '' || $name[0] === '.' || substr_count($ns, ':') >= 125) {
    bootstrap3_media_response(['error' => 'invalid'], 400);
}
$target = ltrim($ns . ':' . $name, ':');
if (auth_quickaclcheck($target . ':*') < AUTH_UPLOAD) {
    bootstrap3_media_response(['error' => 'denied'], 403);
}
if (file_exists(mediaFN($target))) bootstrap3_media_response(['error' => 'exists'], 409);
// A dummy file ID asks the core to create its parent namespace; no file is written.
io_createNamespace($target . ':namespace', 'media');
if (!is_dir(mediaFN($target))) bootstrap3_media_response(['error' => 'failed'], 500);
bootstrap3_media_response(['ns' => $target]);
