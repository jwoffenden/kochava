<?php
namespace JMW;

interface Plugin
{
    function before(&$inputArg);
    function after(&$returnValue);
}
?>