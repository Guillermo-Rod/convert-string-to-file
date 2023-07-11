<?php

namespace GuillermoRod\StringToFile\Models;

use Illuminate\Database\Eloquent\Model;
use GuillermoRod\StringToFile\Services\StorageService;
use GuillermoRod\StringToFile\ServiceProvider;

class StringToFile extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'converted_string_to_files';
    public const TABLE_NAME = 'converted_string_to_files';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    public const HTML_EXTENSION = '.html';
    public const CSS_EXTENSION  = '.css';
    public const JS_EXTENSION   = '.js';
    public const TXT_EXTENSION  = '.txt';

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            StorageService::deleteFile($model, false);
        });
    }    

    /**
     * Model that belongsTo the string file
     *
     * @return object
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Determine if the string was converted to file
     *
     * @return bool
     */
    public function fileCreated()
    {
        return $this->file_name !== null && trim($this->file_name) !== '' && $this->storageFileExists();
    }

    /**
     * Get Storage disk
     *
     * @return object
     */
    public function getDisk()
    {
        return StorageService::getDisk();
    }

    /**
     * Determine if the file exists on storage
     *
     * @return bool
     */
    public function storageFileExists()
    {
        return StorageService::fileExists($this);
    }

    /**
     * Get html view path
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getViewPath()
    {   
        $this->assertFileExtensionIsCompatible(self::HTML_EXTENSION, __FUNCTION__);

        $parts         = explode('.', $this->file_name);
        $fileExtension = '.' . $parts[1];
        return str_replace(DIRECTORY_SEPARATOR, '.', ServiceProvider::VIEWS_NAMESPACE_ACCESSOR. '::' . str_replace($fileExtension, '', $this->getFileWithDirectory()));
    }

    /**
     * Get url asset js or css
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getUrlAsset()
    {
        $this->assertFileExtensionIsCompatible([self::CSS_EXTENSION, self::JS_EXTENSION], __FUNCTION__);

        return $this->getDisk()->url($this->getFileWithDirectory());
    }

    /**
     * Get file contents
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getFileContents()
    {
        $this->assertFileNameIsNotNull();

        return $this->getDisk()->get($this->getFileWithDirectory());
    }

    /**
     * Get the directory . filename
     *
     * @return string
     */
    public function getFileWithDirectory() 
    {
        return StorageService::getFileWithDirectory($this);
    }
    
    /**
     * Get full file path
     *
     * @return string
     */
    public function getFullFilePath() 
    {
        return StorageService::getFullFilePath($this);
    }

    /**
     * Get Full directory path
     *
     * @return string
     */
    public function getFullDirectoryPath() 
    {
        return StorageService::getFullDirectoryPath($this);
    }

    /**
     * Delete File and set the model_attribute as null
     *
     * @return void
     */
    public function deleteFile()
    {            
        StorageService::deleteFile($this);
    }

    /**
     * Updates file content
     *
     * @param string $richText
     * @param string $extension
     * @return bool
     */
    public function updatesFile($richText, $extension = '')
    {
        return StorageService::updatesFile($this, $richText, $extension);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function assertFileNameIsNotNull()
    {
        if ($this->file_name === null || $this->file_name === '') {
            throw new \InvalidArgumentException('View not found, $model->file_name is null.');
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function assertFileExtensionIsCompatible($compatible, $function)
    {
        $this->assertFileNameIsNotNull();

        $parts         = explode('.', $this->file_name);
        $fileExtension = '.' . $parts[1];

        if (is_array($compatible)) {
            if (! in_array($fileExtension, $compatible)) {
                throw new \InvalidArgumentException("File not found. \n{$this->file_name} extension is not compatible on {$function}() [". implode(', ', $compatible) ."].");
            }            
        }else if ($fileExtension !== $compatible) {
            throw new \InvalidArgumentException("File not found. \n{$this->file_name} extension is not compatible on {$function}() [{$compatible}].");
        }
    }
}
