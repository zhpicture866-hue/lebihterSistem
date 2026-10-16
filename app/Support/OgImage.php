<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class OgImage
{
    private const W         = 1200;
    private const H         = 630;
    private const MAX_BYTES = 280 * 1024;

    /**
     * Buat versi 1200x630 JPEG (< 280 KB) dari gambar di disk 'public'.
     * Mengembalikan path relatif (mis. articles/og/xxx.jpg) atau null jika gagal.
     */
    public static function make(string $sourcePath, ?string $oldPath = null): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;   // ekstensi GD belum aktif
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($sourcePath)) {
            return null;
        }

        $bytes = $disk->get($sourcePath);
        $src   = @imagecreatefromstring($bytes);
        if (! $src) {
            return null;
        }

        $src = self::autoRotate($src, $bytes);
        $sw  = imagesx($src);
        $sh  = imagesy($src);

        // Potong (cover) ke rasio 1200:630, condong ke atas supaya wajah aman
        $scale = max(self::W / $sw, self::H / $sh);
        $cw    = (int) round(self::W / $scale);
        $ch    = (int) round(self::H / $scale);
        $cx    = (int) floor(($sw - $cw) / 2);
        $cy    = (int) floor(($sh - $ch) * 0.35);

        $dst = imagecreatetruecolor(self::W, self::H);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));   // latar putih (PNG transparan)
        imagecopyresampled($dst, $src, 0, 0, $cx, $cy, self::W, self::H, $cw, $ch);
        imagedestroy($src);

        // Turunkan kualitas bertahap sampai ukuran aman
        $data = '';
        foreach ([85, 78, 70, 62, 55, 48, 40] as $quality) {
            ob_start();
            imagejpeg($dst, null, $quality);
            $data = (string) ob_get_clean();
            if (strlen($data) <= self::MAX_BYTES) {
                break;
            }
        }
        imagedestroy($dst);

        $path = 'articles/og/' . pathinfo($sourcePath, PATHINFO_FILENAME) . '.jpg';
        $disk->put($path, $data);

        if ($oldPath && $oldPath !== $path) {
            $disk->delete($oldPath);
        }

        return $path;
    }

    /** Putar sesuai EXIF (foto dari ponsel sering "tidur" tanpa ini). */
    private static function autoRotate($img, string $bytes)
    {
        if (! function_exists('exif_read_data')) {
            return $img;
        }

        $exif = @exif_read_data('data://image/jpeg;base64,' . base64_encode($bytes));
        $o    = $exif['Orientation'] ?? 1;
        $deg  = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;

        if ($deg !== 0) {
            $rotated = imagerotate($img, $deg, 0);
            if ($rotated) {
                imagedestroy($img);

                return $rotated;
            }
        }

        return $img;
    }

    /** Pakai versi OG yang sudah ada, atau buat sekali jika belum. */
public static function ensure(string $sourcePath): ?string
{
    $target = 'articles/og/' . pathinfo($sourcePath, PATHINFO_FILENAME) . '.jpg';

    return Storage::disk('public')->exists($target) ? $target : self::make($sourcePath);
}
}