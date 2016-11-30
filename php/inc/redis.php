<?php
if (isset($redisAdapter) == false)
{
    $redisAdapter = new Redis();

    $redisAdapter->pconnect('127.0.0.1', 50002);
    $result = $redisAdapter->auth('redisauthISpasswhee');
}
?>