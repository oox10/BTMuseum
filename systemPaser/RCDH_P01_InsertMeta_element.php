<?php
    
	/*
	將原始資料轉存詮釋資料 20170803
	
	SOURCE : source_digiarchive  source_element
	
	*/
	ini_set("memory_limit", "2048M");
    
    require_once(dirname(dirname(__FILE__)).'/conf/server_config.php');
    require_once(dirname(dirname(__FILE__)).'/mvc/core/DBModule.php');   
    
	
	//-- load meta assist from db 
    $db = new DBModule;
    $db->db_connect('PDO'); 
	
	/*
	// zong map 
	$zong_maps = [];
	$db_zong = $db->DBLink->prepare("SELECT zid,zname,zseries,_zmap FROM meta_zong WHERE zclass='PROVINCIAL';");
	$db_zong->execute();
	while($z = $db_zong->fetch(PDO::FETCH_ASSOC)){
	  $zong_maps[$z['_zmap']]	= [
	    'zcode'=> $z['zid'],
		'zname'=> $z['zname'],
		'series'=>$z['zseries']
	  ];
	}
    
	// series map 
	$class_maps = [];
	$db_class = $db->DBLink->prepare("SELECT class_code,class_name,class_level FROM meta_class WHERE class_type='SERIES' AND _keep=1;");
	$db_class->execute();
	while($c = $db_class->fetch(PDO::FETCH_ASSOC)){
	  $class_maps[$c['class_code']]	= [
	    'class_name'=> $c['class_name'],
		'class_level'=> $c['class_level'],
	  ];
	}
	*/
	
	$data_class = 'relic';
	
	
	$source_table     = 'source_digielement';
	$target_condition = "1";
	$meta_exist = array();
	
	$db_meta  = $db->DBLink->prepare("SELECT * FROM metadata WHERE class=:class AND data_type=:data_type AND identifier=:store_no;");
	$db_insert = $db->DBLink->prepare("INSERT INTO metadata VALUES (NULL,:class,:zong,:data_type,:collection,:identifier,:applyindex,:source_json,:search_json,:dobj_json,:refer_json,:page_count,NULL,'RCDHPaser','NDAPv2','".date('Y-m-d H:i:s')."',:lockmode,:auditint,:checked,:digited,:open,:view,0,0,1);");
	
	//- get source_volume map
	$volume_data = [];
	$db_volume = $db->DBLink->prepare("SELECT * FROM source_digiarchive WHERE _metakeep=1;");
	if(!$db_volume->execute()){
	  throw new Exception("DB Volume Get Fail \n");   
	}
	while($tmp = $db_volume->fetch(PDO::FETCH_ASSOC)){
	  $volume_data[$tmp['store_no']] = $tmp;  
	}
	 
	try{ 
      
	  $db_select = $db->DBLink->prepare("SELECT * FROM ".$source_table." WHERE ".$target_condition." ORDER BY  seno ASC;");
       
	  if( !$db_select->execute() ){
		throw new Exception('查無目錄資料');    
	  }
	  
	  
	  while( $source = $db_select->fetch(PDO::FETCH_ASSOC) ){
		
		//$collect = array_map(function($field){ return htmlentities($field,ENT_QUOTES, "UTF-8");   },$collect);  // 轉換奇怪符號為HTML編碼
		
		echo "\n".$source['store_no']." : ";
		
		// 檢查是否已輸入 META 
		$db_meta->bindValue(':data_type','element');
		$db_meta->bindValue(':class',$data_class);
		$db_meta->bindValue(':store_no',$source['store_no']);
		
		
		$source_array = ['collection'=>$volume_data[$source['collection_id']],'element'=>$source]; 
		
		if($db_meta->execute() && $meta = $db_meta->fetch(PDO::FETCH_ASSOC)){
			
			$source_meta  = json_decode($meta['source_json'],true);
			$system_id    = $meta['system_id'];
			
			if(md5(json_encode($source)) != md5(json_encode($source_meta['element']))){
			  //更新meta source 
              $db_upd = $db->DBLink->prepare("UPDATE metadata SET source_json=:source_json,_index=0,_sync=0 WHERE system_id=:sid;");
			  $db_upd->bindValue(':source_json',json_encode($source_array,true));
			  $db_upd->bindValue(':sid',$system_id);
			  $db_upd->execute();	
			}
			
		}else{
			
			 
			// 初步整編
			$db_insert->bindValue(':class',$volume_data[$source['collection_id']]['class']);
			$db_insert->bindValue(':zong', $volume_data[$source['collection_id']]['zong']);
			$db_insert->bindValue(':data_type','element');
			$db_insert->bindValue(':collection'	,$source['collection_id']);
			$db_insert->bindValue(':identifier'	,$source['store_no']);
			$db_insert->bindValue(':applyindex'	,$source['store_no']);
			$db_insert->bindValue(':source_json',json_encode($source_array,JSON_UNESCAPED_UNICODE));
			$db_insert->bindValue(':search_json','[]');
			$db_insert->bindValue(':dobj_json'	,json_encode(["dopath"=>"001/","count"=>0]));
			$db_insert->bindValue(':refer_json'	,json_encode(array()));
			$db_insert->bindValue(':page_count'	,0);
			$db_insert->bindValue(':lockmode'	,'');
			$db_insert->bindValue(':auditint'	,0);
			$db_insert->bindValue(':checked'	,0);
			$db_insert->bindValue(':digited'	,0);
			$db_insert->bindValue(':open'		,0);
			$db_insert->bindValue(':view'		,'');
			
			if(!$db_insert->execute()){
			  throw new Exception('新增資料失敗'); 	
			}
			$system_id = $db->DBLink->lastInsertId();
		}
		echo $system_id." UPDATE. ";
	    
	  }
	  
	  
    } catch (Exception $e) {
      echo $e->getMessage();
    }	
	
?>