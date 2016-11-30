<?php
namespace JMW;

/// Removes iframes, applets, and plugins from postback data in case
/// it's tested in a browser.
/// It's not a particularly realistic concern for this tiny example app,
/// but the "all input is evil" mantra should never be forsaken without
/// a very very good reason
class FilterUnwantedTags implements Plugin
{
    function before(&$inputData) {}
    function after(&$returnData)
    {
        if ($returnData == false) ///TODO: Project specific, not good generic design
            return;

        do
        {
            // Remove potentially evil tags
            $old_data = $returnData;
            $returnData = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object)[^>]*+>#i', '', $returnData);
        }
        while ($old_data !== $returnData);
    }
}
?>