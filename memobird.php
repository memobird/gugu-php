<?php
class memobird{
	private $ak = ''; //access key
	private $url=array(
		'getUserId' => 'http://open.memobird.cn/home/setuserbind/',
		'printPaper' => 'http://open.memobird.cn/home/printpaper/',
		'getPrintStatus' => 'http://open.memobird.cn/home/getprintstatus/'
	);


	function __construct($ak){
		if($ak){
			$this->ak = $ak;
		}
		date_default_timezone_set('PRC'); 
	}

	public function getUserId($memobirdID,$useridentifying){
		$params=array(
			'ak'=> $this->ak,
			'timestamp'=>date('Y-m-d h:m:s',time()),
			'memobirdID'=>$memobirdID,
			'useridentifying'=>$useridentifying
		);
		$paramsString = http_build_query($params);
		return $this->curl($this->url['getUserId'],$paramsString);
	}

	public function printPaper($printcontent,$memobirdID,$userID){
		$params=array(
			'ak'=> $this->ak,
			'timestamp'=>date('Y-m-d h:m:s',time()),
			'printcontent'=>$printcontent,
			'memobirdID'=>$memobirdID,
			'userID'=>$userID
		);
		$paramsString = http_build_query($params);
		return $this->curl($this->url['printPaper'],$paramsString);
	}
	
	
	//构造printPaper方法中$printcontent格式，多个可以循环并用|拼接
	public function contentSet($type,$content){
		switch($type){
			case 'T':
				$ret = $type.':'.base64_encode($this->charsetToGBK($content)."\n");break;
			case 'P':
				$ret = 'P:'.base64_encode($content);
			default:
		}
		return $ret;
	}
	
	public function getPaperStatus($printcontentID){
		$params=array(
			'ak'=> $this->ak,
			'timestamp'=>date('Y-m-d h:m:s',time()),
			'printcontentID'=>$printcontentID
		);
		$paramsString = http_build_query($params);
		return $this->curl($this->url['getPrintStatus'],$paramsString);
	}


    /**
     * 创建http header参数
     * @param array $data
     * @return bool
     */
    private function createHttpHeader() {
        //
    }
    /**
     * 发起 server 请求
     * @param $action
     * @param $params
     * @param $httpHeader
     * @return mixed
     */
    public  function curl($action,$params) {
        //$action = self::SERVERAPIURL.$action.'.'.$this->format;
        //$httpHeader = $this->createHttpHeader();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $action);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false); //处理http证书问题
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $ret = curl_exec($ch);
        if (false === $ret) {
            $ret =  curl_errno($ch);
        }
        curl_close($ch);
        return $ret;
    }
	
	public function charsetToGBK($mixed){
		if (is_array($mixed)) {
			foreach ($mixed as $k => $v) {
				if (is_array($v)) {
					$mixed[$k] = charsetToGBK($v);
				} else {
					$encode = mb_detect_encoding($v, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
					if ($encode == 'UTF-8') {
						$mixed[$k] = iconv('UTF-8', 'GBK', $v);
					}
				}
			}
		} else {
			$encode = mb_detect_encoding($mixed, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
			//var_dump($encode);
			if ($encode == 'UTF-8') {
				$mixed = iconv('UTF-8', 'GBK', $mixed);
			}
		}
		return $mixed;
	}


}
