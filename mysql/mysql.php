<?php
    use Swoole\Coroutine;
    $swoole_mysql = new Swoole\Coroutine\MySQL();
    $swoole_mysql->connect([
        'host' => '127.0.0.1',
        'port' => 3306,
        'user' => 'root',
        'password' => 'MyNewPass4!',
        'database' => 'sd',
    ]);
    $res = $swoole_mysql->query('select * from user');
    print_r($res);   