之前涉及到的缓存操作有：
key                                     value                   description
ticket_{$actId}_left                    int                     某抢票活动是否还有余票
ticket_{$actId}_{$userId}               int                     某用户在某活动中已抢次数

新增缓存操作：
key
ticket_{$actId}                         array                   该活动内容，缓存时间首次请求后保持一天，保证高峰期时间内能直接从缓存中取到数据即可


wechat 底下Data_Xmu_Jwc中getInfo的返回值：
array(65) {
  ["wid"]=>
  string(14) "23320122203966"
  ["xsbh"]=>
  string(14) "23320122203966"
  ["xh"]=>
  string(14) "23320122203966"
  ["xm"]=>
  string(6) "倪丽"
  ["zpwjm"]=>
  string(14) "23320122203966"
  ["xmpy"]=>
  string(5) "ni li"
  ["xbdm"]=>
  string(1) "2"
  ["mzdm"]=>
  string(2) "01"
  ["hyzkdm"]=>
  string(1) "1"
  ["sfzjlxdm"]=>
  string(1) "1"
  ["sfzjh"]=>
  string(18) "510623199604098626"
  ["zzmmdm"]=>
  string(2) "03"
  ["csrq"]=>
  string(10) "1996-04-09"
  ["jgdm"]=>
  string(6) "510000"
  ["syddm"]=>
  string(6) "650000"
  ["jkzkdm"]=>
  string(2) "10"
  ["lygbdm"]=>
  string(3) "CHN"
  ["xxdm"]=>
  string(1) "1"
  ["hkszd"]=>
  string(15) "新疆昌吉市"
  ["hkxzdm"]=>
  string(1) "2"
  ["xhkszd"]=>
  string(15) "新疆昌吉市"
  ["sg"]=>
  string(3) "163"
  ["yxsh"]=>
  string(8) "05013000"
  ["zydm"]=>
  string(10) "Z013080703"
  ["bjdm"]=>
  string(17) "2012050130002A009"
  ["jdxwdm"]=>
  string(1) "4"
  ["jdxldm"]=>
  string(2) "20"
  ["sfzx"]=>
  string(1) "1"
  ["xjzt"]=>
  string(1) "1"
  ["xznj"]=>
  string(4) "2012"
  ["xz"]=>
  string(1) "4"
  ["yjbynf"]=>
  string(4) "2016"
  ["yjbysj"]=>
  string(10) "2015-04-01"
  ["sfzj"]=>
  string(1) "1"
  ["sfzxzj"]=>
  string(1) "1"
  ["rxqdw"]=>
  string(27) "新疆昌吉市第一中学"
  ["rxrq"]=>
  string(10) "2012-09-15"
  ["xslbdm"]=>
  string(2) "2A"
  ["yks"]=>
  string(1) "0"
  ["sjh"]=>
  string(11) "15711505721"
  ["qqh"]=>
  string(10) "1123621165"
  ["jtdz"]=>
  string(40) "新疆昌吉市绿洲北路197号2幢302"
  ["jtyb"]=>
  string(6) "831100"
  ["xbdm_displayvalue"]=>
  string(3) "女"
  ["mzdm_displayvalue"]=>
  string(6) "汉族"
  ["hyzkdm_displayvalue"]=>
  string(6) "未婚"
  ["sfzjlxdm_displayvalue"]=>
  string(9) "身份证"
  ["zzmmdm_displayvalue"]=>
  string(12) "共青团员"
  ["jgdm_displayvalue"]=>
  string(9) "四川省"
  ["syddm_displayvalue"]=>
  string(24) "新疆维吾尔自治区"
  ["jkzkdm_displayvalue"]=>
  string(15) "健康或良好"
  ["lygbdm_displayvalue"]=>
  string(6) "中国"
  ["xxdm_displayvalue"]=>
  string(4) "A型"
  ["hkxzdm_displayvalue"]=>
  string(15) "非农业户口"
  ["yxsh_displayvalue"]=>
  string(27) "信息科学与技术学院"
  ["zydm_displayvalue"]=>
  string(12) "通信工程"
  ["bjdm_displayvalue"]=>
  string(25) "2012级通信本科二班"
  ["jdxwdm_displayvalue"]=>
  string(6) "学士"
  ["jdxldm_displayvalue"]=>
  string(12) "大学本科"
  ["sfzx_displayvalue"]=>
  string(6) "在校"
  ["xjzt_displayvalue"]=>
  string(1) "1"
  ["sfzj_displayvalue"]=>
  string(6) "在籍"
  ["sfzxzj_displayvalue"]=>
  string(12) "在校在籍"
  ["xslbdm_displayvalue"]=>
  string(24) "普通高校本科学生"
  ["yks_displayvalue"]=>
  string(3) "否"
}