<?php
namespace JMW;
use \R;

require_once "inc/db.php";

class AuditLog implements Plugin
{
    private $inputData;

    function before(&$inputData)
    {
        if (CONFIG\ENABLE_AUDIT_LOG === true)
            $this->inputData = json_encode($inputData);
    }

    function after(&$returnData)
    {
        if (CONFIG\ENABLE_AUDIT_LOG === true)
        {
            $auditLog = R::dispense('auditlog');
            echo "input data is " . $this->inputData . "<br/>";
            $auditLog->inputdata = $this->inputData;
            $auditLog->returndata = $returnData;
            R::store($auditLog);
        }
    }
}
?>