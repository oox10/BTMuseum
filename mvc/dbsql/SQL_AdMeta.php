<?php
  
  /*
  *   [RCDH10 Admin Module] - Meta Sql Library 
  *   System Meta Admin SQL SET
  *
  *   2016-12-01 ed.  
  */
  
  /* [ System Execute function Set ] */ 	
  
  class SQL_AdMeta{
	
	
	/***-- Admin Meta SQL --***/  
	
	//-- Admin Meta :  get zong information
	public static function GET_ZONG_INFO( ){
	  $SQL_String = "SELECT * FROM meta_zong WHERE zclass=:zclass ORDER BY zorder ASC";
	  return $SQL_String;
	}
	
	//-- Admin Meta :  get zong meta
	public static function GET_ZONG_META( ){
	  $SQL_String = "SELECT meta_zong.*,meta_class.class_level FROM meta_zong LEFT JOIN meta_class ON zseries = class_code  WHERE zid=:zid";
	  return $SQL_String;
	}
	
	//-- Admin Meta :  get zong class
	public static function GET_ZONG_CLASS( ){
	  $SQL_String = "SELECT * FROM meta_class WHERE class_code=:zclass AND _keep=1";
	  return $SQL_String;
	}
	
	//-- Admin Meta :  get zong class sub level
	public static function GET_ZONG_LEVEL( ){
	  $SQL_String = "SELECT * FROM meta_class WHERE class_bind=:mcno AND _keep=1";
	  return $SQL_String;
	}
	
	
	//-- Admin Meta : get table descrip
	public static function GET_DBTABLE_DESCRIP($IdArray=array()){
	  $SQL_String = "select COLUMN_NAME,COLUMN_TYPE,COLUMN_COMMENT from INFORMATION_SCHEMA.`COLUMNS` WHERE TABLE_SCHEMA=:dbname AND TABLE_NAME=:table"; //
	  return $SQL_String;
	}
	
	//-- Admin Meta : update table descrip
	public static function SET_DBTABLE_SETFIELD($table='source_digiarchive',$field,$set_type='enum',$set_array=[]){
	  $SQL_String = "ALTER TABLE `".$table."` CHANGE COLUMN `".$field."` `".$field."` ".strtoupper($set_type)."('".join("','",$set_array)."') NOT NULL;"; //
	  return $SQL_String;
	}
	
	//-- Admin Meta : get table format table 
	public static function GET_TABLE_FORMAT(){
	  $SQL_String = "SELECT * FROM meta_format WHERE dbtable=:dbtable AND _active=1 AND _keep=1 ORDER BY mfno;"; //
	  return $SQL_String;
	}
	
	
	//-- Admin Meta : update table format table 
	public static function UPDATE_TABLE_FORMAT(){
	  $SQL_String = "UPDATE meta_format SET pattern=:pattern WHERE dbtable=:dbtable AND dbcolumn=:dbcolumn AND _active=1 AND _keep=1;";
	  return $SQL_String;
	}
	
	
	
	
	//-- Admin Meta :  get search meta list
	public static function GET_SEARCH_META($IdArray=array()){
	  $SQL_String = "SELECT * FROM metadata WHERE system_id=:system_id AND _keep=1"; //
	  return $SQL_String;
	}
	
	//-- Admin Meta :  get user meta selected     
	public static function GET_TARGET_META_SELECTED($MetaArray = array()){
	  $SQL_String = "SELECT * FROM metadata WHERE system_id IN('".join("','",$MetaArray)."') AND _keep=1;";
	  return $SQL_String;
	}  
	
	//-- Admin Meta : logs export record  註冊匯出序號
	public static function LOGS_META_EXPORT(){
	  $SQL_String = "INSERT INTO logs_export VALUES(NULL,:exportkey,:system_id,:meta_version,:user,NULL);";
	  return $SQL_String;
	}
	
	//-- Admin Meta :  get source meta format     
	public static function GET_DB_TABL_FORMAT(){
	  $SQL_String = "SELECT * FROM meta_format WHERE dbtable=:dbtable AND _active=1 AND _keep=1;";
	  return $SQL_String;
	}
	
	//-- Admin Meta : export meta current
	public static function GET_EXPORT_CURRENT_META(){
	  $SQL_String = "SELECT system_id,class,data_type,applyindex,source_json FROM logs_export LEFT JOIN metadata ON meta_id=system_id WHERE export_key=:key;";
	  return $SQL_String;
	}
	
	
	
   
	//-- Admin Built : get source meta
	public static function GET_SOURCE_META($DBTableName='source_digiarchive',$LinkField='store_no'){
	  $SQL_String = "SELECT * FROM ".$DBTableName."  WHERE ".$LinkField."=:id;";
	  return $SQL_String;
	}
	
	//-- Admin Built : get collection elements source_meta
	public static function GET_COLLECTION_ELEMENTS($DBTableName='source_digiarchive'){
	  $SQL_String = "SELECT * FROM ".$DBTableName." WHERE collection_id=:collection AND _metakeep=1 ORDER BY store_no ASC,seno ASC;";
	  return $SQL_String;
	}
	
	//-- Admin Built : get collection other link source 
	public static function GET_COLLECTION_LINK_META($DBTableName='source_research'){
	  $SQL_String = "SELECT * FROM ".$DBTableName." WHERE cid=:collection AND _keep=1 ";
	  return $SQL_String;
	}
	
	
	//-- Admin Built : get source meta from metadata
	public static function GET_SOURCE_FROM_METADATA($DBTableName='source_digiarchive',$LinkField='collection',$TableKey='store_no'){
	  $SQL_String = "SELECT ".$DBTableName.".*,system_id FROM metadata LEFT JOIN ".$DBTableName." ON ".$LinkField."=store_no WHERE ".$TableKey."=:id AND _keep=1 AND _metakeep=1;";
	  return $SQL_String;
	}
	
	
	//-- Admin Built : recount elements
	public static function UPDATE_VOLUME_ELEMENT_COUNT(){
	  $SQL_String = "UPDATE source_digiarchive SET count_element=(SELECT count(*) FROM source_digielement WHERE collection_id=:volume_id AND _metakeep=1) WHERE store_no=:volume_id;";
	  return $SQL_String;
	}
	
	//-- Admin Built : recount dofiles
	public static function UPDATE_VOLUME_DOFILE_COUNT(){
	  $SQL_String = "UPDATE source_digiarchive SET count_dofiles=:docount WHERE store_no=:volume_id;";
	  return $SQL_String;
	}
	
	
	
	//-- Admin Built : update task element data
	public static function UPDATE_SOURCE_META( $ModifyFields = array(1) , $DBTableName='source_digielement' ,$DataIdField='store_no'){
	  $condition = array();
	  foreach($ModifyFields as $field){
	    $condition[] = $field.'=:'.$field;
	  }
	  $SQL_String = "UPDATE ".$DBTableName." SET ".join(',',$condition)." WHERE ".$DataIdField."=:id;";
	  
	  //$SQL_String = "UPDATE metadata SET ".join(',',$condition)." WHERE system_id=:sid AND _keep=1;";
	  return $SQL_String;
	}
	
	
	//-- Admin Built : update task element data
	public static function UPDATE_METADATA_DATA( $MmodifyFields=array(1) , $IdField='system_id' ){
	  $condition = array();
	  foreach($MmodifyFields as $field){
	    $condition[] = $field.'=:'.$field;
	  }
	  $SQL_String = "UPDATE metadata SET ".join(',',$condition)." WHERE ".$IdField."=:sid AND _keep=1;";
	  return $SQL_String;
	}
	
	//-- Admin Built : get task element list
	public static function GET_TARGET_ELEMENT(){
	  $SQL_String = "SELECT * FROM meta_builtitem WHERE taskid=:taskid AND itemid=:itemid AND _keep=1 ;";
	  return $SQL_String;
	}
	
	
	//-- Admin Built : create new volume
	public static function INSERT_VOLUME_DATA( ){
	  $SQL_String = "INSERT INTO source_digiarchive VALUES(null, :class, :zong, :fonds, '', '', '', '', '', '', '','', '新增文物', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '' ,0, '', '', '',0 , 0, '新增' ,0 ,0 ,1 ,'北投文物館' ,'".date('Y-m-d H:i:s')."', :user, NULL, '', 1)";
	  return $SQL_String;
	}
	
	
	//-- Admin Built : create new element
	public static function INSERT_ELEMENT_DATA( ){
	  $SQL_String = "INSERT INTO source_digielement VALUES(null, :collection_id, '' , '', '', '', '', '', '', '', '', '', '不開放', 0, 0, 0, '北投文物館', '".date('Y-m-d H:i:s')."', :user, NULL, '', 1)";
	  return $SQL_String;
	}
	
	//-- Admin Built : create new meta
	public static function INSERT_NEW_METADATA(){
	  $SQL_String = "INSERT INTO metadata VALUES (NULL,:class,:zong,:data_type,:collection,:identifier,:applyindex,:source_json,:search_json,:dobj_json,:refer_json,:page_count,NULL,'RCDHMEDS','RCDHMEDS','".date('Y-m-d H:i:s')."',:lockmode,:auditint,:checked,:digited,:open,:view,0,0,1);";
	  return $SQL_String;
	}
	
	
	//-- Admin Built : mark meta to delete 
	public static function MARK_DB_DELETE($DBTableName='source_digiarchive',$LinkField='collection',$TableKey='store_no' ){
	  $SQL_String = "UPDATE metadata LEFT JOIN ".$DBTableName." ON ".$LinkField."=store_no SET ".$DBTableName."._metakeep=0,".$DBTableName."._userupdate=:user,metadata._index=0,metadata._keep=0 WHERE ".$TableKey."=:id AND _keep=1;";
	  return $SQL_String; 
	}
	
	//-- Admin Built : restore meta from delete 
	public static function UNDO_DB_DELETE($DBTableName='source_digiarchive',$LinkField='collection',$TableKey='store_no' ){
	  $SQL_String = "UPDATE metadata LEFT JOIN ".$DBTableName." ON ".$LinkField."=store_no SET ".$DBTableName."._metakeep=1,metadata._index=1,metadata._keep=1 WHERE ".$TableKey."=:id AND _keep=0;";
	  return $SQL_String; 
	}
	
	//-- Admin Built : Delete Metadata 
	public static function DELETE_MARK_METADATA( ){
	  $SQL_String = "DELETE FROM metadata WHERE system_id=:sid AND _keep=0;";
	  return $SQL_String;
	}
	
	
	
	//-- Admin Meta :  logs meta modify   
	public static function LOGS_META_MODIFY(){
	  $SQL_String = "INSERT INTO logs_metaedit VALUES (NULL,NULL,:zong,:sysid,:identifier,:method,:source,:update,:user,:result);";
	  return $SQL_String;
	}  
	
	
	
	
	//-- Admin Meta : Metadata edit logs 
	public static function GET_TARGET_META_LOGS( ){
	  $SQL_String = "SELECT * FROM logs_metaedit WHERE identifier=:storeno ORDER BY mmno DESC;";
	  return $SQL_String;
	}
	
	
	//-- Admin Built : get metadata _index = 0
	public static function GET_RENEW_META(){
	  $SQL_String = "SELECT * FROM metadata WHERE _index=0 ORDER BY class ASC,zong ASC,collection ASC,identifier ASC;";   
	  return $SQL_String;
	}
	
	//-- Admin Built : get metadata _index = 0
	public static function GET_RENEW_TARGET(){
	  $SQL_String = "SELECT * FROM metadata WHERE system_id=:id AND _index=0 ORDER BY class ASC,zong ASC,collection ASC,identifier ASC;";   
	  return $SQL_String;
	}
	
    //-- Admin Built : newa source_research 
	public static function NEWA_RESEARCH_RECORD(){
	  $SQL_String = "INSERT INTO source_research VALUES(NULL,:cid,:author,:pubyear,:title,:source,:publisher,:user,null,1);";   
	  return $SQL_String;
	} 
	 
	//-- Admin Built : save source_research 
	public static function SAVE_RESEARCH_RECORD(){
	  $SQL_String = "UPDATE source_research SET author=:author,pubyear=:pubyear,title=:title,source=:source,publisher=:publisher,_userupdate=:user WHERE srno=:srno AND cid=:cid AND _keep=1;";   
	  return $SQL_String;
	} 
	 
	//-- Admin Built : read source_research 
	public static function READ_RESEARCH_RECORD(){
	  $SQL_String = "SELECT * FROM source_research WHERE cid=:cid AND srno=:srno AND _keep=1;";   
	  return $SQL_String;
	}
	 
	//-- Admin Built : delete source_research 
	public static function DELE_RESEARCH_RECORD(){
	  $SQL_String = "UPDATE source_research SET _keep=0 WHERE cid=:cid AND srno=:srno AND _keep=1;";   
	  return $SQL_String;
	}
	
	
	//-- Admin Built : newa source_movement
	public static function NEWA_MOVEMENT_RECORD(){
	  $SQL_String = "INSERT INTO source_movement VALUES(NULL,:cid,:move_type,:move_date,:move_location,:move_reason,:move_handler,:user,NULL,1);";   
	  return $SQL_String;
	} 
	 
	//-- Admin Built : save source_movement 
	public static function SAVE_MOVEMENT_RECORD(){
	  $SQL_String = "UPDATE source_movement SET move_type=:move_type,move_date=:move_date,move_location=:move_location,move_reason=:move_reason,move_handler=:move_handler,_userupdate=:user WHERE smno=:smno AND cid=:cid AND _keep=1;";   
	  return $SQL_String;
	} 
	 
	//-- Admin Built : read source_movement 
	public static function READ_MOVEMENT_RECORD(){
	  $SQL_String = "SELECT * FROM source_movement WHERE cid=:cid AND smno=:smno AND _keep=1;";   
	  return $SQL_String;
	}
	 
	//-- Admin Built : delete source_movement 
	public static function DELE_MOVEMENT_RECORD(){
	  $SQL_String = "UPDATE source_movement SET _keep=0 WHERE cid=:cid AND smno=:smno AND _keep=1;";   
	  return $SQL_String;
	}
	
	//-- Admin Built : count source_movement 
	public static function COUNT_MOVEMENT_RECORD(){
	  $SQL_String = "SELECT count(*) FROM source_movement WHERE cid=:cid AND _keep=1;";   
	  return $SQL_String;
	}
	
	
	//-- Admin Built : newa source_display
	public static function NEWA_DISPLAY_RECORD(){
	  $SQL_String = "INSERT INTO source_display VALUES(NULL,:cid,:display_date,:display_topic,:display_place,:display_organ,:user,NULL,1);";   
	  return $SQL_String;
	} 
	 
	//-- Admin Built : save source_display 
	public static function SAVE_DISPLAY_RECORD(){
	  $SQL_String = "UPDATE source_display SET display_date=:display_date,display_topic=:display_topic,display_place=:display_place,display_organ=:display_organ,_userupdate=:user WHERE sdno=:sdno AND cid=:cid AND _keep=1;";   
	  return $SQL_String;
	} 
	 
	//-- Admin Built : read source_display 
	public static function READ_DISPLAY_RECORD(){
	  $SQL_String = "SELECT * FROM source_display WHERE cid=:cid AND sdno=:sdno AND _keep=1;";   
	  return $SQL_String;
	}
	 
	//-- Admin Built : delete source_display 
	public static function DELE_DISPLAY_RECORD(){
	  $SQL_String = "UPDATE source_display SET _keep=0 WHERE cid=:cid AND sdno=:sdno AND _keep=1;";   
	  return $SQL_String;
	}
	
	//-- Admin Built : count source_display 
	public static function COUNT_DISPLAY_RECORD(){
	  $SQL_String = "SELECT count(*) FROM source_display WHERE cid=:cid AND _keep=1;";   
	  return $SQL_String;
	}
	
	
	
	
	
	
	
	
	
	
	/*
	//-- Admin Built :  set target task handler   
	public static function SET_TARGET_TASK_HANDLER(){
	  $SQL_String = "UPDATE meta_builttask SET handler=:handler,_status=:status WHERE task_no=:id AND _keep=1 ;";
	  return $SQL_String;
	}
	
	//-- Admin Built : get task element list
	public static function GET_TARGET_TASK_ELEMENTS(){
	  $SQL_String = "SELECT * FROM meta_builtitem WHERE taskid=:taskid AND _keep=1 ORDER BY itemid ASC,mbio ASC;";
	  return $SQL_String;
	}
	
	//-- Admin Built : get task element list
	public static function GET_TASKS_ELEMENTS_EXPORT($TasksId = array()){
	  $SQL_String = "SELECT * FROM meta_builtitem WHERE taskid IN('".join("','",$TasksId)."') AND _keep=1 ORDER BY taskid ASC, itemid ASC;";
	  return $SQL_String;
	}
	
	
	//-- Admin Built : finish all element
	public static function FINISH_TASK_ELEMENTS(){
	  $SQL_String = "UPDATE meta_builtitem SET _estatus='_finish' WHERE taskid=:taskid AND _keep=1;";
	  return $SQL_String;
	}
	
	
	//-- Admin Built : update task status
	public static function UPDATE_TASK_STATUS(){
	  $SQL_String = "UPDATE meta_builttask SET element_count=(SELECT count(*) FROM meta_builtitem WHERE taskid=:taskid AND _estatus='_finish' AND _keep=1),_status=:status WHERE task_no=:taskid;";
	  return $SQL_String;
	}
	*/
	
	
	
	//-- Admin Meta : get meta can edit field
	public static function ADMIN_META_GET_META_EDIT_DATA(){
	  $SQL_String = "SELECT system_id,collection,identifier,json_string,_open,_view,_onland FROM metadata WHERE identifier=:id AND _keep=1 ;";
	  return $SQL_String;
	} 
	
	
	//-- Admin Meta :  get target meta do config field  
	public static function ADMIN_META_GET_DOBJ_FIELD(){
	  $SQL_String = "SELECT system_id,data_type,dobj_json FROM metadata WHERE identifier=:id AND _keep=1 ;";
	  return $SQL_String;
	}
	
	
	 
	
	
	/***--  使用者上傳 SQL SET  --***/
	
	//- check upload file exist
	//  _upload : 完成上傳
	//  _archived : 完成導入
	public static function CHECK_FILE_UPLOAD_LIST(){
	  $SQL_String = "SELECT folder,_regist FROM system_upload WHERE hash=:hash AND _upload!='' AND _archived!='';";
	  return $SQL_String;
	}
	
	//- regist upload file 
	public static function REGIST_FILE_UPLOAD_RECORD(){
	  $SQL_String = "INSERT INTO system_upload VALUES(NULL,:utkid,:folder,:flag,:user,:hash,:store,:saveto,:name,:size,:mime,:type,:dotag,:last,'".date('Y-m-d H:i:s')."','','','','[]',1);";
	  return $SQL_String;
	}
	
	//- update upload state  
	public static function UPDATE_FILE_UPLOAD_UPLOADED(){
	  $SQL_String = "UPDATE system_upload SET _upload='".date('Y-m-d H:i:s')."' WHERE urno=:urno;";
	  return $SQL_String;
	}
	
	
	//- process upload : get upload file  
	public static function SELECT_TARGET_UPLOAD_FILE(){
	  $SQL_String = "SELECT * FROM system_upload WHERE urno=:urno AND _keep=1;";
	  return $SQL_String;
	}
	
	//- process upload : delete upload file   
	public static function DELETE_TARGET_UPLOAD_FILE(){
	  $SQL_String = "UPDATE system_upload SET _keep=0 ,_process=:process , _logs=:logs WHERE urno=:urno;";
	  return $SQL_String;
	}
	
	
	//-- regist system task 
	public static function REGIST_SYSTEM_TASK(){
	  $SQL_String = "INSERT INTO system_tasks VALUES (NULL,:user,:task_name,:task_type,:task_num,:task_done,:time_initial,'0000-00-00 00:00:00','','',1);";
	  return $SQL_String;
	}
	
	//-- bind photo process task 
	public static function BIND_UPLOAD_TO_TASK(){
	  $SQL_String = "UPDATE system_upload SET utkid=:utkid,_logs=:logs WHERE urno=:urno;";
	  return $SQL_String;
	}
	
	
	
	//- update upload process  
	public static function UPDATE_FILE_UPLOAD_PROCESSED(){
	  $SQL_String = "UPDATE system_upload SET _process='".date('Y-m-d H:i:s')."',_archived=:archive,_logs=:logs WHERE urno=:urno;";
	  return $SQL_String;
	}
	
	//-- get uploaded file list 
	public static function SELECT_UPLOAD_OBJECT_LIST(){
	  $SQL_String = "SELECT * FROM system_upload WHERE user=:user AND folder=:folder AND flag=:flag AND _upload!='' AND _process='';";
	  return $SQL_String;
	}
	
	//-- finish folder upload state 
	public static function FINISH_USER_UPLOAD_TASK(){
	  $SQL_String = "UPDATE system_upload SET uploadtime='',_uploading=0 WHERE owner=:uno AND ufno=:ufno;";
	  return $SQL_String;
	}
	
	//-- Admin Meta :  get dobj download resouce   
	public static function DOBJ_DOWNLOAD_RESOUCE(){
	  $SQL_String = "SELECT * FROM logs_digitalobject WHERE action=:action AND note=:hash AND _user=:user;";
	  return $SQL_String;
	}  
	
	//-- Admin Meta :  logs do modify   
	public static function LOGS_DOBJ_MODIFY(){
	  $SQL_String = "INSERT INTO logs_digitalobject VALUES (NULL,NULL,:file,:action,:store,:note,:user);";
	  return $SQL_String;
	}  
	
	
	
	
	
	
  }

?>