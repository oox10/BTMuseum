<?php
  
  
  /********************************************* 
  **  RCDH Field Control Set **
  *********************************************/
	
  class Field_Controller extends Admin_Controller{
    
	public function __construct(){
	  parent::__construct();	
      $this->Model = new Field_Model;
	}
	
	// PAGE: 欄位管理介面 O
	public function index(){
	  $this->Model->GetUserInfo();
	  $this->Model->ADField_Get_DB_Fields();
	  self::data_output('html','admin_field',$this->Model->ModelResult);
	}
	
	
	// AJAX: 儲存資料 
	public function save($DataNo , $DataJson){
	  $this->Model->ADField_Save_Field_Data($DataNo,$DataJson);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
  }
  
  
  
  
  
?>