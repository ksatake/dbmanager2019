<?php
ini_set('display_errors', 1);

class MainClass
{

//DB接続 	
public function DBconnect()
{	
	$host = 'us-cdbr-iron-east-02.cleardb.net'; //127.0.0.1:[ポート番号]でも良い
	$dbname = 'heroku_27aab7cbbd397b7';
	$dbuser = 'b289779f7fe3fd';
	$dbpassword = 'e3250008';
	
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8","$dbuser","$dbpassword",array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
	));

	return( $pdo );
}
	
	
public function MainFunc($tablename,$requesttype,$id){

//DB操作に用いるクラス
require_once("test2.php");
$DP = new DataProcess();

//DB接続 
$pdo = $this->DBconnect();

// DBレコード検索
if($requesttype=="")
{	
	//検索条件を与える $ary_data の設定
	
	if ($id!=null) 
	{
		// 単一レコード指定の場合
		$aaaaa[]='{"aa":"id","bb":"'.$id.'","cc":"="}';

		$json_data = '['.implode(",", $aaaaa).']';

		$ary_data =  json_decode($json_data,true);

	}else if(isset($_GET['comp']))
	{
		// テーブルを検索する場合
		for($i = 0; $i < count($_GET["colname"]); $i++){
			$aaaaa[]='{"aa":"'.$_GET["colname"][$i].'","bb":"'.$_GET["val"][$i].'","cc":"'.$_GET["comp"][$i].'"}';
		}

		$json_data = '['.implode(",", $aaaaa).']';
		$ary_data =  json_decode($json_data,true);

	}else{
		//検索条件がない場合
		$ary_data =  json_decode('[]',true);
	}
	
	// 該当レコードを取得し、APIとして応答
	$DP->DisplayData2($pdo,$tablename,$ary_data);
}



    





// DBレコード登録
if($requesttype=="insertrecord")
{
	//画像データのエンコード
	$raw_data = base64_encode(file_get_contents($_FILES['upfile']['tmp_name']));
	
	//登録レコードの各情報をまとめる (型・値)
	$aaa=array("int","str","str","int","str");
	$data = array(null,$_POST['title'],$_POST['detail'],$_POST['price'],$raw_data);
	
	//登録
	$DP->InsertData($pdo,$tablename,$aaa,$data);
}

// DBレコード更新
if($requesttype=="editrecord")
{
	//パラメータ等取得
	// POSTのパラメータ-> $put_param  ::  アップロードファイル-> $_FILES['upfile']['tmp_name']
	$put_param = $this->parse_put();
	
	//各種更新
	if (isset($put_param['title'])) $DP->ChangeData($pdo,$tablename,'title',$put_param['title'],$id);
	if (isset($put_param['detail'])) $DP->ChangeData($pdo,$tablename,'detail',$put_param['detail'],$id);
	if (isset($put_param['price'])) $DP->ChangeData($pdo,$tablename,'price',$put_param['price'],$id);
	
	if(isset($_FILES['upfile']['tmp_name'])){
		$raw_data = base64_encode(file_get_contents($_FILES['upfile']['tmp_name']));
		$DP->ChangeData($pdo,$tablename,'picture',$raw_data,$id);
	}
	
}

//DBレコード削除
if($requesttype=="delete")
{
	$DP->DeleteData($pdo,$tablename,$id);
}


//public function MainFunc($tablename,$requesttype,$id) の処理終了
}



//PUTリクエストの際に、パラメータと画像のバイナリを取得
function parse_put()
{
    /* PUT data comes in on the stdin stream */
    $putdata = fopen("php://input", "r");

    /* Open a file for writing */
    // $fp = fopen("myputfile.ext", "w");

    $raw_data = '';

    /* Read the data 1 KB at a time
    and write to the file */
    while ($chunk = fread($putdata, 1024))
    $raw_data .= $chunk;

    /* Close the streams */
    fclose($putdata);

    // Fetch content and determine boundary
    $boundary = substr($raw_data, 0, strpos($raw_data, "\r\n"));

    if(empty($boundary)){
        parse_str($raw_data,$data);
        return;
    }

    // Fetch each part
    $parts = array_slice(explode($boundary, $raw_data), 1);
    $data = array();

    foreach ($parts as $part) {
        // If this is the last part, break
        if ($part == "--\r\n") break;

        // Separate content from headers
        $part = ltrim($part, "\r\n");
        list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);

        // Parse the headers list
        $raw_headers = explode("\r\n", $raw_headers);
        $headers = array();
        foreach ($raw_headers as $header) {
            list($name, $value) = explode(':', $header);
            $headers[strtolower($name)] = ltrim($value, ' ');
        }

        // Parse the Content-Disposition to get the field name, etc.
        if (isset($headers['content-disposition'])) {
            $filename = null;
            $tmp_name = null;
            preg_match(
                '/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/',
                $headers['content-disposition'],
                $matches
            );
            list(, $type, $name) = $matches;

            //Parse File
            if(isset($matches[4]) )
            {
                //if labeled the same as previous, skip
                if(isset($_FILES[$matches[2]]))
                {
                    continue;
                }

                //get filename
                $filename = $matches[4];

                //get tmp name
                $filename_parts = pathinfo($filename);
                $tmp_name = tempnam(sys_get_temp_dir(), $filename_parts['filename']);

                file_put_contents($tmp_name, $body);

                //populate $_FILES with information, size may be off in multibyte situation

                $_FILES[$matches[2]] = array(
                    'error'=>0,
                    'name'=>$filename,
                    'tmp_name'=>$tmp_name,
                    'size'=>strlen($body),
                    'type'=>$value
                );

                //place in temporary directory
                file_put_contents($tmp_name, $body);
            }
            //Parse Field
            else
            {
                $data[$name] = substr($body, 0, strlen($body) - 2);
            }
        }
    }
    return $data;
}



}

?>
