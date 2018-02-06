<?php

  class Main_Model extends Admin_Model{
    
	public function __construct(){
	  parent::__construct();
	  parent::initial_user($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['USER']);
	}
	
	/***--  Function Set --***/
    
	
	/*[ MainPage Function Set ]*/ 
	//-- Get System Space And Data Count
	// [input] : 
	public function Get_System_Information(){
	  
	  $result_key = parent::Initial_Result('space');
	  $result  = &$this->ModelResult[$result_key];
      
	  $system_use_year    = '2020-12-31 23:59:59';
	  $system_start_date  = '2018-01-01';
	  
	  try{
		
		// 磁碟總空間
		/*
		*  當前儲存架構為  
		*  IISPhoStore 
		*  - ORLPHO : TR舊資料
		*  - 2016PHO : 虛擬磁區掛載
		*  - PHOFTP  : FTP上傳暫存    - PHOTMP : 網頁上傳暫存  -PHOOUT : 匯出保存區
		*/
		
		// 磁碟使用空間
		$system_root_space  = disk_total_space(_SYSTEM_DIGITAL_FILE_PATH);
		$system_total_space = $system_root_space;
		
		$limit_year = intval(date('Y',strtotime($system_use_year)));
		$scan_year  = $limit_year;
        $start_year = 2018;		
		
		while($scan_year>=$start_year){
		  $scan_year--;
		  $location = _SYSTEM_DIGITAL_FILE_PATH.($scan_year+1).'PHO/';
		  if(!is_dir($location)) continue;
		  $location_space = disk_total_space($location);
		  if($location_space == $system_root_space) continue;  
		  $system_total_space+= $location_space;
		}
		
		
		// 建構空間圖表資料  2016-01 ~ 2018-12

		// X 軸
		$x_category = array();
		
		$limit_time  = strtotime($system_use_year);
		$date_point   = strtotime($system_start_date); 
		
		do{
		  $x_category[strval(date('Ym',$date_point))] = ( date('m',$date_point) =='12' ) ? date('Y/m',$date_point): date('Y/m',$date_point);
		  $date_point = strtotime('+1 month',$date_point);
		}while($date_point < $limit_time);
		
		
		$DB_OBJ = $this->DBLink->prepare(SQL_Admin::SELECT_ALL_DATA_STORE());
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		$data_count = 0;
		
		$x_rate = array();
		$x_data = array();  
		$x_list = array();
		$x_value= array();
		while($tmp = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){  //stage total_size classcode  
		  if(!isset($x_list[$tmp['fonds']])){
			$x_list[$tmp['fonds']] = array_combine( array_keys($x_category) , array_fill(0, count($x_category), 0));
		  }
		  if(isset($x_list[$tmp['fonds']][$tmp['stage']])){
			$x_list[$tmp['fonds']][$tmp['stage']]+= $tmp['total_size']*1024*1024*1024/10; 
		  }else{
			$x_list[$tmp['fonds']]['201801']+= $tmp['total_size'];  
		  }
		  $data_count += $tmp['count'];
		}
		
		foreach($x_list as $name=>$data){
		  $x_data[$name] = array();
		  $x_rate[$name] = 0;		  
		  foreach($data as $key => $val){
			$x_rate[$name]+= $val;
			$x_data[$name][$key] = ($key <= date('Ym')) ? preg_replace('/\sGB/','',System_Helper::getSymbolByQuantity($x_rate[$name],3)):null; 
		  }
		  array_unshift($x_value,array('name'=>$name,'data'=>array_values($x_data[$name])));  
		}
		
		$system_used_space = array_sum($x_rate);
		
		
		// 圓餅圖資料
		$pi_data = array();
		foreach($x_rate as $name=>$total_size){
		  $rate =  round($total_size/$system_used_space*100,2);
		  $pi_data[] = array($name,$rate);
		}
		
		
		
		
		// 相關參考函數 
		// var_dump(System_Helper::getSymbolByQuantity($x_rate[''],3));
		// echo System_Helper::getSymbolByQuantity($system_total_space);
		// $df = disk_free_space("C:"); 
		// $ds = disk_total_space("D:");
		// $df = disk_total_space("G:/IISPhoStore/2017PHO/"); 
		
		
		$result['data']['space']['total'] = System_Helper::getSymbolByQuantity($system_total_space);
		$result['data']['space']['used']  = System_Helper::getSymbolByQuantity($system_used_space);
		$result['data']['space']['rate']  = round($system_used_space/$system_total_space*100,2);
		
		$result['data']['count']['photo']  = $data_count;
		
		$result['data']['chart']['x_category'] = array_values($x_category);
		$result['data']['chart']['x_data'] = $x_value;
		$result['data']['chart']['y_max_value'] = round($system_total_space/pow(1024,3));
		$result['data']['chart']['y_now_value'] = round($system_used_space/pow(1024,3));
		$result['data']['chart']['pie_data'] = $pi_data;
		
		$result['action'] = true;
		
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	//-- Get Client Page Post List
	// [input] : NULL / group code 
	public function Get_Post_List( ){
	  $result_key = parent::Initial_Result('post');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{    
	  
		// 查詢資料庫
		$DB_OBJ = $this->DBLink->prepare(SQL_Admin::SELECT_INDEX_POSTS());	
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
        $result['action'] = true;		
		
		$result['data']   = $DB_OBJ->fetchAll(PDO::FETCH_ASSOC);;		
	  
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      } 
	  return $result;   
	}
	
	/*[ Post Function Set ]*/ 
	
	//-- Get Client Post List
	// [input] : DataNo = system_post.pno
	public function Get_Client_Post_Target($DataNo){
	  
      $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{    
		
		//:確認申請序號
		if(!preg_match('/^\d+$/',$DataNo)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		$post = array();
		
		$DB_OBJ = $this->DBLink->prepare(SQL_Admin::GET_CLIENT_POST_TARGET());
		if(!$DB_OBJ->execute(array('pno'=>intval($DataNo))) || !$post=$DB_OBJ->fetch(PDO::FETCH_ASSOC) ){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		
		$DB_OBJ = $this->DBLink->prepare(SQL_Admin::CLIENT_POST_HITS());
		$DB_OBJ->execute(array('pno'=>intval($DataNo)));
		
		$post['post_content'] = base64_encode(htmlspecialchars_decode($post['post_content']));
		$post['post_hits'] += 1;
		
		$result['action'] 	= true;		
		$result['data'] 	= $post;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      } 
	  return $result;
	}
	
	
  }
?>