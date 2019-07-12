<?php
ini_set('display_errors', 1);

require_once("test1.php");
$MC = new MainClass();









//ルーティング (通信メソッドによる分類)

preg_match('|' . dirname($_SERVER['SCRIPT_NAME']) . '/([\w%/]*)|', $_SERVER['REQUEST_URI'], $matches);
$paths = explode('/', $matches[1]);
$tablename = isset($paths[0]) ? htmlspecialchars($paths[0]) : null;
$id = isset($paths[1]) ? htmlspecialchars($paths[1]) : null;
switch (strtolower($_SERVER['REQUEST_METHOD'])) {
case 'get':
  GetItem($tablename,$id,$MC);
  break;
case 'post':
  PostItem($tablename,$MC);
  break;
case 'put':
  PutItem($tablename,$id,$MC);
  break;
case 'delete':
  DeleteItem($tablename,$id,$MC);
  break;
}



function GetItem($tablename,$id,$MC)
{
    $MC->MainFunc($tablename , "",$id);     
}



function PostItem($tablename,$MC)
{
    $MC->MainFunc($tablename  , "insertrecord",null);
}

function PutItem($tablename,$id,$MC)
{
    $MC->MainFunc($tablename  , "editrecord",$id);
}

function DeleteItem($tablename,$id,$MC)
{
    $MC->MainFunc($tablename  , "delete",$id);
}





//error_log(print_r("end------------------\n\n\n", TRUE), 3, 'log.txt');

?>