<?php 

namespace GuillermoRod\StringToFile\Services;

use GuillermoRod\StringToFile\Models\StringToFile;
use Illuminate\Support\Facades\Storage;
use GuillermoRod\StringToFile\ServiceProvider;

class StorageService 
{    
    public static function getDisk()
    {
        return Storage::disk(config(ServiceProvider::CONFIG_FILE . '.disk_name'));
    }

    public static function getDirectoryName(StringToFile $model)
    {
        return $model->id;
    }

    public static function getFileName(StringToFile $model)
    {
        return $model->file_name;
    }

    public static function getFileExtensionFromName(StringToFile $model)
    {
        if ($model->file_name === null) return ;
        $parts = explode('.', $model->file_name);
        return '.' . $parts[1];
    }

    public static function fileExists($model)
    {
        return static::getDisk()->exists(static::getFileWithDirectory($model));
    }

    public static function getFileWithDirectory($model)
    {
        return static::getDirectoryName($model) . '/' . static::getFileName($model);
    }

    public static function getFullFilePath(StringToFile $model)
    {
        return static::getFullDirectoryPath($model) . '/' . static::getFileName($model);
    }

    public static function getFullDirectoryPath(StringToFile $model)
    {
        return static::getDisk()->path(static::getDirectoryName($model));
    }


    /**
     * Delete the file and directory
     *
     * @param StringToFile $model
     * @param bool $affectRow
     * @return void
     */
    public static function deleteFile(StringToFile $model, $affectRow = true)
    {
        static::getDisk()->delete(static::getFileWithDirectory($model));

        if ($affectRow) {
            // Preserve model but with filename as null
            $model->update(['file_name' => null]);
        }
    }

    /**
     * Updates or create storage file
     * with contents and extension
     *
     * @param StringToFile $model
     * @param string|null $richText
     * @param string|null $fileExtension
     * @return void
     */
    public static function updatesFile(StringToFile $model, $richText, $fileExtension = '')
    {   
        $fileExtension = $fileExtension ? $fileExtension : config(ServiceProvider::CONFIG_FILE . '.default_extension', StringToFile::HTML_EXTENSION);

        if ($model->file_name === null) {
            $model->file_name = $model->model_attribute . $fileExtension;
        }else {
            // Extension was changed to another
            if (static::getFileExtensionFromName($model) !== $fileExtension) {                
                $model->file_name = $model->model_attribute . $fileExtension;
                static::getDisk()->deleteDirectory(static::getDirectoryName($model)); // Clear directory
            }
        }

        $stored = static::getDisk()->put(static::getFileWithDirectory($model), $richText) !== false;

        if (! $stored) return false;

        // Active the filename
        if ($model->isDirty('file_name')) {
            return $model->update(['file_name' => $model->file_name]);
        }

        return true;
    }
}