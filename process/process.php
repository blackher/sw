<?php
/**
* 
*/
class Process 
{
    public  $worker_max = 10;
    public  $child = 0;
    public $worker =[];
    function __construct()
    {
        # code...
        $this->redis = new Redis();
        $this->redis->connect('192.168.11.98', 6379); //连接Redis
    }


    function run(){

         while (1) {//循环获取
        # code...
        
            if($this->child <= $this->worker_max){
                $process = new swoole_process[$this, 'callback_function'];

                // 启用消息队列 int $msgkey = 0, int $mode = 2
                $process->useQueue(0, 2);
                $pid = $process->start();
                $data = $redis->rpop('process');
                if(!$data){// 管道写入内容
                // $process->write(json_encode(['name' => '进程','pid' => $pid]));
                    continue;
                }//很重要  跑死了               

                $process->push($data);
                $worker_num ++;
                // 进程重命名
                $process->name('child_namne_process_'.$pid);

                // 将每一个进程的句柄存起来
                $workers[$pid] = $process;//fd

                $ret = swoole_process::wait();

                if ($ret){// $ret 是个数组 code是进程退出状态码，
                    $pid = $ret['pid'];
                    //unset($workers[$pid]);
                        echo PHP_EOL."Worker Exit, PID=" . $pid . PHP_EOL;
                }else{
                
                    continue;
                
                }



            }
        }
    }
    function callback_function(swoole_process $worker)
    {
        $recv = $worker->pop();
        // echo "子输出主内容: {$recv}".PHP_EOL;
        // $worker->push("我是子进程内容");

        sleep(rand(1, 3));//模拟执行任务耗时

        echo "From Master: $recv\n";

        $worker->exit(0);
    // $worker->exit(0);


    $data = $worker->pop();
    echo $data;
    sleep(rand(1, 3));
    echo "From Master: $data\n";
    $worker->exit(0);
    }



}
$p = new Process();
$p->run();