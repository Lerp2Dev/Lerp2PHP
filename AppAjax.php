<?php
/**
 * Created by PhpStorm.
 * User: Álvaro
 * Date: 09/08/2017
 * Time: 18:49
 */

//namespace Lerp2PHP;

include 'Loader.php';

class AppAjax extends Core
{
    public static function getAction()
    {
        AppLogger::$CurLogger->SetEventId($_REQUEST['action']);
        $req = $_SERVER['REQUEST_METHOD'];
        switch ($req) {
            case 'GET':
                if(isset($_GET["action"]))
                {
                    switch ($_GET["action"])
                    {
                        case "get-profile":
                            $username = @$_GET['username'];
                            $data = UserUtils::getStatsBy("username", $username);
                            if(!$data)
                                AppLogger::$CurLogger->AddError("wrong_username", $username);
                            else
                                AppLogger::$CurLogger->AddParameter("data", $data);
                            break;
                        default:
                            self::Kill("[GET] Action '".$_GET["action"]."' not set!");
                            break;
                    }
                }
                break;
            case 'POST':
                $authKey = @$_POST["k"];
                $instanceKey = @$_POST["ik"];
                if(isset($authKey))
                { //There are the actions that need the key given to the app.
                    if($_POST["action"])
                    {
                        switch($_POST["action"]) //Voy a hacer que los cases esten mejor escritas, dos palabras la primera en miuscula y la segunda en mayuscula
                        {
                            case "logout":
                                break;
                            case "rememberAuth":
                                //Solicitar mi clave a través de la instance_key, si now - creation_date > valid_for, entonces, hacer un logout y pedir un nuevo login (el cual será automático, si, is_remembered es true)
                                break;
                            /*case 'getOnlinePeople':
                                $data = $_POST['data'];
                                $t = 120;
                                if(isset($data) && is_numeric($data)) {$t = $data*60;}
                                echo count(UserUtils::getOnlinePeople($t));
                                break;*/
                            default:
                                self::Kill("[POST] Action '".@$_POST["action"]."' not registered!");
                                break;
                        }
                    }
                }
                else if(isset($instanceKey))
                {
                    if(isset($_POST["action"]))
                    {
                        switch($_POST["action"]) //Voy a hacer que los cases esten mejor escritas, dos palabras la primera en miuscula y la segunda en mayuscula
                        {
                            case "rememberAuth":
                                //Solicitar mi clave a través de la instance_key, si now - creation_date > valid_for, entonces, hacer un logout y pedir un nuevo login (el cual será automático, si, is_remembered es true)
                                break;
                        }
                    }
                }
                else
                { //This actions doesn't need any key, because they give it... At least login
                    if(isset($_POST["action"]))
                    {
                        switch ($_POST["action"]) //Voy a hacer que los cases esten mejor escritas, dos palabras la primera en miuscula y la segunda en mayuscula
                        {
                            case "login":
                                //$expireTime = @$_POST['duration'];
                                $username = mysqli_escape_string(Database::conn(), @$_POST['username']);
                                $password = @$_POST['password'];
                                if(!UserActions::AppLogin($username, $password))
                                    Debug::Test(); //Restar aquí un intento de login... Aunq esto se puede hacer antes... Aquino se lo q voy a hacer
                                break;
                            case "register":
                                $username = mysqli_escape_string(Database::conn(), @$_POST['username']);
                                $password = @$_POST['password'];
                                $email = mysqli_escape_string(Database::conn(), @$_POST['email']);
                                //$cpass = @$_POST['pass_confirm'];
                                if(!UserActions::AppRegister($username, $password, $email))
                                    Debug::Test();
                                break;
                            default:

                                break;
                        }
                    }
                }
                break;
            default:
                self::Kill(self::StrFormat("Undefined REQUEST_METHOD '{0}' used!", $req));
                /*
                    header("Location: /");
                    exit;
                 * */
                break;
        }
    }
}

//Do action...
AppAjax::getAction();