<?php
namespace JMW;
/*
 * UrlEncode plugin - the url encoding is happening within Go, so this
 * plugin should be disabled in the configuration. It is just included
 * as an example now.
 * */

class UrlEncode implements Plugin
{
    /// UrlEncode::before - operate in-place on input and urlencode any
    /// characters that are not legal characters within a url.
    function before(&$inputData)
    {
        foreach ($inputData->data as &$datum)
        {
            $object_vars = get_object_vars($datum);
            foreach($object_vars as $key=>$value)
            {
                $datum->$key = urlencode($value);
            }
        }
    }
    function after(&$returnData) {}
}
?>