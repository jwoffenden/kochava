<?php

namespace JMW;
require_once "lib/plugin.interface.php";

class PluginWrapper
{
    function wrap($mainFunction, &$inputData)
    {
        $plugins = $this->loadPlugins();

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
            $className = "JMW\\" . $className;
            require_once $pluginDir . $fileName;
            $plugin = new $className();
            $plugins []= $plugin;
        }
        return $plugins;
    }
}
?>