<?php

/**
 * Created by PhpStorm.
 * User: Álvaro
 * Date: 09/08/2017
 * Time: 18:50
 */

//namespace Lerp2PHP_Classes;

#region "User & Visitor & Client Classes"
class ClientUtils extends Core
{
    public static function GetClientIP()
    {
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
    public static function NewGuid($input = null)
    {
        if(empty($input)) $input = str_replace(".", "", self::GetClientIP()); //QUE NO SE ME OLVIDE MÁS LOS MALDITOS SELF:: !!!!!
        $s = strtoupper(md5(base64_encode($input)));
        $guidText =
            substr($s, 0, 8) . '-' .
            substr($s, 8, 4) . '-' .
            substr($s, 12, 4). '-' .
            substr($s, 16, 4). '-' .
            substr($s, 20);
        return $guidText;
    }
}

class User
{
    public $id;
    public $username;
    public $password;
    public $ip;
    public $user_agent;
    public $reg_time;
    public $started_conn_time;
    public $last_activity;
    public $online_time;
    public $real_name;
    public $email;
    public $specialties;
    public $code;
    public $activation;
    public $prem_days;
    public $ref_id;
    public $coins_balance;
    public $exp;
    public $lvl;
    public $avatar;
    public $gender;
    public $birthdate;
    public $location;
    public $rank_id;
    public $ban_time;
    public $ban_duration;
    public $ban_reason;
    public $rank;
    public $rank_duration;
    public function __construct($data)
    {
        $this->id = $data["id"];
        $this->username = $data["username"];
        $this->password = $data["password"];
        $this->ip = $data["ip"];
        $this->user_agent = $data["user_agent"];
        $this->reg_time = $data["reg_time"];
        $this->started_conn_time = $data["started_conn_time"];
        $this->last_activity = $data["last_activity"];
        $this->online_time = $data["online_time"];
        $this->real_name = $data["real_name"];
        $this->email = $data["email"];
        $this->specialties = $data["specialties"];
        $this->code = $data["code"];
        $this->activation = $data["activation"];
        $this->prem_days = $data["prem_days"];
        $this->ref_id = $data["ref_id"];
        $this->coins = $data["coins"];
        $this->exp = $data["exp"];
        $this->lvl = $data["lvl"];
        $this->avatar = $data["avatar"];
        $this->gender = $data["gender"];
        $this->birthdate = $data["birthdate"];
        $this->location = $data["location"];
        $this->rank_id = $data["rank_id"];
        $this->ban_time = $data["ban_time"];
        $this->ban_duration = $data["ban_duration"];
        $this->ban_reason = $data["ban_reason"];
        $this->rank = $data["rank"];
        $this->rank_duration = $data["rank_duration"];
    }
}

class Visitor
{
    public $id;
    public $ip;
    public $user_agent;
    public $reg_time;
    public $last_activity;
    public $hits;
    public function __construct($data)
    {
        $this->id = $data["id"];
        $this->ip = $data["ip"];
        $this->user_agent = $data["user_agent"];
        $this->reg_time = $data["reg_time"];
        $this->last_activity = $data["last_activity"];
        $this->hits = $data["hits"];
    }
}
#endregion