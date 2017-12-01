<?php


class Timers{
    
    private $str ="heoo";

    public function say(){


        echo $this->str;
    }

}


$time = new Timers();

swoole_timer_after(1000,function()use($time){

    $time->say();

})

