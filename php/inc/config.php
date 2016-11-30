<?php
namespace JMW\CONFIG;

const ENABLE_AUDIT_LOG = false;
const ENABLE_POSTBACK_DATABASE_LOG = false;
const DB_TYPE = "mysql";
const DB_NAME = "postbacklog";
const DB_HOST = "localhost";
const DB_USER = "root";
const DB_PASS = "insecure";
const RETRY_REDIS_INTERVAL = 2000; // Microseconds to wait between polling for responses from the go service through redis. Higher numbers introduce more latency. Low numbers tax the cpu more.
const DELIVERY_RETRY_ATTEMPTS = 3;

const PLUGIN_DIR = 'lib/plugins/';

const ACTIVE_PLUGINS = array
(
//    'UrlEncode' => 'urlencode.class.php', // Disabled for this demo - urlencoding happens within the go service.
    'FilterUnwantedTags' => 'filterunwantedtags.class.php',
    'PostbackLog' => 'postbacklog.class.php',
    'AuditLog' => 'auditlog.class.php'
);



const POSTBACK_TIMEOUT = 45;/// Ideally less than the request timeout to provide time for proper error handling/feedback

?>
