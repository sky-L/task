<?php
namespace action;

use Illuminate\Database\Capsule\Manager as Capsule;
use Nette\Mail\Message;
use Predis\Client;
use PHPExcel_IOFactory;
require_once __DIR__ . "/../bootstrap.php";
require_once __DIR__ . "/../config/function.php";

class BaseAction
{

    public $token = "fAy_uKoc6zr9ppjgGnRpsCdIGDkt6O5yI3JJi_GxCDedunI3_OQ31tFVygzdorlwpmZ9md6Ucizvl_-zQsg6WQCy1sJ-HoJFjHEYUvaTCLc";

    /**
     * 连接数据库
     * 
     * @param array $database
     *            默认连接 default
     */
    public function withCapsule($database = null)
    {
        $database_config = require_once BASE_PATH . "/config/database.php";
        
        if (! $database)
        {
            $database = $database_config['default'];
        }
        else 
        {
            $database = $database_config[$database];
        }
        
        $capsule = new Capsule();
        
        $capsule->addConnection($database);
        
        $capsule->bootEloquent();
    }

    /**
     * 发起远程请求
     * @param bool $post            
     * @return mixed string
     */
    public function sendPost($url, $data, $post = true , $header = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        if ($post)
        {
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        
        if (! $post)
        {
            curl_setopt($ch, CURLOPT_POST, 0);
        }
        
        if (count($header) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
      
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        
        $returnValue = curl_exec($ch);
        $returnValue = ($returnValue === false) ? curl_error($ch) : $returnValue;
        curl_close($ch);
        return $returnValue;
    }
    /*
     * 读取excel文件 return array
     */
    public function read_excel($file)
    {
        return PHPExcel_IOFactory::load($file)->getActiveSheet()->toArray(null, true, true, true);
    }

    public function return_token()
    {
        return file_get_contents("http://10.99.121.33/weixin/index.php/Api/getToken");
    }

    /**
     * 连接redis
     */
    public function withRedis()
    {
        $this->redis = new Client(require BASE_PATH . '/config/redis.php');
    }

    /**
     * 发送邮件
     */
    public function mail()
    {
        $mail = new Message();
        
        $mail->addTo("690035384@qq.com");
        
        $mail->setSubject("hello world");
        
        $mailer = new \Nette\Mail\SmtpMailer(require BASE_PATH . "/config/mail.php");
        
        $mailer->send($mail);
    }

    public function down_from_url($url, $filename)
    {
        return file_put_contents($filename, file_get_contents($url));
    }
}
