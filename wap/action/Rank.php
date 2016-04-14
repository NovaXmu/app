<?php
/**
*
*   @copyright  Copyright (c) 2015
*   All rights reserved
*
*   file:           Rank.php
*   description:    Wechat rank
*
*   @author Edward
*   @license Apache v2 License
*
**/
/**
*
*/
class Action_Rank
{

    function __construct($resource)
    {
    }

    public function run()
    {
        $view = new Vera_View(false);//设置为true开启debug模式
        $view->setCaching(Smarty::CACHING_OFF);//关闭缓存

        if (isset($_GET['type']) && isset($_GET['gid']) && isset($_GET['date']) && isset($_GET['page']) && isset($_GET['end'])) {
            echo $this->_search();
        }
        elseif (isset($_GET['week'])) {

            date_default_timezone_set("Asia/Shanghai");

            $saturday_1 = date("Ymd",strtotime("last Saturday -1 week"));
            $sunday_1 = date("Ymd",strtotime("last Saturday -1 week -6 day"));
            $saturday_2 = date("Ymd",strtotime("last Saturday -2 week"));
            $sunday_2 = date("Ymd",strtotime("last Saturday -2 week -6 day"));
            $saturday_3 = date("Ymd",strtotime("last Saturday -3 week"));
            $sunday_3 = date("Ymd",strtotime("last Saturday -3 week -6 day"));
            

            $yesterday = date('Y-m-d',strtotime('-1 day'));
            $year = date("Y");

            $rank = $this->_curl('ranks' , '16282' , $saturday_1 . '_' . $sunday_1 , '1' , '&type=month');
            
            $view->assign('date_1' , $saturday_1 . '_' . $sunday_1);
            $view->assign('date_2' , $saturday_2 . '_' . $sunday_2);
            $view->assign('date_3' , $saturday_3 . '_' . $sunday_3);
            $view->assign('year' , $year);

            $view->assign('rank_data' , $rank);
            $view->dailyBackground();
            $view->display('wap/rank/Week.tpl');
            return true;
        }
        elseif (isset($_GET['wx_name'])) {

            $img_url = "http://open.weixin.qq.com/qr/code/?username=" . $_GET['wx_name'];

            $year = date("Y");
            $view->assign('year' , $year);
            $view->assign('img_url' , $img_url);
            $view->dailyBackground();
            $view->display('wap/rank/Qrcode.tpl');
            return true;
        }
        else {

            $yesterday = date('Y-m-d',strtotime('-1 day'));
            $year = date("Y");

            $rank = $this->_curl('ranks' , '16282' , '' , '1' , '');

            $view->assign('year' , $year);
            $view->assign('yesterday' , $yesterday);
            $view->assign('rank_data' , $rank);
            $view->dailyBackground();
            $view->display('wap/rank/Index.tpl');
            return true;
        }
            
    }

    public function _search() {
        $type = $_GET['type'];
        $gid = $_GET['gid'];
        $date = $_GET['date'];
        $page = $_GET['page'];
        $if_week = $_GET['end'];

        if ($if_week == 1) {
            $end = '&type=month';
        } else {
            $end = '';
        }

        $ranks = array();
        
        do {
            $rank = $this->_curl($type , $gid , $date , $page , $end);
            $ranks = array_merge($ranks , $rank);
            ++$page;
        } while((!empty($rank[0]['wx_nickname'])) && ($gid != '16282') && ($type == 'ranks'));

        return json_encode($ranks);
    }

    public function _curl($type , $gid , $date , $page , $end) 
    {
    
        if ($gid != '16282') {
            
            $category_val = $gid;
            $gid = '16282';
        }

        $category = array("wx_name"=>"value","qingchunxiada"=>1,"xmu_1921"=>1,"xmulib"=>1,"xmupress"=>1,"jyzd_xmu"=>1,"xmu_zs"=>1,"Xiadayiban"=>1,"xmuqcfx"=>1,"paxa110"=>1,"gh_86380353d46c"=>1,"advertising007"=>2,"xmupanshuxiehui"=>2,"xmuguitar"=>2,"xmujyycyxh"=>2,"XMUTENNIS"=>2,"XUMA-together"=>2,"xiadayanxie"=>2,"Greenwild_of_XMU"=>2,"xmublxs"=>2,"gh_087324ec5ed1"=>2,"xiadashiyun"=>2,"xmusunshine"=>2,"XMU18030061005"=>2,"family_love2013"=>2,"xiadalongzhou"=>2,"XMUTourism"=>2,"xmusoftware"=>2,"x-blood1314"=>2,"xmudianshang"=>2,"xmuxawx"=>2,"xibumengxiang"=>2,"xmutcc"=>2,"xmu3X-Game"=>2,"xiadaweilan"=>2,"xmuyxy"=>3,"xmuace"=>3,"xmu_software"=>3,"XMU-SPH"=>3,"xmucoe"=>3,"XMDXXSH"=>3,"xmugsu"=>3,"xmuqnzyz"=>3,"xmuyishutuan"=>3,"xmuyouth"=>3,"xmuslh"=>3,"xmukechuang"=>3,"xaxshwxgzpt"=>3,"Newlegend2014"=>3,"XMUGYxsxczx"=>3,"rwxyxsh2014"=>3,"xmugjxsh"=>3,"xmu_ecoers"=>3,"Math_XMU_Graduate"=>3,"gh_9864c87996d7"=>3,"XMU_SIST"=>3,"xmuwjyh"=>3,"xmuwuji"=>3,"xmu-spa"=>3,"xmu_materials"=>3,"xiadaguoguan"=>3,"xmuxkxsh"=>3,"xdhdyh"=>3,"xmuslhxafh"=>3,"xdyyxczx"=>3,"xdyxyxsh"=>3,"xmu_law"=>3,"xmujytyzx"=>3,"xmu_wwqx"=>3,"XMULifeScience"=>3,"xmu_math"=>3,"XMU_Hsers"=>3,"xmulawxsh"=>3,"xmu_cflc"=>3,"xmu_liuxue"=>3,"xmu_energy"=>3,"huanshengyanhui2013"=>3,"XDHYXSH"=>3,"xuanzhongniang"=>3,"xmu_shsj"=>3,"xmuhall"=>3,"gh_b41cb2577d3e"=>3,"XMU-SOE-NEWS"=>3,"xmucaizhengxi2014"=>3,"gh_e6a369332b64"=>3,"xmugmx"=>3,"international-news"=>3,"xmulostandfound"=>4,"gh_040ecdf49be0"=>4,"loofire"=>4,"xdletsgou"=>4,"qiguanfamily2012"=>4,"xmuexchange"=>4,"xmulecture"=>4,"xmuhelp"=>4,"xmu-edp"=>4,"xmu-gfs"=>4,"xmucas"=>4);
        
        $url = "http://www.gsdata.cn/index.php/rank/" . $type . "?gid=" . $gid . "&date=" . $date . "&page=" . $page . $end; 
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($ch);
        curl_close($ch);
        
        $output =  json_decode($output);
        $results =  $output->data->rows;
        if ($type == 'ranks') {
            
            $i = 0;
            foreach ($results as $row) {

                $result[$i]['rank'] = 50 * ($page - 1) + $i + 1;
                $result[$i]['wx_nickname'] = $row->wx_nickname;
                $result[$i]['url_post'] = $row->url_times . '/' . $row->url_num;
                $result[$i]['readnum_all'] = $row->readnum_all;
                $result[$i]['likenum_all'] = $row->likenum_all;
                $result[$i]['wci'] = $row->wci;
                $result[$i]['wx_name'] = $row->wx_name;
                if (isset($category_val)){
                    if ($category["$row->wx_name"] == $category_val) {
                        ++$i;
                    }
                } else {
                    ++$i;
                }
                
            }

        } elseif ($type == 'articles') {
            $i = 0;
            foreach ($results as $row) {

                $result[$i]['article'] = 10 * ($page - 1) + $i + 1;
                $result[$i]['wx_nickname'] = $row->wx_nickname;
                $result[$i]['wx_name'] = $row->wx_name;
                $result[$i]['title'] = $row->title;
                $result[$i]['content'] = $row->content;
                $result[$i]['url'] = $row->url;
                $result[$i]['posttime'] = $row->posttime;
                $result[$i]['readnum'] = $row->readnum;
                $result[$i]['likenum'] = $row->likenum;
                $result[$i]['picurl'] = "http://121.199.72.33/refer.php/?url=" . $row->picurl;
                $result[$i]['author'] = $row->author;               
                if (isset($category_val)){
                    if ($category["$row->wx_name"] == $category_val) {
                        ++$i;
                    }
                } else {
                    ++$i;
                }
            }
        } else {
            # code...
        }
        $result['start_date'] = $output->data->date;
        if(empty($result['start_date'])) {
            $result['start_date'] = date('Y-m-d',strtotime('-1 day'));;
        }  
        $result['end_date'] = $output->data->end_date;   
        return $result;
    }
}

?>
