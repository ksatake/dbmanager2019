<?php

ini_set('display_errors', 1);

class DataProcess
{

//与えられたデータレコード群を、JSONで表現
public function MakeJSONresponce($stmt)
{
	$echobody2='';

	// foreach文で配列の中身を一行ずつ出力
	$counta2=0;
	$echobody2.='[';
	foreach ($stmt as $row) {
		$counta=0;
		$echobody2.='{';
		
		foreach ($row as $key=>$value) {
			if($counta%2==0){
				//if($counta==8) $echobody2.=$key.':'.$value;
				if($counta==8) $echobody2.='"'.$key.'":"'.$value.'"';
				else $echobody2.='"'.$key.'":"'.$value.'",';					
			}
			$counta++;
		}
		$echobody2.='},';
		// 改行を入れる
		$counta2++;
	}
	
	$echobody2 = rtrim($echobody2,',');
	$echobody2.=']';
	
	if($counta2==0)
	{
		return ("[]"); 
	}
	
	return($echobody2);
}

// レコード追加
public function InsertData($pdo,$tablename,$datatype,$data)
{
	for($i = 0; $i < count($data); $i++){
		$bbb[]=':name'.$i;
	}

	$stmt = $pdo -> prepare('INSERT INTO '.$tablename.' VALUES ('.implode(",", $bbb).')');
	
	for($i = 0; $i < count($data); $i++){
		$this->BindParam($stmt,$datatype[$i],$data[$i],$bbb[$i]);
	}
	
	$result=$stmt->execute();	
}

// レコード更新
public function ChangeData($pdo,$tablename,$columnname,$value,$id)
{	
	$sql = 'update '.$tablename.' set '.$columnname.' ="'.$value.'" where id = '.$id;
	$stmt = $pdo -> prepare($sql);
	$stmt->execute();
}

// レコード削除
public function DeleteData($pdo,$tablename,$id)
{	
	$sql = 'DELETE FROM '.$tablename.' where id = '.$id;
	$stmt = $pdo -> prepare($sql);
	$stmt->execute();
}

// レコード検索
public function DisplayData2($pdo,$tablename,$ary)
{	
	$bbb=[];
	foreach ( $ary as $ary1 ) {
		
		$bbb[]=$ary1["aa"].$ary1["cc"].'"'.$ary1["bb"].'"';
	}
	$ccc = implode(" AND ", $bbb);
	
	if($ccc=="" )
	{
		// SELECT文を変数に格納
		$sql = 'SELECT * FROM '.$tablename;
	}else
	{
		// SELECT文を変数に格納
		$sql = 'SELECT * FROM '.$tablename.' where '.$ccc;
	}
 
	// SQLステートメントを実行し、結果を変数に格納

	$stmt = $pdo -> query($sql);
	
	echo $this->MakeJSONresponce($stmt);
}

public function BindParam($stmt,$type,$name0,$_name0)
{
	if($type=="str") $stmt->bindParam($_name0, $name0, PDO::PARAM_STR);
	if($type=="int") $stmt->bindParam($_name0, $name0, PDO::PARAM_INT);
}


}
?>