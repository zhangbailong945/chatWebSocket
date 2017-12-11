<?php


$config = array(  
'appenders' => array(  
'default' => array(  
//'LoggerAppenderFile','LoggerAppenderDailyFile','LoggerAppenderEcho','LoggerAppenderPDO'  
'class' => 'LoggerAppenderDailyFile',//$two:Appenders（输出源）  
'layout' => array(  
//'LoggerLayoutPattern','LoggerLayoutSimple','LoggerLayoutSerialized','LoggerLayoutXml'  
'class' => 'LoggerLayoutSimple',//$three:Layouts（布局）,  
),  
'params' => array(  
/*以下Layouts（布局）Pattern时才能用*/  
            // 'conversionPattern' => '%date %logger %-5level %msg%n',  //，用来自定义日志内容的格式  
  
  
            /*以下Layouts（布局）LoggerLayoutSimple*/  
            /*case:1 Appenders(输出源)Echo时 */  
            // 'htmlLineBreaks' => 'true',  
            /*case:1Appenders(输出源)Echo时 */  
  
            /*case:2 Appenders(输出源)DailyFile时 */  
'datePattern' => 'Y-m-d',    //去掉该参数,则文件名称时间为：201600412  
'file' => 'file-%s.log',     //文件名称  
'append'=>true  ,             //没填默认true输出内容将追加,，若为false,文件内容将被覆盖。  
            /*case:2Appenders(输出源)file时*/  
  
            /*case:3 Appenders(输出源)File时 */  
            // 'file' => 'file.log',//文件名称  
            // 'append' => false//不追加  
            /*case:3Appenders(输出源)File时*/  
  
            /*case:4 Appenders(输出源)PDO时 */  
            // 'dsn' => 'mysql:host=localhost;dbname=logdb',  
            // 'user' => 'root',  
            // 'password' => 'secret',  
            // 'table' => 'log',  
            /*case:4 Appenders(输出源)PDO时*/  
  
),  
  
      )  
   ),  
'rootLogger' => array(  
'appenders' => array('default')  
   )  
);  