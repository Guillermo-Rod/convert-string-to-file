<?php 

namespace GuillermoRod\StringToFile;

use GuillermoRod\StringToFile\StringToFileObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait ConvertsStringToFile
{
    /**
     * Hook into the Eloquent model events to create or
     * update the slug as required.
     * 
     * @return void
     */
    public static function bootConvertsStringToFile()
    {
        static::observe(app(StringToFileObserver::class));

        static::deleting(function ($model) {
            if (method_exists($model, 'shouldDeletePreservingFile') && ! $model->shouldDeletePreservingFile()) {
                return;
            }
            
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $model->string_files()->cursor()->each(fn ($stringFile) => $stringFile->delete());
        });
    }

    /**
     * Register a converting_string_to_file model event with the dispatcher.
     *
     * @param \Closure|string $callback
     * @return void
     */
    public static function converting_string_to_file($callback)
    {
        static::registerModelEvent('converting_string_to_file', $callback);
    }

    /**
     * Register a converted_string_to_file model event with the dispatcher.
     *
     * @param \Closure|string $callback
     * @return void
     */
    public static function converted_string_to_file($callback)
    {
        static::registerModelEvent('converted_string_to_file', $callback);
    }

    /**
     * Return the model properties configuration array for create the files for this model.
     *
     * @return array
     */
    abstract public function convertStringToFile() : array;

    /**
     * Get the first string file for the attribute
     * 
     * @param string $attribute
     * @return \GuillermoRod\StringToFile\Models\StringToFile|null
     */
    public function getStringFile($attribute)
    {
        return $this->string_files->firstWhere('model_attribute', $attribute);
    }

    /**
     * Get html view path
     *
     * @param string $attribute
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getStringFileViewPath($attribute)
    {
        return $this->getStringFile($attribute)->getViewPath();
    }
    
    /**
     * Get url asset for the converted file
     *
     * @param string $attribute
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getStringFileUrlAsset($attribute)
    {
        return $this->getStringFile($attribute)->getUrlAsset();
    }

    /**
     * Get file contents
     *
     * @param string $attribute
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getStringFileContents($attribute)
    {
        return $this->getStringFile($attribute)->getFileContents();
    }

    /**
     * Determine if the file exists for the attribute
     *
     * @param string $attribute
     * @return bool
     */
    public function hasStringFile($attribute)
    {
        $model = $this->getStringFile($attribute);

        if ($model !== null) return $model->fileCreated();

        return false;
    }    

    public function scopeSelectColumnsToBeConvertedIntoFiles($query)
    {
        $attributes  = $this->convertStringToFile();
        $columns = [];

        foreach ($attributes as $key => $config) {
            $columns[] = is_numeric($key) ? $config : $key; 
        }
        
        return $query->addSelect($columns);
    }

    /**
     * Get the converted files from string property
     * 
     * @return object
     */
    public function string_files()
    {
        return $this->morphMany(\GuillermoRod\StringToFile\Models\StringToFile::class, 'model');        
    }
}
