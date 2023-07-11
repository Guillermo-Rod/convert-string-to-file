<?php 

namespace GuillermoRod\StringToFile;

class BladeDirectives
{

    // Raw code to parse blade directive vars
    // $arguments = array_map(function ($expression) { return str_replace(['\'','"',' '], '', $expression); }, explode(',', $expression));
    // $arguments = array_filter($arguments, function ($argument) { return $argument !== null && $argument !== ''; });
    // $default   = isset($arguments[2]) ? $arguments[2] : '';
    //     return "<?php 
    //         if ($arguments[0]->hasStringFile('$arguments[1]')) {
    //             echo view($arguments[0]->getStringFileViewPath('$arguments[1]'));
    //         }else {
    //             echo '$default';
    //         }
    //     ?";

    public static function includeHtmlFromString($model, $attribute, $default = '')
    {
        if ($model->hasStringFile($attribute)) {
            return view($model->getStringFileViewPath($attribute));
        }else {
            return $default;
        }
    }

    public static function includeFileContentsFromString($model, $attribute, $default = '')
    {        
        if ($model->hasStringFile($attribute)) { 
            return $model->getStringFileContents($attribute);
        }else {
            return $default;
        }
    }

    public static function includeStyleFromString($model, $attribute, $default = '', $mergeElAttributes = '')
    {
        if ($model->hasStringFile($attribute)) { 
            return "<link rel=\"stylesheet\" href=\"{$model->getStringFileUrlAsset($attribute)}\" {$mergeElAttributes}>";
        }else {
            return "<style>{$default}</style>";
        }
    }

    public static function includeScriptFromString($model, $attribute, $default = '', $mergeElAttributes = '')
    {
        if ($model->hasStringFile($attribute)) { 
            return "<script src=\"{$model->getStringFileUrlAsset($attribute)}\" {$mergeElAttributes}></script>";
        }else {
            return "<script>{$default}</script>";
        }
    }
}