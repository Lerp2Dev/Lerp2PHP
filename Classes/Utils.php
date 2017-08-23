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
        if (isset($trace[1]))
            $name = $trace[1]['class'];
        else
            return false;
        switch ($name)
        {
            case "EntityUtils":
                return "lerp2net_entity";
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
        return QueryUtils::getStatBy($par_name, $par_data, self::GetTableName(), $query);
    }

    public static function getStatBy($par_name, $par_data, $stat = "id")
    {
        return self::getStatsBy($par_name, $par_data, $stat)[$stat];
    }
}

class EntityUtils extends Utils
{
    public static function ExistsEntity($mk)
    {
        return self::getStatsBy("sha", $mk) !== false;
    }

    public static function UpdateEntityInfo($mk)
    {
        if(self::Existsentity($mk))
        {
            $res1 = Query::run(self::StrFormat("UPDATE lerp2net_entities SET last_activity = NOW() WHERE sha = '{0}'", $mk));
            $def = self::getStatBy("ip", ClientUtils::GetClientIP()) != ClientUtils::GetClientIP();
            if($def)
                $res2 = Query::run(self::StrFormat("UPDATE lerp2net_entities SET ip = {0} WHERE sha = '{1}'", ClientUtils::GetClientIP(), $mk));
            return !empty($res1) && ($def && !empty($res2) || !$def);
        }
        else
            return false;
    }

    public static function RegisterEntity($mk)
    {
        if(!self::ExistsEntity($mk))
        {
            if (!Query::run(self::StrFormat("INSERT INTO lerp2net_entities (sha, last_ip, creation_date, last_activity) VALUES ('{0}', '{1}', NOW(), NOW())", $mk, ClientUtils::GetClientIP())))
                return AppLogger::$CurLogger->AddError("error_registering_entity");
        }
        else
        {
            if (!self::UpdateEntityInfo($mk))
                return AppLogger::$CurLogger->AddError("error_updating_entity");
        }
        return self::getStatBy("sha", $mk);
    }
}

class TokenUtils extends Utils
{
    public static function RegisterToken($entId, $tokenSha)
    {
        if(!Query::run(self::StrFormat("INSERT INTO lerp2net_tokens (entity_id, sha, creation_date) VALUES ('{0}', '{1}', NOW())", $entId, $tokenSha)))
            return AppLogger::$CurLogger->AddError("error_registering_token");
        return self::getStatBy("entity_id", $entId, "sha");
    }
}

class AuthUtils extends Utils
{
    public static function RegisterAuth($entId, $tokenSha)
    {
        $token_id = TokenUtils::RegisterToken($entId, $tokenSha);
        if(isset($token_id))
        {
            $user_id = UserUtils::getStatBy("ip", ClientUtils::GetClientIP());
            if(isset($user_id))
                if (!Query::run(self::StrFormat("INSERT INTO lerp2net_auth (user_id, token_id, creation_date, valid_until) VALUES ('{0}', '{1}', NOW(), ADD_TIME(NOW(), INTERVAL {2} MINUTE))", $user_id, $token_id, defined("SESSION_TIME") ? SESSION_TIME : 60)))
                    return AppLogger::$CurLogger->AddError("error_registering_auth");
        }
        return true;
    }
}

class SessionUtils extends Utils
{
    public static function StartSession()
    {

    }

    public static function EndSession()
    {

    }
}

class AppUtils extends Utils
{
    public static function GetID($prefix)
    {
        return self::getStatBy("prefix", $prefix);
    }
}