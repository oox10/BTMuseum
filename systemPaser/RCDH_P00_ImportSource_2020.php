<?php
    
	/*
	數位典藏資料
	2017
	*/
	ini_set("memory_limit", "2048M");
    
    require_once(dirname(dirname(__FILE__)).'/conf/server_config.php');
    require_once(dirname(dirname(__FILE__)).'/mvc/core/DBModule.php');   
    require_once(dirname(dirname(__FILE__)).'/mvc/lib/PHPExcel-1.8/Classes/PHPExcel.php');
	
	//-- load meta assist from db 
    $db = new DBModule;
    $db->db_connect('PDO'); 
	
	$file_path  = dirname(__FILE__).'/rawdata/newdata/';
	//項次	入庫日期	文物編號	品名	圖檔	存放地點	展示地點	尺寸	漢族	原住民族	備註/年代	狀況級數	狀況	箱號	建檔者	入庫櫃位
	//2180	2017/1/4	74-A-22-39	泰雅族披肩				113.5*91.2		▲	1960s	A	中間有一處染色。	347	雅	G4-4

	//項次	入庫日期	文物編號	品名	圖檔	尺寸	備註/年代	狀況級數	狀況	入庫櫃位			
	//2485	2018-01-04	76-Z-4-55	皮影道具		26*14	購於古都民藝館。	B	橋桿兩處斷裂，留有殘膠，髒污，部分破損。	E2-2

	//項次	入庫日期	文物編號	品名	圖檔	尺寸	備註/年代	B	狀況	箱號	建檔者	入庫櫃位			
	//2687	2019/1/9	75-C-4-336	東寧擊缽吟後集		22*15*0.6		C	裝訂完全脫落，紫色墨漬汙損，紙張受潮有褐斑首頁文物編號邊標籤直接黏貼，散頁有折損	253	芷	F4-2			

  	

	$fail_sheet = array();
	$counter    = 5000;
	
	try{ 
      
	  $files = array_slice(scandir($file_path),2);
	  
	  if(!count($files)){
		throw new Exception('No Source Files FROM :'.$file_path."\n");    
	  }
	  
	  foreach($files as $source){
		  
		  if(!is_file($file_path.$source)){
			throw new Exception($file_path.': File Not Exist.');  
		  }
		  
		  $db_insert = $db->DBLink->prepare("INSERT INTO source_digiarchive VALUES(".
		    "NULL,:class,:zong,:fonds,:store_no,:store_id,:store_year,:store_type,:store_no1,:store_no2,:store_no3,:store_orl,".
			":title,:categories,:size_info,:ethnic,:period,:saved_year,:acquire_type,:acquire_info,".
			":status_code,:status_descrip,:store_date,:store_location,:store_number,:store_boxid,:store_boxidorl,0,'',:remark,'',0,0,".
			"'館內','0','0','1','北投文物館','0000-00-00 00:00:00',:user,NULL,'',1);");
			
		  $excelReader = PHPExcel_IOFactory::createReaderForFile($file_path.$source);
		  $excelReader->setReadDataOnly(true);
		  $objPHPExcel = $excelReader->load($file_path.$source);
			 
		  $excel_sheet_num = $objPHPExcel->getSheetCount();
		  $excel_sheet_names = $objPHPExcel->getSheetNames();
		  
		  
		  for($sheet=0;$sheet<1;$sheet++){
			  
			  echo $sheet.'-';
			  $objSheet=$objPHPExcel->getSheet($sheet);
			  $row=2;
			  $finish = 0;
			  
			  while( trim($objSheet->getCellByColumnAndRow(0,$row)->getValue()) ){
				
				echo trim($objSheet->getCellByColumnAndRow(2,$row)->getValue());
				
				$orl_id_str  = trim($objSheet->getCellByColumnAndRow( 2 ,$row)->getValue());
				$orl_id_set  = explode('-',$orl_id_str);
				$new_id_set  = [];
				
				$new_id_set[0] = isset($orl_id_set[0]) && intval($orl_id_set[0]) ? str_pad(intval($orl_id_set[0]),3,'0',STR_PAD_LEFT) : '000';
 				$new_id_set[1] = isset($orl_id_set[1]) ? strtoupper($orl_id_set[1]) : 'Z';
 				$new_id_set[2] = isset($orl_id_set[2]) && intval($orl_id_set[2]) ? str_pad(intval($orl_id_set[2]),2,'0',STR_PAD_LEFT) : '00';
 				$new_id_set[3] = isset($orl_id_set[3]) && intval($orl_id_set[3]) ? str_pad(intval($orl_id_set[3]),3,'0',STR_PAD_LEFT) : '000';
 				
				$new_id_set[4] = isset($orl_id_set[4]) && preg_match('/^(\w+)$/',$orl_id_set[4],$match) ? strtolower($match[1]) : '';
				
				if( $new_id_set[4]=='' && isset($orl_id_set[3])  && preg_match('/\((\w{1,2})\)/i',$orl_id_set[3],$match2)){
				  $new_id_set[4] = strtolower($match2[1]);	
				}
				
				$new_id_set = array_filter($new_id_set);
				
				if($orl_id_str=='' || (count(array_filter($orl_id_set))!=4&&count(array_filter($orl_id_set))!=5) || !preg_match('/^\d\d\d\-\w\-\d\d\-\d\d\d(\-\w+)?$/',join('-',$new_id_set))){
				  
				  echo ' id fail.'."\n";
				  $fail_raw = [] ; 
				  for($f=0;$f<16;$f++){
					
					if($f==4) continue;
					$cell_data = trim($objSheet->getCellByColumnAndRow($f,$row)->getValue());
					$fail_raw[] = $f==1 && $cell_data ? date('Y-m-d',PHPExcel_Shared_Date::ExcelToPHP($cell_data)) : $cell_data; 
				  }
				  
                  if($orl_id_str=='' && !count(array_filter($fail_raw))){
					$finish++;  
				  }else{
					$fail_sheet[] = $fail_raw;  
				  }
				  
				  $row++;
				  continue;
				}
				
				$ethnic   = trim($objSheet->getCellByColumnAndRow( 8 ,$row)->getValue()) ? '漢人' : '';
				$ethnic   = trim($objSheet->getCellByColumnAndRow( 9 ,$row)->getValue()) ? '原住民族' : $ethnic;
				
				$store_id = join('-',$new_id_set);
				$store_no = 'BM'.str_pad($counter+1,8,'0',STR_PAD_LEFT);
				
				
                $rowref     = $row+1;
				do{
				  $rowref--;	
				  $remark   = trim($objSheet->getCellByColumnAndRow(10 ,$rowref)->getValue());
				}while($remark=='同上'&& trim($objSheet->getCellByColumnAndRow(10 ,($rowref-1))->getValue()));				
			
				$db_insert->bindValue(':class'		, 'relic' );
				$db_insert->bindValue(':zong'		, '001');
				$db_insert->bindValue(':fonds'		, '館藏文物');
				$db_insert->bindValue(':store_no'	, $store_no);
				$db_insert->bindValue(':store_id'	, $store_id);
				$db_insert->bindValue(':store_year'	, $new_id_set[0]);
				$db_insert->bindValue(':store_type'	, $new_id_set[1]);
				$db_insert->bindValue(':store_no1'	, $new_id_set[2]);
				$db_insert->bindValue(':store_no2'	, $new_id_set[3]);
				$db_insert->bindValue(':store_no3'	, isset($new_id_set[4]) ? $new_id_set[4] : '');
				$db_insert->bindValue(':store_orl'	, $orl_id_str );
				$db_insert->bindValue(':title'		, trim($objSheet->getCellByColumnAndRow( 3 ,$row)->getValue()));
				$db_insert->bindValue(':categories'	, '' );
				$db_insert->bindValue(':size_info'	, trim($objSheet->getCellByColumnAndRow( 7 ,$row)->getValue()));
				$db_insert->bindValue(':ethnic'		, $ethnic);
				$db_insert->bindValue(':period'		, '');
				$db_insert->bindValue(':saved_year'	,  '');
				$db_insert->bindValue(':acquire_type', '');
				$db_insert->bindValue(':acquire_info', '');
				$db_insert->bindValue(':status_code', trim($objSheet->getCellByColumnAndRow( 11,$row)->getValue()));
				$db_insert->bindValue(':status_descrip', trim($objSheet->getCellByColumnAndRow( 12 ,$row)->getValue()));
				
				$store_date = trim($objSheet->getCellByColumnAndRow( 1,$row)->getValue());
				if($store_date){
				  $store_date = strtotime($store_date) ? date('Y-m-d',strtotime($store_date)) : date('Y-m-d',PHPExcel_Shared_Date::ExcelToPHP($store_date));	
				}
				
				$db_insert->bindValue(':store_date'	,$store_date);
				$db_insert->bindValue(':store_location' , trim($objSheet->getCellByColumnAndRow(5 ,$row)->getValue()) ? trim($objSheet->getCellByColumnAndRow(6 ,$row)->getValue()) : '北投庫房' );
				
				$db_insert->bindValue(':store_number' , trim($objSheet->getCellByColumnAndRow( 15,$row)->getValue()));
				$db_insert->bindValue(':store_boxid'  , trim($objSheet->getCellByColumnAndRow( 13,$row)->getValue()));
				$db_insert->bindValue(':store_boxidorl'  , '');
				$db_insert->bindValue(':remark'		, $remark);
				$db_insert->bindValue(':user'		, trim($objSheet->getCellByColumnAndRow(14 ,$row)->getValue()));
				
				
				if(!$db_insert->execute()){
				  throw new Exception('新增資料失敗'); 	
				}
				
				echo "done. \n";
				$row++;
				$counter++;
				
				 
				
			  }
		  }
	  }
	  
	  
	  $objPHPExcel->disconnectWorksheets();  
	  unset($objPHPExcel);
	  
	  
	  //匯出錯誤資料
	  $objReader = PHPExcel_IOFactory::createReader('Excel2007');
	  $file_path  = dirname(__FILE__).'/rawdata/faildata/';
	  $objPHPExcel = $objReader->load($file_path.'importfail.xlsx');
	
	  $objPHPExcel->setActiveSheetIndex(0);
	  $objPHPExcel->getActiveSheet()->setTitle(date('Ymd')."匯入錯誤");
		
	  $col = 0 ;
	  $row = 2 ;
 		
	  foreach( $fail_sheet as $data){
		  $col = 0;
		  foreach($data as $f=>$v){
			$objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $row)->setValueExplicit($v, PHPExcel_Cell_DataType::TYPE_STRING);  	
			$col++;
		  }
		  $row++;
	  }
	  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	  $objWriter->save($file_path.date('Ymd').'import_fail_'.count($fail_sheet).'.xlsx'); 
	  $objPHPExcel->disconnectWorksheets();
	  unset($objPHPExcel);
	  
	} catch (Exception $e) {
      echo $e->getMessage();
    }	
	
?>