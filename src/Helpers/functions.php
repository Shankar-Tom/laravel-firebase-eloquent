<?php
use Illuminate\Support\Facades\App;

if (! function_exists('firebase_db')) {
    /**
     * @return \Kreait\Firebase\Database
     */
    function firebase_db() {
        return App::make('firebase.db');
    }
}
