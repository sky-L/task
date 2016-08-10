<?php
namespace Action;

require "BaseAction.php";

use Illuminate\Database\Eloquent\Model as model;

class ShopAction extends BaseAction
{
    
    public $down_qcode_url = "https://api.weixin.qq.com/bizwifi/qrcode/get?access_token=";

    public function __construct()
    {
        $this->withCapsule();
    }
    /*
     * 先查询再删掉
     */
    public function manage_shop_list()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/poi/getpoilist?access_token=" . $this->return_token();
        
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
        $file = "/Library/WebServer/Documents/github/task/action/shop0621.xlsx";
        $data = $this->read_excel($file);
   
       // unset($data[1]);
       
        
        $url = "http://api.weixin.qq.com/cgi-bin/poi/addpoi?access_token=" . $this->return_token();
        
        foreach ($data as $k => $v)
        {
            $create_data = [
                'business' => [
                    'base_info' => [
                        "sid" => 1,
                        "business_name" => $v['C'],
                        "branch_name" => $v['D'],
                        "province" => $v['E'],
                        "city" => $v['F'],
                        "district" => $v['G'],
                        "address" => $v['H'],
                        "telephone" => $v['K'],
                        "categories" => [
                            "生活服务,生活服务场所"
                        ],
                        "offset_type" => 1,
                        "longitude" => $v['M'],
                        "latitude" => $v['L'],
                        "special" => '免费wifi',
                       // "open_time" => $v['M']
                    ]
                ]
            ];
            
            $json_data = json_encode($create_data, JSON_UNESCAPED_UNICODE);
            
           echo $json_data;
           
            
            $result = $this->sendPost($url, $json_data);
          
            
            echo $k . $result . '<hr>';
            exit(__FILE__ . __LINE__);
            
        }
    }

    /**
     * 获取wifi门店列表
     */
    public function wifi_shop_list()
    {
        $wifi_shop_url = "https://api.weixin.qq.com/bizwifi/shop/list?access_token=" . $this->return_token();
        
        for ($i = 58; $i < 63; $i ++)
        {
            $data = [
                'pageindex' => $i,
                'pagesize' => 20
            ];
            
            $result = $this->sendPost($wifi_shop_url, json_encode($data));
          
            $result = json_decode($result, true);
            
            
  
            foreach ($result['data']['records'] as $k => $v)
            {
                 //  $this->add_device($v['shop_id']);
                
                 $this->down_qcord($v['shop_id'], $v['shop_name']);
                 
                 echo $i . '--' . $k . '_down' . "\n";
            }
        }
    }

    /**
     * 下载门店二维码
     *
     * @param unknown $shop_id            
     */
    public function down_qcord($shop_id, $filename = "")
    {
        $down_url = "https://api.weixin.qq.com/bizwifi/qrcode/get?access_token=" . $this->return_token();
        
        $data = [
            'shop_id' => $shop_id,
            'img_id' => 0
        ];
        
        $result = $this->sendPost($down_url, json_encode($data));
       
        $result = json_decode($result, true);
        
        $qcode_url = $result['data']['qrcode_url'];
        
       
        
        return $this->down_from_url($qcode_url, '/Users/skylee/shop/shoplist/' . $filename . '.png');
    }

    public function add_waterpic($src)
    {
        $file_path =  $src['file_path'] . 'big/*';
        
        $files = glob($file_path);
        
        foreach ($files as $k => $v)
        {
            $file_name = substr($v, 29);
    
            $back_path = $src['file_path'];
            
            $backgroud_img = $back_path . "1.jpg";
            
            $backgroud_bak = $back_path . $file_name;
            
            file_put_contents($backgroud_bak, file_get_contents($backgroud_img));
            
            $this->mark_pic($backgroud_bak, $v, $src['x'], $src['y']);
            
            echo $k . "down\n";
            
        }
        
        // $file_names = str_ireplace("/Users/skylee/shoplist/", '', $files);
    }

    /**
     *
     * @param unknown $background            
     * @param unknown $waterpic            
     * @param unknown $x
     *            开始的横坐标
     * @param unknown $y
     *            开始的纵坐标
     */
    public function mark_pic($background, $waterpic, $x, $y)
    {
        $back = imagecreatefromjpeg($background);
        
        // $water=imagecreatefromjpeg($waterpic);
        $water = imagecreatefrompng($waterpic);
        
        $w_w = imagesx($water);
        $w_h = imagesy($water);
        imagecopy($back, $water, $x, $y, 0, 0, $w_w, $w_h);
        
        imagejpeg($back, $background);
        
        imagedestroy($back);
        imagedestroy($water);
    }

    public function reset_img($src)
    {
        $file_path = "/Users/skylee/shop/shoplist/*"; //二维码原文件
        
        $files = glob($file_path);
        
        foreach ($files as $k => $v)
        {
            $filename = $v;
            
            list ($width, $height) = getimagesize($filename);
            
            $new_width = $src['size'];
            
            $new_height = $src['size'];
            
            // Resample
            $image_p = imagecreatetruecolor($new_width, $new_height);
            
            $image = imagecreatefrompng($filename);
            
            imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            
            $big_name = $src['file_path'] ."big/". substr($v, 28);
            
            // imagejpeg($image_p, $big_name, 100);
            
            imagepng($image_p, $big_name);
            
            echo $k . "down\n";
        }
    }

    public function add_device($shop_id = "")
    {
        $url = "https://api.weixin.qq.com/bizwifi/device/add?access_token=" . $this->return_token();
        
        $data = ['shop_id' => $shop_id , 'ssid' => "lenovo" , "password" => "WXlenovo123"];
        
        $result = $this->sendPost($url, json_encode($data));
    }
    
    
    public function get_more()
    {
        $file = "/Library/WebServer/Documents/github/task/action/0621.xlsx";
        
        $data = $this->read_excel($file);
        
        foreach ($data as $k => $v)
        {
            $file_name = $v['D'] . "(".$v['E'].")";
             
            $this->down_qcord($v['B'], $file_name);
            
            echo $k . '--' . $k . '_down' . "\n";
        }
        
       
    }
}

$obj = new ShopAction();
 $obj->create_shop();
 exit(__FILE__ . __LINE__);
// $obj->create_shop();
// exit(__FILE__ . __LINE__);






$backgroud = [
    1 => [
        'x' => 379,
        'y' => 420,
        'file_path' => "/Users/skylee/shop/back1/",
        'size' => 487
    ],
    
    2 => [
        'x' => 373,
        'y' => 337,
        'file_path' => "/Users/skylee/shop/back2/",
        'size' => 494
    ],
    3 => [
        'x' => 1486,
        'y' => 352,
        'file_path' => "/Users/skylee/shop/back3/",
        'size' => 529
    ],
    4 => [
        'x' => 2016,
        'y' => 536,
        'file_path' => "/Users/skylee/shop/back4/",
        'size' => 855
    ]
];

 $obj->wifi_shop_list(); //下载门店二维码
 exit(__FILE__ . __LINE__);
// header('Content-Type: image/jpeg');
$data = $backgroud[4];
$obj->reset_img($data);
$obj->add_waterpic($data); 

 

 


