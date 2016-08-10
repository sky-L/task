<?php
/**
 * 获取微信全部用户
 * @author skylee
 *
 */
namespace Action;

require "BaseAction.php";

use Illuminate\Database\Eloquent\Model as model;

class Openids extends model
{

    public $timestamps = false;

    protected $table = 'openids';
}

class SyncAction extends BaseAction
{

    public $media_id = "";

    public $group_id = "0";

    public function __construct()
    {
        $this->withCapsule('lenovoweixin');
    }
 
    /*
     * 推送图文
     */
    public function push_openids()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/message/send?access_token=" . $this->token;
        
        $news = [
            'articles' => 

            [
                [
                    'title' => '联想服务周末加班，坚持为您升Win10',
                    'description' => '联想服务周末加班，坚持为您升Win10',
                    'url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjAyNzE4MA==&mid=280755695&idx=1&sn=b6425132cf991476e6e8fdd2551e19a6#rd',
                    'picurl' => 'http://mmbiz.qpic.cn/mmbiz/K1QTs69PnJTPX01VHWKbPibeESz4uatMdcXbUBlVTqNe0FbbVO81kMlaTgo92KN2piaVLAp797iat8hDm50651m8A/640?wx_fmt=jpeg&wxfrom=5&wx_lazy=1'
                ]
            ]
        ];
        
        $page = 1;
        
        $pagesize = 10000;
        
        $size = ($page - 1) * $pagesize;
        
        $openids = Openid::skip(1500)->take(8500)
            ->get()
            ->toArray();
        
        foreach ($openids as $k => $v)
        {
            
            $data = [
                'touser' => $v['openid'],
                'msgtype' => 'news',
                'news' => $news
            ];
            
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
            
            $result = $this->sendPost($url, $data);
            
            var_dump($v['id'] . $result);
        }
    }

    public function sync_openid()
    {
        $result = $this->get_openid();
        
        $this->batch_insert_openid($result['data']['openid']);
        
        $l = ceil($result['total'] / 10000) - 1;
       
        $next_openid = $result['next_openid'];
        
        for ($i = 0; $i < $l; $i ++)
        {
            $result = $this->get_openid($next_openid);
            
            $next_openid = $result['next_openid'];
            
            $this->batch_insert_openid($result['data']['openid']);
            
            echo $i . "\n";
        }
    }

    public function get_openid($next_openid = null)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=" . $this->return_token();
        
        if ($next_openid)
        {
            $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=" . $this->return_token() . "&next_openid=" . $next_openid;
        }
        
        return json_decode($this->sendPost($url, null, false), true);
    }

    public function batch_insert_openid($openids)
    {
        $openid = [];
        
        foreach ($openids as $k => $v)
        {
            $data = [];
            
            $data[]['openid'] = $v;
            
            $openid = array_merge($openid, $data);
        }
        
        Openid::insert($openid);
    }
    
    public function sycn_openid_by_next_openid($next_openid = '')
    {
        $result = $this->get_openid($next_openid);
        
        $this->batch_insert_openid($result['data']['openid']);
        
        echo 'down';
    }

    public function get_count()
    {
        $result = $this->get_openid();
        
        unset($result['data']);
        
        echo "<pre>";
        print_r($result);
    }
}

$obj = new SyncAction();

$obj->sync_openid();





