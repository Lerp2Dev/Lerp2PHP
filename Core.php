<?php

/**
 * Created by PhpStorm.
 * User: Álvaro
 * Date: 09/08/2017
 * Time: 21:53
 */
class Core
{
    public static $instance;
    public static $isDieCalled;

    public static function getInstance ()
    {
        if (is_null(self::$instance)) { self::$instance = new self(); }
        return self::$instance;
    }

    public static function StrFormat()
    {
        $args = func_get_args();
        if (count($args) == 0)
            return false;
        if (count($args) == 1)
            return $args[0];
        $str = array_shift($args);
        if(count($args) == 2 && is_array($args[1]))
            $str = $args[1];
        else if(count($args) > 2 && is_array($args[1]))
            self::Kill("If you pass the second parameter as an array, you can't pass more parameters to this function.");
        $str = preg_replace_callback('/\\{(0|[1-9]\\d*)\\}/', create_function('$match', '$args = '.var_export($args, true).'; return isset($args[$match[1]]) ? $args[$match[1]] : $match[0];'), $str);
        return $str;
    }

    public static function IsValidMail($mail)
    {
        //Esto me es invalido, tendría que usar esto: http://stackoverflow.com/a/13719870
        //if(preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i", $mail))
        return filter_var($mail, FILTER_VALIDATE_EMAIL); //No me hace mucha gracia usar esto
    }

    public static function Kill($msg)
    {
        self::$isDieCalled = true;
        die($msg);
    }

    public static function GetRequest($url)
    {
        // Get cURL resource
        $curl = curl_init();
        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_FAILONERROR => 1
        ));
        // Send the request & save response to $resp
        $resp = curl_exec($curl);
        if($resp === false)
            self::Kill('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
        // Close request to clear up some resources
        curl_close($curl);
        return $resp;
    }

    /*public static function getCurLogger()
    {
        if (is_null(self::$CurLogger)) { self::$CurLogger = new AppLogger(); }
        return self::$CurLogger;
    }*/
}