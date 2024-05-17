<?php

namespace App\Traits;

use Illuminate\Support\Facades\Config;
use Storage;
use Carbon\Carbon;

trait ManageFile
{
    protected $prefixPathStorageFile;
    protected $prefixPathSaveFile;
    protected $storage;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function constructor()
    {
        // Set prefix path save file
        if (env('APP_ENV') != 'local' || env('APP_ENV') == 'local' && env('AWS_S3', false)) {
            $this->prefixPathSaveFile = config('filesystems.disks.s3.url');
            $this->storage = Storage::disk('s3');
            $this->prefixPathStorageFile = '';
        } else {
            $this->prefixPathSaveFile = Config::get('app.url') . '/storage';
            $this->storage = Storage::disk('local');
            $this->prefixPathStorageFile = '/public';
        }
    }

    /**
     * Upload File
     *
     * @param  mixed $filePath
     * @param  mixed $file
     *
     * @return boolean
     */
    public function uploadFileToServer($filePath, $file)
    {
        $this->constructor();

        $fileName = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();
        $path = $this->prefixPathStorageFile . $filePath . $fileName;
        // Upload file
        $stream = fopen($file->getRealPath(), 'r+');
        $upload = $this->storage->put($path, $stream);
        fclose($stream);
        // $upload = $this->storage->put($path, file_get_contents($file));

        if ($upload) {
            return $this->prefixPathSaveFile . $path;
        }
        return null;
    }

    /**
     * Delete File
     *
     * @param  mixed $filePath
     *
     * @return boolean
     */
    public function deleteFileInServer($filePath)
    {
        $this->constructor();

        $filePath = str_replace($this->prefixPathSaveFile, '', $filePath);

        // Delete file
        return $this->storage->delete($this->prefixPathStorageFile . $filePath);
    }
}
