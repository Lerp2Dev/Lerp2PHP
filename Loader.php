<?php
/**
 * Created by PhpStorm.
 * User: Álvaro
 * Date: 09/08/2017
 * Time: 18:50
 */

//namespace Lerp2PHP;

//include 'Core.php';
include 'Settings.php';

class Loader extends Settings
{
    public static $Instance;

    public function __construct()
    {
        //Nothing to do here yet...
    }

    public function DoLoad()
    {
        //SESSION START

        //session_set_cookie_params(2*7*24*60*60);
        //session_start();

        //XAMPP DETECTION
        //define("PROJECT_FOLDER_NAME", "lerp2dev-beta")
        //if(strpos($_SERVER["DOCUMENT_ROOT"], 'xampp') !== false)
        //    $_SERVER["DOCUMENT_ROOT"] .= "/".PROJECT_FOLDER_NAME;

        define("INCLUDES", "includes/", true);
        define("ADMIN_INCLUDES", "includes/admin/includes/", true);
        define("SESSION_TIME", 60, true);
        define("DEBUG", true);
        /*define("MAX_ATTEMPS", 5, true);
        define("FLOOD_TIME", 30, true);

        if(!defined("CONNECT"))
            define("CONNECT", true);
        if(!defined("REGISTER_HIT"))
            define("REGISTER_HIT", true);*/

        // #####################################
        // ######### Include Classes ###########
        // #####################################

        require_once('DevProfiles.php');
        require_once('Settings.php');
        require_once('Classes/Debug.php');
        require_once('Classes/Database.php');
        require_once('Classes/Users.php');
        require_once('Classes/AppLogger.php');
        /*require_once('classes/db.class.php');
        require_once('classes/dir.class.php');
        require_once('classes/lang.class.php');
        require_once('classes/core.class.php');
        require_once('classes/sessions.class.php');
        require_once('classes/settings.class.php');
        require_once('classes/managers.class.php');
        require_once('classes/content.class.php');
        require_once('classes/users.class.php');
        require_once('classes/paginator.class.php');
        require_once('classes/announcement.class.php');*/

        //Prepare Logger for this print
        AppLogger::$CurLogger = new AppLogger();
    }
}

//Do load...
$loader = new Loader();
$loader->DoLoad();