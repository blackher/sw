<?php

class Base{

    private $pros = [];

    const PROCESS_NUM = 3;

    public function __construct(){
	
        for ($i=0; $i < self::PROCESS_NUM  ; $i++) { 
            # code...
            $process = new swoole_process('run',false,true);

            $pid = $process->start();

            $this->pros[$pid] = $process;
        }
        
    }

    public function run(swoole_process $process ){

        $process->name("process test");
        print_r($process);
    

    }




}

new Base();
swoole_process::signal(SIGCHLD, function($sig) {
    while($ret =  swoole_process::wait(false)) {
        echo "PID={$ret['pid']}\n";
    }
  });
