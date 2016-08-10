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

class Test extends model
{

    public $timestamps = false;

    protected $table = 'ls_skill_mapping';
}

class TestAction extends BaseAction
{
   
    public function __construct()
    {
        $this->withCapsule();
    }

    public function index()
    {
       $list = Test::where(['skill' => "Q101"])->get()->toArray();
       
       foreach ($list as $k => $v)
       {
           $data = ['skill' => 'B102','uid' => $v['uid'] , 'create_time' => time() , 'skill_level' => 1];
           
           Test::insert($data);
           
           echo $k . "\n";
       }
       
    }

  
}

$obj = new TestAction();

$obj->index();
 
 

 


