<?php
namespace Shankar\FirebaseDb\Support;

use Kreait\Firebase\Factory;

class FirebaseConnector
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function connect()
    {
        $factory = (new Factory)
            ->withServiceAccount($this->config['credentials'])
            ->withDatabaseUri($this->config['database_url']);

        // Returns Kreait\Firebase\Database
        return $factory->createDatabase();
    }
}
