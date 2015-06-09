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
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;

            }else {
                echo "Empty Input";
            }
        }
           
        
    }
        
    private function checkSignature()
    {
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
}


?>