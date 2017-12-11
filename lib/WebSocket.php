<?php

class WebSocket{
    
	private $master; //连接server的客户端
	private $sockets=array();//不同状态的socket
	private $handshake=false; //是否握手，默认false
	
	public function __construct($address,$port)
	{
		//创建套接字
	    $this->master=socket_create(AF_INET,SOCK_STEAM,SOL_TCP) or die("socket_create() failed!");
	    socket_set_option($this->master,SOL_SOCKET,SO_REUSEADDR,1) or die("socket_set_option() failed!");
	    socket_bind($this->master,$address,$port) or die("socket_bind() failed!");
	    socket_listen($this->master) or die("socket_listen() faild!");
	    $this->sockets[]=$this->master;
	    
	    echo "master socket:".$this->master."\n";
	    
	    while(true)
	    {
	        //自动选择来消息的socket 如果是握手，自动选择主机
	        $write=NULL;
	        $except=NULL;
	        socket_select($this->sockets, $write, $except,NULL);
	        foreach($this->sockets as $socket)
	        {
	            //连接主机的client
	            if($socket==$this->master)
	            {
	                $client=socket_accept($this->master);
	                if($client<0)
	                {
	                    echo "socket_accept() failed!";
	                    continue;
	                }
	                else
	                {
	                    //connect($client);
	                    array_push($this->sockets,$client);
	                    echo "connect client \n";
	                }
	            }
	            else 
	            {
	                 $bytes=@socket_recv($socket,$buffer,2048,0);
	                 print_r($buffer);
	                 if($bytes==0) return;
	                 if(!$this->handshake)
	                 {
	                     //如果没有握手，先握手回应
	                     $this->doHandShake($socket,$buffer);
	                     echo "shake hands\n";
	                 }
	            }
	        }
	    }
	    
	    
	}

}