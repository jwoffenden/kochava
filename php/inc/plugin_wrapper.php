<?php

namespace JMW;

class PluginWrapper
{
    function wrap($mainFunction, &$inputData)
    {
        $plugins = loadPlugins();

        foreach ($plugins as &$plugin)
            $plugin->before($inputData);

        $result = $mainFunction($inputData);

        foreach ($plugins as &$plugin)
            $plugin->after($result);

        return $result;
    }

    function loadPlugins()
    {
        $plugins = array();
        $pluginDir = CONFIG\PLUGIN_DIR;
        $activePlugins = CONFIG\ACTIVE_PLUGINS;
        foreach ($activePlugins as $className => $fileName)
        {
            require_once $pluginDir . $fileName;
            $plugin = new $className();
            $plugins []= $plugin;
        }
        return $plugins;
    }
}
?>