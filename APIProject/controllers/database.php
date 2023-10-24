<?php
// mysqli_report(MYSQLI_REPORT_ALL); // Set the reporting level for MySQLi errors
@$db01 = mysqli_connect('mysql_db', 'agilm', '2]bmj^=qkQ4iO', 'agild', 3306); // Connect to MySQL database

if (mysqli_connect_errno()) { // Check if connection to MySQL database failed
    echo "Failed to connect to Server Database";
    exit();
}

@$redis = new \Redis(); // Create a new Redis object
$redis->connect('redis_cache', 6379); // Connect to Redis server


$username = 'default'; // Username for Redis server authentication
$password = 'zxFvPJIaNe/qfpnSFfQWp8//hA8jgzK56BkoWWP/FcTnqiioTEouo+aE5t9pGJFDqIPtwVatdBF0Hp4HIiIwzbcCVresunQYdbX8SsAvUZ96r86RzbhcX6Jt'; // Password for Redis server authentication
$redis->auth([$username, $password]); // Authenticate with the Redis server

if (!$redis->ping()) { // Check if connection to Redis server failed
    echo "Failed to connect to Cache Server";
    exit();
}
