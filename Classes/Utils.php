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
        if(self::Existsentity($ek))
        {
            $res1 = Query::run(self::StrFormat("UPDATE lerp2net_entities SET last_activity = NOW() WHERE sha = '{0}'", $ek));
            $def = self::getStatBy("last_ip", ClientUtils::GetClientIP()) != ClientUtils::GetClientIP();
            if($def)
                $res2 = Query::run(self::StrFormat("UPDATE lerp2net_entities SET last_ip = '{0}' WHERE sha = '{1}'", ClientUtils::GetClientIP(), $ek));
            return !empty($res1) && ($def && !empty($res2) || !$def);
        }
        else
            return AppLogger::$CurLogger->AddError("entity_not_exists");
    }

    public static function RegisterEntity($ek)
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
    public static function RegisterAuth($entId, $tokenSha)
    {
        $date = self::SQLNow();
        $token_id = TokenUtils::RegisterToken($entId, $tokenSha, $date);
        if(isset($token_id))
        {
            $user_id = UserUtils::getStatBy("ip", ClientUtils::GetClientIP());
            if(isset($user_id))
                if (!Query::run(self::StrFormat("INSERT INTO lerp2net_auth (user_id, token_id, creation_date, valid_until) VALUES ('{0}', '{1}', '{2}', ADD_TIME(NOW(), INTERVAL {3} MINUTE))", $user_id, $token_id, $date, defined("SESSION_TIME") ? SESSION_TIME : 60)))
                    return AppLogger::$CurLogger->AddError("error_registering_auth");
        }
        return array("id" => Query::lastId(), "creation_date" => $date);
    }

    //This will be used a lot because it checks if the current user has still a valid connection or not...
    public static function CheckIfAuthIsvalid($tokenSha, $creationDate)
    {
        $data = Query::run(self::StrFormat("SELECT id FROM lerp2net_tokens WHERE sha = '{0}' AND creation_date = '{1}'", $tokenSha, $creationDate));
        if($data !== false)
        {
            $tokenId = mysqli_fetch_assoc($data)["id"];
            $data2 = Query::run(self::StrFormat("SELECT valid_until FROM lerp2net_auth WHERE token_id = '{0}' AND creation_date = '{1}'", $tokenId, $creationDate));
            if($data2 !== false)
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

    public static function GetUserInfo($authId)
    {
        $userId = self::getStatBy("id", $authId, "user_id");
        if(isset($userId))
        {
            $data = Query::run(self::StrFormat("SELECT {0} FROM lerp2dev_users WHERE id = '{1}'", self::SafeUserRows(), $userId));
            if(isset($data))
                return mysqli_fetch_assoc($data);
            else
                return AppLogger::$CurLogger->AddError("null_userid_reg");
        }
        else
            return AppLogger::$CurLogger->AddError("null_user_reg");
    }
}

class SessionUtils extends Utils
{
    public static function Start($entId, $appId)
    { //Must return its sessionId
        $sha = md5(ClientUtils::NewGuid().time());
        if(isset($entId) && isset($appId))
        {
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
            $sha = md5(ClientUtils::NewGuid().time());
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