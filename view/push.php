<html>
<head>
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type" />
</head>
<body>
<form action="" method="post" target="_blank">

   <div>选择素材 : </div>
<?php  foreach($obj->media_data as $k => $v):?>
    <div  style="display:inline;float: left;" >
        <input type="radio" name="media_id" value="<?php echo $v['media_id']?>"/ >

        <?php  foreach($v['title'] as $_k => $_v):?>
            <ul >
                <li>
                    <?php echo $_v;?>
                </li>
            </ul>

        <?php endforeach;?>
    </div>
<?php endforeach;?>

<div style="margin-top:180px; width: 100%;height: 10px;color:red;"></div>
    <hr/>

    <div>选择分组 :
    <?php  foreach($obj->groups_data as $k => $v):?>

            <input type="radio" name="group_id" value="<?php echo $v['id']?>"/ >

             <?php echo  $v['name']  . "---" . $v['id'] . "---" . $v['count'];?>
             
             <br />

    <?php endforeach;?>
    </div>

    <div style="width: 100%;height: 30px;"></div>
    <hr/>
    <input type="submit" name="action" value="确认发送" style="height: 20px;width: 60px;"/>

</form>

</body>
</html>
