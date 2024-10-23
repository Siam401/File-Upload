<?php

namespace App\Services\FileUpload\Interfaces;

interface FileUploadInterface
{
    public function setConfig(array $settings);

    public function upload($file);

    public static function remove(string $file);

    public static function getUrl(string $path): string;

    public static function render(string $path);
}
