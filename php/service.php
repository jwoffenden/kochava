<?php
require_once 'inc/config.php';
require_once 'inc/redis.php';
require_once 'lib/pluginwrapper.class.php';

$args = $_GET['json_data'];

$jsonObject = json_decode($args);

//RequestKey - a unique key for this request. It is composed of a uiniqid()
// (with extra system entropy) and remote address/port
// uniqid() on its own just seeds with the system time by microsecond,
// which is unreliable for many reasons.
// Appending remote address and port arent theoretically necessary
// but don't really impact performance and are a decent failsafe.
$requestKey = uniqid("", true) . $_SERVER['REMOTE_ADDR'] . $_SERVER['REMOTE_PORT'];
$jsonObject->requestKey = $requestKey;

function executePostback($argumentsObject)
{
    global $requestKey, $redisAdapter; ///TODO - many more tasty ways to handle this, such as a class static, singleton, registry, etc
    $result = $redisAdapter->publish('tasks', json_encode($argumentsObject));

    $startTime = time();
    $message = $redisAdapter->get($requestKey);
    while($message == null || $message == ":1")
    {
        usleep(JMW\CONFIG\RETRY_REDIS_INTERVAL);

        $message = $redisAdapter->get($requestKey);
        if ((time() - $startTime) > JMW\CONFIG\POSTBACK_TIMEOUT)
        {
            error_log("Response timeout for request " . $requestKey);
            http_response_code(500);
            return false;
        }
    }
    $responseObject = json_decode($message);

    return $responseObject->method . " " . $responseObject->url . "\n";
}

$wrapper = new JMW\PluginWrapper();
$output = false;
$attemptsMade = 0;
while ($output === false && $attemptsMade < JMW\CONFIG\DELIVERY_RETRY_ATTEMPTS)
{
    $output = $wrapper->wrap('executePostBack', $jsonObject);
    ++$attemptsMade;
}
echo $output;
?>