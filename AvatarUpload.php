<?php

namespace dokuwiki\template\bootstrap3;

use dokuwiki\Form\Form;
use dokuwiki\Form\InputElement;
use splitbrain\slika\Slika;

/** Profile photos, stored through DokuWiki's normal media upload pipeline. */
class AvatarUpload
{
    public static function mediaId()
    {
        return cleanID('user:' . ($_SERVER['REMOTE_USER'] ?? '') . ':public:avatar.png');
    }

    public static function allowed($delete = false)
    {
        global $USERINFO, $auth, $conf;
        $user = $_SERVER['REMOTE_USER'] ?? '';
        $groups = $USERINFO['grps'] ?? [];
        if ($user === '' || !is_array($groups)
            || !array_intersect(['user', 'admin'], $groups)
            || !actionOK('profile') || !$auth || !$auth->canDo('Profile')) return false;

        $required = $delete || (is_file(mediaFN(self::mediaId())) && !$conf['mediarevisions'])
            ? AUTH_DELETE : AUTH_UPLOAD;
        return auth_quickaclcheck(getNS(self::mediaId()) . ':*') >= $required;
    }

    public static function renderForm()
    {
        if (!self::allowed()) return;
        $form = new Form([
            'id' => 'dw__avatar',
            'action' => tpl_basedir() . 'avatar-upload.php',
            'enctype' => 'multipart/form-data',
        ]);
        $form->addFieldsetOpen()->attr('aria-labelledby', 'update-profile-photo');
        if (is_file(mediaFN(self::mediaId()))) {
            $url = ml(self::mediaId(), ['v' => sha1_file(mediaFN(self::mediaId()))], true, '&');
            $form->addHTML('<p><img class="img-circle" width="120" height="120" alt="" src="' . hsc($url) . '" /></p>');
        }
        $limit = min(php_to_byte(ini_get('upload_max_filesize')), php_to_byte(ini_get('post_max_size')) ?: PHP_INT_MAX);
        $file = new InputElement('file', 'avatar', tpl_getLang('avatar_photo'));
        $file->attrs([
            'accept' => 'image/jpeg,image/png,image/webp',
            'required' => 'required',
            'aria-describedby' => 'dw__avatar_help',
        ]);
        $file->getLabel()->addClass('block');
        $form->addElement($file);
        $form->addHTML('<p id="dw__avatar_help" class="help-block small text-left">' .
            hsc(sprintf(tpl_getLang('avatar_help'), filesize_h($limit))) . '</p>');
        $form->addButton('', tpl_getLang('avatar_save'))->attr('type', 'submit')->addClass('btn btn-primary');
        if (self::allowed(true) && (is_file(mediaFN(self::mediaId())) || is_file(mediaFN(self::jpegId())))) {
            $form->addButton('avatar_action', tpl_getLang('avatar_reset'))->val('reset')->attrs([
                'type' => 'submit',
                'formnovalidate' => 'formnovalidate',
                'title' => tpl_getLang('avatar_reset_help'),
            ])->addClass('btn btn-danger');
        }
        $form->addFieldsetClose();
        echo '<h1 id="update-profile-photo" data-dw-icon="mdi:camera">' .
            hsc(tpl_getLang('avatar_heading')) . '</h1>';
        echo $form->toHTML();
    }

    /** Decode the actual image, correct phone orientation, crop and re-encode. */
    public static function thumbnail($source, $target)
    {
        global $conf;
        $size = @getimagesize($source);
        if (!$size || !in_array($size[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            throw new \RuntimeException(tpl_getLang('avatar_invalid'));
        }
        $pixels = $size[0] * $size[1];
        $memory = php_to_byte(ini_get('memory_limit'));
        // Bound decoding/rotation memory before handing an upload to GD.
        if ($pixels <= 0 || $pixels > 40000000
            || ($memory > 0 && $pixels * 16 + 16777216 > $memory - memory_get_usage(true))) {
            throw new \RuntimeException(tpl_getLang('avatar_too_large'));
        }
        try {
            @Slika::run($source, ['imconvert' => $conf['im_convert']])
                ->autorotate()->crop(120, 120)->save($target, 'png');
        } catch (\Exception $e) {
            throw new \RuntimeException(tpl_getLang('avatar_invalid'));
        }
        $result = @getimagesize($target);
        if (!$result || $result[0] !== 120 || $result[1] !== 120 || $result[2] !== IMAGETYPE_PNG) {
            throw new \RuntimeException(tpl_getLang('avatar_failed'));
        }
    }

    public static function save($upload)
    {
        if (!self::allowed()) throw new \RuntimeException(tpl_getLang('avatar_denied'));
        $error = is_array($upload) ? ($upload['error'] ?? UPLOAD_ERR_NO_FILE) : UPLOAD_ERR_NO_FILE;
        if (in_array($error, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            throw new \RuntimeException(tpl_getLang('avatar_too_large'));
        }
        if ($error !== UPLOAD_ERR_OK || !is_string($upload['tmp_name'] ?? null)
            || !is_uploaded_file($upload['tmp_name'])) {
            throw new \RuntimeException(tpl_getLang('avatar_upload_error'));
        }
        $temp = tempnam(sys_get_temp_dir(), 'dw-avatar-');
        if ($temp === false) throw new \RuntimeException(tpl_getLang('avatar_failed'));
        try {
            self::thumbnail($upload['tmp_name'], $temp);
            require_once DOKU_INC . 'inc/media.php';
            $id = self::mediaId();
            $result = media_save(
                ['name' => $temp, 'mime' => 'image/png', 'ext' => 'png'],
                $id, true, auth_quickaclcheck(getNS($id) . ':*'), 'copy'
            );
            if ($result !== $id) throw new \RuntimeException(tpl_getLang('avatar_failed'));
        } finally {
            if (is_file($temp)) unlink($temp);
        }
    }

    private static function jpegId()
    {
        return substr(self::mediaId(), 0, -4) . '.jpg';
    }

    /** Remove both supported local filenames so an old JPEG cannot reappear. */
    public static function reset()
    {
        if (!self::allowed(true)) throw new \RuntimeException(tpl_getLang('avatar_denied'));
        require_once DOKU_INC . 'inc/media.php';
        $ids = array_filter([self::mediaId(), self::jpegId()], static fn($id) => is_file(mediaFN($id)));
        foreach ($ids as $id) {
            if (media_inuse($id)) throw new \RuntimeException(tpl_getLang('avatar_inuse'));
        }
        foreach ($ids as $id) {
            if (!(media_delete($id, AUTH_DELETE) & DOKU_MEDIA_DELETED)) {
                throw new \RuntimeException(tpl_getLang('avatar_failed'));
            }
        }
        // The existing local-initials provider regenerates the avatar on reload.
        msg(tpl_getLang('avatar_reset_done'), 1);
    }
}
