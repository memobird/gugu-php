# gugu-php
基于PHP的咕咕机API开源项目。

官方开发文档请前往open.memobird.cn下载

想跟其他开发者一起玩耍咕咕机，可以加入咕咕开发者官方QQ群 41965804

memobird.php	memobird开发接接口方法操作类
image.php	图片处理类 

更改日志：


v20151103:
新增	图片处理类
修改	printPaper方法
新增	setContent方法：构造打印内容格式
注意事项：图片内容指file_get_contents获得的内容
图片生成bmp前先灰度处理

v20151030:
开发接口的基本封装
