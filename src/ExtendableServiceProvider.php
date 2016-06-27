<?php
/**
 * Created by PhpStorm.
 * User: antonpauli
 * Date: 30/07/15
 * Time: 14:09
 */

namespace IronShark\Extendable;

use Illuminate\Support\ServiceProvider;

class ExtendableServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        $this->publishes([
            __DIR__.'/migrations/2015_07_23_134516_create_custom_fields_table.php' => database_path('migrations/2015_07_23_134516_create_custom_fields_table.php'),
            __DIR__.'/config/custom-fields.php' => config_path('custom-fields.php'),
        ]);
    }

    public function register()
    {
    }

    public function when()
    {
        return array('artisan.start');
    }
}
