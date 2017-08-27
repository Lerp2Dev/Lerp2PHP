<?php

/**
 * Created by PhpStorm.
 * User: Álvaro
 * Date: 26/08/2017
 * Time: 19:40
 */

class UserActions extends UserUtils
{
    public static function AppRegister($username, $password, $email) //AppRegister doesn't need pass confirmation
    {
        //self::checkValidCaptcha();
        self::checkValidUsername($username, false);
        self::_checkValidPassword($password);
        self::checkValidMail($email, null); //As a reminder...
        $password = md5($password);
        if(!AppLogger::$CurLogger->IsErrored()) //$code = parent::NewGuid();
            self::AddUser($username, $password, $email);
    }
    public static function AddUser($username, $password, $email)
    {
        $ip = parent::GetClientIP();
        if(!Query::run("INSERT INTO lerp2dev_users (username, password, ip, creation_date, email, last_activity) VALUES ('$username', '$password', '$ip', NOW(), '$email', NOW())"))
            Debug::Test();
        //ContentManager::$msg_type = 1;
        //AppLogger::$CurLogger->AddError("register");
    }

    public static function AppLogin()
    {

    }

    public static function AppLogout()
    {

    }
}