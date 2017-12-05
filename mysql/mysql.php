<?php
    $swoole_mysql = new Swoole\MySQL();
    $swoole_mysql->connect([
        'host' => '127.0.0.1',
        'port' => 3306,
        'user' => 'root',
        'password' => 'MyNewPass4!',
        'database' => 'sd',
    ],function(Swoole\Mysql $db,$r){
        if($r == false){
            var_dump($db->connect_errno, $db->connect_error);
            die;
        }
    });

    $sql = 'select * from user';



    $swoole_mysql->query($sql, function (Swoole\Mysql $db, $r) {
        if ($r === false) {
            var_dump($db->error, $db->errno);
        } elseif ($r === true) {
            var_dump($db->affected_rows, $db->insert_id);
        }
        var_dump($r);
    });
     
