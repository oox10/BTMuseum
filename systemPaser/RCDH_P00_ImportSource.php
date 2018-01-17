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
	
	$file_path = dirname(__FILE__).'/rawdata/testdata/';
	
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
		    "NULL,:class,:zong,:fonds,:store_no,:store_year,:store_type,:store_no1,:store_no2,:store_no3,".
			":title,:categories,:size_info,:ethnic,:period,:saved_year,:acquire_type,:acquire_info,".
			":status_code,:status_descrip,:store_date,:store_location,:store_number,:store_boxid,:remark,".
			"'館內','0','0','1','北投測試','0000-00-00 00:00:00',:user,NULL,'',1);");
			
		  $excelReader = PHPExcel_IOFactory::createReaderForFile($file_path.$source);
		  $excelReader->setReadDataOnly(true);
		  $objPHPExcel = $excelReader->load($file_path.$source);
			 
		  $excel_sheet_num = $objPHPExcel->getSheetCount();
		  $excel_sheet_names = $objPHPExcel->getSheetNames();
		  
		  $excel_sheet_num = 1;
		  $counter = 0;
		  
		  for($sheet=0;$sheet<$excel_sheet_num;$sheet++){
			  
			  echo $sheet.'-';
			  $objSheet=$objPHPExcel->getSheet($sheet);
			  $row=2;
			  echo trim($objSheet->getCellByColumnAndRow(1,$row)->getValue());
			  
			  while( trim($objSheet->getCellByColumnAndRow(2,$row)->getValue()) ){
				
				$orl_id_set  = explode('-',trim($objSheet->getCellByColumnAndRow( 2 ,$row)->getValue()));
				$new_id_set  = [];
				
				$new_id_set[0] = isset($orl_id_set[0]) && intval($orl_id_set[0]) ? str_pad(intval($orl_id_set[0]),3,'0',STR_PAD_LEFT) : '000';
 				$new_id_set[1] = isset($orl_id_set[1]) ? strtoupper($orl_id_set[1]) : 'Z';
 				$new_id_set[2] = isset($orl_id_set[2]) && intval($orl_id_set[2]) ? str_pad(intval($orl_id_set[2]),2,'0',STR_PAD_LEFT) : '00';
 				$new_id_set[3] = isset($orl_id_set[3]) && intval($orl_id_set[3]) ? str_pad(intval($orl_id_set[3]),3,'0',STR_PAD_LEFT) : '000';
 				$new_id_set[4] = 'a';
				
				$store_no = join('-',$new_id_set);
				
				
				$db_insert->bindValue(':class'		, 'relic' );
				$db_insert->bindValue(':zong'		, '001');
				$db_insert->bindValue(':fonds'		, '館藏文物');
				$db_insert->bindValue(':store_no'	, $store_no);
				$db_insert->bindValue(':store_year'	, $new_id_set[0]);
				$db_insert->bindValue(':store_type'	, $new_id_set[1]);
				$db_insert->bindValue(':store_no1'	, $new_id_set[2]);
				$db_insert->bindValue(':store_no2'	, $new_id_set[3]);
				$db_insert->bindValue(':store_no3'	, $new_id_set[4]);
				$db_insert->bindValue(':title'		, trim($objSheet->getCellByColumnAndRow( 3 ,$row)->getValue()));
				$db_insert->bindValue(':categories'	, trim($objSheet->getCellByColumnAndRow( 5 ,$row)->getValue()));
				$db_insert->bindValue(':size_info'	, trim($objSheet->getCellByColumnAndRow( 6 ,$row)->getValue()));
				$db_insert->bindValue(':ethnic'		, trim($objSheet->getCellByColumnAndRow( 7 ,$row)->getValue()));
				$db_insert->bindValue(':period'		, trim($objSheet->getCellByColumnAndRow( 8 ,$row)->getValue()));
				$db_insert->bindValue(':saved_year'	, trim($objSheet->getCellByColumnAndRow( 9,$row)->getValue()));
				$db_insert->bindValue(':acquire_type', '');
				$db_insert->bindValue(':acquire_info', trim($objSheet->getCellByColumnAndRow(10 ,$row)->getValue()));
				$db_insert->bindValue(':status_code', trim($objSheet->getCellByColumnAndRow( 11,$row)->getValue()));
				$db_insert->bindValue(':status_descrip', trim($objSheet->getCellByColumnAndRow( 12 ,$row)->getValue()));
				
				$store_date = trim($objSheet->getCellByColumnAndRow( 1,$row)->getValue());
				$store_date = strtotime($store_date) ? date('Y-m-d',strtotime($store_date)) : date('Y-m-d',PHPExcel_Shared_Date::ExcelToPHP($store_date));
				
				$db_insert->bindValue(':store_date'	,$store_date);
				$db_insert->bindValue(':store_location' , trim($objSheet->getCellByColumnAndRow(14 ,$row)->getValue()) ? trim($objSheet->getCellByColumnAndRow(14 ,$row)->getValue()) : '北投庫房' );
				
				$db_insert->bindValue(':store_number' , trim($objSheet->getCellByColumnAndRow( 15,$row)->getValue()));
				$db_insert->bindValue(':store_boxid'  , trim($objSheet->getCellByColumnAndRow( 13 ,$row)->getValue()));
				$db_insert->bindValue(':remark'		, trim($objSheet->getCellByColumnAndRow(17 ,$row)->getValue()));
				$db_insert->bindValue(':user'		, trim($objSheet->getCellByColumnAndRow(16 ,$row)->getValue()));
				
				
				if(!$db_insert->execute()){
				  throw new Exception('新增資料失敗'); 	
				}
				
				echo "done.";
				$row++;
				$counter++;
				
			  }  
		  }
	  }
	  
	  $objPHPExcel->disconnectWorksheets();  
	  unset($objPHPExcel);
    
	} catch (Exception $e) {
      echo $e->getMessage();
    }	
	
?>