<?php
global $mysql;
$mysql = new Swoole\Mysql();
$server = array(
    'host' => '192.168.11.98',
    'port' => 3306,
    'user' => 'root',
    'password' => 'MyNewPass4!',
    'database' => 'sd',
    'charset' => 'utf8', //指定字符集
    'timeout' => 2,  // 可选：连接超时时间（非查询超时时间），默认为SW_MYSQL_CONNECT_TIMEOUT（1.0）
);
$mysql->connect($server, function (Swoole\Mysql $db, $r) {
    if ($r === false) {
        var_dump($db->connect_errno, $db->connect_error);
        die;
    }
});
$sql = 'show tables';
$mysql->query($sql, function (Swoole\Mysql $db, $r) {
    if ($r === false) {
        var_dump($db->error, $db->errno);
    } elseif ($r === true) {
        var_dump($db->affected_rows, $db->insert_id);
    }
    var_dump($r);
});
     
