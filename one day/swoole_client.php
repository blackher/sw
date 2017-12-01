<?php

class Client{

    private $client;

    public function __construct(){

        $this->client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);//客户端TCP

        $this->client->on('Connect', array($this, 'onConnect'));
        $this->client->on('Receive', array($this, 'onReceive'));
        $this->client->on('Close', array($this, 'onClose'));
        $this->client->on('Error', array($this, 'onError'));

        $this->client->on('BufferEmpty',array($this,'onBufferEmpty'));


    }

    //连接socket server
    public function onConnect(){

        fwrite(STDOUT,"entry msg:\n");
        swoole_event_add(STDIN,function($fd){
            global $cli;
            fwrite(STDOUT,"enter msg\n");
            $msg = trim(fgets(STDIN));
            if(empty($msg)){
                fwrite(STDOUT,"msg isnot null\n");
                
            }else{
                $cli -> send($msg);
            }
        });


    }


    public function onReceive($cli,$data){
        echo "get data".$data."\n";
        

    }

    public function onClose(){

        echo "client close";
        
    }

    public function onBufferEmpty(){
        $this->client->close();
    }

    public function send($data){

        $this->client->send($data);


    }

    public function isConnected() {
        return $this->client->isConnected();
    }

    
    public function onError() {
        echo "client error";

    }   
    public function connect() {
		$fp = $this->client->connect("192.168.11.98", 5555 , 1);
		if( !$fp ) {
			echo "Error: {$fp->errMsg}[{$fp->errCode}]\n";
			return;
		}
    }
    


}
$cli = new Client();
$cli->connect();