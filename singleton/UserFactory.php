<?php
namespace Singleton;
use Medoo\Medoo;
/**
 * Singleton class
 *
 */
final class UserFactory
{
    /**
     * Private ctor so nobody else can instantiate it
     *
     */
    private function __construct()
    {

    }

    /**
     * Call this method to get singleton
     *
     * @return UserFactory
     */
    static public function Instance()
    {
        static $inst = null;
        if ($inst === null) {
            try {
                $config = require('./config.php');
                $inst = new Medoo([
                    'database_type' => $config['DB_TYPE'],
                    'database_name' => $config['DB_NAME'],
                    'server'        => $config['DB_HOST'],
                    'username'      => $config['DB_USER_NAME'],
                    'password'      => $config['DB_PASSWORD']
                ]);
            } catch (\Exception $ex) {
                echo $ex->getMessage();
            }
            
        }
        return $inst;
    }

    
}