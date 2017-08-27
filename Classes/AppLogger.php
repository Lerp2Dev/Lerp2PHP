<?php

/**
 * Created by PhpStorm.
 * User: Álvaro
 * Date: 09/08/2017
 * Time: 21:28
 */
class AppLogger extends Core
{ //Muestra un objeto JSON con la respuesta de una petición
    /*
     *
     * Este sistema va a ser sencillo lo unico que necesitamos es una array y un metodo que le vaya añadiendo parametros.
     * Y un metodo final que será el que muestre todo.
     * Tb tendremos que hacer un metodo que cree la base de una respuesta cualquiera, con los parámetros success o error-codes
     * Sucess: solo contrendrá o no la id de un codigo de exito
     * Error-Codes: Contendrá varias ids, una o ninguna, correspondiente a cada error
     *
     * */

    public static $CurLogger;

    public $Log;
    public $EventId;

    public function __construct()
    {
        $this->Log = array(
            "errors" => array()
        );
    }

    public function __destruct()
    {
        if(!$this->IsErrored())
            $this->Log["success"] = isset($_REQUEST['detailed']) || isset($_REQUEST['detailedsuc']) ? array(array_keys(AppIdCodes::$EventIds)[$this->EventId] => array_values(AppIdCodes::$EventIds)[$this->EventId]) : $this->EventId;
        if(!self::$isDieCalled)
            echo self::$CurLogger->DisplayJSON();
    }

    public function SetEventId($name)
    {
        $Id = array_search($name, AppIdCodes::$EventIds);
        if($Id === false)
            self::Kill(self::StrFormat("[{0}] Event ID with name '{2}' doesn't exist! (Query string: {1})", $_SERVER['REQUEST_METHOD'], $_SERVER['QUERY_STRING'], $name));
        $this->EventId = $Id;
    }

    public function AddParameter($index, $obj)
    {
        $this->Log[$index] = $obj;
    }

    public function AddError($ErrorName)
    {
        $Error = array_search($ErrorName, array_keys(AppIdCodes::$Error)); //Obtenemos el numero del index del array a partir de un array asociativo

        if($Error === false)
            self::Kill(self::StrFormat("Error with name '{0}' doesn't exist!", $ErrorName));

        if(isset($_REQUEST['detailed']) || isset($_REQUEST['detailederr']))
        {
            $args = func_get_args();
            $ErrorStr = AppIdCodes::$Error[$ErrorName];
            $Error = array($ErrorName => count($args) == 1 ? $ErrorStr : self::StrFormat($ErrorStr, array_pop($args)));
        }
        $this->Log["errors"][] = $Error;
        return false; //Siempre debe de devolver false
    }

    public function DisplayJSON()
    {
        $IsPretty = isset($_REQUEST['pretty']);
        $PrettyStr = $IsPretty ? "<pre>{0}<pre>" : "{0}";
        return self::StrFormat($PrettyStr, $IsPretty ? json_encode(self::$CurLogger->Log, JSON_PRETTY_PRINT) : json_encode(self::$CurLogger->Log));
    }

    public function IsErrored()
    {
        return !empty($this->Log["errors"]);
    }
}

class AppIdCodes
{ //Los codigos de exito o error con una pequeña descripción
    public static $Success = array(
        "register" => "Succesfully register",
        "login" => "Succesfully login",
        "logout" => "Succesfully logout",
        "get-profile" => "Succesfully got data",
        "get-tags" => "",
        "get-tree" => "",
        "getAppId" => "",
        "regenAuth" => "",
        "rememberAuth" => "",
        "createAuth" => "",
        "registerEntity" => "",
        "startAppSession" => "",
        "endAppSession" => ""
    );
    public static $Error = array(
        "wrong_username" => "The username called {0} hasn't been found in the DB!",
        "emptyUsername" => "",
        "forbiddenChars" => "",
        "userExists" => "",
        "shortUsername" => "",
        "longUsername" => "",
        "emptyPassword" => "",
        "shortPassword" => "",
        "wrongCPassword" => "",
        "numPassword" => "",
        "letterPassword" => "",
        "emptyEmail" => "",
        "invalidMail" => "",
        "wrongCMail" => "",
        "github_error" => "Error with Github Api",
        "error_registering_entity" => "",
        "error_updating_entity" => "",
        "error_registering_token" => "",
        "error_registering_auth" => "",
        "error_registering_account" => "",
        "wrong_app_prefix" => "... {0} ...",
        "error_starting_session" => "",
        "error_ending_session" => "",
        "error_finalizing_session" => "",
        "entity_not_exists" => "",
        "null_auth_reg" => "",
        "null_token_reg" => "",
        "null_user_reg" => "",
        "strformat_arr_empty_gaps" => "Empty arr gap #{0}",
        "strformat_str_empty_gaps" => "Empty str gap #{0}",
        "wrong_credentials" => "",
        "error_unset_parameters" => "There are unsetted parameters: {0} in the function...",
        "queryError" => "{0}",
        "phpError" => "Error: [{0}] {1}\n\nFile: {2}:{3}\n\nTrace: {4}",
        "hackTry" => "Hack try! With the following code: {0}"
    );
    public static $EventIds = array(
        "register", "login", "logout", "get-profile", "get-tags", "get-tree", "getAppId", "regenAuth", "rememberAuth", "createAuth", "registerEntity", "startAppSession", "endAppSession", "getAppId"
    );
}