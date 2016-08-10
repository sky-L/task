<?php
namespace Action;

require "BaseAction.php";

use Endroid\QrCode\QrCode as BaseQrcode;

use Illuminate\Database\Eloquent\Model as model;




class qrcode extends BaseAction
{

    public function init()
    {

        header('Content-Type: image/png');
        $qrCode = new BaseQrcode();
        $qrCode->setText("http://www.baidu.com") ->render();
        
        
        
//             ->setSize(300)
//             ->setPadding(10)
//             ->setErrorCorrection('high')
//             ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
//             ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
//             ->setLabel('My label')
//             ->setLabelFontSize(16)
//             ->render()
       
       // var_dump($qrCode);
    }

    public function test()
    {
        $i = "ask";
        while(true){
            sleep(1);
            echo $i . "\n";

        }
    }

}

$obj = new qrcode();

$obj->init();


