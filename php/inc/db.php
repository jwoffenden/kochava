<?php
namespace JMW;
use \R;
// Don't load the (somewhat heavyweight) RedBean library and attempt to
// connect to mysql if the audit log is disabled.
if (CONFIG\ENABLE_AUDIT_LOG === true || CONFIG\ENABLE_POSTBACK_DATABASE_LOG === true)
{

    require_once 'inc/config.php';
    require_once 'lib/rb.php'; // RedBean PHP ORM/rapid database prototyping library

    $dbServer = CONFIG\DB_TYPE . ":" .
                "host=" . CONFIG\DB_HOST . ";" .
                "dbname=" . CONFIG\DB_NAME;
    R::setup($dbServer, CONFIG\DB_USER, CONFIG\DB_PASS);
}
?>
