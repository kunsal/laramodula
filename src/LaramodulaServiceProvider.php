<?php
namespace Kunsal\Laramodula;

use Illuminate\Support\ServiceProvider;

class LaramodulaServiceProvider extends ServiceProvider
{
    public function boot() {
       $this->loadRoutesFrom(__DIR__.'/web.php');
    }

    public function register()
    {
        parent::register(); // TODO: Change the autogenerated stub
    }
}