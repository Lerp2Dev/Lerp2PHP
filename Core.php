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
    { //Realmente con esto se hace functionar mucho mas al servidor... Solamente se requiere en el logger y lo estoy usando en las consultas de SQL donde se puede hacer perfectamente un {$var}
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
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: PHP'
                ]
            ]
        ];

        $context = stream_context_create($opts);
        $content = file_get_contents($url, false, $context);
        return $content;
    }

    public static function GetJSONRequest($url)
    {
        return json_decode(self::GetRequest($url));
    }

    public static function SQLNow()
    {
        return date("Y-m-d H:i:s");
    }

    public static function SafeUserRows()
    {
        return "id, entity_id, username, email, ip, creation_date, last_activity, conn_time, coins_balance";
    }

    public static function IsValidMD5($md5 = '')
    {
        return preg_match('/^[a-f0-9]{32}$/', $md5);
    }

    public function SetErrorHandler()
    {
        //Set error handler
        set_error_handler(array($this, 'ErrorHandling'));
    }

    public function ErrorHandling($errno, $errstr)
    {
        AppLogger::$CurLogger->AddError("phpError", isset($errno) ? $errno : "No Err Code", $errstr);
    }
}