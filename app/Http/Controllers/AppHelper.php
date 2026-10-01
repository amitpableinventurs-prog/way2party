<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Exception;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class AppHelper extends Controller

{
    public function deleteFile($fileName)
    {
        if ($fileName != "default.jpg") {
            $filePath = "images/upload/" . $fileName;
            if (file_exists($filePath)) {
                if (unlink($filePath)) {
                    return true;
                } else {
                   return false;
                }
            } else {
                return false;
            }

        }
    }
    public function saveImage($request)
    {
        $image = $request->file('image');
        $name = uniqid() . '.' . $image->getClientOriginalExtension();
        $destinationPath = public_path('/images/upload');
        $image->move($destinationPath, $name);
        return $this->optimizeImage($destinationPath, $name);
    }

    /**
     * Shrink an uploaded image in place: cap it at 1600px wide, recompress it, and store
     * opaque PNGs (usually photos — 2MB+ each) as JPEG. Returns the file name to save,
     * which changes only for a PNG converted to JPEG. Any failure keeps the original.
     */
    public function optimizeImage($dir, $name, $maxWidth = 1600)
    {
        $path = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $name;
        if (!extension_loaded('gd') || !is_file($path)) {
            return $name;
        }
        try {
            $info = @getimagesize($path);
            if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP])) {
                return $name;
            }
            [$width, $height, $type] = $info;
            $originalSize = filesize($path);
            if ($width <= $maxWidth && $originalSize < 250 * 1024) {
                return $name;
            }

            $src = match ($type) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
                IMAGETYPE_PNG => @imagecreatefrompng($path),
                IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            };
            if (!$src) {
                return $name;
            }

            // Re-encoding drops EXIF, so bake in the orientation phones record there.
            if ($type == IMAGETYPE_JPEG && function_exists('exif_read_data')) {
                $orientation = (@exif_read_data($path)['Orientation'] ?? 1);
                $angle = [3 => 180, 6 => -90, 8 => 90][$orientation] ?? 0;
                if ($angle) {
                    $src = imagerotate($src, $angle, 0);
                    [$width, $height] = [imagesx($src), imagesy($src)];
                }
            }

            // GD re-encodes transparent PNG/WebP (logos, cut-outs) larger than they started,
            // and slowly — leave those alone.
            if ($type != IMAGETYPE_JPEG && $this->imageHasTransparency($src, $width, $height)) {
                imagedestroy($src);
                return $name;
            }
            if ($width > $maxWidth) {
                $newHeight = (int) round($height * $maxWidth / $width);
                $dst = imagecreatetruecolor($maxWidth, $newHeight);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
                imagedestroy($src);
                $src = $dst;
            }

            $newName = $name;
            $tmp = $path . '.tmp';
            if ($type == IMAGETYPE_PNG) {
                $newName = pathinfo($name, PATHINFO_FILENAME) . '.jpg';
                $ok = imagejpeg($src, $tmp, 82);
            } elseif ($type == IMAGETYPE_WEBP) {
                $ok = imagewebp($src, $tmp, 82);
            } else {
                imageinterlace($src, true);
                $ok = imagejpeg($src, $tmp, 82);
            }
            imagedestroy($src);

            if (!$ok || !is_file($tmp) || filesize($tmp) >= $originalSize) {
                @unlink($tmp);
                return $name;
            }
            $newPath = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $newName;
            if (!rename($tmp, $newPath)) {
                @unlink($tmp);
                return $name;
            }
            if ($newPath !== $path) {
                @unlink($path);
            }
            return $newName;
        } catch (\Throwable $e) {
            @unlink($path . '.tmp');
            return $name;
        }
    }

    private function imageHasTransparency($img, $width, $height)
    {
        if (!imageistruecolor($img)) {
            return imagecolortransparent($img) >= 0;
        }
        // Sampling a grid is enough to tell a photo from a logo/cut-out.
        $step = max(1, (int) floor(min($width, $height) / 200));
        for ($x = 0; $x < $width; $x += $step) {
            for ($y = 0; $y < $height; $y += $step) {
                if ((imagecolorat($img, $x, $y) >> 24) & 0x7F) {
                    return true;
                }
            }
        }
        return false;
    }

    public function saveApiImage($request)
    {
        $img = $request->image;
        $img = str_replace('data:image/png;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $img_code = base64_decode($img);
        $Iname = uniqid();
        $file = public_path('/images/upload/') . $Iname . ".png";
        $success = file_put_contents($file, $img_code);
        $image_name = $Iname . ".png";
        return $image_name;
    }

    public function saveEnv($envData)
    {
        $envFile = app()->environmentFilePath();
        if ($envFile) {
            $str = file_get_contents($envFile);
            if (count($envData) > 0) {
                foreach ($envData as $envKey => $envValue) {
                    $keyPosition = strpos($str, "{$envKey}=");
                    $endOfLinePosition = strpos($str, "\n", $keyPosition);
                    $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);
                    if (!$keyPosition || !$endOfLinePosition || !$oldLine) {
                        $str .= "{$envKey}={$envValue}\n";
                    } else {
                        $str = str_replace($oldLine, "{$envKey}={$envValue}", $str);
                    }
                }
            }
            $str = substr($str, 0, -1);
            try {
                if (file_put_contents($envFile, $str)) {
                    return true;
                }
            } catch (Exception $e) {
                Log::info($e->getMessage());
                return redirect()->route('admin-setting')->with('Exception', $e->getMessage());

                return $e;
            }
        }
    }

    public function mailConfig()
    {
        $setting = Setting::current();
        if ($setting->mail_notification) {
            Config::set('mail.default', $setting->mail_mailer);
            Config::set('mail.mailers.smtp.host', $setting->mail_host);
            Config::set('mail.mailers.smtp.port', $setting->mail_port);
            Config::set('mail.mailers.smtp.username', $setting->mail_username);
            Config::set('mail.mailers.smtp.password', $setting->mail_password);
            Config::set('mail.mailers.smtp.encryption', $setting->mail_encryption);
            Config::set('mail.from',  ['address' => $setting->sender_email, 'name' => $setting->app_name]);
        }
        return true;
    }

    public function sendOneSignal($for, $device_token, $message,$imageUrl=null)
    {
        $setting = Setting::current();
        if ($for == 'organizer') {
            $app_id = $setting->or_onesignal_app_id;
        } else {
            $app_id = $setting->onesignal_app_id;
        }
        try {
            $content1 = array("en" => $message);
            $fields1 = array(
                'app_id' => $app_id,
                'include_player_ids' => array($device_token),
                'data' => null,
                'contents' => $content1,
                'big_picture' => $imageUrl,
            );
            $fields1 = json_encode($fields1);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $response = curl_exec($ch);
            curl_close($ch);
        } catch (\Throwable $th) {
        }
        return true;
    }

    public function eventStatusChange()
    {
        // Called from almost every page/API request. Sweep at most once a minute, and as a
        // single UPDATE rather than loading every pending order and saving it one by one.
        if (!Cache::add('orders:complete-ended-events', true, 60)) {
            return true;
        }
        Order::whereOrderStatus('Pending')->whereHas('event', function ($q) {
            $q->where('end_time', '<=', Carbon::now()->format('Y-m-d H:i:s'));
        })->update(['order_status' => 'Complete']);
        return true;
    }
}
