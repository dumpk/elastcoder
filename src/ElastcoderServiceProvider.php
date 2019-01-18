<?php


namespace Dumpk\Elastcoder;


use Illuminate\Support\ServiceProvider;

class ElastcoderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publish config files
        $this->publishes([
            __DIR__.'/config.php' => config_path('elastcoder.php'),
        ],'elastcoder');
    }

    public function register()
    {
        //
    }

}