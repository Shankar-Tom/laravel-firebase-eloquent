<?php
namespace Shankar\FirebaseDb;

use Illuminate\Support\ServiceProvider;
use Shankar\FirebaseDb\Support\FirebaseConnector;

class FirebaseDbServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/firebase-db.php', 'firebase-db');

        $this->app->singleton('firebase.db', function () {
            return (new FirebaseConnector(config('firebase-db')))->connect();
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/firebase-db.php' => config_path('firebase-db.php'),
        ], 'firebase-db-config');
    }
}
