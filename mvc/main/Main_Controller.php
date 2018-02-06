<?php
  
  class Main_Controller extends Admin_Controller{
    
	public function __construct(){
	  parent::__construct();	
      $this->Model = new Main_Model;
	}
	
	// PAGE: 系統首頁
	public function index(){
	  $this->Model->GetUserInfo();
	  $this->Model->Get_System_Information();
	  $this->Model->Get_Post_List();
	  self::data_output('html','admin_index',$this->Model->ModelResult);
	}
	
	// PAGE: 權限不足
	public function denial(){
	  $this->Model->GetUserInfo();
	  $this->Model->Get_System_Information();
	  $this->Model->ModelResult['denial'] = array('action'=>false,'message'=>array('_SYSTEM_ERROR_PERMISSION_DENIAL'));
	  self::data_output('html','admin_index',$this->Model->ModelResult);
	}
	
	/***--- POST ACTION SET ---***/
	// PAGE: get client announcement
	public function getann($DataNo){   
	  $this->Model->Get_Client_Post_Target($DataNo);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	
  }

?>  


