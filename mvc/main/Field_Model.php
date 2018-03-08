<?php

  class Field_Model extends Admin_Model{
    
	
	/***--  Function Set --***/
    public function __construct(){
	  parent::__construct();
	  parent::initial_user($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['USER']);  
	}
	
	
	//-- Admin Staff Page Initial 
	// [input] : none;
	public function ADField_Get_DB_Fields(){
	  
	  $result_key = parent::Initial_Result('records');
	  $result  = &$this->ModelResult[$result_key];
	  	  
	  try{
	    
		// 取得欄位資料
		$DB_OBJ = $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdField::GET_META_FORMATS()));
		if(!$DB_OBJ->execute(array('dbtable'=>'source_digiarchive'))){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		$source_digiarchive_fields = $DB_OBJ->fetchAll(PDO::FETCH_ASSOC);
		 
		$result['action'] = true;		
		$result['data']['source_digiarchive']   = $source_digiarchive_fields;		
	  
	  } catch (Exception $e) {
		$result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	 
	
	
	//-- Admin Staff Save Staff Data 
	// [input] : DataNo    :  \d+  = DB.user_info.uid;
	// [input] : FormatString  :   urlencode(base64encode(json_pass()))  = array( 'field_name'=>new value , ......   ) ;  // 修改之欄位 - 變動
	 
	public function ADField_Save_Field_Data( $DataNo=0 , $FormatString='' ){
	  
	  $result_key = parent::Initial_Result();
	  $result  = &$this->ModelResult[$result_key];
	  
	  $data_format = json_decode(base64_decode(str_replace('*','/',rawurldecode($FormatString))),true); 
	  
	  try{  
		
		// 檢查序號
	    if(!preg_match('/^\d+$/',$DataNo) || !is_array($data_format)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		
		// 取得欄位資料
		$meta_format = [];
		$DB_OBJ = $this->DBLink->prepare( SQL_AdField::GET_TARGET_META_FORMAT());
		if(!$DB_OBJ->execute(array('mfno'=>$DataNo))){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		if(!$meta_format = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		
		
		
		
		if(($meta_format['module']=='S' || $meta_format['module']=='E') && $meta_format['pattern']!=$data_format['pattern']){
          // 更新source資料表
		  $field_value_setad = array_filter(explode(';',$data_format['pattern']));
		  $field_value_module= $meta_format['module']=='S' ? 'set' : 'enum';
		  
		  foreach($field_value_setad as $i=>$term){
			
			// 替換
            if(count(explode('=>',$term)) > 1){
			  $field_value_set_temp = $field_value_setad;	
			  list($to,$tm) = explode('=>',$term);
			  $field_value_set_temp[] = $to;
			  $field_value_set_temp[] = $tm;
              
			  //擴充
              $DB_EXP = $this->DBLink->prepare( SQL_AdMeta::SET_DBTABLE_SETFIELD($meta_format['dbtable'],$meta_format['dbcolumn'],$field_value_module,$field_value_set_temp));	
		      $DB_EXP->execute();
			  
			  //變更資料
			  $DB_UPDB = $this->DBLink->prepare( "UPDATE ".$meta_format['dbtable']." SET ".$meta_format['dbcolumn']."=REPLACE(".$meta_format['dbcolumn'].",'".$to."','".$tm."') WHERE ".$meta_format['dbcolumn']." LIKE '%".$to."%';");	
		      $DB_UPDB->execute(); 
			  
			  $field_value_setad[$i]=$tm;
			  
			  $data_format['pattern'] = join(';',$field_value_setad);
			  
			}
		  }
		  
		  $DB_UPDB = $this->DBLink->prepare( SQL_AdMeta::SET_DBTABLE_SETFIELD($meta_format['dbtable'],$meta_format['dbcolumn'],$field_value_module,$field_value_setad));	
		  $DB_UPDB->execute();
		}
		
		
		
		$DB_SAVE	= $this->DBLink->prepare(SQL_AdField::UPDATE_META_FORMAT());
		$DB_SAVE->bindValue(':mfno' , $DataNo);
		$DB_SAVE->bindValue(':can_export' , $data_format['can_export']);
		$DB_SAVE->bindValue(':can_printout' , $data_format['can_printout']);
		$DB_SAVE->bindValue(':pattern' , $data_format['pattern']);
		$DB_SAVE->bindValue(':user' , $this->USER->UserID);
		 
		if( !$DB_SAVE->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
	    
		
		 
		// final 
		$result['data'] = $DataNo;
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	
	
	
  }
?>