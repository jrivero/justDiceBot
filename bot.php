<?php

$config['login']['username'] = "";
$config['login']['password'] = "";
$config['general']['timeout'] = 1;
$config['general']['vervose'] = 3; //0, 1, 2 (none, infos, debug)
$config['general']['siteUrl'] = "https://just-dice.com/";

$res =  curl($config['general']['siteUrl']);
//Cloudflare Bypass
if(strstr($res, "DDoS protection by CloudFlare")){
	echo colorize("CloudFlare detected...", "warning");
	//Get the math calc
	$math_calc = get_between($res, "a.value = ", ";");
	if($math_calc){
		//Resolve the math calc
		$math_result = (int)eval("return ($math_calc);");
		if(is_numeric($math_result)){
			echo colorize("Math resolved: (".$math_calc." = ".$math_result.")", "success");
			$math_result += 13; //Domain lenght (just-dice.com)
			//Send the CloudFlare's form
			$getData = "cdn-cgi/l/chk_jschl";
			$getData .= "?jschl_vc=".get_between($res, 'name="jschl_vc" value="', '"');
			$getData .= "&jschl_answer=".$math_result;
			$res = curl($config['general']['siteUrl'].$getData.$getData);
			//Cloudflare Bypassed?
			if(strstr($res, "DDoS protection by CloudFlare")){
				echo colorize("CloudFlare not bypassed...", "error");
				exit;
			}else{
				echo colorize("CloudFlare bypassed!", "success");
			}
		}
	}
}
//Login
if($config['login']['username']){
	echo colorize("Login in...");
	$res =  curl($config['general']['siteUrl'], $config['login']);
	if(strstr($res, "Your user ID is ")){
		echo colorize("Logged!", "success");
	}
}
//Bet
/*
	Get status
	Request URL: https://just-dice.com/socket.io/1/xhr-polling/_yqqOdVHdTkkiJyhO79A?t=1382806602549
	Response: 5:::{"name":"result","args":[{"bankroll":"58599.16860267","bet":"0.05","betid":204158365,"bets":"60665","chance":"49.5","date":1382806891,"high":false,"luck":"100.53%","lucky":465205,"max_profit":"293.00","name":"langlang (122775)","nonce":346,"payout":2,"ret":"0.1","this_profit":"+0.05","uid":"122775","wagered":"366.16156380","win":true,"stats":{"bets":204157514,"profit":-6066.97780045,"wins":95889899,"losses":108267615,"purse":58599.16860287,"commission":961.5341930614599,"wagered":4274610.35363795,"luck":204853936.26771772},"investment":0,"percent":0,"invest_pft":0}]}

	0.001 (Lose)
	Request URL: https://just-dice.com/socket.io/1/xhr-polling/_yqqOdVHdTkkiJyhO79A?t=1382806602179
	Request Payload: 5:::{"name":"bet","args":["fexkgqZV2LHv",{"chance":"50","bet":"0.001","which":"hi"}]}
	Response: 1

	0.002 (Win)
	Request URL: https://just-dice.com/socket.io/1/xhr-polling/_yqqOdVHdTkkiJyhO79A?t=1382806616275
	Request Payload:  5:::{"name":"bet","args":["fexkgqZV2LHv",{"chance":"50","bet":"0.002","which":"hi"}]}
	Response: 1
*/

function curl($url, $post="") {
	//debug
	$print = "<curl> ".$url;
	if($post){
		$print .= " | ".implode(", ", $post);
	}
	echo colorize($print, "debug");
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	if($post){
		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $post);
	}
	curl_setopt ($ch, CURLOPT_COOKIEJAR, "cookie.txt");
	curl_setopt ($ch, CURLOPT_COOKIEFILE, "cookie.txt");
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	$result = curl_exec($ch);
	$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	return $result;
}

function get_between($string,$start,$end){
	$string = " ".$string;
	$ini = strpos($string, $start);
	if($ini==0) return "";
	$ini += strlen($start);
	$len = strpos($string, $end, $ini) - $ini;
	return substr($string, $ini, $len);
}

//http://softkube.com/blog/generating-command-line-colors-with-php/
function colorize($text, $status=null) {
	global $config;
	if($config['general']['vervose']){
		$out = "";
	 	switch(strtolower($status)){
	  		case "banner":
	   			$out = "[0;32m"; //Green
	  		break;
	  		case "title":
	  			$text = "[+] ".$text;
	   			$out = "[0;34m"; //Blue
	  		break;
	  		case "debug":
	  			if($config['general']['vervose']>=2){
		  			$text = " * ".$text;
	  			}else{
		  			$text = "";
	  			}
	   		break;
	  		case "success":
	  			$text = " - ".$text;
	   			$out = "[0;32m"; //Green
	   		break;
	  		case "error":
	   			$text = " ! ".$text;
	   			$out = "[0;31m"; //Red
	   		break;
	  		case "warning":
	  			$text = " X ".$text;
	   			$out = "[1;33m"; //Yellow
	   		break;
	  		case "notice":
	  			$text = " - ".$text;
	   			$out = "[0;34m"; //Blue
	   		break;
	   		default:
	   			$text = " | ".$text;
	   		break;
	   		case "table":
	  			$lines = explode("\n", $text);
	  			$text = "";
	  			if($lines){
	  				$l = count($lines)-1;
	  				foreach($lines as $i=>$line){
	  					$text .= " ".$line;
	  					if($i<$l) $text .= "\n";
	  				}
	  			}
	   		break;
		}
		if($text){
			if($out)
		 		return chr(27).$out.$text.chr(27)."[0m \n";
		 	else
		 		return $text."\n";
		 }
	 }
}
?>