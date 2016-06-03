<?php
include_once('image.php');
include_once('memobird.php');

$image = new image();
$memobird = new memobird('abcdefg');//此处修改为自己的access Key

/*
你可以通过直接修改此文件或通过引用此文件来进行类方法的调用。
以下为示例代码：
*/
//example code
//绑定设备
$memobirdID = 'MEMOBIRDID';//此次修改为您的设备编码
$user = $memobird->getUserId($memobirdID,'test');//'MEMOBIRDID'为设备编码，'test'是开发者自定义
var_dump($user);
/*array(
  'showapi_res_code'=>'1',
  'showapi_res_error'=>'ok',
  'showapi_userid'=>'8'
);*/
//构造打印内容
$contents = $memobird->contentSet('T','欢迎使用咕咕机~');//'T'表示文字类型，后面是文字内容
//$contents = $memobird->contentSet('P',$image_contents);
//'P'表示图片类型，$image_contents是图片数据-图片内容的获取参见“图片处理”
//打印文字
$print = $memobird->printPaper($contents,$memobirdID,$user['showapi_userid']);
var_dump($print);

//打印图片
$im = imagecreatefromjpeg('filename');//读入一个jpg图片 'filename'为文件路径
$im = $image->resizeImage($im);//将宽缩小到不超过384px;
$image_contents = $image->imagebmp($im,false,true);//第二三个参数分别为生成文件名、是否返回内容
//构造打印内容
$contents = $memobird->contentSet('P',$image_contents);
//打印
$print = $memobird->printPaper($contents,$memobirdID,$user['showapi_userid']);
var_dump($print);

