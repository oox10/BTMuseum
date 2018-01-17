<?php
    
	/*
	將原始資料轉存詮釋資料 20171201
	
	*/
	ini_set("memory_limit", "2048M");
    
    require_once(dirname(dirname(__FILE__)).'/conf/server_config.php');
    require_once(dirname(dirname(__FILE__)).'/mvc/core/DBModule.php');   
    
	
	//-- load meta assist from db 
    $db = new DBModule;
    $db->db_connect('PDO'); 
	
	$source_table     = 'metadata';
	$target_condition = "1";
	//$target_condition = "class='PUBLICATION'";
	
	$meta_exist = array();
	
	ob_start();
	
	$db_update = $db->DBLink->prepare("UPDATE metadata SET search_json=:search_json,_lockmode=:lockmode,_auditint=:auditint,_open=:open,_view=:view,_index=0,_sync=1 WHERE system_id=:system_id;");
	
	try{ 
      
	  
	  $db_select = $db->DBLink->prepare("SELECT count(*) FROM ".$source_table." WHERE ".$target_condition." ORDER BY system_id ASC;");
       
	  if( !$db_select->execute() ){
		throw new Exception('查無目錄資料');    
	  }
	  
	  $total_count = $db_select->fetchColumn();
	  $paser_coimt = 0;
	  $limit = 0;
	  $frame = 10000;
	  
	  echo "[PASER] metadata search paser start : ".$total_count;
	  
	  while($limit < $total_count ){
	  
	    $db_select = $db->DBLink->prepare("SELECT * FROM ".$source_table." WHERE ".$target_condition." ORDER BY system_id ASC LIMIT ".$limit.",".$frame.";");
       
	    if( !$db_select->execute() ){
		  throw new Exception('查無目錄資料');    
	    }  
	  
	  
		  while( $meta = $db_select->fetch(PDO::FETCH_ASSOC) ){
			
			//$collect = array_map(function($field){ return htmlentities($field,ENT_QUOTES, "UTF-8");   },$collect);  // 轉換奇怪符號為HTML編碼
			$paser_coimt++;
			echo "\n".str_pad($paser_coimt,6,'0',STR_PAD_LEFT).'. '.$meta['system_id']." : ";
			
			//if(isset($meta_exist[$source['StoreNo']])){
			//  echo "skip.";
			//  continue;		  
			//}
			
			// 依據不同類型檔案設定搜尋項目
			
			$search_field= [   //測試用 
			  
			  'class'			=>'',   // 資料類別
			  
			  'zong'			=>'',   // 全宗序號
			  'fonds'			=>'',   // 全宗名稱
			  'data_type'		=>'',   // 資料類型  collection / element
			  
			  'collection' 		=>'',   // 
			  'identifier' 		=>'',   //
			  'applyindex' 		=>'',   //
			  
			  'date_string'		=>'',   // 日期描述
			  'date_start'		=>'',   // 日期起 for search
			  'date_end'		=>'',   // 日期迄 for search
			  
			  'title'			=>'',   // 名稱 
			  
			  'categories'	    =>'',   // 類型
			  'ethnic'	    	=>'',   // 族群
			  
			  'acquire_type'	=>'',   // 取得方式 
			  'acquire_info'    =>'',   // 來源說明
			  'status_code'		=>'',   // 狀況級數
			  'status_descrip'  =>'',   // 狀況描述
			  
			  'store_date'		=>'',   // 入庫時間
			  'store_location'  =>'',
			  'store_number'    =>'',
			  'store_boxid'     =>'',
			  
			  'store_information'  =>'',
			  
			  
			  'count_dofiles'   =>'',
			  'count_element'   =>'',
			  
			  
			  'storeyear'		=>[],   // 入庫年代
			  'savedyear'		=>[],   // 入藏年代
			  
			  'list_store_type'	=>[],   
			  'list_ethnic'		=>[],    
			  'list_status_code'=>[],   
			  'list_store_location'=>[],
			  
			  //提供索引檢索
			  '_flag_secret'	=>'一般',    // 密等設定     // 一般 密 機密 極機密 解密
			  '_flag_privacy'	=>0,    	 // 隱私資料     1/0
			  '_flag_open'		=>1,  	 	 // 公開檢索     1/0 
			  '_flag_mask'		=>0,    	 // 含有遮蔽影像 1/0
			  '_flag_update'	=>0,    	 // 最後更新時間 int
			  '_flag_view'		=>'公開',    // 開放方式     // 開放、限閱、會內、不開放、實體
			];
			
			
			
			$search_conf = [];
			
			// 提供系統處理 
			$lockmode   = '';  // 密等狀態
			$auditint   = 0;   // 隱私權註冊
			$open       = 0;
			$view		= '公開';  
			
			$source = json_decode($meta['source_json'],true);
			
			switch($meta['class']){
			  
			  case 'relic':
				
				$search_conf['class']   = $meta['class'];
				$search_conf['zong']    = $meta['zong'];
				$search_conf['fonds']   = $source['collection']['fonds'];
				
				$search_conf['data_type']   = $meta['data_type'];
				$search_conf['collection']  = $meta['collection'];
				$search_conf['identifier']  = '';
				$search_conf['applyindex']  = $meta['applyindex'];
				
				$search_conf['store_id']    = $source['collection']['store_id'];
				
				if($meta['data_type']=='collection'){
					
					
					// 確認日期
					$search_conf['date_string'] = '民國'.$source['collection']['store_no1'].'年入館';
					
					$meta_date[] = $source['collection']['store_date'];
					$parsedate = paser_date($meta_date);
					$search_conf['date_start'] = $parsedate['ds'];
					$search_conf['date_end']   = $parsedate['de'];
					
					$search_conf['storeyear']  = ['民國'.$source['collection']['store_no1'].'年'];
					$search_conf['savedyear']  = $parsedate['years'];
					
					$search_conf['title']   		= $source['collection']['title'];
					$search_conf['categories']  	= $source['collection']['categories'];
					$search_conf['ethnic']   		= $source['collection']['ethnic'];
					$search_conf['acquire_type']   	= $source['collection']['acquire_type'];
					$search_conf['status_code']   	= $source['collection']['status_code'];  
					$search_conf['acquire_info']   	= $source['collection']['acquire_info'];
					$search_conf['status_descrip']  = $source['collection']['status_descrip'];
					$search_conf['store_date']   	= $source['collection']['store_date'];
					$search_conf['store_location']  = $source['collection']['store_location'];
					$search_conf['store_number']   	= $source['collection']['store_number'];
					$search_conf['store_boxid']   	= $source['collection']['store_boxid'];
					
					$search_conf['store_information'] = $search_conf['store_location'].' / '.$search_conf['store_number'];
					if($source['collection']['store_boxid']){
					  $search_conf['store_information'] .= ' / '.$source['collection']['store_boxid'];	
					}
					
					$search_conf['remark']   	= $source['collection']['remark'];
					
					$search_conf['count_dofiles']   	= $source['collection']['count_dofiles'];
					$search_conf['count_element']   	= $source['collection']['count_element'];
					
					
					
					// 後分類篩選
					$search_conf['list_store_type']		= paser_postquery([$source['collection']['store_type']]);
					$search_conf['list_ethnic'] 		= paser_postquery([$source['collection']['ethnic']]);
					$search_conf['list_status_code'] 	= paser_postquery([$source['collection']['status_code']]);
					$search_conf['list_store_location'] = paser_postquery([$source['collection']['store_location']]);
					
					// 系統設定
					$search_conf['logout_flag']   = $source['collection']['logout_flag'];
					$search_conf['_flag_secret']  = $source['collection']['_flag_secret'];
					$search_conf['_flag_privacy'] = intval($source['collection']['_flag_privacy']);
					$search_conf['_flag_open']    = intval($source['collection']['_flag_open']);
					$search_conf['_flag_mask']    = 0;
					$search_conf['_flag_update']  = 0;
					$search_conf['_flag_view']    = $source['collection']['_view'];
					
					$lockmode   = '普通';
					$auditint   = $source['collection']['_flag_privacy'];
					$open       = $source['collection']['_flag_open'];
					$view		= $source['collection']['_view'];	
				
				}else{  // 單篇meta
				
				    $search_conf['identifier']  = $meta['identifier'];
				    $search_conf['applyindex']  = $meta['applyindex'];
				    $search_conf['data_type']   = 'element';
				    
				    $search_conf['location']   	= $source['element']['location'];
					$search_conf['period']   	= $source['element']['period'];
					$search_conf['creator']   	= $source['element']['creator'];
					$search_conf['title']   	= $source['element']['doname'];
					$search_conf['abstract']    = $source['element']['abstract'];
					$search_conf['remark']   	= $source['element']['remark'];
					
					
					// 後分類篩選
					$search_conf['list_dotype'] 		= paser_postquery([$source['element']['dotype']]);
					
					$search_conf['_flag_secret']  = $source['element']['_flag_secret'];
					$search_conf['_flag_privacy'] = intval($source['element']['_flag_privacy']);
					$search_conf['_flag_open']    = intval($source['element']['_flag_open']);
					$search_conf['_flag_mask']    = 0;
					$search_conf['_flag_update']  = 0;
					$search_conf['_flag_view']    = $source['element']['_view'];
					
					$lockmode   = '普通';
					$auditint   = $source['element']['_flag_privacy'];
					$open       = $source['element']['_flag_open'];
					$view		= $source['element']['_view'];   
					
				}
				
				break;
			  
			  
			  default: 
				exit(1);
				break;
			}
			
			// 更新 meta
			$db_update->bindValue(':lockmode' , $lockmode);
			$db_update->bindValue(':auditint' , $auditint);
			$db_update->bindValue(':open'	  , $open );
			$db_update->bindValue(':view'	  , $view);

			$db_update->bindValue(':search_json',json_encode($search_conf,JSON_UNESCAPED_UNICODE));
			$db_update->bindValue(':system_id',$meta['system_id']);
			
			if(!$db_update->execute()){
			  throw new Exception('新增資料更新失敗'); 	
			}
			echo "update .".date('c');
			
			ob_flush();
			flush();
		  }
		  
		  $limit+=$frame;  
		  
	  }
	  
    } catch (Exception $e) {
      echo $e->getMessage();
    }	
	
	
	
	
	// 轉換類別對照
	function map_to_serial($zone,$series,$fuid,$fufuid){
	  global $collection_mapping , $class_map;
	  
	  $class_level = array();
	  
	  if( !count($collection_mapping) || !count($class_map) ){
		return "";  
	  }
	  
	  if(!isset($collection_mapping[$zone])){
		return "";  
	  }
	  
	  $class_level[0] = $collection_mapping[$zone];
	  
	  $pattern = array('/\(/','/\)/','/\//');
	  $replace = array('&#40;','&#41;','&#47;');
	  
	  $class_level[1] = isset($class_map['series'][$series])  ? preg_replace($pattern,$replace,$class_map['series'][$series]) : "-";
	  $class_level[2] = isset($class_map['fuid'][$fuid])      ? preg_replace($pattern,$replace,$class_map['fuid'][$fuid]) : "-";
	  $class_level[3] = isset($class_map['fufuid'][$fufuid])  ? preg_replace($pattern,$replace,$class_map['fufuid'][$fufuid]) : "-";
	  
	  return join('/',$class_level);
	}
    

	      
	// 處理人名	  
	function paser_person($MemberArray){
	  $paser_return = [];
	  
	  if(!is_array($MemberArray)){
		return $paser_return;  
	  }
	  
	  
	  $data_queue  = array();
	  $data_return = array();
	  
	  foreach($MemberArray as $mbr_string){
		$data_queue = array_merge( $data_queue , preg_split('/(，|、|；|;|,)/u',$mbr_string));
	  }
	  
	  foreach($data_queue as $mbrset){
        
        if(preg_match('/[\n\/／．]/u',$mbrset)){
		  list($mbr,$role) = preg_split('/[\n\/／．]/u',$mbrset);  	
		}else{
		  $mbr = $mbrset;
		}
		
		$mbr_que[] = $mbr;
		
	  }
	  
	  $data_queue = array_unique(array_filter($mbr_que));
	  
	  if(!count($data_queue)){
		return $paser_return;   
	  }
	  
	  return array_values($data_queue); 
	}	  
	

	// 處理單位  
	function paser_organ($OrganArray){
	  $paser_return = [];
	   	
	  if(!is_array($OrganArray)){
		return $paser_return;  
	  }
	  
	  $data_queue = array();
	  
	  foreach($OrganArray as $org_string){
		$data_queue += preg_split('/(，|、|；|;|,|\s+)/u',$org_string);
	  }
	  
	  $data_queue = array_unique(array_filter($data_queue));
	  
	  foreach($data_queue as $key=>$ditem){
		if(preg_match('/:|：/',$ditem)){
		  $ditemsplit = preg_split('/:|：/',$ditem);
		  $data_queue[$key] = array_shift($ditemsplit);	
		}
	  }
	  
	  if(!count($data_queue)){
		return $paser_return;   
	  }
	  
	  return array_values($data_queue); 
	} 
		 
	// 處理多重欄位	  
	function paser_postquery($FieldArray){
	  $paser_return = [];
	   	
	  if(!is_array($FieldArray)){
		return $paser_return;  
	  }
	  
	  $data_queue = array();
	  
	  foreach($FieldArray as $field_string){
		$data_queue += preg_split('/(，|、|；|;|,)/u',$field_string);
	  }
	  
	  $data_queue = array_unique(array_filter($data_queue));
	  
	  if(!count($data_queue)){
		return $paser_return;   
	  }
	  return array_values($data_queue); 
	}	 
	 
		 
		 
	// 處理時間
	function paser_date($DateArray){
      
	  $paser_return = [
	    'ds'=>NULL,
		'de'=>NULL,
		'years'=>['none'],
	  ];
	  
	  $date_queue = array();
	  
	  if(!is_array($DateArray)){
		return $paser_return;  
	  }
	  
	  
	  
	  foreach($DateArray as $dstr){
        
		if(preg_match('/(\d+)年/u',$dstr,$match1)){
		  $date_change = [];
		  $date_change[] =  ( intval($match1[1]) < 200 ) ? (1911+intval($match1[1])) : intval($match1[1]);
		
		  if(preg_match('/(\d+)月/u',$dstr,$match2)){
		    $date_change[] =  ( intval($match2[1]) >= 1 && intval($match2[1]) <= 12 ) ? str_pad(intval($match2[1]),2,'0',STR_PAD_LEFT) : '00';
		  }  
		
		  if(preg_match('/(\d+)日/u',$dstr,$match3)){
		    $date_change[] =  ( intval($match3[1]) >= 1 && intval($match3[1]) <= 31 ) ? str_pad(intval($match3[1]),2,'0',STR_PAD_LEFT) : '00';
		  }
		
		  $dstr = join('-',$date_change);
		}
		
		
		$ynum = intval(substr($dstr,0,4));		  
        $dset = preg_split('/(\/|\-|\.)/',$dstr);
		 
		if(!strtotime(strtr($dstr,'.','-')) || ( $ynum < 1700 || $ynum > date('Y'))){
		  continue;
		}
		
		if(isset($dset[1]) && !intval($dset[1])){
		  $dset[1]='01';		
		}
		
		if(isset($dset[2]) && !intval($dset[2])){
		  $dset[2]='01';		
		}
		
		$date_queue[] = strtotime(join('-',$dset));	
	  }
	  
	  if(!count($date_queue)){
		return $paser_return;    
	  }
	  
	  $paser_return['ds'] = date('Y-m-d',min($date_queue));
	  $paser_return['de'] = (count($date_queue) > 1) ? date('Y-m-d',max($date_queue)) : date('Y-m-t',min($date_queue));
	  $paser_return['years'] = [];
	  
	  $dnmaps = [
	    '清嘉慶' => [1796,1821],
		'清道光' => [1821,1851],
		'清咸豐' => [1851,1861],
		'清祺祥' => [1861,1862],
		'清同治' => [1862,1875],
		'清光緒' => [1875,1909,1874,1894],
		'清宣統' => [1909,1912,9999],
		'日明治' => [1868,1912,1894],
		'日大正' => [1912,1926],
		'日昭和' => [1926,1989,1925,1945],
	    '民國' => [1912,2020,1945],
		
	  ];
	  
	  for($i=intval(substr($paser_return['ds'],0,4)); $i<= intval(substr($paser_return['de'],0,4)) ; $i++){
		$yearnum = $i; //str_pad($i,4,'0',STR_PAD_LEFT);
		$yeardys = [];
		foreach($dnmaps as $dy=>$yrange){
		  if($yearnum >=$yrange[0] &&  $yearnum <= $yrange[1]){
			
			if( isset($yrange[2]) && $yearnum < $yrange[2]) continue;  //作用範圍
			if( isset($yrange[3]) && $yearnum > $yrange[3]) continue;  //作用範圍
			
			$yeardys[] = $dy.str_pad(($yearnum-$yrange[0]+1),2,'0',STR_PAD_LEFT).'年';  
		  }
		}
		$paser_return['years'][] = str_pad($yearnum,4,'0',STR_PAD_LEFT).' '.(count($yeardys)>1 ? '西元'.str_pad($yearnum,4,'0',STR_PAD_LEFT) : join(',',$yeardys));
	  }
	  //var_dump($paser_return);
	  return $paser_return; 
	  
	  
	}
	
	
	
	// 轉國字數字
	function getChineseNumber($num){
      $conver = array(
	   0 => '-',
	   1 => '一',
	   2 => '二',
	   3 => '三',
	   4 => '四',
	   5 => '五',
	   6 => '六',
	   7 => '七',
	   8 => '八',
	   9 => '九',
	   10 => '十',
	   11 => '十一',
	   12 => '十二',
	   13 => '十三',
	   14 => '十四',
	   15 => '十五',
	   16 => '十六',
	   17 => '十七',
	   18 => '十八',
	   19 => '十九',
	   20 => '二十',
	  );
	  
	  return isset($conver[$num]) ? $conver[$num] : '-';
	}
	
	
	
?>