<?php
/**
 * Created by PhpStorm.
 * User: Álvaro
 * Date: 09/08/2017
 * Time: 18:49
 */

//namespace Lerp2PHP;

include 'Core.php';

class Settings extends Core
{
    protected static $host;
    protected static $dbuser;
    protected static $dbpass;
    protected static $dbname;
    protected static $dbport;
    //public static $adminpass = '1234';

    public static function getSettings()
    {
        DevProfiles::getProfile();
    }
}