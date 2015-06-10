<?php
define("TOKEN", "lgdpm2015");
$wechatObj = new wechatCallbackapiTest();
//$wechatObj->valid();
$wechatObj->responseMsg();

class wechatCallbackapiTest
{
    public function valid(){ //真实性验证
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)){
                
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);//转换成xml
            $fromUsername = $postObj->FromUserName;//公众号id
            $toUsername = $postObj->ToUserName;//用户id每个用户关注不同的公众号id不同。
            $keyword = trim($postObj->Content);//用户发送的内容。
            $time = time();//时间
            
            $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                        </xml> "; //向用户返回消息型数据
            
            
            if (!empty($keyword)) { //验证用户发送内容不能为空
                $msgType = "text";
                $contentStr = "Hello";
                $time = date("Y-m-d H:m:s",time());
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $keyword);
                $this -> saveSql($fromUsername,$toUsername,$time,$msgType,$keyword);//数据入库
                echo $resultStr;

            }else {
                echo "Empty Input";
            }
        }
           
        
    }
        
    private function checkSignature() {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];    
                
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    function saveSql($fromUsername,$toUsername,$time,$msgType,$contentStr) { //将收到数据储存进数据库
        $mysql = new SaeMysql();
        $weixinname = $fromUsername;
        $username = $toUsername;
        $userimg = "http://image.baidu.com/detail/newindex?col=%E8%B5%84%E8%AE%AF&tag=%E5%A8%B1%E4%B9%90&pn=0&pid=5692241461084217137&aid=&user_id=10086&setid=-1&sort=0&newsPn=0&star=angelababy&fr=&from=1";
        $times = date("Y-m-d H:m:s",$time);
        $type = $msgType;
        $content = $contentStr;

        $link=mysql_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS);//链接主库
        if($link) {
            $link_db = mysql_select_db(SAE_MYSQL_DB,$link);
            if ($link_db) {
                //存入代码
                $sql = "INSERT  INTO `test` ( `name`, `userimg`, `content`,`time`,`type`) 
                    VALUES ('"  . $username . "' , '"  . $userimg . "'  , '"  . $content . "' , '"  . $times . "','"  . $type . "') " ;
                $mysql->runSql($sql);
                if($mysql->errno() != 0) {
                    die("Error:" . $mysql->errmsg());
                }
                $mysql->closeDb();
            }else{
                echo "数据库链接失败";
            }
            
        }else {
            echo "数据库主库链接失败";
        }
    }
}


?>