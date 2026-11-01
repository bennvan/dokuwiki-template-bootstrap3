<?php
/** Receive an avatar photo from the profile page. */
if (file_exists(__DIR__ . '/doku_inc.php')) require_once __DIR__ . '/doku_inc.php';
if (!defined('DOKU_INC')) define('DOKU_INC', dirname(__DIR__, 3) . '/');
// Share the wiki's login-cookie path, including installs in a subdirectory.
$_SERVER['SCRIPT_NAME'] = preg_replace(
    '~/lib/tpl/[^/]+/avatar-upload\.php$~D', '/doku.php', $_SERVER['SCRIPT_NAME']
);
require_once DOKU_INC . 'inc/init.php';
require_once __DIR__ . '/AvatarUpload.php';

header('Cache-Control: no-store');
if ($INPUT->server->str('REQUEST_METHOD') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit;
}
$avatarAction = $INPUT->post->str('avatar_action', 'upload');
if (!in_array($avatarAction, ['upload', 'reset'], true)) {
    http_response_code(400);
    exit;
}
if ($conf['template'] !== 'bootstrap3' || !\dokuwiki\template\bootstrap3\AvatarUpload::allowed($avatarAction === 'reset')) {
    http_response_code(403);
    exit;
}
$postLimit = php_to_byte(ini_get('post_max_size'));
if ($postLimit > 0 && $INPUT->server->int('CONTENT_LENGTH') > $postLimit) {
    msg(hsc(tpl_getLang('avatar_too_large')), -1);
} elseif (checkSecurityToken($INPUT->post->str('sectok'))) {
    try {
        if ($avatarAction === 'reset') {
            \dokuwiki\template\bootstrap3\AvatarUpload::reset();
        } else {
            \dokuwiki\template\bootstrap3\AvatarUpload::save($_FILES['avatar'] ?? null);
        }
    } catch (\RuntimeException $e) {
        msg(hsc($e->getMessage()), -1);
    }
}
send_redirect(wl('', ['do' => 'profile'], true, '&') . '#dw__avatar');
