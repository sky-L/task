<?php
namespace Action;

require "BaseAction.php";

class PushAction extends BaseAction
{

    public  $groups_data = "";

    public  $media_data = "";

    public $media_id = "";

    public $group_id  ="";

    public function __construct()
    {
        $this->token = file_get_contents("http://10.99.121.33/weixin/index.php/Api/getToken");
        //$this->token = file_get_contents("http://weixin.lenovo.com.cn/weixin/index.php/Api/getToken");

        $this->get_media();
        
        $this->get_group();
        
    }

    public function get_media()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=" . $this->token;

        $mdata = ['type' => 'news', 'offset' => 0, 'count' => 2];

        $media_ids = $this->sendPost($url, json_encode($mdata));
        
        $return_data = "";

        foreach(json_decode($media_ids,true)['item'] as $k=>$v)
        {
            $return_data[$k]['media_id'] = $v['media_id'];

            $return_data[$k]['title'] = $this->return_title($v['content']['news_item']);
        }
 
        $this->media_data = $return_data;
    }

    public function return_title($content)
    {
        $return_title = "";

        foreach($content as $k => $v)
        {
            $return_title[] = $v['title'];
        }

        return $return_title;
    }

    /****
     * 分组推送
     */
    public function push_by_group()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=" . $this->token;

        $media_id = $this->media_id;

        $group_id = $this->group_id;

        $data = ['filter' => ['is_to_all' => false, 'group_id' => $group_id], 'mpnews' => ['media_id' => $media_id], 'msgtype' => 'mpnews'];

        $re = $this->sendPost($url, json_encode($data));

        $time = date('Y-m-d H:i:s');

        var_dump("分组id" . $group_id . "<hr />" . $time.$re);


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

        $data = ['touser' => $openid, 'mpnews' => ['media_id' => $media_id], 'msgtype' => 'mpnews'];

        $re = $this->sendPost($url, json_encode($data));

        var_dump($re);

    }

    /***
     * 把用户移动到别的分组
     */
    public function push_group()
    {
        for ($i = 301; $i <= 400; $i++) {

            $start = ($i - 1) * 50;

            $openids = Openid::skip($start)->take(50)->get()->toArray();

            $openid_arr = [];

            foreach ($openids as $k => $v) {

                $openid_arr[] = $v['openid'];

            }

           // $openid_arr = ['oLHCTjk9UjXprhuOTa9n0AV4QLPE'];

            $url = "https://api.weixin.qq.com/cgi-bin/groups/members/batchupdate?access_token=" . $this->token;

            $data = [];

            $data = ['openid_list' => $openid_arr, 'to_groupid' => $this->to_group_id];

            $re = $this->sendPost($url, json_encode($data));


            echo $i . '<hr>';
            var_dump($re);
        }
    }

    public function get_group()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/groups/get?access_token=" . $this->token;

        $re = $this->sendPost($url, '');

        $re = json_decode($re, true);

        $this->groups_data = $re['groups'];
    }

    public function create_group()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/groups/create?access_token=" . $this->token;

        $data = ['group' => ['name' => '联想内部测试分组']];

        $re = $this->sendPost($url, json_encode($data, JSON_UNESCAPED_UNICODE));

        var_dump($re);
    }


    public function push_openids()
    {

        $url = "https://api.weixin.qq.com/cgi-bin/message/send?access_token=" . $this->token;

        $news = ['articles' =>

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

        $openids = Openid::skip(1500)->take(8500)->get()->toArray();

        foreach ($openids as $k => $v) {

            $data = ['touser' => $v['openid'], 'msgtype' => 'news', 'news' => $news];

            $data = json_encode($data, JSON_UNESCAPED_UNICODE);

            $result = $this->sendPost($url, $data);

            var_dump($v['id'] . $result);

        }
    }
}

$obj = new PushAction;
 
if(!empty($_POST['action']))
{
    if(!$_POST['media_id'])
    {
        exit("没选啊！");
    }

    $obj->media_id = $_POST['media_id'];

    $obj->group_id = $_POST['group_id'];

//   $obj->push_by_group();
    
    
}
else
{
    require  "../view/push.php";
}



