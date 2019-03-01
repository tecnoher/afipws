<?php

namespace AfipWS;

use \Illuminate\Support\ServiceProvider;
use AfipWS\Afip;

/**
 * Afip Service Provider
 *
 * Registers Afip with Laravel while also registering the Facade and Template extensions.
 * The Alias is also automatically loaded so you can access Afip with the "Lava::" syntax
 *
 *
 * @package    AfipWS\Afip
 * @subpackage Laravel
 * @author     Hernan Torres <tecnoher@gmail.com>
 * @copyright  (c) 2017, tecnoher Designs
 * @license    http://opensource.org/licenses/MIT MIT
 */
class AfipServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        /**
         * If the package method exists, we're using Laravel 4
         */
        if (method_exists($this, 'package')) {
            $this->package('AfipWS/Afip');
        }
    }

    public function register()
    {
        $this->app->singleton('Afip', function() {
            return new Afip;
        });

        $this->app->booting(function() {
            $loader = AliasLoader::getInstance();
            $loader->alias('Afip', 'afip-ws\afip\Afip\Afip');
        });

    }

    public function provides()
    {
        return ['afip'];
    }

}
