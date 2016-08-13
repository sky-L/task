<?php

/**
 * 
 * @author skylee
 *
 */
namespace Action;

require "BaseAction.php";

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector;
use follow;
use tieba;

class SpiderAction extends BaseAction
{

    public static $cookie_arr = [
        "BAIDUID" => "AC6F5D52C6315E2D31C5E642D580F146:FG=1",
        "BDUSS" => "UZlc3dVSkdlc0dnVjJKcjNXS1l-QTN0SEdxVzR3ZGJSd3lqU1JITi1YcHBiODVYQUFBQUFBJCQAAAAAAAAAAAEAAAByQf6esKLArbXPtvdGAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGnipldp4qZXY",
        "LONGID" => "2667463026",
        "TIEBAUID" => "7fe8d83ccd6036623078c716",
        "TIEBA_USERTYPE" => "aba520f5775792af116824cf"
    ];

    public function __construct()
    {
        $this->withCapsule("tieba");
    
       // $this->init_tieba();
        
        $keys = tieba::where([
            'tieba_status' => 1
        ])->skip(0)
            ->take(1)
            ->get()
            ->toArray();
        
        if ($keys)
        {
            foreach ($keys as $k => $v)
            {
                tieba::where([
                    'id' => $v['id']
                ])->update([
                    'tieba_status' => 2
                ]);
            }
            
            foreach ($keys as $id => $t_name)
            {
                $name = $this->record_follow_tieba($t_name['name']);
                
                $l = count($name);
                
                echo "-fetching detail\n";
                
                foreach ($name as $k => $v)
                {
                    $this->detail($v, $k, $t_name['name'],$l);
                }
            }
        }
    }

    
    public function init_tieba()
    {
        
        $url = "http://tieba.baidu.com/f/like/mylike?&pn=1";
        
        $html = $this->request("get", $url);
        
        $html = mb_convert_encoding($html, "UTF-8", "gbk,gb2312,UTF-8");
       
        echo "--geting html---\n";
        
        preg_match('/\<a href="\/f\/like\/mylike\?\&pn=(.*?)"\>尾页\<\/a\>/', $html, $total);
        
        $total = $total[1];
        
        $names = [];
        
        echo "--geting title---\n";
        
        for($i = 1; $i <= $total; $i++)
        {
            $url = "http://tieba.baidu.com/f/like/mylike?&pn=" . $i; 
            
            $names = array_merge($names,$this->fetch_tieba($url));
        }
       
        echo "--insert database---\n";
        
        foreach ($names as $k => $v)
        {
            if(!tieba::where(['name' => $v])->get()->first())
            {
                \tieba::create(['name' => $v]);
            }
        }
     
        echo "-- down insert ---\n";
        
        exit(__FILE__ . __LINE__);
    }
    
    public function fetch_tieba($url)
    {
        $html = $this->request("get", $url);
        
        $html = mb_convert_encoding($html, "UTF-8", "gbk,gb2312,UTF-8");
         
        $crawler = $this->craw($html);
        
        $found = $crawler->filter("table tr");
        
        if ($found->count())
        {
            $data = $found->each(function (Crawler $node, $i)
            {
                if($node->filter("a")->count())
                {
                    return $node->filter("a")->eq(0)->attr("title");
                }
                 
                return  false;
            });
        
            $data = array_filter($data);
        }
        
        return $data;
    }
    
    public function detail($name = "", $i = "", $tieba = "",$count = "")
    {
        $url = "http://www.baidu.com/p/" . $name . "/detail";
        
        $content = $this->request("get", $url);
        
        $content = mb_convert_encoding($content, "UTF-8", "gbk,gb2312,UTF-8");
        
        preg_match('/\<span class=profile-attr\>个人简介\<\/span\>\s+\<span class=profile-cnt\>(.*?)\<\/span\>/', $content, $detail);
        
        if ($detail && $profile = return_mobile($detail[1]))
        {
            follow::create([
                'name' => $name,
                "profile" => $profile,
                "tieba" => $tieba
            ]);
        }
        
        echo $tieba . "-" .$count ."--". $i . "--down\n";
    }

    /**
     * 获取所有楼主
     * 
     * @param string $key
     *            贴吧名称
     * @return multitype:
     */
    public function record_follow_tieba($key = "中国文玩核桃")
    {
        $content = $this->request("get", "http://tieba.baidu.com/f?kw=" . $key . "&ie=utf-8&pn=0");
        
        $content = mb_convert_encoding($content, "UTF-8", "gbk,gb2312,UTF-8");
        
        preg_match_all('/\<span class="red_text"\>(.*?)\<\/span\>/', $content, $total);
        
        $total = $total[1][0];
        
        $l = ceil($total / 50);
        
        $name = [];
        
        echo "-geting tieba name\n";
        
        for ($i = 0; $i < $l; $i ++)
        {
            $url = "http://tieba.baidu.com/f?kw=" . $key . "&ie=utf-8&pn=" . $i * 50;
            
            $name = array_merge($name, $this->return_name($url));
        }
        
        return $name;
    }

    /**
     * 发帖人用户名
     * 
     * @param unknown $url            
     * @return unknown
     */
    public function return_name($url)
    {
        $content = $this->request("get", $url);
        
        $content = mb_convert_encoding($content, "UTF-8", "gbk,gb2312,UTF-8");
        
        preg_match_all('/"主题作者:\s+(.*?)"/', $content, $names);
        
        return $names[1];
    }

    public static function getCookie()
    {
        $cookie = '';
        foreach (self::$cookie_arr as $key => $value)
        {
            if ($key != 'TIEBA_USERTYPE')
                $cookie .= $key . '=' . $value . ';';
            else
                $cookie .= $key . '=' . $value;
        }
        return $cookie;
    }

    /**
     * 处理html
     *
     * @return boolean multitype:boolean
     */
    public function craw($content = "")
    {
        $crawler = new Crawler();
        
        $crawler->addHtmlContent($content);
        
        return $crawler;
        
        $found = $crawler->filter(".content-sec .left-sec a");
        
        if ($found->count())
        {
            $data = $found->each(function (Crawler $node, $i)
            {
                // return $title = $node->filter(".left-cont-fixed")->attr("id");
                
                return $title = $node->html();
            });
            
            echo "<pre>";
            print_r($data);
        }
        else
        {
            $data = [];
            $continue = false;
        }
    }

    public function request($method, $url, $fields = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_COOKIE, self::getCookie());
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.130 Safari/537.36');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 浏览器输出
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        if ($method === 'POST')
        {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        }
        return $result = curl_exec($ch);
        
        // return $result;
        
        // return mb_convert_encoding($result, "UTF-8", "gbk,gb2312,UTF-8");
    }
}

$obj = new SpiderAction();





