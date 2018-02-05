<?php
    
	/*
	處理數位檔案連結與縮圖 20180123
	
	從提供者端提供資料並歸檔
	
	DOSOURCE : _DOFOLDER/ORIGINAL\20180120
	SOURCE : source_archive 
	METADATA : 
	
	*/
	ini_set("memory_limit", "2048M");
    
    require_once(dirname(dirname(__FILE__)).'/conf/system_config.php');
    require_once(dirname(dirname(__FILE__)).'/mvc/core/DBModule.php');   
	
	$lib_imagemagic =  _SYSTEM_ROOT_PATH.'mvc/lib/ImageMagick-7.0.3-7-portable-Q16-x64/convert.exe ';
    
   
	$file_allow = array('jpg','png','tiff','wmv','mp4','mp3');
	
	define('_SOURCE_LOCATION',_SYSTEM_DIGITAL_FILE_PATH.'ORIGINAL/20180120/');
	define('_STORE_LOCATION',_SYSTEM_DIGITAL_FILE_PATH);
	define('_SOURCE_UNPASER',_SYSTEM_DIGITAL_FILE_PATH.'ORIGINAL/unpaser/');
	 
	//-- load meta assist from db 
    $db = new DBModule;
    $db->db_connect('PDO'); 
	
	$source_table     = 'source_digiarchive';
	$target_condition = "1";
	$meta_exist = array();
	
	ob_start();
	
	
	
	try{ 
      
	  // 掃描圖檔並重新整理ID
	  $do_source_array = [];
	  
	  $dosource = array_slice(scandir(_SOURCE_LOCATION),2);
	  if(!count($dosource)){
		throw new Exception('無來源資料');     
	  }
	   
	 
	  foreach($dosource as $dofile){
		
		if(preg_match('/(\s+)?(\((\d+)\))?\.(JPG|jpg)/',$dofile,$match)){
		  $donumber = intval($match[3]);	
		}else{
		  $donumber = 0;	
		}
		
		$do_name_set = explode('-',preg_replace('/(\s+)?(\(\d+\))?\.(JPG|jpg)/','',$dofile));
        
		
		$do_store_id = str_pad($do_name_set[0],3,'0',STR_PAD_LEFT).'-'.$do_name_set[1].'-'.str_pad($do_name_set[2],2,'0',STR_PAD_LEFT).'-'.str_pad($do_name_set[3],3,'0',STR_PAD_LEFT); 
		
		if(!isset($do_source_array[$do_store_id])) $do_source_array[$do_store_id] = [];
        
		if($donumber){
		  $do_source_array[$do_store_id][$donumber] = [
		    'bookid' => $do_store_id,
		    'filename' => $dofile,
		    'filepath' => _SOURCE_LOCATION.$dofile,
		  ];	
		}else{
		  $do_source_array[$do_store_id][] = [
		    'bookid' => $do_store_id,
		    'filename' => $dofile,
		    'filepath' => _SOURCE_LOCATION.$dofile,
		  ];	
		}
	  }
	  
	  
	  $page_count = 0;
	  
	  foreach($do_source_array as $bookid => $bookimages){
		
		ksort($bookimages);
		
		$db_select = $db->DBLink->prepare("SELECT * FROM ".$source_table." WHERE store_id LIKE :bookid;");
		$db_select->execute(array('bookid'=>$bookid.'%'));
	    if(!$bookmeta = $db_select->fetch(PDO::FETCH_ASSOC)){
		  echo "\n".$bookid." unfound ";	
		  file_put_contents('image_unfound.log',$bookid."\n",FILE_APPEND);
		  foreach($bookimages as $file){
			copy($file['filepath'],_SOURCE_UNPASER.$file['filename']); 
		  } 
		  continue;
		}
        
        		
		$store_location = _STORE_LOCATION.$bookmeta['zong'];
				
		//-- 建構檔案儲存結構
		if(!is_dir($store_location.'/browse/'.$bookmeta['store_no'].'/')) mkdir($store_location.'/browse/'.$bookmeta['store_no'].'/',0777,true);
		if(!is_dir($store_location.'/saved/'.$bookmeta['store_no'].'/'))  mkdir($store_location.'/saved/'.$bookmeta['store_no'].'/',0777,true);
		if(!is_dir($store_location.'/thumb/'.$bookmeta['store_no'].'/'))  mkdir($store_location.'/thumb/'.$bookmeta['store_no'].'/',0777,true);
		if(!is_dir($store_location.'/profile/'.$bookmeta['store_no'].'/'))  mkdir($store_location.'/profile/'.$bookmeta['store_no'].'/',0777,true);
		
		
		// read folder profile 
		if(file_exists($store_location.'/profile/'.$bookmeta['store_no'].'.conf')){
		  $store_profile = json_decode(file_get_contents($store_location.'/profile/'.$bookmeta['store_no'].'.conf'),true);	
		}else{
		  $store_profile = array('store'=>$store_location.'/browse/'.$bookmeta['store_no'].'/',"saved"=>date('Y-m-d H:i:s'),"items"=>[]);  
		}
		
		
		
		echo "\n".$bookmeta['store_no'].':PASER START..'; 
        
		foreach($bookimages as $file){
			
			echo "\n".$file['filename'].': ';
			
			//-- 已存在檔案
		    $booksaved = array_slice(scandir($store_location.'/saved/'.$bookmeta['store_no'].'/'),2);
		
			
			$file_from = $file['filepath'];
			$file_name = $bookmeta['store_no'].'-'.str_pad((count($booksaved)+1),3,'0',STR_PAD_LEFT).'.jpg';
			
			
			// 確認型態
			if(!in_array(strtolower(pathinfo($file_from,PATHINFO_EXTENSION)),$file_allow) ){
			  echo "skip.";
			  continue;	
			}
			
			list($iw, $ih, $it, $attr) = getimagesize($file_from);	
			
			// 複製原始檔案
			echo " S: ";
			if(is_file($store_location.'/saved/'.$bookmeta['store_no'].'/'.$file_name)){
			  echo 'skip. ';	
			}else{
			  echo copy($file_from,$store_location.'/saved/'.$bookmeta['store_no'].'/'.$file_name) ? 'OK':'fail';	
			}
			
			// 建立瀏覽級檔案
			$file_save = $store_location.'/browse/'.$bookmeta['store_no'].'/'.$file_name;
			echo " B: ";
			if( $iw > 1190 || $ih > 1684 ){
			  $config = ($iw >= $ih) ?  ' -resize 1190x ' : ' -resize x1684 ';
			  $config.= '-quality 70 ';
			  exec($lib_imagemagic.$config.$store_location.'/saved/'.$bookmeta['store_no'].'/'.$file_name.' '.$file_save ,$result); 
		    }else{
			  copy($file_from,$file_save);   
		    }
			
			
			// 建立縮圖
			$file_save = $store_location.'/thumb/'.$bookmeta['store_no'].'/'.$file_name;
			echo " T: ";
			$config = ($iw >= $ih) ?  ' -thumbnail 150 ' : ' -thumbnail x200 ';
			$config.= '-quality 50 ';  
			exec($lib_imagemagic.$config.$store_location.'/saved/'.$bookmeta['store_no'].'/'.$file_name.' '.$file_save ,$result); 
			 
			$page_count++;
		    
			list($fw, $fh, $ft, $attr) = getimagesize($store_location.'/browse/'.$bookmeta['store_no'].'/'.$file_name);	
			
		    $newfileconf = [
			  'file'=>$file_name,
			  'width'=> $fw,
			  'height'=> $fh,
			  'size'=>filesize($store_location.'/saved/'.$bookmeta['store_no'].'/'.$file_name),
			  'dotype'=>'整理照'
			];
			
			$store_profile['items'][]=$newfileconf; 
		}
		
		file_put_contents($store_location.'/profile/'.$bookmeta['store_no'].'.conf',json_encode($store_profile));	
		
		
		// 更新 db
		
		$booksaved = array_slice(scandir($store_location.'/saved/'.$bookmeta['store_no'].'/'),2);
		
		$db_update = $db->DBLink->prepare("UPDATE source_digiarchive SET count_dofiles=:count_dofiles WHERE store_no=:store_no;");
		$db_update->bindValue(':count_dofiles',count($booksaved));
		$db_update->bindValue(':store_no',$bookmeta['store_no']);
			
		if(!$db_update->execute()){
		  throw new Exception('更新失敗'); 	
		}
			
	    echo "update .".date('c');
			
		ob_flush();
		flush();
		
	  }
	  
	  echo "\n 匯入完成!";
	   
    } catch (Exception $e) {
      echo $e->getMessage();
    }	
	
	
	
?>