<?php
set_time_limit(0);

class WoShou{
     var $master; //连接server的client
     var $sockets=array(); //不同状态的socket管理
     var $is_handshanke=false;
     
     public function __construct($address,$port)
     {
     	 //建立套接字
         $this->master=socket_create(AF_INET,SOCK_STREAM,SOL_TCP) or die("socket_create() is failed!");
         socket_set_option($this->master,SOL_SOCKET,SO_REUSEADDR,1) or die("socket_set_option() is failed!");
         socket_bind($this->master,$address,$port) or die("socket_bind() failed!");
         socket_listen($this->master,20) or die("socket_listen() is failed!");
         $this->sockets[]=$this->master;
         //debug
         echo "Master socket:".$this->master."\n";
         while(true)
         {
             //自动选择来消息的socket 如果是握手，自动选择主机
             $write=NULL;
             $except=NULL;
             socket_select($this->sockets,$write,$except,NULL);
             foreach($this->sockets as $socket)
             {
                 //连接主机的客户端
                 if($socket==$this->master)
                 {
                 	 $client=socket_accept($this->master);
                     if($client<0)
                     {
                         //debug
                         echo "socket_accept() failed!";
                         continue;
                     }
                     else 
                     {
                          //debug
                          array_push($this->sockets,$client);
                          echo "connect client! \n";
                     }
                 }
                 else 
                 {
                     $bytes=@socket_recv($socket, $buffer,2048,0);
                     print_r($buffer);
                     if($bytes==0) return;
                     if(!$this->is_handshanke)
                     {
                        //如果没有握手，先握手回应
                        echo "shakehands \n";
                        $this->doHandShake($socket,$buffer);
                     }
                     else 
                     {
                         //如果已经握手，直接接受数据，并处理
                         $buffer=$this->decode($buffer);
                         
                         echo $this->send($socket,$buffer);
                     }
                 }
             }
         }
     }
     
     //提取Sec-WebSocket-Key
     private function getKey($req)
     {
        $key=null;
        if(preg_match("/Sec-WebSocket-Key:(.*)\r\n/",$req,$match))
        {
           $key=$match[1];
        }
        return $key;
     }
     
     //加密Sec-WebSocket-Key
     private function encry($req)
     {
         $key=trim($this->getKey($req));
         //debug
         print_r($key);
         //$mask="258EAFA5-E914-47DA-95CA-C5AB0DC85B11";
         return base64_encode(sha1($key.'258EAFA5-E914-47DA-95CA-C5AB0DC85B11',true));
     }
     
     //握手
     private function doHandShake($socket,$req)
     {
        //获取加密key
        $acceptKey=$this->encry($req);
        $upgrade="HTTP/1.1 101 Switching Protocols\r\n".
                 "Upgrade:websocket\r\n".
                 "Connection:Upgrade\r\n".
                 "Sec-WebSocket-Accept: ".$acceptKey."\r\n"."\r\n";
        //写入socket
        socket_write($socket,$upgrade.chr(0),strlen($upgrade.chr(0)));
        //标记握手已经成功，下次接受数据采用数据帧格式
        $this->is_handshanke=true;
     }
     
	// 解析数据帧
	private function decode($buffer)  {
	    $len = $masks = $data = $decoded = null;
	    $len = ord($buffer[1]) & 127;
	
	    if ($len === 126)  {
	        $masks = substr($buffer, 4, 4);
	        $data = substr($buffer, 8);
	    } else if ($len === 127)  {
	        $masks = substr($buffer, 10, 4);
	        $data = substr($buffer, 14);
	    } else  {
	        $masks = substr($buffer, 2, 4);
	        $data = substr($buffer, 6);
	    }
	    for ($index = 0; $index < strlen($data); $index++) {
	        $decoded .= $data[$index] ^ $masks[$index % 4];
	    }
	    return $decoded;
	}
	
	// 返回帧信息处理
	private function frame($s) {
	    $a = str_split($s, 125);
	    if (count($a) == 1) {
	        return "\x81" . chr(strlen($a[0])) . $a[0];
	    }
	    $ns = "";
	    foreach ($a as $o) {
	        $ns .= "\x81" . chr(strlen($o)) . $o;
	    }
	    return $ns;
	}
	
	// 返回数据
	public function send($client, $msg){
	    $msg = $this->frame($msg);
	    socket_write($client, $msg, strlen($msg));
	}

}

$ws=new WoShou("localhost",9999);