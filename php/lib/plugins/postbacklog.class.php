<?php
namespace JMW;
use CONFIG\POSTBACK_TIMEOUT;
use \R;
require_once "inc/db.php";

class PostbackLog implements Plugin
{
    private $input;

    function before(&$inputData) {}

    /// TODO: tie postback log entries to auditlog entries
    function after(&$returnData)
    {
        global $requestKey, $redisAdapter; /// Ideally these would be provided via dependency injection, a singleton, a class static, or something more savory

        if (CONFIG\ENABLE_POSTBACK_DATABASE_LOG === true)
        {
            if ($returnData == false) ///Project specific - not good generic design
                return;

            $startTime = time();
            $logData = $redisAdapter->get($requestKey . "_log");
            while ($logData == null)
            {
                usleep(CONFIG\RETRY_REDIS_INTERVAL);
                $logData = $redisAdapter->get($requestKey . "_log");
                if ((time() - $startTime) > CONFIG\POSTBACK_TIMEOUT)
                {
                    error_log("Timeout recieving postback log data for request " . $requestKey);
                    return;
                }
            }
            $redisAdapter->del($requestKey . "_log"); // TODO: use expire instead

            $postbackBean = R::dispense("postback");
            $pbObject = json_decode($logData);
            $postbackBean->deliverytime = $pbObject->deliverytime;
            $postbackBean->responsecode = $pbObject->responsecode;
            $postbackBean->responsetime = $pbObject->responsetime;
            $postbackBean->responsebody = $pbObject->responsebody;
            R::store($postbackBean);
        }
    }
}
?>
