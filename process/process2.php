<?php


/*
自己写的水平差  还是看官方的demo吧

*/
class Process1
{
	public $mpid=0;//当前进程id
    public $works=[];//记录子进程
    public $max_precess=10;//最多进程数
    public $new_index=0;//记录子进程数

    public $max_child =800;//最大子进程数  不能设置的过小 造成堵塞
    public function  __construct(){

    	 try {
            //declare(ticks=1);//每执行一次低级语句会检查一次该进程是否有未处理过的信号
            ini_set("memory_limit","80M");//防止内存过小
    	    // install signal handler for dead kids
            pcntl_signal(SIGCHLD, [$this, "sig_handler"]);  //参考简书 https://www.jianshu.com/p/54ffd360454f
           // pcntl_signal_dispatch();//调用每个等待信号通过pcntl_signal() 安装的处理器
        	//这就导致一个问题：当执行N个任务之后，任务系统空闲的时候主进程是阻塞的，而在发生阻塞的时候子进程还在执行，所以就无法完成最后几个子进程的进程回收。。。

			  //process.php 就有这个问题  直接内存cpu 消耗太大

	        $this->redis = new Redis();
            $this->redis->connect('192.168.11.98', 6379); //连接Redis
            swoole_set_process_name(sprintf('php-ps:%s', 'master'));//进程命名
            $this->mpid = posix_getpid();//获取当前进程id
            $this->run();//创建子进程
            //$this->processWait();//子进程回收
        }catch (\Exception $e){
            die('ALL ERROR: '.$e->getMessage());
        }

    }

    public function run(){
             $this->CreateProcess();
    }


     public function CreateProcess($index=null){
     	//创建子进程  闭包写法  匿名函数
     	//获取redis 数据
     	while (1) {
     		# code...
     	    //限制子进程数量
            if($this->new_index <= $this->max_child){
    	     		$data = $this->redis->rpop('process');//rpop  阻塞
    	     		if(!$data){
    						//sleep(1);
    	     			continue;//没有数据
    	     		}
						$this->new_index++;
						//echo "has {$this->new_index}child process";
    	            $process = new swoole_process(function(swoole_process $worker)use($index){
    	            // if(is_null($index)){
    	            //     $index=$this->new_index;
    	            //     $this->new_index++;
    	            // }//是否是新增子进程
    	            swoole_set_process_name(sprintf('php-ps:%s',$this->new_index));//重新命名当前子进程
                    //$this->checkMpid($worker);
    	            $recv = $worker->pop();            //recive data to master
                	sleep(rand(1, 3));//模拟耗时
                	echo "From Master: {$recv}\n";
					echo "this->new_index :{$this->new_index}\n";
                	//exit;


	        }, false, false);
            $process->useQueue();
	          $pid=$process->start();  //执行fork系统调用，启动进程 放回子进程pid。
            $process->push($data);
	          $this->works[$index]=$pid;//记录当前pid
		pcntl_signal_dispatch();				//$this->processWait();
        }
	        //return $pid;
    	}
    }
    //杀死主进程
    public function checkMpid(&$worker){
        if(!swoole_process::kill($this->mpid,0)){
            $worker->exit();
            // 这句提示,实际是看不到的.需要写到日志中
            echo "Master process exited, I [{$worker['pid']}] also quit\n";
        }
    }

    //重启子进程
    public function rebootProcess($ret){
        $pid=$ret['pid'];
        $index=array_search($pid, $this->works);//判断是否释放掉
        if($index!==false){//未释放掉  重新启用
            $index=intval($index);
            $new_pid=$this->CreateProcess($index);
            echo "rebootProcess: {$index}={$new_pid} Done\n";
            return;
        }
        throw new \Exception('rebootProcess Error: no pid');
    }
    //回收   子进程结束必须要执行wait进行回收，否则子进程会变成僵尸进程
    public function processWait(){
        while(1) {
            if(count($this->works)){//若子进程未空,代表任务空闲 //结束循环
                $ret = swoole_process::wait();//空闲的子进程
								echo "res: {$ret['pid']}";
                if ($ret) {
                    $this->rebootProcess($ret);
                }
            }else{
                break;
            }
        }
    }
    private function sig_handler($signo) {
//        echo "Recive: $signo \r\n";
        switch ($signo) {
            case SIGCHLD:
                while($ret = swoole_process::wait(false)) {
//                    echo "PID={$ret['pid']}\n";
                    $this->new_index--;
                    echo "{$ret['pid']} process exit";
                }
        }
    }

//作者：闫大伯
//链接：https://www.jianshu.com/p/54ffd360454f
//來源：简书
//著作权归作者所有。商业转载请联系作者获得授权，非商业转载请注明出处。


}

new Process1();
