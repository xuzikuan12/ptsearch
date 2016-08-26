<?php
$availableSite = array(
	"npupt", 
	"sjtupt", 
	"hitpt",
	"whupt",
	"byrbt",
	"hdcmct",
	"hdsky",
	"hdchina",
);

function searchOtherSite($site){
	switch ($site){
		case "npupt":
			$searchSite = new npupt();
			break;
		case "sjtupt":
			$searchSite = new sjtupt();
			break;
		case "hitpt":
			$searchSite = new hitpt();
			break;
		case "whupt":
			$searchSite = new whupt();
			break;
		case "byrbt":
			$searchSite = new byrbt();
			break;
		case "hdcmct":
			$searchSite = new hdcmct();
			break;
		case "hdsky":
			$searchSite = new hdsky();
			break;
		case "hdchina":
			$searchSite = new hdchina();
			break;
		default:
			die;
	}
	return $searchSite;
}

function curl_get($url){
    $cookie_jar = "D:\FFOutput\phpstudy\include\cookie";	
	$ch = curl_init();
	$timeout = 2;
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查  
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
	$file_contents = curl_exec($ch);
	curl_close($ch);
	return $file_contents;
}

class npupt{
	var $host = "npupt.com";
	var $name = "npupt";
	var $cutNum = 10;
	var $url;
	function get_html($url){
		$html = curl_get($url);
		if(!$html)	return 0;
		if(!strpos($html, "torrentname")) return 1;
		$html = substr($html, strpos($html, "torrentname") - 310);
		$html = substr($html, 0, strrpos($html, "class=\"nobr\"") + 420);
		$html = substr($html, 0, strrpos($html, ">") + 1);
		return $html;
	}
	
	function search($search){
		$url = "http://" . $this -> host . "/torrents.php?" . $search . "&notnewword=1&nodupe=1";
		$this -> url = $url;
		$html = $this -> get_html($url);
		if(!$html) return "error:" . __LINE__ . ", can not connect to the " . $this -> name;
		preg_match_all('/<a\stitle\=\"(.*?)\"\shref\=\"details\.php\?id\=([0-9]+)\&amp;hit\=1/', $html, $match);
		//var_dump($match);
		$html = preg_replace('/<a\stitle\=\"(.*?)\"\shref\=\"details\.php\?id\=([0-9]+)\&amp;hit\=1/', '<', $html);	//清除标题 链接
		$html = preg_replace('/<\/span>\s([0-9]+)<\/a><\/div><\/td><td/', '\/div>\\1<div', $html);	//提取完成数
		$html = preg_replace('/<\/span>\s([0-9]+)<\/div><\/td><td/', '\/div>\\1<div', $html);	//提取完成数 为个位数的
		$html = preg_replace('/<div.*?\/div>/', '', $html);
		$html = preg_replace('/<small.*?\/small>/', '', $html);
		$html = preg_replace('/<span.*?pushpin.*?<\/span>&nbsp;/', '', $html);	//清除置顶
		$html = preg_replace('/&nbsp;/', '', $html);	//清除&nbsp;等等
		$html = preg_replace('/<span.*?<br \/>.*?<\/span>/', '', $html);	//清除副标题
		$html = preg_replace('/target=\'_blank\'><b>.*?<\/b>/', '', $html);	//清除标题
		$res = preg_replace('/<.*?>/', ' ', $html);
		$res = preg_replace('/\n+ +/', ' ', $res);
		$res = preg_replace('/[\s]{2,}/', '  ', $res);
		$resArr = explode('  ', $res);
		if(!isset($resArr[9])) return "error:" . __LINE__ . ", torrents not found";
		//var_dump($resArr);
		array_splice($resArr, 0, 1);
		array_splice($resArr, -1, 1);
		//var_dump($resArr);
		$cutNum = $this -> cutNum;
		$num = count($resArr) / $cutNum;
		for($i=0; $i<$num; $i++){
			//if(!is_numeric($resArr[$i*$cutNum + 5])) break;
			$torrent[$i] = array(
			"category" 	=> getCategoryByName($resArr[$i*$cutNum + 0]),
			"tid" 		=> $match[2][$i], 
			"name" 		=> $match[1][$i],
			"size" 		=> $resArr[$i*$cutNum + 1],
			"seeders" 	=> $resArr[$i*$cutNum + 3],
			"leechers" 	=> $resArr[$i*$cutNum + 5],
			"comments"	=> $resArr[$i*$cutNum + 6],
			"completed" => $resArr[$i*$cutNum + 7],
			"owner"		=> $resArr[$i*$cutNum + 8],
			"added"		=> $resArr[$i*$cutNum + 9]);
		}
		if(isset($torrent))
			return $torrent;
		else
			return "error:" . __LINE__ . ", torrents not found";
	}
}

class sjtupt{
	var $host = "pt.sjtu.edu.cn";
	var $name = "sjtupt";
	var $cutNum = 7;
	var $url;
	function get_html($url){
		$html = curl_get($url);
		if(!$html)	return 0;
		if(!strpos($html, "torrentname")) return 1;
		$html = substr($html, strpos($html, "torrentname") + 23);
		$html = substr($html, 0, strrpos($html, "viewsnatches.php") + 100);
		$html = substr($html, 0, strrpos($html, ">") + 1);
		return $html;
	}
	
	function search($search){
		$url = "https://" . $this -> host . "/torrents.php?" . $search . "&incldead=1";
		$this -> url = $url;
		$html = $this -> get_html($url);
		if(!$html) return "error:" . __LINE__ . ", can not connect to the " . $this -> name;
		preg_match_all('/<a\stitle\=\"(.*?)\"\s\s\shref\=\"details\.php\?id\=([0-9]+)\&hit\=1/', $html, $match);
		//var_dump($match);
		$html = preg_replace('/<a\stitle\=\"(.*?)\"\s\s\shref\=\"details\.php\?id\=([0-9]+)\&hit\=1/', '<', $html);	//提取标题 和 tid
		$html = preg_replace('/firstpage\"><b>.*?<\/b>/', '', $html);	//清除标题
		$html = preg_replace('/<b>\[<font.*?<\/font>\]<\/b>/', '', $html);	//清除[热门]等等
		$html = preg_replace('/\(<font.*?<\/font>\)/', '', $html);	//清除(0day)等等
		$html = preg_replace('/<br\s\/>.*?<\/td><td\swidth/', '<', $html);	//清除副标题
		$res = preg_replace('/<.*?>/', ' ', $html);
		$res = preg_replace('/\n+ +/', ' ', $res);
		$res = preg_replace('/[\s]{2,}/', '  ', $res);
		$resArr = explode('  ', $res);
		if(!isset($resArr[6])) return "error:" . __LINE__ . ", torrents not found";
		//var_dump($resArr);
		array_splice($resArr, 0, 1);
		array_splice($resArr, -1, 1);
		//var_dump($resArr);
		$cutNum = $this -> cutNum;
		$num = count($resArr) / $cutNum;
		for($i=0; $i<$num; $i++){
			//if(!is_numeric($resArr[$i*$cutNum + 0])) break;
			$torrent[$i] = array(
			"name" 		=> $match[1][$i], 
			"tid" 		=> $match[2][$i],
			"comments" 	=> $resArr[$i*$cutNum + 0],
			"added" 	=> $resArr[$i*$cutNum + 1],
			"size" 		=> $resArr[$i*$cutNum + 2],
			"seeders"	=> $resArr[$i*$cutNum + 3],
			"leechers" 	=> $resArr[$i*$cutNum + 4],
			"completed"	=> $resArr[$i*$cutNum + 5],
			"owner"		=> $resArr[$i*$cutNum + 6]);
		}
		if(isset($torrent))
			return $torrent;
		else
			return "error:" . __LINE__ . ", torrents not found";
	}
}

class hitpt{
	var $host = "pt.hit.edu.cn";
	var $name = "hitpt";
	var $cutNum = 7;
	var $url;
	function get_html($url){
		$html = curl_get($url);
		if(!$html)	return 0;
		if(!strpos($html, "torrentname")) return 1;
		$html = substr($html, strpos($html, "torrentname") - 231);
		$html = substr($html, 0, strrpos($html, "viewsnatches.php") + 170);
		$html = substr($html, 0, strrpos($html, ">") + 1);
		return $html;
	}
	
	function search($search){
		$url = "http://" . $this -> host . "/torrents.php?" . $search . "&incldead=1&notnewword=1";
		$this -> url = $url;
		$html = $this -> get_html($url);
		if(!$html) return "error:" . __LINE__ . ", can not connect to the " . $this -> name;
		preg_match_all('/<a\starget\="_blank"\stitle\="(.*?)\"\s\shref\=\"details\.php\?id\=([0-9]+)\&amp;hit\=1/', $html, $match);
		//var_dump($match);
		$html = preg_replace('/<a\starget\="_blank"\stitle\="(.*?)"\s\shref\="details\.php\?id\=([0-9]+)\&amp;hit\=1">/', '', $html);	//清除标题 链接
		$html = preg_replace('/<td\sclass="embedded"><b>.*?<\/b>/', '', $html);	//清除标题 文字
		$html = preg_replace('/<b>\[<font.*?<\/font>\]<\/b>/', '', $html);	//清除[热门]等等
		$html = preg_replace('/\(<font.*?<\/font>\)/', '', $html);	//清除(0day)等等
		$html = preg_replace('/<br\s\/>.*?<\/td><td\swidth/', '<', $html);	//清除副标题
		$html = preg_replace('/8pt">.*?<\/span>/', '<', $html);	//清除评分
		$res = preg_replace('/<.*?>/', ' ', $html);
		$res = preg_replace('/\n+ +/', ' ', $res);
		$res = preg_replace('/[\s]{2,}/', '  ', $res);
		$resArr = explode('  ', $res);
		if(!isset($resArr[6])) return "error:" . __LINE__ . ", torrents not found";
		//var_dump($resArr);
		array_splice($resArr, 0, 1);
		array_splice($resArr, -1, 1);
		//var_dump($resArr);
		$cutNum = $this -> cutNum;
		$num = count($resArr) / $cutNum;
		for($i=0; $i<$num; $i++){
			//if(!is_numeric($resArr[$i*$cutNum + 0])) break;
			$torrent[$i] = array(
			"name" 		=> $match[1][$i], 
			"tid" 		=> $match[2][$i],
			"comments" 	=> $resArr[$i*$cutNum + 0],
			"added" 	=> $resArr[$i*$cutNum + 1],
			"size" 		=> $resArr[$i*$cutNum + 2],
			"seeders"	=> $resArr[$i*$cutNum + 3],
			"leechers" 	=> $resArr[$i*$cutNum + 4],
			"completed"	=> $resArr[$i*$cutNum + 5],
			"owner"		=> $resArr[$i*$cutNum + 6]);
		}
		if(isset($torrent))
			return $torrent;
		else
			return "error:" . __LINE__ . ", torrents not found";
	}
}

class whupt{
	var $host = "pt.whu.edu.cn";
	var $name = "whupt";
	var $cutNum = 7;
	var $url;
	function get_html($url){
		$html = curl_get($url);
		//$html = file_get_contents("cache/pt.whu.edu.cn.tmp.php");
		if(!$html)	return 0;
		if(!strpos($html, "torrent-title")) return 1;
		$html = substr($html, strpos($html, "torrent-title") - 231);
		$html = substr($html, 0, strrpos($html, "rowfollow") + 40);
		$html = substr($html, 0, strrpos($html, ">") + 1);
		return $html;
	}
	
	function search($search){
		$url = "https://" . $this -> host . "/torrents.php?" . $search . "&incldead=1&notnewword=1";
		$this -> url = $url;
		$html = $this -> get_html($url);
		if(!$html) return "error:" . __LINE__ . ", can not connect to the " . $this -> name;
		preg_match_all('/<a\stitle\="(.*?)\"\shref\=\"\/\/pt\.whu\.edu\.cn\/details\.php\?id\=([0-9]+)\&amp;hit\=1/', $html, $match);
		$html = preg_replace('/<a\stitle\="(.*?)\"\shref\=\"\/\/pt\.whu\.edu\.cn\/details\.php\?id\=([0-9]+)\&amp;hit\=1">/', '', $html);	//清除标题 链接
		//var_dump($match);
		$html = preg_replace('/<h2\sclass\=\'transparentbg\'>.*?<\/a>/', '', $html);	//清除标题 文字
		$html = preg_replace('/\[<span.*?<\/span>\]/', '', $html);	//清除[热门]等等
		//$html = preg_replace('/\(<font.*?<\/font>\)/', '', $html);	//清除(0day)等等
		$html = preg_replace('/<h3\stitle\=".*?<\/h3>/', '', $html);	//清除副标题
		$html = preg_replace('/<a\shref\="http\:\/\/www\.imdb\.com\/title.*?<\/a>/', '', $html);	//清除评分
		$html = preg_replace('/<a\shref\="http\:\/\/www\.rottentomatoes\.com.*?<\/a>/', '', $html);	//清除评分
		$res = preg_replace('/<.*?>/', ' ', $html);
		$res = preg_replace('/\n+ +/', ' ', $res);
		$res = preg_replace('/[\s]{2,}/', '  ', $res);
		$resArr = explode('  ', $res);
		if(!isset($resArr[6])) return "error:" . __LINE__ . ", torrents not found";
		//var_dump($resArr);
		array_splice($resArr, 0, 1);
		array_splice($resArr, -1, 1);
		//var_dump($resArr);
		$cutNum = $this -> cutNum;
		$num = count($resArr) / $cutNum;
		for($i=0; $i<$num; $i++){
			//if(!is_numeric($resArr[$i*$cutNum + 0])) break;
			$torrent[$i] = array(
			"name" 		=> $match[1][$i], 
			"tid" 		=> $match[2][$i],
			"comments" 	=> $resArr[$i*$cutNum + 0],
			"added" 	=> $resArr[$i*$cutNum + 1],
			"size" 		=> $resArr[$i*$cutNum + 2],
			"seeders"	=> $resArr[$i*$cutNum + 3],
			"leechers" 	=> $resArr[$i*$cutNum + 4],
			"completed"	=> $resArr[$i*$cutNum + 5],
			"owner"		=> $resArr[$i*$cutNum + 6]);
		}
		if(isset($torrent))
			return $torrent;
		else
			return "error:" . __LINE__ . ", torrents not found";
	}
}

class byrbt{
	var $host = "bt.byr.cn";
	var $name = "byrbt";
	var $cutNum = 7;
	var $url;
	function get_html($url){
		$html = curl_get($url);
		//$html = file_get_contents("cache/" . $this -> host . ".tmp.php");
		if(!$html)	return 0;
		if(!strpos($html, "torrentname")) return 1;
		$html = substr($html, strpos($html, "torrentname") + 13);
		$html = substr($html, 0, strrpos($html, "rowfollow") + 40);
		$html = substr($html, 0, strrpos($html, ">") + 1);
		return $html;
	}
	
	function search($search){
		$url = "http://" . $this -> host . "/torrents.php?" . $search . "&incldead=1&notnewword=1";
		$this -> url = $url;
		$html = $this -> get_html($url);
		if(!$html) return "error:" . __LINE__ . ", can not connect to the " . $this -> name;
		preg_match_all('/<a\stitle\="(.*?)"\shref\="details\.php\?id\=([0-9]+)\&amp;hit\=1/', $html, $match);
		$html = preg_replace('/<a\stitle\="(.*?)"\shref\="details\.php\?id\=([0-9]+)\&amp;hit\=1">/', '', $html);	//清除标题 链接
		//var_dump($match);
		$html = preg_replace('/embedded"><b>.*?<\/b>/', '', $html);	//清除标题 文字
		$html = preg_replace('/\[<font.*?<\/font>\]/', '', $html);	//清除[热门]等等
		$html = preg_replace('/<br>.*?<\/td><td\swidth/', '<', $html);	//清除副标题
		$html = preg_replace('/<a\shref\="http\:\/\/www\.imdb\.com\/title.*?<\/a>/', '', $html);	//清除评分
		$html = preg_replace('/<span\stitle.*?label.*?<\/span>/', '', $html);	//清除徽章
		$res = preg_replace('/<.*?>/', ' ', $html);
		$res = preg_replace('/\n+ +/', ' ', $res);
		$res = preg_replace('/[\s]{2,}/', '  ', $res);
		$resArr = explode('  ', $res);
		if(!isset($resArr[6])) return "error:" . __LINE__ . ", torrents not found";
		//var_dump($resArr);
		array_splice($resArr, 0, 1);
		array_splice($resArr, -1, 1);
		//var_dump($resArr);
		$cutNum = $this -> cutNum;
		$num = count($resArr) / $cutNum;
		for($i=0; $i<$num; $i++){
			//if(!is_numeric($resArr[$i*$cutNum + 0])) break;
			$torrent[$i] = array(
			"name" 		=> $match[1][$i], 
			"tid" 		=> $match[2][$i],
			"comments" 	=> $resArr[$i*$cutNum + 0],
			"added" 	=> $resArr[$i*$cutNum + 1],
			"size" 		=> $resArr[$i*$cutNum + 2],
			"seeders"	=> $resArr[$i*$cutNum + 3],
			"leechers" 	=> $resArr[$i*$cutNum + 4],
			"completed"	=> $resArr[$i*$cutNum + 5],
			"owner"		=> $resArr[$i*$cutNum + 6]);
		}
		if(isset($torrent))
			return $torrent;
		else
			return "error:" . __LINE__ . ", torrents not found";
	}
}

class hdcmct{
	var $host = "hdcmct.org";
	var $name = "hdcmct";
	var $cutNum = 7;
	var $url;
	function get_html($url){
		$html = curl_get($url);
		//$html = file_get_contents("cache/" . $this -> host . ".tmp.php");
		if(!$html)	return 0;
		if(!strpos($html, "torrentname")) return 1;
		$html = substr($html, strpos($html, "torrentname") + 13);
		$html = substr($html, 0, strrpos($html, "rowfollow") + 40);
		$html = substr($html, 0, strrpos($html, ">") + 1);
		return $html;
	}
	
	function search($search){
		$url = "https://" . $this -> host . "/torrents.php?" . $search . "&incldead=1&notnewword=1";
		$this -> url = $url;
		$html = $this -> get_html($url);
		if(!$html) return "error:" . __LINE__ . ", can not connect to the " . $this -> name;
		preg_match_all('/<a\stitle\="(.*?)"\s\shref\="details\.php\?id\=([0-9]+)\&amp;hit\=1/', $html, $match);
		//var_dump($match);
		$html = preg_replace('/<a\stitle\="(.*?)"\s\shref\="details\.php\?id\=([0-9]+)\&amp;hit\=1">/', '', $html);	//清除标题 链接
		$html = preg_replace('/<img\sclass\="sticky.*?>\&nbsp;/', '', $html);	//清除置顶
		$html = preg_replace('/<td\sclass="embedded"><b>.*?<\/b>/', '', $html);	//清除标题 文字
		$html = preg_replace('/<b>\[<font.*?<\/font>\]<\/b>/', '', $html);	//清除[热门]等等
		$html = preg_replace('/\(<font.*?<\/font>\)/', '', $html);	//清除(0day)等等
		$html = preg_replace('/\(.*?<\/span>\)/', '', $html);	//清除限时free
		$html = preg_replace('/<br\s\/>.*?<\/td><td\swidth/', '<', $html);	//清除副标题
		$html = preg_replace('/8pt">.*?<\/span>/', '<', $html);	//清除评分
		$res = preg_replace('/<.*?>/', ' ', $html);
		$res = preg_replace('/\n+ +/', ' ', $res);
		$res = preg_replace('/[\s]{2,}/', '  ', $res);
		$resArr = explode('  ', $res);
		if(!isset($resArr[6])) return "error:" . __LINE__ . ", torrents not found";
		//var_dump($resArr);
		array_splice($resArr, 0, 1);
		array_splice($resArr, -1, 1);
		//var_dump($resArr);
		$cutNum = $this -> cutNum;
		$num = count($resArr) / $cutNum;
		for($i=0; $i<$num; $i++){
			//if(!is_numeric($resArr[$i*$cutNum + 0])) break;
			$torrent[$i] = array(
			"name" 		=> $match[1][$i], 
			"tid" 		=> $match[2][$i],
			"comments" 	=> $resArr[$i*$cutNum + 0],
			"added" 	=> $resArr[$i*$cutNum + 1],
			"size" 		=> $resArr[$i*$cutNum + 2],
			"seeders"	=> $resArr[$i*$cutNum + 3],
			"leechers" 	=> $resArr[$i*$cutNum + 4],
			"completed"	=> $resArr[$i*$cutNum + 5],
			"owner"		=> $resArr[$i*$cutNum + 6]);
		}
		if(isset($torrent))
			return $torrent;
		else
			return "error:" . __LINE__ . ", torrents not found";
	}
}

class hdsky{
	var $host = "hdsky.me";
	var $name = "hdsky";
	var $cutNum = 7;
	var $url;
	function get_html($url){
		$html = curl_get($url);
		//$html = file_get_contents("cache/" . $this -> host . ".tmp.php");
		if(!$html)	return 0;
		if(!strpos($html, "torrentname")) return 1;
		$html = substr($html, strpos($html, "torrentname") + 13);
		$html = substr($html, 0, strrpos($html, "rowfollow") + 40);
		$html = substr($html, 0, strrpos($html, ">") + 1);
		return $html;
	}
	
	function search($search){
		$url = "https://" . $this -> host . "/torrents.php?" . $search . "&incldead=1&notnewword=1";
		$this -> url = $url;
		$html = $this -> get_html($url);
		if(!$html) return "error:" . __LINE__ . ", can not connect to the " . $this -> name;
		preg_match_all('/<a\stitle\="(.*?)"\s\shref\="details\.php\?id\=([0-9]+)\&amp;hit\=1/', $html, $match);
		//var_dump($match);
		$html = preg_replace('/<a\stitle\="(.*?)"\s\shref\="details\.php\?id\=([0-9]+)\&amp;hit\=1">/', '', $html);	//清除标题 链接
		$html = preg_replace('/<img\sclass\="sticky.*?>\&nbsp;/', '', $html);	//清除置顶
		$html = preg_replace('/<td\sclass="embedded"\stitle\=""><b>.*?<\/b>/', '', $html);	//清除标题 文字
		$html = preg_replace('/<b>\[<font.*?<\/font>\]<\/b>/', '', $html);	//清除[热门]等等
		$html = preg_replace('/\(<font.*?<\/font>\)/', '', $html);	//清除(0day)等等
		$html = preg_replace('/\(.*?<\/span>\)/', '', $html);	//清除限时free
		$html = preg_replace('/<br\s\/>.*?<\/td><td\swidth/', '<', $html);	//清除副标题
		$html = preg_replace('/width\="16px">.*?<\/a>/', '', $html);	//清除评分
		$html = preg_replace('/<td\sclass\="rowfollow">[0-9]{1,3}\%<\/td>/', '', $html);	//清除进度
		$res = preg_replace('/<.*?>/', ' ', $html);
		$res = preg_replace('/\n+ +/', ' ', $res);
		$res = preg_replace('/[\s]{2,}/', '  ', $res);
		$resArr = explode('  ', $res);
		if(!isset($resArr[6])) return "error:" . __LINE__ . ", torrents not found";
		//var_dump($resArr);
		array_splice($resArr, 0, 1);
		array_splice($resArr, -1, 1);
		//var_dump($resArr);
		$cutNum = $this -> cutNum;
		$num = count($resArr) / $cutNum;
		for($i=0; $i<$num; $i++){
			//if(!is_numeric($resArr[$i*$cutNum + 0])) break;
			$torrent[$i] = array(
			"name" 		=> $match[1][$i], 
			"tid" 		=> $match[2][$i],
			"comments" 	=> $resArr[$i*$cutNum + 0],
			"added" 	=> $resArr[$i*$cutNum + 1],
			"size" 		=> $resArr[$i*$cutNum + 2],
			"seeders"	=> $resArr[$i*$cutNum + 3],
			"leechers" 	=> $resArr[$i*$cutNum + 4],
			"completed"	=> $resArr[$i*$cutNum + 5],
			"owner"		=> $resArr[$i*$cutNum + 6]);
		}
		if(isset($torrent))
			return $torrent;
		else
			return "error:" . __LINE__ . ", torrents not found";
	}
}

class hdchina{
	var $host = "hdchina.club";
	var $name = "hdchina";
	var $cutNum = 7;
	var $url;
	function get_html($url){
		$html = curl_get($url);
		//$html = file_get_contents("cache/" . $this -> host . ".tmp.php");
		if(!$html)	return 0;
		if(!strpos($html, "tbname")) return 1;
		$html = substr($html, strpos($html, "tbname") + 13);
		$html = substr($html, 0, strrpos($html, "t_uploader") + 40);
		$html = substr($html, 0, strrpos($html, ">") + 1);
		return $html;
	}
	
	function search($search){
		$url = "https://" . $this -> host . "/torrents.php?" . $search . "&incldead=1&notnewword=1";
		$this -> url = $url;
		$html = $this -> get_html($url);
		if(!$html) return "error:" . __LINE__ . ", can not connect to the " . $this -> name;
		preg_match_all('/<a\stitle\="(.*?)"\s\shref\="details\.php\?id\=([0-9]+)\&amp;hit\=1/', $html, $match);
		//var_dump($match);
		$html = preg_replace('/<a\stitle\="(.*?)"\s\shref\="details\.php\?id\=([0-9]+)\&amp;hit\=1">/', '', $html);	//清除标题 链接
		$html = preg_replace('/<img\sclass\="sticky.*?>\&nbsp;/', '', $html);	//清除置顶
		$html = preg_replace('/<h3>.*?<\/h3>/', '', $html);	//清除标题 文字
		$html = preg_replace('/<b>\[<font.*?<\/font>\]<\/b>/', '', $html);	//清除[热门]等等
		$html = preg_replace('/\(<font.*?<\/font>\)/', '', $html);	//清除(0day)等等
		$html = preg_replace('/<\/p><span\stitle.*?<\/span>/', '', $html);	//清除限时free
		$html = preg_replace('/<h4>.*?<\/h4>/', '', $html);	//清除副标题
		$html = preg_replace('/<\/em>.*?<\/a>/', '', $html);	//清除评分
		$html = preg_replace('/<td\sclass\="rowfollow">[0-9]{1,3}\%<\/td>/', '', $html);	//清除进度
		$res = preg_replace('/<.*?>/', ' ', $html);
		$res = preg_replace('/\n+ +/', ' ', $res);
		$res = preg_replace('/[\s]{2,}/', '  ', $res);
		$resArr = explode('  ', $res);
		if(!isset($resArr[6])) return "error:" . __LINE__ . ", torrents not found";
		//var_dump($resArr);
		array_splice($resArr, 0, 1);
		array_splice($resArr, -1, 1);
		//var_dump($resArr);
		$cutNum = $this -> cutNum;
		$num = count($resArr) / $cutNum;
		for($i=0; $i<$num; $i++){
			//if(!is_numeric($resArr[$i*$cutNum + 0])) break;
			$torrent[$i] = array(
			"name" 		=> $match[1][$i], 
			"tid" 		=> $match[2][$i],
			"comments" 	=> $resArr[$i*$cutNum + 0],
			"added" 	=> $resArr[$i*$cutNum + 1],
			"size" 		=> $resArr[$i*$cutNum + 2],
			"seeders"	=> $resArr[$i*$cutNum + 3],
			"leechers" 	=> $resArr[$i*$cutNum + 4],
			"completed"	=> $resArr[$i*$cutNum + 5],
			"owner"		=> $resArr[$i*$cutNum + 6]);
		}
		if(isset($torrent))
			return $torrent;
		else
			return "error:" . __LINE__ . ", torrents not found";
	}
}

class cache{
	function get_value($url){
		return file_get_contents("cache/" . $url);
	}
	function cache_value($url, $file, $time){
		return file_put_contents("cache/" . $url, $file);
	}
}

$Cache = new cache();

class medal{

	var $host = "2016.hupu.com";
	var $url = "http://2016.hupu.com/medal";
	var $name = "hupu";
	var $cutNum = 6;
	function get_html($url){
		global $Cache;
		if(!$html = $Cache->get_value('2016.hupu.com.content')){
			$html = curl_get($url);
			$Cache->cache_value('2016.hupu.com.content', $html, 300);
		}
		//$html = file_get_contents("cache/" . $this -> host . ".tmp.php");
		if(!$html)	return 0;
		if(!strpos($html, "china-medals")) return 1;
		$html = substr($html, strpos($html, "china-medals") - 12);
		$html = substr($html, 0, strpos($html, "hp-copyright") - 12);
		return $html;
	}
	
	function get_china_medal(){
		$html = $this -> get_html($this -> url);
		if(!$html) return "error:" . __LINE__ . ", can not connect to the " . $this -> name;
		$html = substr($html, 0, strpos($html, "</table>") - 12);
		$html = preg_replace('/<.*?>/', ' ', $html);
		$html = preg_replace('/\n+ +/', ' ', $html);
		$html = preg_replace('/[\s]{2,}/', '  ', $html);
		$resArr = explode('  ', $html);
		array_splice($resArr, 0, 1);
		array_splice($resArr, -1, 1);
		//var_dump($resArr);
		$china = array(
			"rank"	=>	$resArr[6],
			"gold"	=>	$resArr[8],
			"silver"	=>	$resArr[9],
			"bronze "	=>	$resArr[10],
		);
		return $china;
	}
	
	function get_medal_rank(){
		$html = $this -> get_html($this -> url);
		if(!$html) return "error:" . __LINE__ . ", can not connect to the " . $this -> name;
		$html = substr($html, strpos($html, "page-medal-rank") + 502);
		$html = preg_replace('/<.*?>/', ' ', $html);
		$html = preg_replace('/\n+ +/', ' ', $html);
		$html = preg_replace('/[\s]{2,}/', '  ', $html);
		$resArr = explode('  ', $html);
		array_splice($resArr, 0, 1);
		array_splice($resArr, -1, 1);
		//var_dump($resArr);
		$cutNum = $this -> cutNum;
		$num = count($resArr) / $cutNum;
		for($i=0; $i<$num; $i++){
			//if(!is_numeric($resArr[$i*$cutNum + 0])) break;
			$rank[$i] = array(
			"rank"	=>	$resArr[$i*$cutNum + 0],
			"name"	=>	$resArr[$i*$cutNum + 1],
			"gold"	=>	$resArr[$i*$cutNum + 2],
			"silver"	=>	$resArr[$i*$cutNum + 3],
			"bronze "	=>	$resArr[$i*$cutNum + 4],
			"total"	=>	$resArr[$i*$cutNum + 5]);
		}		
		return $rank;
	}
}

class medal2{
	var $host = "2016.hupu.com";
	var $url = "http://2016.hupu.com/medal";
	var $name = "hupu";
	var $cutNum = 6;
	
	function curl_get($url){
		$ch = curl_init();
		$timeout = 2;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$file_contents = curl_exec($ch);
		curl_close($ch);
		return $file_contents;
	}	
	function get_html($url){
		$html = $this -> curl_get($url);
		if(!$html)	return 0;
		if(!strpos($html, "china-medals")) return 1;
		$html = substr($html, strpos($html, "china-medals") - 12);
		$html = substr($html, 0, strpos($html, "hp-copyright") - 12);
		return $html;
	}	
	function get_china_medal(){
		global $Cache;
		if(!$chinaCache = $Cache->get_value('2016.hupu.com.content')){
			$html = $this -> get_html($this -> url);
			if(!$html) return "error:" . __LINE__ . ", can not connect to the " . $this -> name;
			$html = substr($html, 0, strpos($html, "</table>") - 12);
			$html = preg_replace('/<.*?>/', ' ', $html);
			$html = preg_replace('/\n+ +/', ' ', $html);
			$html = preg_replace('/[\s]{2,}/', '  ', $html);
			$resArr = explode('  ', $html);
			array_splice($resArr, 0, 1);
			array_splice($resArr, -1, 1);
			$china = "中国 " . $resArr[8] . "金" . $resArr[9] . "银" . $resArr[10] . "铜 排名第" . $resArr[6];
			$Cache->cache_value('2016.hupu.com.content', $china, 300);
			return $china;
		}
		return $chinaCache;
	}
}

class bangumi{
	var $host = "bgm.tv";
	var $name = "bangumi";
	var $url;
	function get_html($url){
		//$html = curl_get($url);
		//file_put_contents("cache/" . $this->host . ".tmp.php", $html);
		$html = file_get_contents("cache/" . $this -> host . ".tmp.php");
		if(!$html)	return 0;
		if(!strpos($html, "id=\"infobox")) return 1;
		$html = substr($html, strrpos($html, "infobox") - 8);
		$html = substr($html, 0, strpos($html, "</ul>") + 5);
		return $html;
	}
	
	function get_describe($bgmid){
		$url = "http://" . $this -> host . "/subject/" . $bgmid;
		$this -> url = $url;
		$html = $this -> get_html($url);
		if(!$html) return "error:" . __LINE__ . ", can not connect to the " . $this -> name;		
		$html = preg_replace('/\s\/\s/', '/', $html);
		$html = preg_replace('/>\s/', '>', $html);
		$html = preg_replace('/<.*?>/', ' ', $html);
		$html = preg_replace('/\n+ +/', ' ', $html);
		$html = preg_replace('/[\s]{2,}/', '  ', $html);
		$resArr = explode('  ', $html);
		if(!isset($resArr[6])) return "error:" . __LINE__ . ", torrents not found";
		//var_dump($resArr);
		array_splice($resArr, 0, 1);
		array_splice($resArr, -1, 1);
		var_dump($resArr);
		$STAFF = "";
		$num = count($resArr);
		for($i=0; $i<$num; $i += 2){
			$STAFF .= $resArr[$i] . " " . $resArr[$i + 1] . "\n";
		}
		if($STAFF)
			return $STAFF;
		else
			return "error:" . __LINE__ . ", did no found the STAFF";
	}
	function getByApi($bid){
		$res = curl_get("http://api.bgm.tv/subject/". $bid ."?responseGroup=large");
		$resArr = json_decode($res);
		var_dump($resArr);
	}
}

class DoubanSite{
	var $cookie_jar = dirname(__FILE__)."/badge/cookie";
	var $sinaapp = "movieinfogen.sinaapp.com/get_info";
	
	function getResult($url){			
		if(strpos($descr, "http://www.imdb.com/title/tt"))
			$imdburl = substr($descr, strpos($descr, "http://www.imdb.com/title/tt"), 35);
		if(strpos($descr, "movie.douban.com/subject/"))
			$dburl = substr($descr, strpos($descr, "movie.douban.com/subject/"), 33);
		$category = "";
		$small_descr = "";
		$name = "";
		$dbId = parse_douban_id($dburl);
		$dbapiurl = "http://api.douban.com/v2/movie/subject/".$dbId;	
		$ch = curl_init();
		$timeout = 2;
		curl_setopt($ch, CURLOPT_URL, $dbapiurl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$file_contents = curl_exec($ch);
		curl_close($ch);	
		$contentArray = json_decode($file_contents,true);
		if($arr['msg'] != "movie_not_found"){
			if($contentArray['images']['large']) 
				$poster = "[img=".$contentArray['images']['large']."]";
			$descr = $poster."\r\n".$descr;
			$category = getCategoryByName($contentArray['subtype']);
			$small_descr = implode('/', $contentArray['genres']);
		}
		if($dburl) $dburl = "https://".$dburl;
		$result = array(
		'status'		=>		'OK',
		'category'		=>		$category,
		'name'			=>		$name,
		'small_descr'	=>		$small_descr,
		'descr'			=>		$descr,
		'url'			=>		$imdburl,
		'dburl'			=>		$dburl);
	}
}

function getCategoryByName($str){
	switch($str){
		case "电影":
			$cat = 401;
			break;
		case "剧集":
			$cat = 402;
			break;
		case "动漫":
			$cat = 403;
			break;
		case "动漫":
			$cat = 403;
			break;
		case "体育":
			$cat = 405;
			break;
		case "记录":
			$cat = 406;
			break;
		case "音乐":
		case "MV":
			$cat = 407;
			break;
		case "学习":
			$cat = 408;
			break;
		case "软件":
			$cat = 409;
			break;
		case "游戏":
			$cat = 410;
			break;
		default :
			$cat = 411;
	}
	return $cat;
}



		
