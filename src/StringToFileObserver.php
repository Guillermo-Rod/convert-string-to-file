<?php 

namespace GuillermoRod\StringToFile;

use GuillermoRod\StringToFile\Services\FileCreatorService;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;


class StringToFileObserver
{
    /**
     * @var \GuillermoRod\StringToFile\Services\FileCreatorService
     */
    private $fileCreatorService;

    /**
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    private $events;

    /**
     * Observer constructor.
     *
     * @param \GuillermoRod\StringToFile\Services\FileCreatorService $fileCreatorService
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     */
    public function __construct(FileCreatorService $fileCreatorService, Dispatcher $events)
    {
        $this->fileCreatorService = $fileCreatorService;
        $this->events = $events;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return bool|void
     */
    public function saved(Model $model)
    {
        $this->makeFileFromString($model, 'saved');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $event
     * @return array|null
     */
    protected function makeFileFromString(Model $model, string $event)
    {
        // If the "converting_string_to_file" event returns false, abort
        if ($this->fireConvertingStringToFilEvent($model, $event) === false) return ;

        $convertedAttributes = $this->fileCreatorService->make($model);

        $this->fireConvertedStringToFile($model, $convertedAttributes);

        return $convertedAttributes;
    }

    /**
     * Fire the namespaced validating event.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  string $event
     * @return bool|null
     */
    protected function fireConvertingStringToFilEvent(Model $model, string $event): ?bool
    {
        return $this->events->until('eloquent.converting_string_to_file: ' . get_class($model), [$model, $event]);
    }

    /**
     * Fire the namespaced post-validation event.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  array $convertedAttributes
     * @return void
     */
    protected function fireConvertedStringToFile(Model $model, array $convertedAttributes): void
    {
        $this->events->dispatch('eloquent.converted_string_to_file: ' . get_class($model), [$model, $convertedAttributes]);
    }
}
