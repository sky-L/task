<?php
/**
 * 用户分组
 * @author skylee
 *
 */

namespace Action;

require "BaseAction.php";

use Illuminate\Database\Eloquent\Model as model;
use Predis\Command\PubSubPublish;

class Openid extends model
{

    public $timestamps = false;

    protected $table = 'openid';
}

class UserAction extends BaseAction
{

    public $media_id = "";

    public $group_id = "";

    public $to_group_id = "165";

    public function __construct()
    {
        $this->withCapsule();
    }

    /**
     * 按openid推送
     */
    public function push_by_openids()
    {
        $openids = Article::all()->toArray();
        
        $openid_arr = [];
        foreach ($openids as $k => $v)
        {
            $openid_arr[] = $v['openid'];
        }
        
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=" . $this->token;
        
        $media_id = $this->media_id;
        
        $data = [
            'touser' => $openid_arr,
            'mpnews' => [
                'media_id' => $media_id
            ],
            'msgtype' => 'mpnews'
        ];
        
        echo json_encode($data);
        
        $res = $this->sendPost($url, json_encode($data));
        
        var_dump($res);
    }

    public function get_media()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=" . $this->token;
        
        $mdata = [
            'type' => 'news',
            'offset' => 2,
            'count' => 1
        ];
        
        $media_ids = $this->sendPost($url, json_encode($mdata));
        echo '<pre>';
        print_r(json_decode($media_ids, true));
    }

    /**
     * 分组推送
     */
    public function push_by_group()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=" . $this->token;
        
        $media_id = $this->media_id;
        
        $group_id = $this->group_id;
        
        $data = [
            'filter' => [
                'is_to_all' => false,
                'group_id' => $group_id
            ],
            'mpnews' => [
                'media_id' => $media_id
            ],
            'msgtype' => 'mpnews'
        ];
        
        $re = $this->sendPost($url, json_encode($data));
        
        $time = date('Y-m-d H:i:s');
        
        var_dump($time . $re);
    }

    /**
     * 测试推送
     */
    public function push_by_openid()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=" . $this->token;
        
        $url = "https://api.weixin.qq.com/cgi-bin/message/send?access_token=" . $this->token;
        
        $media_id = $this->media_id;
        
        $openid = "oLHCTjk9UjXprhuOTa9n0AV4QLPE";
        
        $data = [
            'touser' => $openid,
            'mpnews' => [
                'media_id' => $media_id
            ],
            'msgtype' => 'mpnews'
        ];
        
        $re = $this->sendPost($url, json_encode($data));
        
        var_dump($re);
    }

    /**
     * 把用户移动到别的分组
     */
    public function push_group()
    {
        for ($i = 1; $i <= 100; $i ++)
        {
            $start = ($i - 1) * 50;
            
            $openids = Openid::skip($start)->take(50)
                ->get()
                ->toArray();
            
            $openid_arr = [];
            
            foreach ($openids as $k => $v)
            {
                $openid_arr[] = $v['openid'];
            }
            
            $url = "https://api.weixin.qq.com/cgi-bin/groups/members/batchupdate?access_token=" . $this->return_token();
            
            $data = [];
            
            $data = [
                'openid_list' => $openid_arr,
                'to_groupid' => $this->to_group_id
            ];
            
            $re = $this->sendPost($url, json_encode($data));
            
            $data = json_decode($re, true);
            
            if ($data['errcode'] != 0)
            {
                sleep(6);
            }
            
            echo $i . '<hr>';
            var_dump($re);
        }
    }

    /**
     * 删除分组 batch
     */
    public function del_group()
    {
        $url = "http://api.weixin.qq.com/cgi-bin/groups/delete?access_token=" . $this->token;
        
        $data = [
            'group' => [
                'id' => '130'
            ]
        ];
        
        $re = $this->sendPost($url, json_encode($data));
        
        var_dump($re);
    }

    /**
     * 获取分组
     */
    public function get_group()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/groups/get?access_token=" . $this->token;
        
        $re = $this->sendPost($url, '');
        
        $re = json_decode($re, true);
        
        echo '<pre>';
        print_r($re);
    }

    /**
     * 添加分组
     */
    public function create_group()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/groups/create?access_token=" . $this->token;
        
        $data = [
            'group' => [
                'name' => '5'
            ]
        ];
        
        $re = $this->sendPost($url, json_encode($data, JSON_UNESCAPED_UNICODE));
        
        var_dump($re);
    }

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
    
    /*
     * 先查询再删掉
     */
    public function shop_list()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/poi/getpoilist?access_token=" . $this->return_token();
        
        $shop_list = [
            '宝城路店',
            '巴王路店',
            '联想服务3C生活馆',
            '海淀知春路店',
            '联想3C服务中心望京店',
            '联想3C服务中心朝阳路店'
        ];
        
        $poi_id = [
            '401101819',
            '292339870',
            '401101739',
            '292248640',
            '277969143',
            '277979603'
        ];
        
        for ($i = 1; $i < 32; $i ++)
        {
            $limit = 10;
            
            $start = ($i - 1) * $limit;
            
            $begin = [
                'begin' => $start,
                "limit" => $limit
            ];
            
            $re = $this->sendPost($url, json_encode($begin, JSON_UNESCAPED_UNICODE));
            
            $re = json_decode($re, true);
            
            $data = $re['business_list'];
            
            foreach ($data as $k => $v)
            {
                $true_data = $v['base_info'];
                
                if (! in_array($true_data['poi_id'], $poi_id))
                {
                    
                    $result = $this->shop_del($true_data['poi_id']);
                    echo $i . $result;
                }
                else
                {
                    echo $i;
                }
            }
        }
    }

    public function shop_del($poi_id)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/poi/delpoi?access_token=" . $this->return_token();
        
        $data = [
            'poi_id' => $poi_id
        ];
        
        return $this->sendPost($url, json_encode($data));
    }

    /**
     * 创建门店
     */
    public function create_shop()
    {
       // $file = "shop_list.xls";
        
        $data = $this->read_excel($file);
        
        unset($data[1]);
        unset($data[2]);
       
        
        $url = "http://api.weixin.qq.com/cgi-bin/poi/addpoi?access_token=" . $this->return_token();
       
        foreach ($data as $k => $v)
        {
            $create_data = [
                'business' => [
                    'base_info' => [
                        "sid" => $v['B'],
                        "business_name" => $v['C'],
                        "branch_name" => $v['D'],
                        "province" => $v['E'],
                        "city" => $v['F'],
                        "district" => $v['G'],
                        "address" => $v['H'],
                        "telephone" => $v['L'],
                        "categories" => [
                            "生活服务,信息咨询中心"
                        ],
                        "offset_type" => 1,
                        "longitude" => $v['J'],
                        "latitude" => $v['I'],
                        "special" => $v['O'],
                        "open_time" => $v['M']
                    ]
                ]
            ];
           
            $json_data = json_encode($create_data, JSON_UNESCAPED_UNICODE);
           
            $result = $this->sendPost($url, $json_data);
            
             echo $k . $result; 
        }
    }
}

$obj = new UserAction();

$obj->push_group ();
 
 

 


