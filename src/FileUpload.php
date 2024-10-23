<?php

namespace Siam401\FileUpload;

use Siam401\FileUpload\Abstracts\FileUploadAbstract;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class FileUpload extends FileUploadAbstract
{
    use Singleton;

    private $file;
    private $multiple = false;

    /**
     * Request file upload process
     *
     * @param UploadedFile|array $file
     * @return array|string
     */
    public function upload($file)
    {
        if ($file instanceof UploadedFile) {

            $this->file = $file;

            $this->response = $this->store();
        } elseif (is_array($file)) {

            $this->multiple = true;
            $this->uploadArray($file);
        } else {

            $this->uploadError = 'Invalid file provided';
        }

        return $this->result();
    }

    /**
     * Handle upload process for array of uploaded files
     *
     * @param array $files
     * @return void
     */
    public function uploadArray(array $files)
    {
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $this->file = $file;
                array_push($this->response, $this->store());
            }
        }
    }

    /**
     * Execute file upload process
     *
     * @return array
     */
    public function store(): array
    {

        $result = Storage::putFileAs($this->uploadPath(), $this->file, $this->generateName());

        if (!$result) {
            return [
                'error' => "File upload failed [{$this->getOriginalName()}]"
            ];
        } else {
            return [
                'filename' => $this->getOriginalName(),
                'mime' => $this->getMimeType(),
                'extention' => $this->getExtension(),
                'path' => $result,
                'url' => Storage::url($result),
                'size' => $this->getFileSize()
            ];
        }
    }

    /**
     * Return file upload process result
     *
     * @return mixed
     */
    public function result()
    {
        return empty($this->response) ? $this->uploadError : (
            $this->response['error'] ?? $this->response
        );
    }

    /**
     * Get total size of the uploaded file in kilobyte
     *
     * @return integer
     */
    protected function getFileSize(): int
    {
        return ceil($this->file->getSize()) / 1024;
    }

    /**
     * Get extension of the uploaded file
     *
     * @return string
     */
    protected function getExtension(): string
    {
        return strtolower($this->file->getClientOriginalExtension());
    }

    /**
     * Get mime-type information of the uploaded file
     *
     * @return string
     */
    protected function getMimeType(): string
    {
        return $this->file->getMimeType();
    }

    /**
     * Get original name of the uploaded file
     *
     * @return string
     */
    protected function getOriginalName(): string
    {
        return pathinfo($this->file->getClientOriginalName(), PATHINFO_FILENAME);
    }

    /**
     * Build unique dynamic file name for the uploaded file
     *
     * @return string
     */
    protected function generateName(): string
    {
        return (!empty($this->defaults['prefix']) && is_string($this->defaults['prefix']) ? $this->defaults['prefix'] . '_' : '')
            . Str::random(8)
            . time()
            . '.' . $this->getExtension();
    }

    /**
     * Get storage path for the uploaded file
     *
     * @return string
     */
    protected function uploadPath(): string
    {
        return date('Ymd') . ((isset($this->defaults['path']) && $this->defaults['path']) ? DIRECTORY_SEPARATOR . $this->defaults['path'] : '');
    }
}
