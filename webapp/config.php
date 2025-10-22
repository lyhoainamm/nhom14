<?php
function envx($k,$d=null){ $v=getenv($k); return ($v===false||$v==='')?$d:$v; }

define('DB_HOST', envx('DB_HOST','mysql'));
define('DB_NAME', envx('DB_NAME','nhom14_ui'));
define('DB_USER', envx('DB_USER','root'));
define('DB_PASS', envx('DB_PASS','root'));

define('KAFKA_REST_PROXY', envx('KAFKA_REST_PROXY','http://kafka-rest:8082'));
define('KAFKA_DEFAULT_TOPIC', envx('KAFKA_DEFAULT_TOPIC','demo-topic'));
define('TOPIC_ORDER_VIEWED', envx('TOPIC_ORDER_VIEWED','order-viewed'));
