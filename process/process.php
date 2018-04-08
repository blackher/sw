<?php
/**
 * 创建多进程
 */
$worker_num         = 6;        // 默认进程数
$workers             = [];        // 进程保存
$redirect_stdout    = false;    // 重定向输出  
$redis = new Redis();
$redis->connect('192.168.11.98', 6379); //连接Redis
/*
*主进程创建
*/
function run_process(){

    while (1) {
        # code...
        for($i = 0; $i < $worker_num; $i++){
            $process = new swoole_process('callback_function', $redirect_stdout);

            // 启用消息队列 int $msgkey = 0, int $mode = 2
            $process->useQueue(0, 2);
            $pid = $process->start();
            $data = $redis->rpop('process');
            // 管道写入内容
            // $process->write(json_encode(['name' => '进程','pid' => $pid]));

            $process->push($data);

            // 进程重命名
            $process->name('child_namne_process_'.$worker->pid);

            // 将每一个进程的句柄存起来
            $workers[$pid] = $process;
        }


    }



}


/**
 * 子进程回调
 * @param  swoole_process $worker [description]
 * @return [type]                 [description]
 */
function callback_function(swoole_process $worker)
{
     $recv = $worker->pop();
    // echo "子输出主内容: {$recv}".PHP_EOL;
    // $worker->push("我是子进程内容");
    
    sleep(rand(1, 3));//模拟执行任务耗时

    echo "From Master: $recv\n";

    $worker->exit(0);
    // $worker->exit(0);
}


/**
 * 监控/回收子进程
 */
while(1){
    $ret = swoole_process::wait();
    if ($ret){// $ret 是个数组 code是进程退出状态码，
        $pid = $ret['pid'];
        //unset($workers[$pid]);
        echo PHP_EOL."Worker Exit, PID=" . $pid . PHP_EOL;
    }else{
        break;
    }
}
run_process();


######### 信号操作，操作信号时主进程不会结束 #################################
/**
 * 监控子进程信号
 */
// swoole_process::signal(SIGTERM, function($signo) {
//     echo "关闭进程";
// });


/**
 * 子进程结束信号（异步信号回调）
 */
// swoole_process::signal(SIGCHLD, function($sig) {
//     //必须为false，非阻塞模式
//     while($ret =  swoole_process::wait(false)) {
//         echo "PID={$ret['pid']}\n";
//     }
// });