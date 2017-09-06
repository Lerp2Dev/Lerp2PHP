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
    public static $sess;

    public static function getInstance ()
    {
        if (is_null(self::$instance)) { self::$instance = new self(); }
        return self::$instance;
    }

    public static function SQLFormat()
    {
        $arr = func_get_args();
        $shifted = array_shift($arr);
        $escaped = array();

        foreach($arr as $v)
            $escaped[] = mysqli_real_escape_string(Database::conn(), $v);

        return self::StrFormat($shifted, $escaped);
    }

    public static function StrFormat()
    { //Realmente con esto se hace functionar mucho mas al servidor... Solamente se requiere en el logger y lo estoy usando en las consultas de SQL donde se puede hacer perfectamente un {$var}
        $args = func_get_args();
        if (count($args) == 0)
            return false;
        if (count($args) == 1)
            return $args[0];
        $str = array_shift($args); //Así, solamente es la string a fomatear
        if(count($args) == 2 && is_array($args[0]))
            $str = $args[0]; //Si no lacadena de texto se convierte en la array que hay que formatear
        else if(count($args) > 2 && is_array($args[0]))
            self::Kill("If you pass the second parameter as an array, you can't pass more parameters to this function.");
        $str = preg_replace_callback('/\\{(0|[1-9]\\d*)\\}/', function($match) use($args, $str)
        {
            //$args = var_export($args, true);
            //self::StrFormatError($match, $args, $str)
            $trace = debug_backtrace();
            if(is_array($args[0]) && empty($args[0][$match[1]]))
                return $trace[2]["function"] == "StrFormat" ? false : AppLogger::$CurLogger->AddError('strformat_arr_empty_gaps', $match[1]);
            else if(!is_array($args[0]) && empty($args[$match[1]]))
                return $trace[2]["function"] == "StrFormat" ? false : AppLogger::$CurLogger->AddError('strformat_str_empty_gaps', $match[1]);
            return isset($args[0]) && is_array($args[0]) && isset($match[1]) ? $args[0][$match[1]] : $args[$match[1]];
        }, $str);
        //create_function('$match', '$args = '.var_export($args, true).'; return isset($args[0]) && is_array($args[0]) ? (isset($match[1]) && isset($args[0][$match[1]]) ? $args[0][$match[1]] : "Str Format error: ($args: ".print_r($args, true)."; $match: ".print_r($match, true).") Undefined index: {$match[1]}") : (isset($match[1]) && isset($args[$match[1]]) ? $args[$match[1]] : "Str Format error: ($args: ".print_r($args, true)."; $match: ".print_r($match, true).") Undefined index: {$match[1]}");')
        return $str;
    }

    private static function StrFormatError($match, $args, $str)
    {
        return "Str Format error: (args: '".print_r($args, true)."';\n\nmatch: '".print_r($match, true)."';\n\nstr: '".(is_array($str) ? print_r($str, true) : $str)."')\n\nUndefined index: {$match[1]}";
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

    public static function GenerateSha()
    {
        return md5(ClientUtils::NewGuid().time());
    }

    /*public function SetErrorHandler()
    {
        //Set error handler
        set_error_handler(function($errno, $errstr)
        {
            $this->ErrorHandling($errno, $errstr);
        });
    }*/

    public static function ErrorHandling($errno, $errstr, $errfile, $errline)
    {
        AppLogger::$CurLogger->AddError("phpError", $errno, $errstr, $errfile, $errline, print_r(debug_backtrace(), true));
    }
}