<?php
namespace action;

use Illuminate\Database\Capsule\Manager as Capsule;

use Nette\Mail\Message;

use Predis\Client;

require_once __DIR__ . "/../bootstrap.php";


class BaseAction
{

    /**
     * 连接数据库
     * @param array $database 默认连接 default
     */
    public function withCapsule(array $database = null)
    {
        $database_config = require_once BASE_PATH . "/config/database.php";

        if (!$database) {

            $database = $database_config['default'];
        }

        $capsule = new Capsule;

        $capsule->addConnection($database);

        $capsule->bootEloquent();
    }


    /**
     * 发起远程请求
     * @param $url
     * @param $data
     * @param bool $post
     * @return mixed|string
     */
    public function sendPost($url, $data, $post = true)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        if(!$post)
        {
            curl_setopt($ch, CURLOPT_POST, 0);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        if (count($header) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        $returnValue = curl_exec($ch);
        $returnValue = ($returnValue === false) ? curl_error($ch) : $returnValue;
        curl_close($ch);
        return $returnValue;
    }


    /**
     *连接redis
     */
    public function withRedis()
    {
        $this->redis = new Client(require BASE_PATH . '/config/redis.php');
    }
    /**
     *发送邮件
     */
    protected function mail()
    {
        $mail = new Message;

        $mail->addTo("690035384@qq.com");

        $mail->setSubject("hello world");

        $mailer = new \Nette\Mail\SmtpMailer(require BASE_PATH ."/config/mail.php");

        $mailer->send($mail);
    }
}
