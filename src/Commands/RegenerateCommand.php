<?php

namespace GuillermoRod\StringToFile\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use GuillermoRod\StringToFile\Models\StringToFile as StringToFileModel;
use GuillermoRod\StringToFile\Jobs\PerformStringToFile;

class RegenerateCommand extends Command
{
    protected $signature = 'string-to-file:regenerate {modelType}';

    protected $description = 'Regenerate the derived files of model';

    protected $errorMessages = [];

    public function handle()
    {               
        $this->processFiles();

        $this->displayErrorMessages();
        
        $this->info('All done!');
    } 

    private function processFiles()
    {
        $files       = $this->getModelsToBeRegenerated();        
        $progressBar = $this->output->createProgressBar($files->count());

        $files->each(function ($owner) use ($progressBar) {
            try {
                PerformStringToFile::dispatch($owner);
            } catch (Exception $exception) {
                $this->errorMessages[$owner->getKey()] = $exception->getMessage();
            }
            $progressBar->advance();
        });

        $progressBar->finish();
    }

    private function displayErrorMessages()
    {
        if (count($this->errorMessages)) {
            $this->warn('All done, but with some error messages:');
            foreach ($this->errorMessages as $fileId => $message) $this->warn("File id {$fileId}: `{$message}`");
        }
    }

    private function getModelsToBeRegenerated()
    {
        $modelOwner = '\\'.$this->argument('modelType');
        $owner      = new $modelOwner;
        
        return $owner::select($owner->getKeyName())
                ->selectColumnsToBeConvertedIntoFiles()
                ->with('string_files')
                ->get();
    }
}
