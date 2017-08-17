<?php
/**
 * Created by PhpStorm.
 * User: Álvaro
 * Date: 09/08/2017
 * Time: 18:49
 */

//namespace Lerp2PHP;

class DevProfiles extends Settings
{
    public static function getProfile()
    {
        $path = "";
        if(isset($_SERVER["PATH"]))
            $path = $_SERVER["PATH"];
        if(strpos($path, "Alvaro") !== false)
        {
            parent::$host = "localhost";
            parent::$dbuser =  "lerp2dev_admin";
            parent::$dbpass = "";
            parent::$dbname = "lerp2dev_db";
            parent::$dbport = 3306;
        } //Vayan poniendo aquí el nombre de sus usuarios con un else if, revisen: C:/Users/<nombre de usuario>
        else
        {
            parent::$host = "localhost";
            parent::$dbuser =  "root";
            parent::$dbpass = "";
            parent::$dbname = "lerp2dev_db";
            parent::$dbport = 3306;
        }
    }
}