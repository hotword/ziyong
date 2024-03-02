<?php
error_reporting(E_ERROR | E_PARSE);//屏蔽错误
header('Content-Type: text/json;charset=utf-8');//utf-8格式
header('Access-Control-Allow-Origin:*'); // *代表允许任何网址请求

$ac = isset($_GET['ac']) ? $_GET['ac'] : '';
$wd = isset($_GET['wd']) ? $_GET['wd'] : '';
$play = isset($_GET['play']) ? $_GET['play'] : '';

$result = null;

switch ($ac) {
    case '':       
        if ($wd != '') {
            $result =search($wd);
        }elseif($play){
	        $result = play($play);
        }else{
            $result = home();
        }
        break;
    case 'detail':
        $result = detail();
        break;
    default:
        $result = [
            'code' => -1,
            'msg' => '错误请求'
        ];
        break;
}

echo json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);

function detail(){//详情处理

    $t = isset($_GET['t']) ? $_GET['t'] : '';
    if ($t != '') {
        $pg = intval($_GET['pg']);
        return category($t, $pg);
    }

    $wd = isset($_GET['wd']) ? $_GET['wd'] : '';
    if ($wd != '') {
        return search($wd);
    }

    $result = [
        'list' => [],
    ];
	return $result;
}	
function search($wd){//精准搜索
    $result = [
        'list' => [],
    ];
    $wd = urlencode($wd);
    $api = 'https://www.pansearch.me/_next/data/j2kDT2pUbuQX-JEO93lDw/search.json?keyword='.$wd.'&pan=aliyundrive';
    $data = GET($api);
    //echo $data;
    //exit;
    $json = json_decode($data,true);
    $list = $json['pageProps']['data']['data'];
    if($list){
	    foreach($list as $item){
    		$pan = $item['pan'];
    		if($pan == 'aliyundrive'){
    			$data = $item['content'];
    			$name = tourl($data)[0];
    			$link = tourl($data)[1];
				if($link!=null){
    			    $vod = [
    			        'vod_id' => 'push://'.$link,
    			        'vod_name' => $name,
    			        'vod_pic' => 'https://www.dmoe.cc/random.php',
    	                'vod_remarks' => '('.$json['data']['singername'].')'
    	            ];					
    			    $result['list'][] = $vod;
				}				
    		}	
    	}
    }
    return $result;
}

function tourl($data){
	$array = explode("\n\n",$data);
	$namedx = forch($array,"名称：");
	$urldx = forch($array,"链接：");
	if($namedx!==1000){
		$name = $array[$namedx];
		$name = str_replace(["<span class='highlight-keyword'>","</span>"],"",$name);
	}
	if($urldx!==1000){
		$url = $array[$urldx];
		preg_match("/href=\"(.*?)\"/", $url, $pp);
		$url = $pp[1];
	}	
	$newarray = array($name,$url);
	return $newarray;
}

function forch($list,$name){
    for ($i=0; $i<count($list); $i++) {//数组循环,寻找和名称一致的组。
       //echo $list[$i]['vod_name']."###".$video['name']."###";				
       if(strstr($list[$i],$name) !== false){//匹配到和名称一样的组
           $index=$i;//找到名字返回名字所在的索引
	       break;
	   }else{
	      $index=1000; //没找到，返回假
	   }
	}
	return $index;
}

function GET($url) {//get函数
	$curl = curl_init();
	$header = array( 
	    	   // "X-FORWARDED-FOR:".long2ip(mt_rand(1884815360, 1884890111)),
	    		// "CLIENT-IP:".long2ip(mt_rand(1884815360, 1884890111)),
	    		// "X-Real-IP:".long2ip(mt_rand(1884815360, 1884890111)),
	    		// "Accept:application/json, text/javascript, */*; q=0.01",
	    		// "Accept-Language:zh-CN,zh;q=0.9",
	    		"Connection: Keep-Alive",
	    		"User-Agent: User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.5735.289 Safari/537.36",
	         );
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($curl, CURLOPT_HEADER,0);
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($curl, CURLOPT_TIMEOUT, 10);
	$output = curl_exec($curl);		 
	curl_close($curl); // 结束 Curl
	return $output;
}

