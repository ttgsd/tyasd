<?php

/**
 */
class ApiTicketUtil {    
    
    const WECHAT_APPID = '';
    const SANGRIA_APPID = '';
    const SANGRIA_SECRT = '';
    const SANGRIA_GZID = '';
    
    const FILE_PATH = 'api_ticket.dat';
    
    const PROJECT_ID = '';
    
    public static $charSet = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','J','K','L','M','N','P','Q','R','S','T','U','V','W','X','Y',
        'a','b','c','d','e','f','g','h','j','k','m','n','p','q','r','s','t','u','v','w','x','y');
    
    private static function getBaseUrl(){
        
        //获取基础的地址，要根据几个环境来判断，留意！         斜杠结尾
     
                
        $base_url = 'http://xxxxx.com/' ;
                
        return $base_url ;
        
    }
    
    
    //这里要改成用文件来缓存
    private static function handleCached( $key  , $func_get_data ){                       
        $info = json_decode( file_get_contents(self::FILE_PATH));
        $exp_time = time();
        
        if (! empty($info)) {
            $secs = $info[1] - $exp_time ;
             
            if($secs > 0){
                return $info[0] ;
            }
        }
                
        $value_and_expiredat = $func_get_data();
        $value = $value_and_expiredat[0];
        $expired_at = $value_and_expiredat[1];
        //var_dump($value_and_expiredat);
        $file = fopen(self::FILE_PATH, "w");
        
        if ($value){                 
            //更新缓存文件
            fwrite($file, json_encode($value_and_expiredat));
            fclose($file);
            return $value ;
        }        
        
        return false;
    }

    public static function jsapi_ticket($gz_account_id){
    
       
        $key = 'sangria_jsapi_ticket_'.$gz_account_id ;
    
        $func = function () use ($gz_account_id){

            $appId = self::SANGRIA_APPID;
            $secret = self::SANGRIA_SECRT;
            
            $ret = self::get_jsapi_ticket($gz_account_id, $appId, $secret);
        
//             var_dump($ret);
//             exit;
            $value = isset ( $ret['data']) && isset($ret['data'] ['ticket']) ? $ret['data'] ['ticket'] : false;
            $expired_at = isset ( $ret ['data']) && isset($ret['data'] ['expired_at']) ? $ret['data'] ['expired_at'] : 1;
            return [$value,$expired_at] ;
        };

        return self::handleCached($key, $func) ;
    }

    private static function get_jsapi_ticket($caller_id,$app_id,$key){
        
        $tokenUrl = self::getBaseUrl() .'ticket/jsapi_ticket';
        
        $params = array (
            'appid' => $app_id,
            'src' => self::PROJECT_ID,
            'sign' => self::encrypt( [
                'appid' => $app_id
            ] ,$key )
        );    
    
        try{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $tokenUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            $res = curl_exec($ch);
            curl_close($ch);
            
        }catch(Exception $ex){
             
            return null ;
        }
    
        //转化工作
        if(empty($res)){
            return null ;
        }
         
        $arr = json_decode($res,true) ;
         
        return $arr;
    }
    


    static  function  encrypt($param,$secretToken){
        //按键名排序参数数组
        ksort($param);
        //拼成加密串
        $signStr = http_build_query($param,null,'&',PHP_QUERY_RFC3986);
        $signStr = md5(md5($signStr).$secretToken);
        //返回
        return $signStr;
    }
    
    //过滤
    public static function filter($string){
    
        if (!get_magic_quotes_gpc()) {
            return addslashes($string);
        }
        return $string;    
    }
    
    public static function checkUrl($url){
        return preg_match( "/(http|https|ftp|file){1}(:\/\/)?([\da-z-\.]+)\.([a-z]{2,6})([\/\w \.-?&%-=]*)*\/?/" , $url ) ;
    }
    
    /**
     * Generate the random string
     *
     * @param  int $length                     Code的随机位长度。   The length of generated string
     * @param  string $prefix                  Code的固定位内容。
     * @param  boolean $isLowerCase     是否允许Code中出现小写字母。
     * @return random string
     */
    public static function getRandomString($length,$prefix="",$isLowerCase=false)
    {
        $returnString = $prefix;
        for($i = 0; $i < $length; $i ++)
        {
        //mt_srand((double)microtime()*1000000);
        $arrayIndex = self::getCharLength($isLowerCase);
        $randASC = self::$charSet[mt_rand(0,$arrayIndex)];
        $returnString .=$randASC;
        }
        return $returnString;
    }
    
    public static function getCharLength($isLowerCase) {
        if($isLowerCase){
            $arrayIndex = count(self::$charSet);
        }else{
            $arrayIndex = 0;
            foreach (self::$charSet as $char) {
                if (($char >= '0' && $char <= '9') || ($char >= 'A' && $char <= 'Z')) {
                    $arrayIndex++;
                }
            }
        }
        $arrayIndex--;
        return $arrayIndex;
    }
    
    public static function jsonExit($code = 0, $msg = "success") {
        return json_encode ( array (
            "code" => $code,
            "message" => $msg
        ) );
    }
    
    public static function jsonData($code = 0, $msg = "succces", $data = null) {
        return json_encode ( array (
            "code" => $code,
            "message" => $msg,
            "data" => $data
        ) );
    }
}
