#! /usr/bin/php
<?php
set_time_limit(0);

$hash = md5(time());

$url = "http://www.ziroom.com/z/vr/%s.html";
include("snoopy.class.php");
$snoopy = new Snoopy;
$snoopy->agent = 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0); 360Spider';
$snoopy->referer = "http://www.renren.com";
$snoopy->rawheaders["X_FORWARDED_FOR"] = "172.16.5.111";


for($i = 1;$i< 200000; $i++) {
    $url_n = sprintf($url, $i);
    //$snoopy->fetch($url); //获取所有内容
    
    sleep(0.1);
    
    $snoopy->fetch($url_n);
    $content_all = $snoopy->results;
    //print_r($snoopy->results); //显示结果
    $temp1 = preg_split('/<div class="room_detail_right">/', $content_all);
    $temp2 = preg_split('/<div class="room_btn_section clearfix">/', $temp1[1]);

    //$temp2[0]  最终结果
    $final = $temp2[0];

    //价格
    $price = preg_split('/<span class="room_price">/', $final);
    $price = preg_split('/<\/span>\&nbsp;元/', $price[1]);
    $price = $price[0];
    //价格 END

    if(empty($price)) {
        //echo $i . ' failed !' . "\n";
        continue;
    }

    $room = preg_split('/<div class="room_name">/', $final);
    $room = preg_split('/<div class="room_ambient">/', $room[1]);
    $room = preg_split('/<p>/', $room[0]);
    $room = preg_split('/<\/p>/', $room[1]);
    $room = trim($room[0]);
    if(strstr($room, '<span>月')) {
        $type = '月租';
        $room = str_replace('<span>月</span>', '', $room);
    } else {
        $type = '年租';
    }
    echo $i . ' | ' . $room . ' | ' . $price . ' | ' . $type . "\n" ;
    error_log(print_r($i . ' ; ' . $room . ' ; ' . $price . ' ; ' . $type . "\n", 1), 3, './house.' . $hash . '.csv');    



}

//可选以下
//$snoopy->fetch($url); //获取所有内容
//$snoopy->fetchtext($url) //获取文本内容（去掉html代码）
//$snoopy->fetchlinks($url) //获取链接
//$snoopy->fetchform($url)  //获取表单
