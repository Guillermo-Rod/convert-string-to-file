<?php 

namespace GuillermoRod\StringToFile\Services;

use Illuminate\Database\Eloquent\Model;

class FileCreatorService 
{    
    private $model;
    private $modelClass;

    /**
     * Slug the current model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param bool $force
     *
     * @return bool
     */
    public function make(Model $model, bool $force = false)
    {
        $this->model         = $model;
        $this->modelClass    = get_class($model);
        $convertedAttributes = [];

        foreach ($this->model->convertStringToFile() as $key => $config) {

            $fileExtension = '';
            
            if (is_numeric($key)) {
                // Default extension 
                $attribute = $config;
            }else {
                // Custom extension
                $attribute = $key;
                $fileExtension = $config['extension'];
            }

            if (! $this->needsConversion($attribute)) {
                $this->deleteConversionIfExists($attribute);
                continue ;
            }

            if ($this->updateOrCreateConversion($attribute, $fileExtension, $force)) {
                $convertedAttributes[] = $attribute;
            }        
        }

        return $convertedAttributes;
    }

    /**
     * Get the first string file for the attribute
     * 
     * @return \GuillermoRod\StringToFile\Models\StringToFile
     */
    private function getConversionInstance($attribute)
    {
        return $this->model->string_files()->where('model_attribute', $attribute)->first();
    }

    /**
     * Delete the converted string from the database and 
     * delete his folder
     *
     * @param string $attribute
     * @return void
     */
    private function deleteConversionIfExists($attribute)
    {
        // If the attribute not exists on the attributes, ignore it
        if (array_key_exists($attribute, $this->model->getAttributes())) {        
            if (($conversion = $this->getConversionInstance($attribute)) !== null) {
                $conversion->deleteFile();
            }
        }
    }

    /**
     * Determines if the model attribute needs conversion
     *
     * @param string $attribute
     * @return void
     */
    private function needsConversion($attribute)
    {
        $value = $this->model->getAttributeValue($attribute);
        return $value !== null || trim($value) !== '';
    }

    /**
     * Update or create string to html file
     *
     * @param string $attribute
     * @param string $fileExtension
     * @param bool $force
     * @return bool
     */
    private function updateOrCreateConversion($attribute, $fileExtension = '', $force = false)
    {
        $conversion = $this->getConversionInstance($attribute);        

        if ($conversion === null) {
            $conversion = $this->model->string_files()->create(['file_name' => null, 'model_attribute' => $attribute]);
        }

        if ($force || ! $conversion->fileCreated()) {
            return $conversion->updatesFile($this->model->getAttributeValue($attribute), $fileExtension);
        }

        if (! $this->model->isDirty($attribute)) {
            return false;
        }
        
        return $conversion->updatesFile($this->model->getAttributeValue($attribute), $fileExtension);
    }    
}
