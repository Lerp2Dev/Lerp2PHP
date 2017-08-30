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
        define("MAX_ATTEMPS", 5, true);
        define("FLOOD_TIME", 30, true);
        define("COMMENT_MIN_LENGTH", 20);

        if(!defined("CONNECT"))
            define("CONNECT", true);

        if(!defined("REGISTER_HIT"))
            define("REGISTER_HIT", true);

        // #####################################
        // ######### Include Classes ###########
        // #####################################

        require_once('DevProfiles.php');
        require_once('Settings.php');
        require_once('Classes/Session.php');
        require_once('Classes/Debug.php');
        require_once('Classes/Database.php');
        require_once('Classes/Utils.php');
        require_once('Classes/Actions.php');
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

        //Setting session:
        //Core::$sess = Session::getInstance();

        //Set error handler
        //parent::getInstance()->SetErrorHandler();
    }
}

//Do load...
$loader = new Loader();
$loader->DoLoad();

/**
 * Error handler, passes flow over the exception logger with new ErrorException.
 */
function log_error( $num, $str, $file, $line, $context = null )
{
    log_exception( new ErrorException( $str, 0, $num, $file, $line ) );
}

/**
 * Uncaught exception handler.
 */
function log_exception( Exception $e )
{
    global $config;

    /*if ( $config["debug"] == true )
    {
        print "<div style='text-align: center;'>";
        print "<h2 style='color: rgb(190, 50, 50);'>Exception Occured:</h2>";
        print "<table style='width: 800px; display: inline-block;'>";
        print "<tr style='background-color:rgb(230,230,230);'><th style='width: 80px;'>Type</th><td>" . get_class( $e ) . "</td></tr>";
        print "<tr style='background-color:rgb(240,240,240);'><th>Message</th><td>{$e->getMessage()}</td></tr>";
        print "<tr style='background-color:rgb(230,230,230);'><th>File</th><td>{$e->getFile()}</td></tr>";
        print "<tr style='background-color:rgb(240,240,240);'><th>Line</th><td>{$e->getLine()}</td></tr>";
        print "</table></div>";
    }
    else
    {
        $message = "Type: " . get_class( $e ) . "; Message: {$e->getMessage()}; File: {$e->getFile()}; Line: {$e->getLine()};";
        file_put_contents( $config["app_dir"] . "/tmp/logs/exceptions.log", $message . PHP_EOL, FILE_APPEND );
        header( "Location: {$config["error_page"]}" );
    }*/

    Core::ErrorHandling($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());

    exit();
}

/**
 * Checks for a fatal error, work around for set_error_handler not working on fatal errors.
 */
function check_for_fatal()
{
    $error = error_get_last();
    if ( $error["type"] == E_ERROR )
        log_error( $error["type"], $error["message"], $error["file"], $error["line"] );
}

register_shutdown_function( "check_for_fatal" );
set_error_handler( "log_error" );
set_exception_handler( "log_exception" );
ini_set( "display_errors", "off" );
error_reporting( E_ALL );