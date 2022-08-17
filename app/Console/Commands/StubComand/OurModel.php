<?php

namespace App\Console\Commands\StubComand;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Pluralizer;

class OurModel extends Command
{
    /**
     * Filesystem instance
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:ourmodel {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make our model';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $path = $this->getSourceFilePath();

        $this->makeDirectory(dirname($path));

        $contents = $this->getSourceFile();

        if (!$this->files->exists($path)) {
            $this->files->put($path, $contents);
            $this->info("{$this->argument('name')} has created on {$path}");
        } else {
            $this->info("{$this->argument('name')} already exists on {$path}");
        }

    }

    public function getSingularClassName($name)
    {
        return ucwords(Pluralizer::singular($name));
    }

    public function getStubPath()
    {
        return __DIR__ . '/../../../../stubs/model.stub';
    }

    public function getStubVariables()
    {
        return [
            '$NAMESPACE'         => 'App\\Models',
            '$CLASS_NAME'        => $this->getSingularClassName($this->argument('name')),
        ];
    }


    /**
     * Get the stub path and the stub variables
     *
     * @return bool|mixed|string
     *
     */
    public function getSourceFile()
    {
        return $this->getStubContents($this->getStubPath(), $this->getStubVariables());
    }


    /**
     * Replace the stub variables(key) with the desire value
     *
     * @param $stub
     * @param array $stubVariables
     * @return bool|mixed|string
     */
    public function getStubContents($stub , $stubVariables = [])
    {

        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace)
        {
            $contents = str_replace($search , $replace, $contents);
        }

        return $contents;

    }

    /**
     * Get the full path of generate class
     *
     * @return string
     */
    public function getSourceFilePath()
    {
        return base_path('App\\Http\\Base\\Controllers') .'\\' .$this->getSingularClassName($this->argument('name')) . '.php';
    }


    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }

        return $path;
    }
}
