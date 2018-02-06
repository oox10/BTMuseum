<?php
  /*
  *   [RCDH10 Admin Module] - Customized Module
  *   2016 ed.  
  */
  
  /*
  *    NDAP Admin - Meta Admin Module
  *    詮釋資料建檔管理模組
  *      - Meta_Model.php
  *      -- SQL_AdMeta.php
  *      - admin_meta.html5tpl.php
  *      -- theme/css/css_meta_admin.css
  *      -- js_meta_admin.js  
  *      - admin_built.htmltpl.php
  *      -- theme/css/css_built_admin.css  
  *      -- js_built_admin.js  
  */
  
  
  class Meta_Controller extends Admin_Controller{
    
	public function __construct(){
	  parent::__construct();	
      $this->Model = new Meta_Model;
	}
	
	
	
	/***== [ Meta Admin Module ] 搜尋列表模組  ==***/
	/*------------------------------------------------------------------------------------------------------------------------*/
	
	
	// PAGE: 資料管理介面 O
	public function index($Page='1-50',$Search=''){
	  $this->Model->GetUserInfo();
	  $result = $this->Model->ADMeta_Process_Filter($Search);
	  $this->Model->ADMeta_Execute_Search($result['data']['esparams'],$Page);
	  $this->Model->ADMeta_Get_Page_List(5);
	  $this->Model->ADMeta_Get_Table_Fields(); // 取得欄位設定
	  $this->Model->ADMeta_Get_Folder_List();  // 設定工作資料夾
	  self::data_output('html','admin_meta',$this->Model->ModelResult);
	}
	
	// AJAX: 批次處理
	public function batch($Records,$Action,$Setting=''){
	  switch(strtolower($Action)){
        default: $this->Model->ADMeta_Execute_Batch($Records,strtolower($Action),$Setting); break;	  
	  }
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	 
	// AJAX: 批次匯出檔案預備
	public function batchexport($ExportEncodeString){
	  $this->Model->ADMeta_Export_Selected($ExportEncodeString);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 批次匯出下載
	public function getexport($ExportName){
	  $this->Model->ADMeta_Access_Export_File($ExportName);
	  self::data_output('file','',$this->Model->ModelResult);
	}
	
	// PAGE: 生成列印頁面
	public function printout($ExportEncodeString){
	  $this->Model->ADMeta_PrintOut_Selected($ExportEncodeString);
	  self::data_output('html','admin_printout',$this->Model->ModelResult);
	}
	
	
	
	
	/* [ 新增文物資料 ] */
	
	// AJAX: 新增文物
	public function volumenew(){
	  $this->Model->ADMeta_Volume_Create();
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 刪除文物
	public function volumedele($DataNo){
	  $this->Model->ADMeta_Volume_Delete($DataNo);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	
	
	/* [ 個人資料夾部分 ] */
	
	// AJAX: 切換資料夾
	public function folderatt($FolderId=''){
	  $forder_queue = isset($_SESSION[_SYSTEM_NAME_SHORT]['_ADMETA_FOLDERS']) ? $_SESSION[_SYSTEM_NAME_SHORT]['_ADMETA_FOLDERS'] : []; 
	  $this->Model->ADMeta_Folder_Switch($FolderId,$forder_queue);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 新增資料夾
	public function foldernew($FolderEncodeString=''){
	  $forder_queue = isset($_SESSION[_SYSTEM_NAME_SHORT]['_ADMETA_FOLDERS']) ? $_SESSION[_SYSTEM_NAME_SHORT]['_ADMETA_FOLDERS'] : []; 
	  $this->Model->ADMeta_Folder_Initial($FolderEncodeString,$forder_queue);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 刪除資料夾
	public function folderdel($FolderEncodeString=''){
	  $forder_queue = isset($_SESSION[_SYSTEM_NAME_SHORT]['_ADMETA_FOLDERS']) ? $_SESSION[_SYSTEM_NAME_SHORT]['_ADMETA_FOLDERS'] : []; 
	  $this->Model->ADMeta_Folder_Delete($FolderEncodeString,$forder_queue);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 加入資料夾
	public function folderadd($FolderEncodeString=''){
	  $forder_queue = isset($_SESSION[_SYSTEM_NAME_SHORT]['_ADMETA_FOLDERS']) ? $_SESSION[_SYSTEM_NAME_SHORT]['_ADMETA_FOLDERS'] : []; 
	  $this->Model->ADMeta_Folder_DataAdd($FolderEncodeString,$forder_queue);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 資料移出資料夾
	public function folderout($FolderEncodeString=''){
	  $forder_queue = isset($_SESSION[_SYSTEM_NAME_SHORT]['_ADMETA_FOLDERS']) ? $_SESSION[_SYSTEM_NAME_SHORT]['_ADMETA_FOLDERS'] : []; 	
	  $this->Model->ADMeta_Folder_Remove($FolderEncodeString,$forder_queue);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 儲存資料夾備註
	public function foldernote($FolderEncodeString=''){
	  $this->Model->ADMeta_Folder_Remark($FolderEncodeString);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	 
	 
	
	/***== [ Meta Admin Module ] 檔案編輯模組  ==***/
	/*------------------------------------------------------------------------------------------------------------------------*/
	
	// PAGE: 資料管理介面 O
	public function editor($DataNo){
	  $this->Model->GetUserInfo();
	  $result = $this->Model->ADMeta_Get_Editor_Resouse($DataNo);
	  $this->Model->ADMeta_Get_Table_Fields(); // 取得欄位設定
	  self::data_output('html','admin_built_document',$this->Model->ModelResult);
	}
	
	/*
	// PAGE: 資料管理介面 O
	public function review($DataNo){
	  $this->Model->GetUserInfo();
	  $this->Model->ADBuilt_Get_Task_Resouse($DataNo,'review');
	  $this->Model->ADBuilt_Get_Task_Element($DataNo);
	  self::data_output('html','admin_built',$this->Model->ModelResult);
	}
	*/
	
	// AJAX: 讀取卷資料 
	public function volumeread($DataStoreNo){  
	  $this->Model->ADBuilt_Get_Volume_Meta($DataStoreNo);   
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 儲存卷資料 
	public function volumesave($DataStoreNo,$DataJson){  
	  $action = $this->Model->ADBuilt_Save_Volume_Meta($DataStoreNo,$DataJson); //$_SESSION[_SYSTEM_NAME_SHORT]['METACOLLECTION']暫時沒用到
	  if($action['action']){
		$this->Model->ADMeta_Process_Meta_Update();  // 更新系統meta
		$this->Model->ADBuilt_Get_Volume_Meta($DataStoreNo);   
	  }
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 刪除件資料 
	public function volumeswitch($SystemId,$SwitchMethod,$UiConfigEncode){
	  $folder_config = isset($_SESSION[_SYSTEM_NAME_SHORT]['_ADMETA_FOLDERS'])?$_SESSION[_SYSTEM_NAME_SHORT]['_ADMETA_FOLDERS'] : []; 
	  
	  $this->Model->ADBuilt_Editor_Target_Switch($SystemId,$SwitchMethod,$folder_config,$UiConfigEncode);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	 
	// AJAX: 取得資料內容
	public function itemread($VolumnStoreNo,$ElementStoreNo){
	  $this->Model->ADBuilt_Get_Element_Data($VolumnStoreNo,$ElementStoreNo);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 儲存件資料 
	public function itemsave($VolumnStoreNo,$DataNo,$DataJson){  
	  $action = $this->Model->ADBuilt_Save_Element_Data($VolumnStoreNo,$DataNo,$DataJson); //$_SESSION[_SYSTEM_NAME_SHORT]['METACOLLECTION']暫時沒用到    
	  if($action['action']){
		$this->Model->ADMeta_Process_Meta_Update($action['data']['system_id']);  // 更新系統meta  
		$this->Model->ADBuilt_Get_Element_Data($VolumnStoreNo,$action['data']['source_id']);  
	  }
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 刪除件資料 
	public function itemdele($VolumnStoreNo,$ElementStoreNo){
	  $this->Model->ADBuilt_Dele_Element_Data($VolumnStoreNo,$ElementStoreNo);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 讀取歷史 
	public function history($DataNo){
	  $this->Model->ADMeta_Read_Item_Logs($DataNo);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 編輯暫存工具 
	public function keeper($Method,$DataIndex,$PaserData=''){
	  $this->Model->ADMeta_Editor_Keeper($Method,$DataIndex,$PaserData);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	
	
	
	
	
	// AJAX: 研究引用讀取 
	public function vrread($VolumnStoreNo,$DataNo){
	  $this->Model->ADBuilt_Research_Read($VolumnStoreNo,$DataNo);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 研究引用儲存 
	public function vrsave($VolumnStoreNo,$DataNo,$PaserData=''){
	  $result = $this->Model->ADBuilt_Research_Save($VolumnStoreNo,$DataNo,$PaserData);
	  if($result['action']){
		$this->Model->ADBuilt_Research_Read($VolumnStoreNo,$result['data']);  
	  }
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 研究引用刪除 
	public function vrdele($VolumnStoreNo,$DataNo){
	  $this->Model->ADBuilt_Research_Delete($VolumnStoreNo,$DataNo);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	
	// AJAX: 異動讀取 
	public function vmread($VolumnStoreNo,$DataNo){
	  $this->Model->ADBuilt_Movement_Read($VolumnStoreNo,$DataNo);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 異動儲存 
	public function vmsave($VolumnStoreNo,$DataNo,$PaserData=''){
	  $result = $this->Model->ADBuilt_Movement_Save($VolumnStoreNo,$DataNo,$PaserData);
	  if($result['action']){
		$this->Model->ADBuilt_Movement_Read($VolumnStoreNo,$result['data']['sno']);  
	  }
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 異動刪除 
	public function vmdele($VolumnStoreNo,$DataNo){
	  $this->Model->ADBuilt_Movement_Delete($VolumnStoreNo,$DataNo);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	
	// AJAX: 展覽讀取 
	public function vdread($VolumnStoreNo,$DataNo){
	  $this->Model->ADBuilt_Display_Read($VolumnStoreNo,$DataNo);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 展覽儲存 
	public function vdsave($VolumnStoreNo,$DataNo,$PaserData=''){
	  $result = $this->Model->ADBuilt_Display_Save($VolumnStoreNo,$DataNo,$PaserData);
	  if($result['action']){
		$this->Model->ADBuilt_Display_Read($VolumnStoreNo,$result['data']['sno']);  
	  }
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 展覽刪除 
	public function vddele($VolumnStoreNo,$DataNo){
	  $this->Model->ADBuilt_Display_Delete($VolumnStoreNo,$DataNo);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	
	
	
	
	
	
	/*
	// AJAX: 新增資料 
	public function newaitem($TaskNo,$DataJson){
	  $action = $this->Model->ADBuilt_Newa_Item_Data($TaskNo,$_SESSION[_SYSTEM_NAME_SHORT]['METACOLLECTION'],$DataJson);
	  if($action['action']){
		$this->Model->ADBuilt_Get_Item_Data($TaskNo, $action['data']);
	  }
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 完成資料 
	public function doneitem($TaskNo,$DataNo){
	  $this->Model->ADBuilt_Done_Item_Data($TaskNo,$DataNo);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	
	
	// AJAX: 完成任務 
	public function finish($TaskNo){
	  $this->Model->ADBuilt_Finish_Work_Task($TaskNo);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 退回任務 
	public function goback($TaskNo){
	  $this->Model->ADBuilt_Return_Work_Task($TaskNo);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
    
	// AJAX: 確認任務 
	public function checked($TaskNo){
	  $this->Model->ADBuilt_Checked_Work_Task($TaskNo);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	
	// FILE: 下載任務資料 
	public function export($TaskNoString){
	  $this->Model->ADBuilt_Export_Work_Task($TaskNoString);
	  self::data_output('xlsx','template_built_task_export.xlsx',$this->Model->ModelResult);
	}

	*/
	




	/* [ Dobj Control Module ] 數位檔案控制函數	*/
	/*------------------------------------------------------------------------------------------------------------------------*/
	
	// AJAX : 
	public function doedit($DataNo,$PageName,$ConfString){
	  $this->Model->ADMeta_DObj_Conf_Save($DataNo,$PageName,$ConfString);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX : 
	public function doview($DataNo,$PageName,$HideFlag){
	  $this->Model->ADMeta_DObj_Display_Switch($DataNo,$PageName,$HideFlag);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	
	
	
	
	/* [ Dobj Edit Module ] 控管數位檔案函數	*/
    /*------------------------------------------------------------------------------------------------------------------------*/
	
	// AJAX: 讀取數位設定檔
	public function doconf($DataType,$Folder){  
	  $action = $this->Model->ADMeta_Read_Dobj_Profile($DataType,$Folder);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 重新命名勾選數位檔
	public function dorename($DataType,$Folder,$FilePreName,$FileStartNum,$DataJson){  
	  $action = $this->Model->ADMeta_Dobj_Batch_Rename($DataType,$Folder,$FilePreName,$FileStartNum,$DataJson);
	  if($action['action']) $this->Model->ADMeta_Dobj_Buffer_Update($DataType,$Folder);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 重新排序數位檔
	public function doreorder($DataType,$Folder,$DataJson){  
	  $action = $this->Model->ADMeta_Dobj_Batch_Reorder($DataType,$Folder,$DataJson);
	  if($action['action']) $this->Model->ADMeta_Dobj_Buffer_Update($DataType,$Folder);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 刪除勾選數位檔
	public function dodele($DataType,$Folder,$DataJson,$Recapture=''){  
	  $verificationCode = isset($_SESSION['turing_string']) ? $_SESSION['turing_string'] : NULL;	
	  $action = $this->Model->ADMeta_Dobj_Batch_Delete($DataType,$Folder,$DataJson,$Recapture,$verificationCode);
	  if($action['action']) $this->Model->ADMeta_Dobj_Buffer_Update($DataType,$Folder);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 打包數位原始檔案
	public function doprepare($DataType,$Folder,$DoFileName){  
	  $action = $this->Model->ADMeta_Dobj_Prepare($DataType,$Folder,$DoFileName);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// File: 下載數位原始檔案
	public function dodownload($DownloadHash){  
	  $action = $this->Model->ADMeta_Dobj_Get_Download($DownloadHash);
	  self::data_output('file','',$this->Model->ModelResult); 
	}
	
	// File: 設定封面
	public function dosetcover($DataType,$Folder,$DoFileName){  
	  $action = $this->Model->ADMeta_Dobj_Set_Cover($DataType,$Folder,$DoFileName);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	
	
	
	
	
	
	/* [ File Upload Module ] 檔案上傳設定	*/
	/*------------------------------------------------------------------------------------------------------------------------*/
	
	// AJAX: 上傳檢查
	public function uplinit( $data ){
      $this->Model->ADMeta_Upload_Task_Initial($data); 
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 上傳圖片
	public function upldobj( $Zong, $FolderId, $TimeFlag , $DataPass ){
      $_FILES['file']['lastmdf'] = $_REQUEST['lastmdf'];
	  $this->Model->ADMeta_Uploading_Dobj( $Zong, $FolderId , $TimeFlag , $DataPass , $_FILES); 
	  self::data_output('upload','',$this->Model->ModelResult);
	}
	
	// AJAX: 上傳結束
	public function uplend( $Zong, $FolderId, $TimeFlag){
      $this->Model->ADMeta_Upload_Task_Finish($Zong , $FolderId, $TimeFlag); 
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 刪除上傳資料
	public function upldel( $PassData){
      $this->Model->ADMeta_Process_Upload_Delete($PassData); 
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 刪除上傳資料
	public function uplimport( $PassData){
      $this->Model->ADMeta_Process_Upload_Import($PassData); 
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	// POST: 更新議員頭像
	public function mbrpho($DataNo=''){
	  $this->Model->ADMeta_Upload_Member_Photo($DataNo,$_FILES);
	  self::data_output('html','admin_callback_reloadportrait',$this->Model->ModelResult); 
	}
	
  }
  
  
  
  
  
?>


