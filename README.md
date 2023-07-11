# Laravel - Rich String to File

_Converts rich string to css, js, html or text files_


## Publish table and config

``php artisan vendor:publish --tag="string-to-file:migrations"``

``php artisan vendor:publish --tag="string-to-file:config"``

## Trait methods

```php
    class Product extends Model {
        
        use \GuillermoRod\StringToFile\ConvertsStringToFile;

        /**
         * Return the model properties configuration array for create the files for this model.
         *
         * @return array
         */
        public function convertStringToFile(): array
        {
            return ['description', 'details', 'data_sheet'];
        }
    }

    // .. Some controller method
    $product   = Product::with('string_files')->first();
    $attribute = 'details';

    // Determine if the file was created
    $product->hasStringFile($attribute); // : bool

    // Get the model instance that belongs to the attribute,
    // Inside of model there are more methods
    $product->getStringFile($attribute); // : \GuillermoRod\StringToFile\Models\StringToFile

    // If the file extension is css or js, get the url
    $product->getStringFileUrlAsset($attribute); // : string

    // If the file extension is html, get view path
    $product->getStringFileViewPath($attribute); // : string

    // Get the file contents
    $product->getStringFileContents($attribute); // : string
```

## Config Options

```php
    class Product extends Model {
        
        use \GuillermoRod\StringToFile\ConvertsStringToFile;

        /**
         * Return the model properties configuration array for create the files for this model.
         *
         * @return array
         */
        public function convertStringToFile(): array
        {
            return [
                'description' => [
                    'extension' => \GuillermoRod\StringToFile\Models\StringToFile::HTML_EXTENSION
                ], 
                'styles' => [                    
                    'extension' => \GuillermoRod\StringToFile\Models\StringToFile::CSS_EXTENSION
                ], 
                'details' => [                    
                    'extension' => \GuillermoRod\StringToFile\Models\StringToFile::JS_EXTENSION
                ], 
                'data_sheet' => [                    
                    'extension' => \GuillermoRod\StringToFile\Models\StringToFile::TXT_EXTENSION
                ],
            ];
        }
    }
    
```


## Observer class

The conversions are generated automatically after save the model 

```php
    $product = Product::first();

    // create or update content
    $product->details = '<h1>New content</h1>';
    $product->save();

    // Updating model but not affect to the file content, No changes
    $product->some_another_field = 'product_1';
    $product->save();

    // Deleting file
    $product->details = null;
    $product->save();


    $newProduct = new Product;
    $newProduct->details = '<h1>Hello</h1>';
    $newProduct->save();
```

## Blade Directives

```php
    // .. Some controller method
    $product = Product::with('string_files')->first();

    return view('test', compact('product')),
```

```php
    //.. test.blade.php    

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $attribute
     * @param string $richText - default value to show if file not exists
     */
    @includeHtmlFromString($product, 'details', $product->details) // : html content
    @includeFileContentsFromString($product, 'details', $product->details) // : string

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $attribute
     * @param string $richText - default value to show if file not exists
     * @param string $mergeElementAttributes defer, fingerprint="" etc..
     */
    @includeStyleFromString($product, 'details', $product->details) // : <link> and <style>$default</style> for default value
    @includeScriptFromString($product, 'details', $product->details, 'defer') // : <script src> and <script>$default</script> for default value

    // Define manually scripts
    @if ($product->hasStringFile('details'))
        <script src="{{ $product->getStringFileUrlAsset('details') }}"></script>
    @else 
        <script>
            function some() {/* ... */}
        </script>
    @endif

    // Define manually styles 
    @if ($product->hasStringFile('details'))
        <link src="{{ $product->getStringFileUrlAsset('details') }}">
    @else 
        <style>
            .some-class {/* ... */}
        </style>
    @endif

    //Define manually content 
    @if ($product->hasStringFile('details'))
        {{ $product->getStringFileContents() }}
    @else 
        {{-- ... some --}}
    @endif
```

## Regenerate files

You can regenerate the files executing the nex command

``php artisan string-to-file:regenerate {modelType}``

__php artisan string-to-file:regenerate "App\Models\Product"__


## Author

Guillermo Rodriguez


## Contributions

If you want to make tests, you may send me a email :)