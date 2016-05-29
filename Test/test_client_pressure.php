<?php
/**
 * 模拟客户端的测试脚本
 * 用来测试服务端的链接数容量
 * 特性: 较多的链接数, 极快的发送频率
 * Created by lixuan-it@360.cn
 * User: lane
 * Date: 16/4/26
 * Time: 下午2:32
 * E-mail: lixuan868686@163.com
 * WebSite: http://www.lanecn.com
 */

ini_set('memory_limit', '1024M');

$statistic['sendFailedCount'] = 0;
$statistic['readFailedCount'] = 0;
$statistic['sendCount'] = 0;
$statistic['readCount'] = 0;

$clientList = array();
$clientCount = 20000;
while (true) {
    if (count($clientList) >= $clientCount) {
        break;
    }
    $errno = $errmsg = '';
    $client = stream_socket_client('111.206.61.177:19910', $errno, $errmsg);
    if (!$client) {
        var_dump($errno);
        var_dump($errmsg);
        continue;
    }
    $clientList[] = $client;
}
echo "创建成功\n";
while (1) {
    foreach($clientList as $key => $client){
        $result = fwrite($client, "PING\n");
        if(!$result){
            $statistic['sendFailedCount']++;
            var_dump("一个链接断开了\n");
            fclose($client);
            unset($clientList[$key]);
            $clientList[] = stream_socket_client('127.0.0.1:19910', $errno, $errmsg);
            continue;
        }
        $statistic['sendCount']++;
        $data = '';
        while (feof($client) !== true) {
            $data .= fread($client, 2000);
            if ($data[strlen($data) - 1] === "\n") {
                break;
            }
        }
        if($data === "PONG\n"){
            $statistic['readCount']++;
        }else{
            $statistic['readFailedCount']++;
        }
    }
    file_put_contents('/home/lixuan-it/meepops-statistic', json_encode($statistic));
    sleep(1);
}