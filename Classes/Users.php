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
        if(empty($input)) $input = str_replace(".", "", GetClientIP());
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
class UserUtils extends ClientUtils
{
    public function __call($name, $args) {

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
    /*public static function getStats($id = null, $query = "*")
    {
        if(isset($id))
            return mysqli_fetch_array(Query::run("SELECT {$query} FROM users WHERE id = '$id'"));
        else
            if(self::isOnline())
            {
                $me = SessionManager::getLoginInfo($_COOKIE['loginSession']);
                $id = $me['user_id'];
                if(isset($me))
                    return mysqli_fetch_array(Query::run("SELECT {$query} FROM users WHERE id = '$id'"));
            }
        return false;
    }
    public static function getStat($id = null, $stat = "id")
    {
        return self::getStats($id, $stat)[$stat];
    }*/
    public static function getStatsBy($par_name, $par_data, $query = "*")
    {
        return mysqli_fetch_assoc(Query::run("SELECT {$query} FROM lerp2dev_users WHERE $par_name = '$par_data'"));
    }
    public static function getStatBy($par_name, $par_data, $stat = "id")
    {
        return self::getStatsBy($par_name, $par_data, $stat)[$stat];
    }
    //By... IP
    /*public static function getStatsByIP($ip = null, $query = "*")
    {
        if(isset($ip))
            return mysqli_fetch_array(Query::run("SELECT {$query} FROM users WHERE ip = '$ip'"));
        else
            if(self::isOnline())
            {
                $ip = ClientUtils::GetClientIP();
                if(isset($me))
                    return mysqli_fetch_array(Query::run("SELECT {$query} FROM users WHERE ip = '$ip'"));
            }
        return false;
    }
    public static function getStatByIP($ip = null, $stat = "id")
    {
        return self::getStatsByIP($ip, $stat)[$stat];
    }*/
    /*public static function isRanked($name)
    {
        if(self::isOnline())
        {
            $myRank = self::getStat(null, "rank_id");
            return mysqli_fetch_array(Query::run("SELECT name FROM ranks WHERE id = '$myRank'"))["name"] == $name;
        }
        return false;
    }*/
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
    /*public static function getOnlinePeople($t = 120)
    {
        $elem = array();
        $result = Query::run(ContentUtils::str_format("SELECT * FROM visitors WHERE {0}-last_activity < {1}", CoreUtils::Now(), $t));
        while($rs = mysqli_fetch_row($result))
            $elem[] = $rs;
        return $elem;
    }
    public static function checkValidCaptcha()
    {
        if (empty($_POST["vercode"]))
            AppLogger::$CurLogger->AddError('emptyCaptcha');
        else if($_POST["vercode"] != $_SESSION["vercode"])
            AppLogger::$CurLogger->AddError('incorrectCaptcha');
    }*/
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
    /*public static function getAvatar($a)
    {
        return strlen($a) ? $a : './images/avatars/no-avatar.png';
    }
    public static function getGender($g)
    {
        return $g ? Lang::$lang->text['man'] : Lang::$lang->text['woman'];
    }
    public static function getRankCapById($id)
    {
        $r = Query::firstResult("SELECT caption FROM ranks WHERE id = $id");
        return is_array($r) && array_key_exists(Lang::$lang->lang_name, $r) ? $r[Lang::$lang->lang_name] : 'Usuario'; //cambiar name por caption
    }
    public static function getCurStatus($last_act)
    {
        return CoreUtils::Now() - $last_act < SESSION_TIME * 60 ? '<img src="./images/icons/online.png" /><span>Online</span>' : '<img src="./images/icons/offline.png" /><span>Offline</span>';
    }*/
}
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

    /*public static function Login($expireTime, $username, $password)
    {
        if(empty($username))
            AppLogger::$CurLogger->AddError('emptyUsername');
        if(empty($password))
            AppLogger::$CurLogger->AddError('emptyPassword');
        if(is_numeric($expireTime))
            $expireTime = (int)$expireTime;
        else
            AppLogger::$CurLogger->AddError('hackTry');
        if(!ContentManager::$msg_list) {
            $row = mysqli_fetch_assoc(Query::run("SELECT id FROM users WHERE username='{$username}' AND password='".md5($password)."'"));
            if(!isset($row['id']))
            {
                //LoginAttemps('wrong'); //?
                self::LoginAttemps();
                if(UserUtils::Attemps('LOGIN', 'get') == 0)
                    AppLogger::$CurLogger->AddError('attempsWasted');
                else
                    AppLogger::$CurLogger->AddError('wrongCredentials');
                $_SESSION['LOGIN_LAST_ATTEMP'] = time();
            }
            else
            {
                //$_SESSION['login']['username'] = $row['username'];
                //$_SESSION['login']['id'] = $row['id'];
                $user_id = $row['id'];
                $ip = ClientUtils::GetClientIP();
                $session_id = UserUtils::NewGuid();
                $reg_time = CoreUtils::Now();
                $exp_time = $reg_time+$expireTime*60;
                setcookie("loginSession", $session_id, $exp_time, "/");
                Query::run("INSERT INTO login_sessions (user_id, ip, session_id, reg_time, exp_time) VALUES ('$user_id', '$ip', '$session_id', '$reg_time', '$exp_time')");
                ContentManager::$msg_type = 1;
                AppLogger::$CurLogger->AddError("login");
            }
        }
    }*/

    /*public static function LoginAttemps()
    {
        if(UserUtils::Attemps('LOGIN', 'get') > 0)
            UserUtils::Attemps('LOGIN', 'substract');
        else
            if(isset($_SESSION['LOGIN_LAST_ATTEMP']) && (time() - $_SESSION['LOGIN_LAST_ATTEMP'] > SESSION_TIME*60))
                UserUtils::Attemps('LOGIN', 'reset');
    }*/

    /*public static function Logout()
    {
        $_SESSION = array();
        session_destroy();
        setcookie('loginSession', null, -1, '/');
        ContentManager::$msg_type = 1;
        AppLogger::$CurLogger->AddError('logout');
    }*/
}
#endregion