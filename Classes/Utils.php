<?php
/**
 * Created by PhpStorm.
 * User: Álvaro
 * Date: 23/08/2017
 * Time: 3:00
 */

class Utils extends Core
{
    private static function GetTableName()
    {
        $trace = debug_backtrace();
        if (isset($trace[3]))
            $name = $trace[3]['class'];
        else
            return false;
        switch ($name)
        {
            case "EntityUtils":
                return "lerp2net_entities";
            case "TokenUtils":
                return "lerp2net_tokens";
            case "AuthUtils":
                return "lerp2net_auth";
            case "SessionUtils":
                return "lerp2net_sessions";
            case "AppUtils":
                return "lerp2net_apps";
            case "UserUtils":
                return "lerp2dev_users";
            default:
                return false;
        }
    }

    public static function getStatsBy($par_name, $par_data, $query = "*")
    {
        return QueryUtils::getStatsBy($par_name, $par_data, self::GetTableName(), $query);
    }

    public static function getStatBy($par_name, $par_data, $stat = "id")
    {
        return self::getStatsBy($par_name, $par_data, $stat)[$stat];
    }
}

class EntityUtils extends Utils
{
    public static function ExistsEntity($ek)
    {
        return self::getStatsBy("sha", $ek) !== false;
    }

    public static function UpdateEntityInfo($ek)
    {
        if(isset($ek))
        {
            if (self::ExistsEntity($ek))
            {
                $res1 = Query::run(self::StrFormat("UPDATE lerp2net_entities SET last_activity = NOW() WHERE sha = '{0}'", $ek));
                $def = self::getStatBy("last_ip", ClientUtils::GetClientIP()) != ClientUtils::GetClientIP();
                if ($def)
                    $res2 = Query::run(self::StrFormat("UPDATE lerp2net_entities SET last_ip = '{0}' WHERE sha = '{1}'", ClientUtils::GetClientIP(), $ek));
                return !empty($res1) && ($def && !empty($res2) || !$def);
            }
            else
                return AppLogger::$CurLogger->AddError("entity_not_exists");
        }
        else
            return AppLogger::$CurLogger->AddError("error_unset_parameters", "entitySha");
    }

    public static function RegisterEntity($ek)
    {
        if(isset($ek))
        {
            if(!self::ExistsEntity($ek))
            {
                if (!Query::run(self::StrFormat("INSERT INTO lerp2net_entities (sha, last_ip, creation_date, last_activity) VALUES ('{0}', '{1}', NOW(), NOW())", $ek, ClientUtils::GetClientIP())))
                    return AppLogger::$CurLogger->AddError("error_registering_entity");
            }
            else
            {
                if (!self::UpdateEntityInfo($ek))
                    return AppLogger::$CurLogger->AddError("error_updating_entity");
            }
        }
        else
            return AppLogger::$CurLogger->AddError("error_unset_parameters", "entitySha");
        return Query::lastId(); //self::getStatBy("sha", $ek); //Return the id (porque para que vas a devolver un valor que has pasado como parametro)
    }
}

class TokenUtils extends Utils
{
    public static function RegisterToken($entId, $tokenSha, $date = "")
    {
        if(isset($entId) && isset($tokenSha))
        {
            if (!Query::run(self::StrFormat("INSERT INTO lerp2net_tokens (entity_id, sha, creation_date) VALUES ('{0}', '{1}', {2})", $entId, $tokenSha, $date == "" ? "NOW()" : "'".$date."'")))
                return AppLogger::$CurLogger->AddError("error_registering_token");
        }
        else
            return AppLogger::$CurLogger->AddError("error_unset_parameters", "entId, tokenSha");
        return self::getStatBy("entity_id", $entId, "sha");
    }

    public static function GetID($tokenSha)
    {
        return self::getStatBy("sha", $tokenSha);
    }

    /*public static function GetEntityId($tokenSha)
    { //This also return the ID, but is less useful.
        return self::getStatBy("sha", $tokenSha, "entity_id");
    }*/
}

class AuthUtils extends Utils
{
    public static function RegisterAuth($entId, $tokenSha, $username, $password)
    {
        if(isset($entId) && isset($tokenSha) && isset($username) && isset($password))
        {
            $date = self::SQLNow();
            $token_id = TokenUtils::RegisterToken($entId, $tokenSha, $date);
            if (isset($token_id)) {
                $user_id = QueryUtils::getStatBy("username", $username, "lerp2dev_users");
                if (isset($user_id))
                    if (!Query::run(self::StrFormat("INSERT INTO lerp2net_auth (user_id, token_id, creation_date, valid_until) VALUES ('{0}', '{1}', '{2}', NOW() + INTERVAL {3} MINUTE)", $user_id, $token_id, $date, defined("SESSION_TIME") ? SESSION_TIME : 60)))
                        return AppLogger::$CurLogger->AddError("error_registering_auth");
            }
            $authId = Query::lastId();
            $userData = self::GetUserInfo($authId, $username, $password);
            if (isset($userData))
                return array("id" => $authId, "creation_date" => $date, "user_data" => $userData);
            else
                return AppLogger::$CurLogger->AddError("hackTry", "auth_id: ".$authId."; username: ".$username."; password: ".$password);
        }
        else
            return AppLogger::$CurLogger->AddError("error_unset_parameters", "entId, tokenSha, username, password");
    }

    //This will be used a lot because it checks if the current user has still a valid connection or not...
    public static function CheckIfAuthIsvalid($tokenSha, $creationDate)
    {
        if(isset($tokenSha) && isset($creationDate))
        {
            $data = Query::run(self::StrFormat("SELECT id FROM lerp2net_tokens WHERE sha = '{0}' AND creation_date = '{1}'", $tokenSha, $creationDate));
            if($data != false)
            {
                $tokenId = mysqli_fetch_assoc($data)["id"];
                $data2 = Query::run(self::StrFormat("SELECT valid_until FROM lerp2net_auth WHERE token_id = '{0}' AND creation_date = '{1}'", $tokenId, $creationDate));
                if($data2 != false)
                {
                    $validUntil = mysqli_fetch_assoc($data2)["valid_until"];
                    return array("token_id" => $tokenId, "valid_until" => $validUntil, "is_valid" => strtotime($validUntil) >= time());
                }
                else
                    return AppLogger::$CurLogger->AddError("null_auth_reg");
            }
            else
                return AppLogger::$CurLogger->AddError("null_token_reg");
        }
        else
            return AppLogger::$CurLogger->AddError("error_unset_parameters", "tokenSha, creationDate");
        //return AppLogger::$CurLogger->AddError("hack_try");
    }

    private static function GetUserInfo($authId, $username, $password)
    {
        if(isset($authId) && isset($username) && isset($password))
        {
            $userId = self::getStatBy("id", $authId, "user_id");
            if (isset($userId))
            {
                $data = Query::run(self::StrFormat("SELECT {0} FROM lerp2dev_users WHERE id = '{1}' AND username = '{2}' AND password = '{3}'", self::SafeUserRows(), $userId, $username, self::IsValidMD5($password) ? $password : md5($password)));
                if (empty($data)) //Deberia saltar este error...
                    return AppLogger::$CurLogger->AddError("wrong_credentials");
                else
                    return mysqli_fetch_assoc($data);
            }
            else
                return AppLogger::$CurLogger->AddError("null_user_reg");
        }
        else
            return AppLogger::$CurLogger->AddError("error_unset_parameters", "authId, username, password");
    }
}

class SessionUtils extends Utils
{
    public static function Start($entId, $appId)
    { //Must return its sessionId
        if(isset($entId) && isset($appId))
        {
            $sha = self::GenerateSha();
            if (!Query::run(self::StrFormat("INSERT INTO lerp2net_sessions (app_id, entity_id, sha, start_time) VALUES ('{0}', '{1}', '{2}', NOW())", $appId, $entId, $sha)))
                return AppLogger::$CurLogger->AddError("error_starting_session");
        }
        else
            return AppLogger::$CurLogger->AddError("error_unset_parameters", "entId, appId");
        return $sha;
    }

    public static function End($sha)
    {
        return self::EndWithDate($sha);
    }

    public static function EndStartedSession($sha, $date = "")
    {
        return self::EndWithDate($sha, $date == "" ? self::SQLNow() : $date);
    }

    private static function EndWithDate($sha, $date = "")
    {
        if(isset($sha))
        {
            //$sha = md5(ClientUtils::NewGuid().time());
            if(!Query::run(self::StrFormat("UPDATE lerp2net_sessions SET end_time = '{0}' WHERE sha = '{1}'", $date == "" ? self::SQLNow() : $date, $sha)))
                return AppLogger::$CurLogger->AddError("error_ending_session");
        }
        else
            return AppLogger::$CurLogger->AddError("error_unset_parameters", "sha");
        return true;
    }

    public static function RecordNewSession($entId, $appId, $startTime, $endTime)
    {
        if(isset($entId) && isset($appId) && isset($sha) && isset($startTime) && isset($endTime))
        {
            $sha = self::GenerateSha();
            if(!Query::run(self::StrFormat("INSERT INTO lerp2net_sessions (app_id, entity_id, sha, start_time, end_time) VALUES ('{0}', '{1}', '{2}', '{3}', '{4}')", $appId, $entId, $sha, $startTime, $endTime)))
                return AppLogger::$CurLogger->AddError("error_finalizing_session");
        }
        else
            return AppLogger::$CurLogger->AddError("error_unset_parameters", "entId, appId, sha, sessionId, endTime");
        return Query::lastId();
    }

    public static function GetID($sha)
    {
        return self::getStatBy("sha", $sha);
    }
}

class AppUtils extends Utils
{
    public static function GetID($prefix)
    {
        return self::getStatBy("prefix", $prefix);
    }
}

class UserUtils extends Utils
{
    public function __call($name, $args)
    {

        switch ($name) {
            case 'checkValidPassword':
                switch (count($args)) {
                    case 1:
                        return call_user_func_array(array($this, '_checkValidPassword'), $args);
                    case 2:
                        return call_user_func_array(array($this, '__checkValidPassword'), $args);
                }
                break;
        }
    }

    public static function Attemps($name, $action)
    {
        if(empty($_SESSION['ATTEMPS'])) {$attempArray = array();} else {$attempArray = $_SESSION['ATTEMPS'];}
        switch ($action) {
            case 'set':
            case 'reset': //Sets or reset all the attemps
                $attempArray[$name] = array('TRIES' => MAX_ATTEMPS);
                break;
            case 'substract':
                if(isset($attempArray[$name])) {
                    $attempArray[$name]['TRIES'] -= 1;
                } else {
                    UserUtils::Attemps($name, 'set');
                    $attempArray[$name]['TRIES'] = MAX_ATTEMPS - 1;
                }
                break;
            case 'get':
                if(isset($attempArray[$name])) {
                    return $attempArray[$name]['TRIES'];
                } else {
                    UserUtils::Attemps($name, 'set');
                    return MAX_ATTEMPS;
                }
                break;
            default:
                return false;
                break;
        }
        $_SESSION['ATTEMPS'] = $attempArray;
        return false;
    }

    public static function getRankIdByName($name)
    {
        return Query::firstResult("SELECT id FROM ranks WHERE name = '$name'");
    }

    public static function isOnline($id = null)
    {
        if(!isset($id))
            return isset($_COOKIE["loginSession"]);
        else
            return mysqli_fetch_array(Query::run("SELECT last_activity FROM users WHERE id = '$id'"))['last_activity'] + SESSION_TIME*60 > time();
    }

    public static function UserExists($username)
    {
        return Query::count('id', 'lerp2dev_users', "WHERE username = '$username'");
    }

    public static function checkValidUsername($username, $edit)
    {
        if(empty($username))
            AppLogger::$CurLogger->AddError('emptyUsername');
        else if(preg_match('/\^|`|\*|\+|<|>|\[|\]|¨|´|\{|\}|\||\\|\"|\@|·|\#|\$|\%|\&|\¬|\/|\(|\)|=|\?|\'|¿|ª|º/', $username))
            AppLogger::$CurLogger->AddError('forbiddenChars');
        else if(!$edit && self::UserExists($username))
            AppLogger::$CurLogger->AddError('userExists');
        else if(strlen($username) <= 4)
            AppLogger::$CurLogger->AddError('shortUsername');
        else if(strlen($username) > 20)
            AppLogger::$CurLogger->AddError('longUsername');
    }

    protected static function _checkValidPassword($password)
    {
        self::__checkValidPassword($password, $password);
    }

    protected static function __checkValidPassword($password, $cpass)
    {
        if(empty($password))
            AppLogger::$CurLogger->AddError('emptyPassword');
        else if(strlen($password) < 6)
            AppLogger::$CurLogger->AddError('shortPassword');
        else if($cpass != $password)
            AppLogger::$CurLogger->AddError('wrongCPassword');
        else if(!preg_match("#[0-9]+#", $password))
            AppLogger::$CurLogger->AddError('numPassword');
        else if(!preg_match("#[a-zA-Z]+#", $password))
            AppLogger::$CurLogger->AddError('letterPassword');
    }

    public static function checkValidMail($email, $cmail)
    {
        if(empty($email))
            AppLogger::$CurLogger->AddError('emptyEmail');
        else if(!self::IsValidMail($email))
            AppLogger::$CurLogger->AddError('invalidMail');
        else if(isset($cmail) && $cmail != $email) //Se podría dejar así por el momento
            AppLogger::$CurLogger->AddError('wrongCMail');
    }
}