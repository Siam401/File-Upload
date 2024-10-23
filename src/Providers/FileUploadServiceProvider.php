<?php

namespace YourVendorName\FileUpload\Providers;

use Illuminate\Support\ServiceProvider;
use FileUpload\FileUpload;

class FileUploadServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton('fileupload', function ($app) {
            return new FileUpload();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // You can include package routes or publish assets here if necessary
    }
}
