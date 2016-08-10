<?php
namespace Action;
use Illuminate\Database\Eloquent\Model as model;
/**
 * post模拟登录
 * @param $url
 * @param $cookie  存放登录cookie的文件路径
 * @param $post
 */
function login_post($url, $cookie, $post)
{
    $curl = curl_init();//初始化curl模块
    curl_setopt($curl, CURLOPT_URL, $url);//登录提交的地址
    curl_setopt($curl, CURLOPT_HEADER, 0);//是否显示头信息
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 0);//是否自动显示返回的信息
    curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie); //设置Cookie信息保存在指定的文件中
    curl_setopt($curl, CURLOPT_POST, 1);//post方式提交
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));//要提交的信息
    curl_exec($curl);//执行cURL
    curl_close($curl);//关闭cURL资源，并且释放系统资源
}

/**
 * 获取登录后的数据
 * @param $url
 * @param $cookie 模拟存放登录cookie的文件路径
 * @return mixed
 */
function get_content($url, $cookie)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie); //读取cookie
    $rs = curl_exec($ch); //执行cURL抓取页面内容
    curl_close($ch);
    return $rs;
}

function D($table)
{
    $model = new model();
    
    $model->table = $table;
    
    return $model;
}



function return_mobile($str = "一手货源，保质保量，不是商人的地道核农，品种齐全，批发青皮、成品对、vx13932252400")
{
    if(preg_match('/\d{11}/', $str,$mobile))
    {
        return $mobile[0];
    }

    return false;
}