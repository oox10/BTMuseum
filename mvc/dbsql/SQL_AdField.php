<?php
  /*
  *   Admin Field SQL SET
  *  
  *
  */
  
  /* [ System Execute function Set ] */ 	
  
  class SQL_AdField{
	
   
	/***-- Admin Field SQL --***/
	
	//-- Admin Field : Get meta format List
	public static function GET_META_FORMATS(){
	  $SQL_String = "SELECT * FROM meta_format WHERE dbtable=:dbtable AND _keep=1 ORDER BY mfno ASC;";
	  return $SQL_String;
	}
	
	//-- Admin Field : Get meta format 
	public static function GET_TARGET_META_FORMAT(){
	  $SQL_String = "SELECT * FROM meta_format WHERE mfno=:mfno;";
	  return $SQL_String;
	}
	
	
	
    //-- Admin Field : Save meta format
	public static function UPDATE_META_FORMAT(){
	  $SQL_String = "UPDATE meta_format SET pattern=:pattern , can_export=:can_export , can_printout=:can_printout,_userupdate=:user WHERE mfno=:mfno;";
	  return $SQL_String;
	}	

	
	
	
  }
  
  
?>