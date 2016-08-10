<?php
namespace Action;

require "BaseAction.php";

use Illuminate\Database\Eloquent\Model as model;
use GuzzleHttp\json_encode;
use GuzzleHttp\json_decode;

class Openid extends model
{

    public $timestamps = false;

    protected $table = '0617';
}

class PushOpenidAction extends BaseAction
{
    public $media_id = "mRZrFt_pQkTUkOyVoQxM96cZrmpjnoZENo2wyDqsT_AxuXsxFrynVOhdtg35CQu3";
    
    public function __construct()
    {
        $this->withCapsule();
    }

    public function index()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=" . $this->return_token();
        
        $result = Openid::get()->toArray();
        
        foreach ($result as $k => $v)
        {
            $openid[] = $v['openid'];
        }
        
        $data = ['touser' => $openid, 'mpnews' => ['media_id' => $this->media_id], 'msgtype' => 'mpnews'];
        
        $re = $this->sendPost($url, json_encode($data));
        
        var_dump($re);
        
      
    }

    public function preview()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=" . $this->return_token();
        
        $data = ['touser' => "", 'mpnews' => ['media_id' => $this->media_id], 'msgtype' => 'mpnews'];
        
        $openids = ['oLHCTjk9UjXprhuOTa9n0AV4QLPE'];
        
        foreach ($openids as $k => $v)
        {
            $data['touser'] = $v;
            
            $re = $this->sendPost($url, json_encode($data));
            
            var_dump($re);
        }
    }
}



$obj = new PushOpenidAction();

$obj->index();

//


 
 

 


