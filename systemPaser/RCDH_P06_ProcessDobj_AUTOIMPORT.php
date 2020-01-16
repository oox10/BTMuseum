<?php
    
	/*
	處理數位檔案連結與縮圖 20180123
	
	掃描上傳資料夾並匯入
	
	DOSOURCE : _DOFOLDER/ORIGINAL\20180120
	SOURCE : source_archive 
	METADATA : 
	
	*/
	ini_set("memory_limit", "2048M");
    
    require_once(dirname(dirname(__FILE__)).'/conf/system_config.php');
    require_once(dirname(dirname(__FILE__)).'/mvc/core/DBModule.php');   
	
	$lib_imagemagic =  _SYSTEM_ROOT_PATH.'mvc/lib/ImageMagick-7.0.3-7-portable-Q16-x64/convert.exe ';
    
   
	$file_allow = array('jpg','png','tiff','wmv','mp4','mp3');
	
	define('_SOURCE_LOCATION',_SYSTEM_DIGITAL_FILE_PATH.'DOBATCH/');
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
	  
	  
	   
	  
	  $dofolders = array_slice(scandir(_SOURCE_LOCATION),2);
	  
	  foreach($dofolders as $dofolder){
		  
        if($dofolder=='DONE') continue;	//另存資料夾
		if(!is_dir(_SOURCE_LOCATION.$dofolder)) continue;	//非資料夾
		if(preg_match('/x\d{8}$/',$dofolder)) continue;		//處理過了 
         
        $do_name_set = explode('-',$dofolder);
        $do_store_id = str_pad($do_name_set[0],3,'0',STR_PAD_LEFT).'-'.$do_name_set[1].'-'.str_pad($do_name_set[2],2,'0',STR_PAD_LEFT).'-'.str_pad($do_name_set[3],3,'0',STR_PAD_LEFT); 
		
		
		$dofiles = array_slice(scandir(_SOURCE_LOCATION.$dofolder),2);
		
		if(!count($dofiles)) continue;
		
		// 建立預備資料
		if(!isset($do_source_array[$do_store_id])) $do_source_array[$do_store_id] = [];
        
		foreach($dofiles as $dofile){
			$do_source_array[$do_store_id][] = [
				'bookid' => $do_store_id,
				'folder' => $dofolder,
				'filename' => $dofile,
				'filepath' => _SOURCE_LOCATION.$dofolder.'/'.$dofile,
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
		  $store_profile = array('store'=>$store_location.'/browse/'.$bookmeta['store_no'].'/',"saved"=>date('Y-m-d H:i:s'),"items"=>[],"dotype"=>['文物卡','整理照','出版照','相片','底片','翻拍','其他']);  
		}
        
		
		//-- 清除舊有影像
        $doold = array_slice(scandir($store_location.'/saved/'.$bookmeta['store_no'].'/'),2);
        foreach($doold as $odo){
			unlink($store_location.'/saved/'.$bookmeta['store_no'].'/'.$odo);
		}
		$doold = array_slice(scandir($store_location.'/browse/'.$bookmeta['store_no'].'/'),2);
        foreach($doold as $odo){
			unlink($store_location.'/browse/'.$bookmeta['store_no'].'/'.$odo);
		}
		$doold = array_slice(scandir($store_location.'/thumb/'.$bookmeta['store_no'].'/'),2);
        foreach($doold as $odo){
			unlink($store_location.'/thumb/'.$bookmeta['store_no'].'/'.$odo);
		}
		$store_profile['items'] = [];
		
		
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
			
			// 刪除來源檔案
			$resave_folder = _SOURCE_LOCATION.'DONE/'.$bookimages['folder'].'/';
			if(!is_dir($resave_folder))  mkdir($resave_folder,0777);
			
			copy($file_from,$resave_folder.$file['filename']);
			unlink($file_from);
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
	  
	  
	  // 處理原始資料夾
	  $dofolders = array_slice(scandir(_SOURCE_LOCATION),2);
	  
	  foreach($dofolders as $dofolder){
		
		if($dofolder=='DONE') continue;  
        if(!is_dir(_SOURCE_LOCATION.$dofolder)) continue;		
		
        $do_name_set = explode('-',$dofolder);
        $do_store_id = str_pad($do_name_set[0],3,'0',STR_PAD_LEFT).'-'.$do_name_set[1].'-'.str_pad($do_name_set[2],2,'0',STR_PAD_LEFT).'-'.str_pad($do_name_set[3],3,'0',STR_PAD_LEFT); 
		
		$dofiles = array_slice(scandir(_SOURCE_LOCATION.$dofolder),2);
		if(!count($dofiles)){
			rmdir(_SOURCE_LOCATION.$dofolder);
		}else{
			rename(_SOURCE_LOCATION.$dofolder,_SOURCE_LOCATION.$dofolder.'x'.date('Ymd'));
		}
		
	  }
	  
	  
	  echo "\n 匯入完成!";
	   
    } catch (Exception $e) {
      echo $e->getMessage();
    }	
	
	
	
?>