<?php

namespace App\Services\FileUpload\Abstracts;

use App\Services\FileUpload\Interfaces\FileUploadInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;

abstract class FileUploadAbstract implements FileUploadInterface
{
    protected $defaults;
    protected $uploadError;
    protected $response;

    public function __construct()
    {
        $this->defaults = config('filesystems.settings');
        $this->uploadError = false;
        $this->response = [];
    }

    public function setConfig(array $settings)
    {
        // if (array_key_exists("validation", $settings)) {
        //     $settings['validation'] = array_merge($this->defaults['validation'], $settings['validation']);
        // }

        $this->defaults = array_merge($this->defaults, $settings);

        if(isset($this->defaults['validation']['maxSize']) && $this->defaults['validation']['maxSize']) {
            $this->defaults['validation']['maxSize'] = $this->_sizeByteConvertion($this->defaults['validation']['maxSize']) / 1024;
        }

        if (isset($this->defaults['validation']['minSize']) && $this->defaults['validation']['minSize']) {
            $this->defaults['validation']['minSize'] = $this->_sizeByteConvertion($this->defaults['validation']['minSize']) / 1024;
        }

        return $this;
    }

    abstract protected function getExtension():string;

    abstract protected function getFileSize():int;

    abstract protected function generateName():string;

    abstract protected function getOriginalName():string;

    public function upload($file){}

    public static function getUrl(string $path): string
    {
        if (!self::exists($path)) {
            return url('assets/images/not-found.jpg');
        }

        $url = Storage::url($path);

        if(filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
        return url($url);
    }

    public static function render(string $path)
    {

        if (!self::exists($path)) {
            abort(404, 'File not found');
        }

        $uploadPath = config('filesystems.disks')[config('filesystems.default')]['root'] . DIRECTORY_SEPARATOR . $path;

        $file = File::get($uploadPath);
        $type = File::mimeType($uploadPath);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    }

    public static function remove(string|null $path)
    {
        return empty($path) ? false :  Storage::delete($path);
    }

    public static function removeDirectory(string $path): bool
    {
        if (self::exists($path)) {
            Storage::deleteDirectory($path);
        }

        return true;
    }

    public static function storagePath(string $path): string|null
    {
        if (!self::exists($path)) {
            return null;
        }

        return Storage::path($path);
    }

    public static function exists(string $path): bool
    {
        return Storage::exists($path);
    }

    public static function getUploadTime(string $path)
    {
        if (!self::exists($path)) {
            return null;
        }

        return Carbon::parse(date('Y-m-d H:i:s', Storage::lastModified($path)));
    }

    private function _sizeByteConvertion($maxSize): int|null
    {
        if(is_numeric($maxSize)) {
            return intval($maxSize);
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $number = substr($maxSize, 0, -2);
        $suffix = strtoupper(substr($maxSize, -2));

        if (is_numeric(substr($suffix, 0, 1))) {
            return preg_replace('/[^\d]/', '', $maxSize);
        }

        $exponent = array_flip($units)[$suffix] ?? null;
        if ($exponent === null) {
            return null;
        }

        return $number * (1024 ** $exponent);
    }
}
