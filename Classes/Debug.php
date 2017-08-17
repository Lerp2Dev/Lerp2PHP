<?php

/**
 * Created by PhpStorm.
 * User: Álvaro
 * Date: 10/08/2017
 * Time: 4:33
 */
class Debug extends Core
{
    public static function Test()
    {
        if(defined("DEBUG_PHASE"))
            echo "Hello world!";
        else
            echo "";
    }
}