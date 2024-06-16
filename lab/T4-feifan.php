<?php
header('Content-Type: application/json');
error_reporting(E_ERROR | E_PARSE);//屏蔽错误

define(HOST,'http://ffzy5.tv/api.php/provide/vod/from/ffm3u8/');http://cj.ffzyapi.com/


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
			'list' => [],
		];
        break;
}

function play($url){
	$parse = 0;
	$jx = 'http://43.248.186.15:8082/jx/?url=';//初始化去广告接口
	$data = GETdetail($jx.$url);
	$json = json_decode($data,true);
    if($json['code']==200){	
		$playurl = $json['url'];
	}else{//解析判断失败
		$playurl = $url;
		$parse = 1;
	}
	$result = [
        'url'=> $playurl,
		'parse'=> $parse,
    ];
	if($parse==1){$result['jx']=1;}//如果需要解析，走json解析
	return $result;
}

echo json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);

function home(){
	$result = [
        'list' => [],
    ];	
	$filters = json_decode($classtr,true);
	$data = GETdetail(HOST.'?ac=list');
	$sx = '[{"key":"area","name":"地区","value":[{"v":"","n":"全部"},{"v":"中国大陆","n":"中国大陆"},{"v":"中国香港","n":"中国香港"},{"v":"中国台湾","n":"中国台湾"},{"v":"美国","n":"美国"},{"v":"日本","n":"日本"},{"v":"韩国","n":"韩国"},{"v":"英国","n":"英国"},{"v":"法国","n":"法国"},{"v":"德国","n":"德国"},{"v":"印度","n":"印度"},{"v":"泰国","n":"泰国"},{"v":"丹麦","n":"丹麦"},{"v":"瑞典","n":"瑞典"},{"v":"巴西","n":"巴西"},{"v":"加拿大","n":"加拿大"},{"v":"俄罗斯","n":"俄罗斯"},{"v":"意大利","n":"意大利"},{"v":"比利时","n":"比利时"},{"v":"爱尔兰","n":"爱尔兰"},{"v":"西班牙","n":"西班牙"},{"v":"澳大利亚","n":"澳大利亚"},{"v":"其他","n":"其他"}]},{"key":"year","name":"年份","value":[{"v":"","n":"全部"},{"v":"2024","n":"2024"},{"v":"2023","n":"2023"},{"v":"2022","n":"2022"},{"v":"2021","n":"2021"},{"v":"2020","n":"2020"},{"v":"2019","n":"2019"},{"v":"2018","n":"2018"},{"v":"2017","n":"2017"},{"v":"2016","n":"2016"},{"v":"2015","n":"2015"}]}]';
	$filters = json_decode($sx,true);
	if($data){
		$json = json_decode($data,true);
		$Remove = ["电影片" ,"连续剧" ,"综艺片" ,"伦理片"];//要移除的内容   
		$result = $json; 
		$result['class'] = array_filter($json['class'], function($item) use ($Remove) {   
			return !in_array($item['type_name'], $Remove);  
		});  
		$result['class'] = array_values($result['class']); // 重置 class 数组的索引
		
		foreach($result['class'] as $item2){
			$type_id = $item2['type_id'];			
			$result['filters'][$type_id] = $filters;
		}
	}    
	return $result;
}

function category($t, $pg = 1, $size =15){
	
    $result = [
        'list' => [],
    ];
	
	if($_GET['ext']){//筛选扩展
		$str=$_GET['ext'];
		$jsonstr = base64_decode($str);//64解码
		$json = json_decode($jsonstr,true);
		$type = $json['type'];//类型 动作/喜剧
		$year = $json['year'];//年代
		$area = $json['area'];//地区，act=明星？
		$pay = $json['pay'];//资费
	}
	$分类 = '&t='.$t;
	$地区 = isset($area)? '&area='.urlencode($area) : '';
    $剧情 = isset($type)? '&type='.urlencode($class) : '';
    $年代 = isset($year)? '&year='.$year : '';
	$排序 = isset($pay)? '&pay='.$pay : '';
	$页数 = '&pg='.$pg;

	$listApi = HOST.'?ac=detail'.$分类.$地区.$剧情.$排序.$年代.$页数;
	$data = GETdetail($listApi);
	if($data){
		$result = json_decode($data, true);
	}
    return $result;
}

function detail(){
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
    $ids = isset($_GET['ids']) ? $_GET['ids'] : '';
	$detailApi = HOST.'?ac=detail&ids='.$ids.'&page=1';
	$data = GETdetail($detailApi);
	if($data){
		$data = str_replace('ffm3u8','非凡云播',$data);//替换播放器名称
		$data = str_replace('bfm3u8','暴风云播',$data);//替换播放器名称
		$result = json_decode($data, true);		
	}	
    return $result;		
}				
	
function search($wd){
    $result = [
        'list' => [],
    ];
	$wdApi = HOST.'?ac=detail&wd='.$wd;
	$data = GETdetail($wdApi);
	if($data){
		$result = json_decode($data, true);
		// foreach($result['list'] as &$item){
			// $item['vod_pic'] = 'https://www.cuplayer.com/statics/images/bn/c_2020_5_2.png';
		// }
		// unset($item);
	}
    return $result;
}

function GETdetail($url,$post_data=null) {//get函数			
		$header = array( 
		    "X-FORWARDED-FOR:".long2ip(mt_rand(1884815360, 1884890111)),
		    "CLIENT-IP:".long2ip(mt_rand(1884815360, 1884890111)),
		    "X-Real-IP:".long2ip(mt_rand(1884815360, 1884890111)),
			"Connection: Keep-Alive",		
			"User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.5359.125 Safari/537.36",
		);
        $curl = curl_init();		
		curl_setopt($curl, CURLOPT_URL, $url);
		if ($post_data !== null) {  
			// 使用POST方法  
			curl_setopt($curl, CURLOPT_POST, 1);  
			curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);  
		} 
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 允许重定向
		curl_setopt($curl, CURLOPT_HEADER,0);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); //强制协议为1.0
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); //强制使用IPV4协议解析域名
		$output=curl_exec($curl);
		curl_close($curl); // 结束 Curl
		return $output;
}
