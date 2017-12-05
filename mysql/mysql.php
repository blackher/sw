<?php
global $mysql;
$mysql = new Swoole\Mysql();
$server = array(
    'host' => '127.0.0.1',
    'port' => 3306,
    'user' => 'root',
    'password' => 'MyNewPass4!',
    'database' => 'sd',
    'charset' => 'utf8', //指定字符集
    'timeout' => 2,  // 可选：连接超时时间
);
$mysql->connect($server, function (Swoole\Mysql $db, $r) {
    if ($r === false) {
        var_dump($db->connect_errno, $db->connect_error);
        die;
    }
    //start query
    $sql = 'select * from user';
    
    $db->query($sql, function (Swoole\Mysql $db, $r) {
        if ($r === false) {
            var_dump($db->error, $db->errno);
        } elseif ($r === true) {
            var_dump($db->affected_rows, $db->insert_id);
        }
        var_dump($r);
    });

});


    
//$mysql->close();
     
