<?php
namespace Action;

require "BaseAction.php";

use Illuminate\Database\Eloquent\Model as model;



class Article extends model
{
    public $timestamps = false;

    protected $table = 'fuck';
}

class UserAction extends BaseAction
{


    public function __construct()
    {
        $this->withCapsule();
    }

    public function init()
    {
        $re = Article::all()->toArray();

        $this->withRedis();

        $this->redis->set('k','v');



        //$this->mail();

        var_dump($this->redis->get('k'));
    }



}

$obj = new UserAction;

$obj->init();
