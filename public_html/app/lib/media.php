<?php
declare(strict_types=1);

const MEDIA_MAX_BYTES = 8 * 1024 * 1024;   // 8 MB per image
const MEDIA_MAX_WIDTH = 1800;              // larger images are scaled down
const MEDIA_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];

/**
 * Validates and stores one uploaded image. Returns the media row, or throws with a readable message.
 * Images are re-encoded with GD when available, which strips anything hidden inside the file.
 */
function store_upload(array $file, ?int $userId, string $alt = ''): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $msg = [
            UPLOAD_ERR_INI_SIZE => 'The image is larger than your server allows. Try one under ' . ini_get('upload_max_filesize') . '.',
            UPLOAD_ERR_FORM_SIZE => 'The image is too large.',
            UPLOAD_ERR_PARTIAL => 'The upload was interrupted. Try again.',
            UPLOAD_ERR_NO_FILE => 'Choose an image to upload.',
        ][$file['error'] ?? UPLOAD_ERR_NO_FILE] ?? 'The upload failed. Try again.';
        throw new RuntimeException($msg);
    }
    if (!is_uploaded_file($file['tmp_name'])) throw new RuntimeException('The upload failed. Try again.');
    if ($file['size'] > MEDIA_MAX_BYTES) throw new RuntimeException('Images can be up to 8 MB.');

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) ?: '';
    if (!isset(MEDIA_TYPES[$mime])) throw new RuntimeException('Use a JPG, PNG, WebP or GIF image.');
    $info = @getimagesize($file['tmp_name']);
    if (!$info || $info[0] < 1 || $info[1] < 1) throw new RuntimeException('That file isn’t a readable image.');
    [$w, $h] = $info;
    if ($w * $h > 40000000) throw new RuntimeException('That image is too large in pixels. Resize it under 40 megapixels.');

    $ext = MEDIA_TYPES[$mime];
    $sub = gmdate('Y/m');
    $dir = UPLOAD_DIR . '/' . $sub;
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) throw new RuntimeException('The uploads folder isn’t writable. Set the uploads folder permission to 755.');
    $base = slugify(pathinfo((string)$file['name'], PATHINFO_FILENAME));
    $name = substr($base, 0, 60) . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = $dir . '/' . $name;

    $saved = false;
    if ($mime !== 'image/gif' && function_exists('imagecreatetruecolor')) {
        $src = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
            'image/png' => @imagecreatefrompng($file['tmp_name']),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file['tmp_name']) : false,
            default => false,
        };
        if ($src) {
            if ($mime === 'image/jpeg' && function_exists('exif_read_data')) $src = fix_orientation($src, $file['tmp_name'], $w, $h);
            $nw = min($w, MEDIA_MAX_WIDTH);
            $nh = (int)round($h * $nw / $w);
            $dst = imagecreatetruecolor($nw, $nh);
            if ($mime !== 'image/jpeg') { imagealphablending($dst, false); imagesavealpha($dst, true); }
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            $saved = match ($mime) {
                'image/jpeg' => imagejpeg($dst, $dest, 84),
                'image/png' => imagepng($dst, $dest, 7),
                'image/webp' => imagewebp($dst, $dest, 84),
            };
            imagedestroy($src); imagedestroy($dst);
            if ($saved) { $w = $nw; $h = $nh; }
        }
    }
    if (!$saved && !move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('The image couldn’t be saved. Check that the uploads folder is writable.');
    }
    @chmod($dest, 0644);

    $path = 'uploads/' . $sub . '/' . $name;
    q('INSERT INTO media (path, original_name, mime, width, height, size, alt, uploaded_by, created_at) VALUES (?,?,?,?,?,?,?,?,?)',
        [$path, mb_substr((string)$file['name'], 0, 255), $mime, $w, $h, (int)filesize($dest), mb_substr($alt, 0, 255), $userId, now_utc()]);
    return q_one('SELECT * FROM media WHERE id = ?', [(int)db()->lastInsertId()]);
}

function fix_orientation($img, string $file, int &$w, int &$h)
{
    $exif = @exif_read_data($file);
    $o = (int)($exif['Orientation'] ?? 1);
    $deg = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
    if ($deg) {
        $r = imagerotate($img, $deg, 0);
        if ($r) { imagedestroy($img); $img = $r; if ($deg !== 180) [$w, $h] = [$h, $w]; }
    }
    return $img;
}

/** How many articles use this image (as cover or inside the text). */
function media_usage(array $m): int
{
    $like = '%' . $m['path'] . '%';
    return (int)q_val('SELECT COUNT(*) FROM posts WHERE cover_image = ? OR content LIKE ?', [$m['path'], $like]);
}

function delete_media(array $m): void
{
    $file = ROOT_DIR . '/' . $m['path'];
    if (str_starts_with($m['path'], 'uploads/') && is_file($file)) @unlink($file);
    q('DELETE FROM media WHERE id = ?', [(int)$m['id']]);
}
