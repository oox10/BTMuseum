<?php

  /***
  *   TLCDA Admin System Main SQL SET
  *  
  *
  */
  
  
  class SQL_Admin{
	
	/* [ System Execute function Set ] */ 	
	
	
	
	
	/***-- System Work Sqls --***/
	//-- 系統紀錄 
	public static function SYSTEM_LOGS_USED_ACTION(){
	  $SQL_String = "INSERT INTO logs_system VALUES(NULL,NULL,:acc_ip,:acc_act,:acc_url,:session,:request,:acc_from,:result,:agent);";
	  return $SQL_String;
	}
	
	
	// 查詢 system_info 表取得相關 info
	public static function SELECT_INDEX_POSTS(){
	  $SQL_String = "SELECT pno,post_type,post_from,post_level,post_time_start,post_title,post_content FROM system_post WHERE post_to='管理系統' AND post_display=1 AND post_keep=1 AND ( (NOW() BETWEEN post_time_start AND post_time_end ) OR post_level=4 ) ORDER BY post_level DESC,post_time_start DESC,pno DESC;";
	  return $SQL_String;
	}
	
	
    /***-- System Main Page Sqls --***/
	
	// 查詢系統總資料空間
	public static function SELECT_ALL_DATA_STORE(){
	  $SQL_String = "SELECT EXTRACT(YEAR_MONTH FROM _timeupdate) AS stage,sum(count_dofiles) as total_size,count(*) AS count ,fonds FROM source_digiarchive WHERE 1 GROUP BY stage ORDER BY stage ASC;";
	  return $SQL_String;
	}
	
	
	// 查詢系統總數
	public static function SELECT_ARCHIVE_ZONG_COUNT(){
	  $SQL_String = "SELECT EXTRACT(YEAR_MONTH FROM _timeupdate) AS stage,sum(count_dofiles) as total_size,count(*) AS count ,fonds FROM source_digiarchive WHERE 1 GROUP BY fonds ORDER BY count DESC;";
	  return $SQL_String;
	}
	
	
	
	/***-- System Module Config Sqls --***/
	
	// 
	public static function UPDATE_MODULE_CONFIG(){
	  $SQL_String = "UPDATE system_config SET setting=:setting,_update_user=:user WHERE module=:module AND field=:field AND _keep=1;";
	  return $SQL_String;
	}
	
	//-- Client Post:  get post user select 
	public static function GET_CLIENT_POST_TARGET(){
	  $SQL_String = "SELECT pno,post_type,post_from,post_level,post_time_start,post_title,post_content,post_hits FROM system_post WHERE post_to IN('管理系統','所有系統') AND post_display=1 AND post_keep=1 AND ( (NOW() BETWEEN post_time_start AND post_time_end ) OR post_level=4 ) AND pno=:pno;";
	  return $SQL_String;
	}
	
	//-- Client Post:  update post hits 
	public static function CLIENT_POST_HITS(){
	  $SQL_String = "UPDATE system_post SET post_hits=(post_hits+1) WHERE pno=:pno;";
	  return $SQL_String;
	}
	
  }
  
  
?>