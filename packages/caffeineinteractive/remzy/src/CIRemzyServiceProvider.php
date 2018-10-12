<?php 
namespace CaffeineInteractive\Remzy;

use Illuminate\Support\ServiceProvider;
use View;
use Illuminate\Support\Facades\Validator;

class CIRemzyServiceProvider extends ServiceProvider
{

	public function boot()
	{
		$this->loadRoutesFrom(__DIR__.'/routes.php');    
	}

	public function register()
	{

	}
}