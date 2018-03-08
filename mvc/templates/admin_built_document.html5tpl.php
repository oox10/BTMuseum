<!DOCTYPE HTML>
<html>
  <head>
    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" >
	<title><?php echo defined('_SYSTEM_HTML_TITLE') ? _SYSTEM_HTML_TITLE : 'RCDH System'; ?></title>
	
	<!-- CSS -->
	<link type="text/css" href="tool/jquery-ui-1.12.1.custom/jquery-ui.structure.min.css" rel="stylesheet" />
	<link type="text/css" href="tool/jquery-ui-1.12.1.custom/jquery-ui.theme.min.css" rel="stylesheet" />
	<link type="text/css" href="tool/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet" />
	
	<!-- JS -->
	<script type="text/javascript" src="tool/jquery-3.2.1.min.js"></script>
	<script type="text/javascript" src="tool/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
	<script type="text/javascript" src="tool/canvasloader-min.js"></script>	
	<script type="text/javascript" src="tool/html2canvas.js"></script>	  
	
	
	<!-- jquery lazyload -->
	<script type="text/javascript" src="tool/lazy-load-xt-master/src/jquery.lazyloadxt.js"></script>
	
	<link type="text/css" href="tool/jquery.scrollbar/jquery.scrollbar.css" rel="stylesheet" />
	<script type="text/javascript" src="tool/jquery.scrollbar/jquery.scrollbar.min.js"></script>
	
	<!-- fabric canves library -->
	<script type="text/javascript" src="tool/fabric.min.js"></script>
	
	<!-- dropzone file uoloader -->
	<script type="text/javascript" src="tool/dropzone-4.2.0/dropzone.min.js"></script>
	
	<!-- dropzone file uoloader -->
	<script type="text/javascript" src="tool/dropzone-4.2.0/dropzone.min.js"></script>
	
	<script type="text/javascript" src="tool/jquery-mousewheel-3.1.13/jquery.mousewheel.min.js"></script>
	
	<!-- Self -->
	<link rel="stylesheet" type="text/css" href="theme/css/css_default.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_main.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_ad10.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_built_admin.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_built_document.css" />
	
    <script type="text/javascript">
    //-- fast key initial
    var FLAG_SYSTEM_HOTKEY_START = false;  // 快速鍵啟動flag
    var FLAG_SYSTEM_EDITOR_LEVEL = 1;      // 目前所在的編輯階層
  
    var FLAG_MOUSE_ON_DOVIEW = false;
    var FLAG_MOUSE_IN_USED   = false; 
	var FLAG_MOUSE_TO_MOVE	 = false;  // 目前是否正在移動顯示物件
	var FLAG_MARK_ON_DOVJ	 = false;
    </script>

	
	<script type="text/javascript" src="js_library.js"></script>
	<script type="text/javascript" src="js_admin.js"></script>
	<script type="text/javascript" src="js_built_dobjad.js"></script>
	<script type="text/javascript" src="js_built_document.js"></script>
	
	
	<!-- PHP -->
	<?php
	$user_info 		= isset($this->vars['server']['data']['user']) 		? $this->vars['server']['data']['user'] 	: array('user'=>array('user_name'=>'Anonymous'),'group'=>array());
	$page_info 		= isset($this->vars['server']['info']) ? $this->vars['server']['info'] : '';  
	
	$edit_mode  	= isset($this->vars['server']['data']['resouse']['edit_mode']) 		? $this->vars['server']['data']['resouse']['edit_mode'] : 'edit';  
	$edit_form  	= isset($this->vars['server']['data']['resouse']['form_mode']) 		? $this->vars['server']['data']['resouse']['form_mode'] : '009';  
	
	$meta_activeid  = isset($this->vars['server']['data']['resouse']['active_systemid'])? $this->vars['server']['data']['resouse']['active_systemid'] : 0; 
	$meta_collect   = isset($this->vars['server']['data']['resouse']['meta_collection'])? $this->vars['server']['data']['resouse']['meta_collection'] : [];  
	$meta_element   = isset($this->vars['server']['data']['resouse']['meta_elements']) 	? $this->vars['server']['data']['resouse']['meta_elements'] : []; 
	$editor_config  = isset($this->vars['server']['data']['resouse']['editor_config']) 	? $this->vars['server']['data']['resouse']['editor_config'] : []; 
	
	/*
	$meta_research   = isset($this->vars['server']['data']['resouse']['meta_research']) 	? $this->vars['server']['data']['resouse']['meta_research'] : []; 
	$meta_movement   = isset($this->vars['server']['data']['resouse']['meta_movement']) 	? $this->vars['server']['data']['resouse']['meta_movement'] : []; 
	$meta_display   = isset($this->vars['server']['data']['resouse']['meta_display']) 	? $this->vars['server']['data']['resouse']['meta_display'] : []; 
	*/
	
	
	$fields_config  = isset($this->vars['server']['data']['dbfield']) 	? $this->vars['server']['data']['dbfield'] : ['volume'=>[],'element'=>[]]; //欄位設定
	 
	$dobj_conf  	= isset($this->vars['server']['data']['resouse']['dobj_config'])	? $this->vars['server']['data']['resouse']['dobj_config'] : array();  
	
	// 檢查權限
	$user_admin    = false;
	$user_roles    = '';
	foreach($user_info['group'] as $gset){
	  if($gset['now']){
		$user_roles =  join(',',$gset['roles']); 
	    if(isset($gset['roles']['R00']) ||  (isset($gset['roles']['R02']) && $gset['roles']['R02']==2 ) ){
		  $user_admin = true;	
		}
	  }
	}
	
	?>
	
	<meta id='SYSTEMID' data-set='<?php echo $meta_activeid;?>' ></meta>
	<meta id='VOLUMEID' data-set='<?php echo $dobj_conf['folder'];?>' ></meta>
	<meta id='DATAROOT' data-set='<?php echo $dobj_conf['root'];?>' ></meta>
	<meta id='DOFOLDER' data-set='<?php echo $dobj_conf['folder'];?>'  ></meta>
    
	<!-- 資料儲存 -->
	<data  id='taskid' data-refer='<?php //echo $meta_task['task_no']?>'></data>
	<data  id='collection_meta' data-refer='<?php //echo $meta_collect;?>' ></data>
    		  
  </head>
  
  <body hotkey=0 >
	<div class='system_main_area'>
	  <div class='system_manual_area'>
	  <?php include('area_admin_manual.php'); ?>
	  </div>
	  <div class='system_content_area wide_mode'>
        <div class='tool_banner' >
		</div>
		
		
		<div class='main_content' >
		   
		  <!-- 資料編輯區 -->
		  <div class='module_container' id='admeta_module' mode='mtedit' move='0'  >	  
			  
			<div class='block_wrapper'> 
			  
			  
			  <!-- 卷資料 -->
			  <div class='data_record_block' id='record_master'  >
				<header class='record_header'>
				  <span class='option md_anchor' ><i class="fa fa-thumb-tack" aria-hidden="true"></i><i class="fa fa-arrows-alt" aria-hidden="true"></i></span>
				  <h1 class='record_title'>
				    <label>資料管理：</label><i class='_variable' id='' ><?php echo $meta_collect['META-V-store_no']; ?></i>
				  </h1>
				  <span  class='record_switch'> 
				    <button class='act_switch_editor dofunc' to='prev'  title='切換上一筆'> <i class="fa fa-chevron-left" aria-hidden="true"></i> 上一筆 <b>z</b></button>
				    <button class='act_switch_editor dofunc' to='next'	title='切換下一筆'> 下一筆 <i class="fa fa-chevron-right" aria-hidden="true"></i> <b>c</b></button>
				  </span>
				  <span  class='record_tasks'>  
					<button class='dofunc ' id='act_create_volume_meta'	title='新增文物資料'><i class="fa fa-plus" aria-hidden="true"></i></button>	
					<button class='dofunc ' id='act_print_volume_meta'	title='列印資料頁面'><i class="fa fa-print" aria-hidden="true"></i></button>	
					<button class='dofunc ' id='act_save_volume_meta'	title='儲存文物資料'><i class="fa fa-floppy-o" aria-hidden="true"></i><b><i class="fa fa-times" aria-hidden="true"></i></b></button>	
				  </span>
				</header> 
				<div class='record_body'>
				  
				  <?php 
				  $volume_switch_target = isset($editor_config['uiconfig']['volume_forms_switcher']) ? $editor_config['uiconfig']['volume_forms_switcher']:'volume_meta';
				  ?>	
				  <ul class='meta_group_switch _uikeep' id='volume_forms_switcher' >
					<li class='meta_group_sel option <?php echo $volume_switch_target=='volume_meta'?'_atthis':''?>' data-group='volume_meta'  > 主要欄位 </li>
					<li class='meta_group_sel option <?php echo $volume_switch_target=='element_list'?'_atthis':''?>' data-group='element_list' > 影像目錄 </li>
					<li class='meta_group_sel option <?php echo $volume_switch_target=='research_meta'?'_atthis':''?>' data-group='research_meta' > 研究引用 </li>
					<li class='meta_group_sel option <?php echo $volume_switch_target=='movement_meta'?'_atthis':''?>' data-group='movement_meta' > 異動紀錄 </li>
					<li class='meta_group_sel option <?php echo $volume_switch_target=='display_meta'?'_atthis':''?>' data-group='display_meta' >  展覽紀錄</li>	
					<li class='meta_group_sel option <?php echo $volume_switch_target=='_all'?'_atthis':''?>' data-group='_all' > 顯示全部欄位 </li> 
				  </ul>	 	
				  
				   <!-- 卷層級欄位 -->
				  <div class='form_group meta_group_block <?php echo $volume_switch_target=='volume_meta' || $volume_switch_target=='_all' ?'_display':''?>' id='volume_meta' >
                   					
                    <div class='system_meta' > <!-- 預設欄位 -->
					    
						<div class='field_set'>    
						  <div class='data_col ' id='meta_field_volume_no'> 
						    <label class='data_field _must' > 系統號 </label>
						    <div class='data_value _must ' >   
							  <input type='text' class='_volume _variable _must' id='META-V-store_no' default='' readonly=true value='<?php echo $meta_collect['META-V-store_no'];?>' />
						    </div>
						  </div>
						  <div class='data_col ' id='meta_field_store_no_set' > 
						    <label class='data_field ' > 文物編號<i style='font-size:0.8em;'></i> </label>
						    <div class='data_value _bundle _must' id='store_no_field' >   
							  <input type='text' class='_volume _variable _update _must' id='META-V-store_year'	default=''  value='<?php echo $meta_collect['META-V-store_year'];?>' /> - 
							  <input type='text' class='_volume _variable _update _must' id='META-V-store_type'	default=''  value='<?php echo $meta_collect['META-V-store_type'];?>' /> - 
							  <input type='text' class='_volume _variable _update _must' id='META-V-store_no1'	default=''  value='<?php echo $meta_collect['META-V-store_no1'];?>' /> - 
							  <input type='text' class='_volume _variable _update _must' id='META-V-store_no2'	default=''  value='<?php echo $meta_collect['META-V-store_no2'];?>' /> - 
						      <input type='text' class='_volume _variable _update' id='META-V-store_no3'	default=''  value='<?php echo $meta_collect['META-V-store_no3'];?>' />
							  ：
							</div>
						  </div>
						  <div class='data_col ' id='meta_field_store_no_value'> 
						    <label class='data_field _must' > 典藏號 </label>
						    <div class='data_value _must _bundle' >   
							  <input type='text' class='_volume _variable _update _must' id='META-V-store_id' default='' readonly=true value='<?php echo $meta_collect['META-V-store_id'];?>' />
						    </div>
						  </div>
						  
					    </div>
						
						<!--
						<div class='field_set'>   
						  <div class='data_col _flat'  title='開放檢索'> 
							<label class='data_field _necessary'>資料檢索</label>
							<div class='data_value'>   
							  <label class="switch">
								<input type="checkbox" class="boolean_switch _volume _variable _update _flag" name="META-V-_flag_open" id='META-V-_flag_open' data-save="1" data-default="0" <?php echo intval($meta_collect['META-V-_flag_open'])? 'checked' : '';?> />
								<div class="slider round"></div>
							  </label>
							  <span>開放/不開放</span>
							</div>
						  </div>
						  
						  <div class='data_col _flat' id='' > 
							<label>檔案閱覽</label>
							<div class='data_value'>   
							  <select id='META-V-_view' class='_volume _variable _update'>
								<option value='開放'	<?php echo $meta_collect['META-V-_view']=='開放'	? 'selected':'';?>   >開放閱覽 </option>
								<option value='館內'	<?php echo $meta_collect['META-V-_view']=='館內'	? 'selected':'';?>   >館內閱覽 </option>
								<option value='調閱'	<?php echo $meta_collect['META-V-_view']=='調閱'	? 'selected':'';?>   >到館調閱 </option>
								<option value='不開放'	<?php echo $meta_collect['META-V-_view']=='不開放'	? 'selected':'';?>   >不開放閱覽 </option>
							  </select>  
							</div>
						  </div>
						  
					    </div>
						-->
					</div>
					
				    <div class='search_meta' > <!-- 資料欄位 -->
					  
					  <div class='field_set'>  
						  <div class='data_col' id='meta_field_title' > 
							<label class='data_field _must'>品名</label>
							<div class='data_value'>   
							  <input type='text'  class='_volume _variable _update _must' name="META-V-title" id='META-V-title'  placeholder='名稱' value='<?php echo $meta_collect['META-V-title']; ?>'  />
							</div>
						  </div>
						  <div class='data_col ' id='meta_field_store_orl'> 
						    <label class='data_field' > 原編號 </label>
						    <div class='data_value' >   
							  <input type='text' class='_volume _variable' id='META-V-store_orl' default='' readonly=true value='<?php echo $meta_collect['META-V-store_orl'];?>' />
						    </div>
						  </div>
					  </div>
					  
					  <div class='field_set'> 
					    <div class='data_col' id='' > 
					      <label class='data_field '>尺寸</label>
					      <div class='data_value '>   
					        <input type='text' class='_volume _variable _update' name="META-V-size_info" id='META-V-size_info' value='<?php echo $meta_collect['META-V-size_info']; ?>' />
						  </div>
					    </div>
						<div class='data_col' id='' > 
					      <label class='data_field '>所屬年代</label>
					      <div class='data_value '>   
					        <input type='text' class='_volume _variable _update' name="META-V-period" id='META-V-period' value='<?php echo $meta_collect['META-V-period']; ?>' />
						  </div>
					    </div>
						<div class='data_col' id='' > 
					      <label class='data_field '>入藏年代</label>
					      <div class='data_value '>   
					        <input type='text' class='_volume _variable _update' name="META-V-saved_year" id='META-V-saved_year' value='<?php echo $meta_collect['META-V-saved_year']; ?>' />
						  </div>
					    </div>
					  </div>
					  
					  <div class='data_col' id='' > 
					    <label class='data_field '>類別</label>
					    <div class='data_value '> 
                          <?php if(isset( $fields_config['volume']['categories']) && $fields_config['volume']['categories']['pattern']): ?>
						  <ul class='value_set' >
						  <?php $value_sets = explode(';',$fields_config['volume']['categories']['pattern']);?>
						  <?php foreach($value_sets as $item): ?> 
						  <li> <input type='checkbox' class='_volume _variable _update' value='<?php echo $item; ?>' name='META-V-categories' ><?php echo $item; ?></li>
						  <?php endforeach; ?>
						  <li> 
						    <input type='checkbox' class='_volume _variable _update' value='_newa' name='META-V-categories' >
						    <input type='text'     class='_volume _variable' value=''      name='META-V-categories_newa' placeholder='新增類別' >   
						  </li>
						  </ul>
                          <?php else: ?>
                          <input type='text' class='_volume _variable _update' name="META-V-categories" id='META-V-categories' value='<?php echo $meta_collect['META-V-categories']; ?>' /> 
						  <?php endif; ?>
						</div>
					  </div>
					  
					  <div class='data_col _flat' id='' > 
					    <label class='data_field '>族群</label>
					    <div class='data_value '> 
                        <?php if(isset( $fields_config['volume']['ethnic']) && isset($fields_config['volume']['ethnic']['pattern'])): ?>
						<?php 
						      
							  $ethnic   = [];
							  $value_sets = explode(';',$fields_config['volume']['ethnic']['pattern']);
							  foreach($value_sets as $item){
							  	  $ethsplit = explode('/',$item);
							      if(!isset($ethnic[$ethsplit[0]])) $ethnic[$ethsplit[0]] = [];
								  if(!isset($ethsplit[1])) continue;
							      $ethnic[$ethsplit[0]][] = $ethsplit[1];
							  }
						?>    
							<!-- LV1 -->
							<select class='_volume _variable _update _ethnic_main' name='META-V-ethnic_1' >
						      <option value='' >-</option> 
							  <?php foreach($ethnic as $emain=>$esub): ?>
							  <option value='<?php echo $emain;?>'  <?php echo preg_match('/^'.$emain.'/',$meta_collect['META-V-ethnic']) ? 'selected':''; ?>  ><?php echo $emain;?></option> 
							  <?php endforeach; ?>
							</select>  
							
							<!-- LV2 -->
							<ul class='ethnic_assist' >
							<?php foreach($ethnic as $emain=>$esub): ?>
							  <?php if($emain=='其他'): ?>  
							  <li ethnic='<?php echo $emain;?>'>   
								<input type='text' class='_volume _variable _update' name='META-V-ethnic_2' placeholder='請填寫新增族群'/>
							  </li> 	
							  <?php elseif(count($esub)): ?>
                              <li ethnic='<?php echo $emain;?>' > 							  
							    <select class='_volume _variable _update _ethnic_sub' name='META-V-ethnic_2'>
								 <option value=''>-</option>
								<?php foreach($esub as $e): ?>  
								  <option value='<?php echo $e;?>'  <?php echo preg_match('/'.$e.'$/',$meta_collect['META-V-ethnic']) ? 'selected':''; ?> >
								    <?php echo $e;?>
								  </option>
								<?php endforeach; ?>
							    </select>
								<input type='text' class='_volume _variable _update newa_sub_ethnic' name='META-V-ethnic_3' placeholder='請填寫新增原住民族' />
							  </li> 
							  <?php endif; ?>
							<?php endforeach; ?>
							</ul>
							
                          <?php else: ?>
                          <input type='text' class='_volume _variable _update' name="META-V-ethnic" id='META-V-ethnic' value='<?php echo $meta_collect['META-V-ethnic']; ?>' /> 
						  <?php endif; ?>
						</div>
					  </div>
					  
					  <div class='data_col _flat' id='' > 
					    <label class='data_field '>文物來源</label>
					    <div class='data_value '> 
                          <?php if(isset( $fields_config['volume']['acquire_type']) && isset($fields_config['volume']['acquire_type']['pattern'])): ?>
						  <?php $value_sets = explode(';',$fields_config['volume']['acquire_type']['pattern']);?>
						  <ul class='value_set' >
						  <?php foreach($value_sets as $item): ?> 
						  <li> <input type='radio' class='_volume _variable _update' value='<?php echo $item; ?>' name='META-V-acquire_type' <?php echo $meta_collect['META-V-acquire_type']==$item ? 'checked' :''; ?> ><?php echo $item; ?></li>
						  <?php endforeach; ?>
						  <li> 
						    <input type='radio' class='_volume _variable _update' value='_newa' name='META-V-acquire_type' >
						    <input type='text'  class='_volume _variable' value='' name='META-V-acquire_type_newa' placeholder='其他' >   
						  </li>
						  </ul>
                          <?php else: ?>
                          <input type='text' class='_volume _variable _update' name="META-V-acquire_type" id='META-V-acquire_type' value='<?php echo $meta_collect['META-V-acquire_type']; ?>' /> 
						  <?php endif; ?>
						</div>
					  </div>
					  <div class='data_col _flat' id='' > 
					    <label class='data_field '>來源說明</label>
					    <div class='data_value '>   
					      <input type='text' class='_volume _variable _update ' name="META-V-acquire_info" id='META-V-acquire_info' value='<?php echo $meta_collect['META-V-acquire_info']; ?>' />
						</div>
					  </div>
					  
					  <div class='data_col _flat' id='' > 
					    <label class='data_field '>狀況級數</label>
					    <div class='data_value '> 
                          <?php if(isset( $fields_config['volume']['status_code']) && isset($fields_config['volume']['status_code']['pattern'])): ?>
						  <?php $value_sets = explode(';',$fields_config['volume']['status_code']['pattern']);?>
						  <ul class='value_set' >
						  <?php foreach($value_sets as $item): ?> 
						  <li 
						    <?php if($item=='A'):?>
						    title='狀況良好、完整、具代表性、特殊性' 
						    <?php elseif($item=='B'):?>
						    title='略帶破損但仍具代表性或特殊性' 
						    <?php elseif($item=='C'):?>
						    title='破損嚴重，不可展覽' 
						    <?php endif; ?>
						  > 
						    <input type='radio' class='_volume _variable _update' value='<?php echo $item; ?>' name='META-V-status_code' <?php echo $meta_collect['META-V-status_code']==$item ? 'checked' :''; ?>   ><?php echo $item; ?>
						  </li>
						  <?php endforeach; ?>
						  <li> 
						    <input type='radio' class='_volume _variable _update' value='_newa' name='META-V-status_code' >
						    <input type='text'  class='_volume _variable' value=''      name='META-V-status_code_newa' placeholder='其他' >   
						  </li>
						  </ul>
                          <?php else: ?>
                          <input type='text' class='_volume _variable _update' name="META-V-status_code" id='META-V-status_code' value='<?php echo $meta_collect['META-V-status_code']; ?>' /> 
						  <?php endif; ?>
						</div>
					  </div>
					  <div class='data_col ' id='' > 
					    <label class='data_field '>狀況簡述</label>
					    <div class='data_value '>   
					      <textarea class='_volume _variable _update ' name="META-V-status_descrip" id='META-V-status_descrip' ><?php echo $meta_collect['META-V-status_descrip']; ?></textarea>
						</div>
					  </div>
					  
					   <div class='field_set'> 
					    <div class='data_col' id='' > 
					      <label class='data_field '>入庫時間</label>
					      <div class='data_value '>   
					        <input type='text' class='_volume _variable _update' name="META-V-store_date" id='META-V-store_date' value='<?php echo $meta_collect['META-V-store_date']; ?>' />
						  </div>
					    </div>
						<div class='data_col' id='' > 
					      <label class='data_field '>存放位置</label>
					      <div class='data_value '>   
					      <?php if(isset( $fields_config['volume']['store_location']) && isset($fields_config['volume']['store_location']['pattern'])): ?>
						  <?php $value_sets = explode(';',$fields_config['volume']['store_location']['pattern']);?>  
							<select class='_volume _variable _update'  name="META-V-store_location" id='META-V-store_location' >
							  <?php foreach($value_sets as $item): ?> 
							  <option value='<?php echo $item; ?>'><?php echo $item; ?></option> 
							  <?php endforeach; ?>
							  <option value='_newa'>新增其他</option> 
							</select>
							<input type='text' class='_volume _variable' value='' name='META-V-store_location_newa' id='META-V-store_location_newa' placeholder='新增儲存地' >   
						  <?php else: ?>
							<input type='text' class='_volume _variable _update'  name="META-V-store_location" id='META-V-store_location' value='<?php echo $meta_collect['META-V-store_location']; ?>' />
						  <?php endif; ?>
							
						  </div>
					    </div>
						<div class='data_col' id='' > 
					      <label class='data_field '>架號</label>
					      <div class='data_value '>   
					        <input type='text' class='_volume _variable _update' name="META-V-store_number" id='META-V-store_number' value='<?php echo $meta_collect['META-V-store_number']; ?>' />
						  </div>
					    </div>
						<div class='data_col' id='' > 
					      <label class='data_field '>箱號</label>
					      <div class='data_value '>   
					        <input type='text' class='_volume _variable _update' name="META-V-store_boxid" id='META-V-store_boxid' value='<?php echo $meta_collect['META-V-store_boxid']; ?>' />
						  </div>
					    </div>
					  </div>
					  <div class='data_col' id='' > 
					    <label class='data_field '>備註</label>
					    <div class='data_value '>   
					      <input type='text' class='_volume _variable _update ' name="META-V-remark" id='META-V-remark' value='<?php echo $meta_collect['META-V-remark']; ?>' />
						</div>
					  </div>
					  
					  <div class='field_set' id='data_logout'>    
						  <div class='data_col ' > 
							<label class='data_field '>文物註銷</label>
							<div class='data_value'>   
							  <label class="switch">
								<input type="checkbox" class="boolean_switch _volume _variable _update _flag" name="META-V-logout_flag" id='META-V-logout_flag'  data-default="0" <?php echo intval($meta_collect['META-V-logout_flag'])? 'checked' : '';?> />
								<div class="slider round"></div>
							  </label>
							</div>
						  </div>
						  
						  <div class='data_col' id='' > 
							<label class='data_field '>註銷原因</label>
							<div class='data_value '>   
							  <input type='text' class='_volume _variable _update ' name="META-V-logout_descrip" id='META-V-logout_descrip' value='<?php echo $meta_collect['META-V-logout_descrip']; ?>' placeholder='日期/原因/經手人' />
							</div>
						  </div>
					  </div>
					  
					</div>
					
				  </div>   
				  
				  
				  <!-- 單件資料 -->
				  <div class='form_group meta_group_block <?php echo $volume_switch_target=='element_list'||$volume_switch_target=='_all' ?'_display':''?>' id='element_list' >
					<div class='data_col FIELD_volume_table'  >   
					  <h1 > 
					    <span>影像目錄 :</span> 
					    <!--<button class='dofunc' id='act_create_category' title='新增目錄'><i class="fa fa-plus" aria-hidden="true"></i><b>i</b></button> -->
					  </h1>
					   
					  <div class='data_value' > 	
						  <table class='record_list' id='docatalog'>
							<thead>
								<tr class='data_field'>
								  <td title='序號'	align='center'	>序號</td>
								  <td title='編號'	align='center'	>編號</td>
								  <td title='類型'	align='center'	>類型</td>
								  <td title='名稱'	align='center'	>名稱</td>
								  <td title='格式'	align='center'	>格式</td>
								  <td title='狀態'	align='center'	>狀態</td>
								</tr>   
								 
								<tr class='data_record _element_read _record_template ' id='' no='' page='' mode='edit' >
									<td field='no' >new</td>
									<td field='META-E-store_no'> </td>
									<td field='META-E-dotype'> </td>
									<td field='META-E-doname'> </td>
									<td field='META-E-doformat'> </td>
									<td>
									  <a class='option' >
										<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
									  </a>
									</td>
								</tr>
							</thead>
							<tbody class='data_result' mode='list' >   <!-- list / search--> 
							<?php $counter=1; ?>
							<?php foreach($meta_element as $i=>$meta): ?>
							<tr class='data_record _element_read' id='<?php echo $i;?>' no='<?php echo $i;?>' page='' mode='edit' >
								<td field='no' ><?php echo $counter++; ?> </td>
								<td field='META-E-store_no'> <?php echo $meta['META-E-file_store_id'];?></td>
								<td field='META-E-dotype'> <?php echo $meta['META-E-dotype'];?></td>
								<td field='META-E-doname'> <?php echo $meta['META-E-doname'];?></td>
								<td field='META-E-doformat'> <?php echo $meta['META-E-doformat'];?></td>
								<td>
								  <a class='option' >
									<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
								  </a>
								</td>
							</tr>
							<?php endforeach; ?>
							</tbody>
							<tbody class='data_target'>
							</tbody>
						  </table>
					  </div>
					  
					</div>  
				  </div>
				  
				  
				  <!-- 研究引用欄位 -->
				  <div class='form_group meta_group_block <?php echo $volume_switch_target=='research_meta'||$volume_switch_target=='_all' ?'_display':''?>' id='research_meta' >
				    <div class='data_col FIELD_volume_table'  >  <!-- 資料欄位 -->
				      <h1 > 
					    <span>研究引用紀錄 :</span> 
					    <button class='dofunc' id='act_create_research' title='新增紀錄'><i class="fa fa-plus" aria-hidden="true"></i><b>i</b></button> 
					  </h1>
					  <table class="record_list" id='research'>
					    <thead>
							<tr class='data_field'>
							    <td> 序號 </td>  
							    <td> 研究引用 </td> 
							    <td> 功能 </td> 
							</tr>
							<tr class='research_template research_record' mode='view' no=''>
							    <td>
								  <i class='research_rno'>no</i>
								  <a class='option act_redearch_delete' ><i class="fa fa-trash-o" aria-hidden="true"></i></a>
								</td>
							    <td>
								  <div class='research_descrip'>
								    新增研究引用
								  </div>
								  <div class='research_editor'>
								    <div class='data_col _flat' id='' > 
									  <label class='data_field '>題目</label>
									  <div class='data_value '>   
										<input type='text' class='_vresearch _variable _update' name="META-R-title"  value='' />
									  </div>
									</div>
									<div class='data_col _flat' id='' > 
									  <label class='data_field '>書籍期刊名稱</label>
									  <div class='data_value '>   
										<input type='text' class='_vresearch _variable _update' name="META-R-source"  value='' />
									  </div>
									</div>
									<div class='data_col _flat' id='' > 
									  <label class='data_field '>作者</label>
									  <div class='data_value '>   
										<input type='text' class='_vresearch _variable _update' name="META-R-author"  value='' />
									  </div>
									</div>
									<div class='data_col _flat' id='' > 
									  <label class='data_field '>發表年代</label>
									  <div class='data_value '>   
										<input type='text' class='_vresearch _variable _update' name="META-R-pubyear"  value='' />
									  </div>
									</div>
									<div class='data_col _flat' id='' > 
									  <label class='data_field '>出版單位</label>
									  <div class='data_value '>   
										<input type='text' class='_vresearch _variable _update' name="META-R-publisher"  value='' />
									  </div>
									</div>
									<div class='data_col _flat' id='' > 
									  <label class='data_field '>編輯資訊</label>
									  <div class='data_value '>   
										<div type='text' class='_vresearch _variable' name="META-R-update"  ></div>
									  </div>
									</div>
								  </div>
								</td> 
							    <td>
								  <a class='option act_research_option' > 
								    <i class="fa fa-floppy-o" aria-hidden="true"></i>
                                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
								  </a>
								</td>   
							</tr>
						</thead>
					    <tbody id='research_contents'>
						</tbody>
					  
					  
					  </table>
					
					
					
					
					
					
					</div> 
				  </div>	
				  
				  <!-- 異動紀錄欄位 -->
				  <div class='form_group meta_group_block <?php echo $volume_switch_target=='movement_meta'||$volume_switch_target=='_all' ?'_display':''?>' id='movement_meta' >
				    <div class='data_col FIELD_volume_table'  >  <!-- 資料欄位 -->
				      <h1 > 
					    <span>異動紀錄 :</span> 
					    <button class='dofunc' id='act_create_movement' title='新增紀錄'><i class="fa fa-plus" aria-hidden="true"></i><b>i</b></button> 
					  </h1>
					  <table class="record_list" id='movement'>
					    <thead>
							<tr class='data_field'>
							    <td> 序號 </td> 
                                <td> 類型 </td> 
							    <td> 地點 </td>  
								<td> 說明 </td>
                                <td> 日期 </td>
                                <td> 經手人 </td>	
                                <td> 編輯 </td>									
							</tr>
							<tr class='movement_template movement_record' mode='view' no=''>
							    <td>
								  <i class='movement_no'>no</i>
								  <a class='option act_movement_delete' ><i class="fa fa-trash-o" aria-hidden="true"></i></a>
								</td>
							    <td>
								  <div class='data_col' id='' > 
									<div class='data_value '>   
									  <select class='_vmovement _variable _update act_movement_type_sel' name='META-M-move_type'  disabled=true >
										<option value='移動'>移動</option> 
										<option value='修復'>修復</option> 
										<option value='_new'>其他</option> 
									  </select>
									  <input type='text' class='_vmovement _variable _update' name='META-M-move_type'   readonly=true />
								    </div>
								  </div>	
								</td>
								<td>
								  <div class='data_col' id='' > 
									<div class='data_value '>   
									  <input type='text' class='_vmovement _variable _update' name='META-M-move_location' placeholder='異動地點'  readonly=true />
								    </div>
								  </div>	
								</td>
                                <td>
								  <div class='data_col' id='' > 
									<div class='data_value '>   
									  <input type='text' class='_vmovement _variable _update' name='META-M-move_reason' placeholder='描述異動理由'  readonly=true />
								    </div>
								  </div>	
								</td>
								<td>
								  <div class='data_col' id='' > 
									<div class='data_value '>   
									  <input type='text' class='_vmovement _variable _update' name='META-M-move_date' placeholder='' value='<?php echo date('Y-m-d');?>'  readonly=true  />
								    </div>
								  </div>	
								</td>
                                <td>
								  <div class='data_col' id='' > 
									<div class='data_value '>   
									  <input type='text' class='_vmovement _variable _update' name='META-M-move_handler' placeholder='' value='<?php echo $user_info['user']['user_name']?>'  readonly=true  />
								    </div>
								  </div>	
								</td>
								<td>
								  <a class='option act_movement_option' > 
								    <i class="fa fa-floppy-o" aria-hidden="true"></i>
                                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
								  </a>
								</td>   
							</tr>
						</thead>
					    <tbody id='movement_contents'>
						</tbody>
					  </table>
					  
					</div> 
				  </div>
				  
				  <!-- 佈展紀錄欄位 -->
				  <div class='form_group meta_group_block <?php echo $volume_switch_target=='display_meta'||$volume_switch_target=='_all' ?'_display':''?>' id='display_meta' >    
				    <div class='data_col FIELD_volume_table'  >  <!-- 資料欄位 -->
				      <h1 > 
					    <span>展覽紀錄 :</span> 
					    <button class='dofunc' id='act_create_display' title='新增展覽'><i class="fa fa-plus" aria-hidden="true"></i><b>i</b></button> 
					  </h1>
					  <table class="record_list" id='display'>
					    <thead>
							<tr class='data_field'>
							    <td> 序號 </td> 
                                <td> 日期 </td> 
							    <td> 展覽主題 </td>  
								<td> 展覽地點 </td>
								<td> 展覽單位 </td>
                                <td> 編輯 </td>									
							</tr>
							<tr class='display_template display_record' mode='view' no=''>
							    <td>
								  <i class='display_no'>no</i>
								  <a class='option act_display_delete' ><i class="fa fa-trash-o" aria-hidden="true"></i></a>
								</td>
							    <td>
								  <div class='data_col' id='' > 
									<div class='data_value '>   
									  <input type='text' class='_vdisplay _variable _update' name='META-D-display_date' placeholder='展覽日期'  value='<?php echo date('Y-m-d');?>' readonly=true />
								    </div>
								  </div>	
								</td>
                                <td>
								  <div class='data_col' id='' > 
									<div class='data_value '>   
									  <input type='text' class='_vdisplay _variable _update' name='META-D-display_topic' placeholder='展覽主題'  value=''  readonly=true />
								    </div>
								  </div>	
								</td>
								<td>
								  <div class='data_col' id='' > 
									<div class='data_value '>   
									  <input type='text' class='_vdisplay _variable _update' name='META-D-display_place' placeholder='地點' value=''  readonly=true  />
								    </div>
								  </div>	
								</td>
                                <td>
								  <div class='data_col' id='' > 
									<div class='data_value '>   
									  <input type='text' class='_vdisplay _variable _update' name='META-D-display_organ' placeholder='單位' value=''  readonly=true  />
								    </div>
								  </div>	
								</td>
								<td>
								  <a class='option act_display_option' > 
								    <i class="fa fa-floppy-o" aria-hidden="true"></i>
                                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
								  </a>
								</td>   
							</tr>
						</thead>
					    <tbody id='display_contents'>
						</tbody>
					  </table>
					  
					</div> 
				  </div>
				  
				  		  
				</div>
				
				<footer class='record_footer'>
				  <div>
				    <label>編輯設定</label>
				    <select id='act_meta_built_function' >
				      <option value='' selected disabled >功能選項</option>
					  <optgroup label='介面設定'>
					    <option value='act_uiconf_reset'   >預設介面設定</option>
					  </optgroup>
					  <!--
					  <optgroup label='批次作業'>
					    <option value='act_volume_export'  >下載本卷資料</option>
					    <option value='act_volume_update'  >更新本卷資料</option>
					  </optgroup>
					  -->
					  <optgroup label='編輯作業'>
					    <option value='act_meta_editlogs'  >調閱編輯紀錄</option>
					    <option value='act_volume_create'  >新增文物資料</option>
					  </optgroup>
					  <optgroup label='移除作業'>
					    <option value='act_volume_delete'  >刪除文物資料</option>
					  </optgroup>
				    </select>
				  </div>
				  <div class='edit_logs' >
					<label>統計：</label>
				    <span>件數 <i class='_volume _variable' id='META-V-_NumOfItem' ><?php echo $meta_collect['META-V-count_element'];?></i>, </span>
					<span>影像 <i class='_volume _variable' id='META-V-_NumOfDobj' ><?php echo $meta_collect['META-V-count_dofiles'];?></i> </span>
					，
					<label>更新：</label>
				    <span class='_volume _variable' id='META-V-_timeupdate' > <?php echo $meta_collect['META-V-_timeupdate'];?> </span> 
				    @
				    <span class='_volume _variable' id='META-V-_userupdate' > <?php echo $meta_collect['META-V-_userupdate'];?> </span> 
				    </div>
				</footer>
				
				
			  </div>
			   
			  
			  <!-- 單件目錄資料-->
			  <div class='data_record_block'  id='record_member' status=''>
				<header class='record_header'>
				  <span class='option md_closer' id='act_close_element_editor' ><i class="fa fa-times" aria-hidden="true"></i></span>
				  <label class='record_title'>
				    影像內容編輯<i class='_variable' id='' ></i>
				  </label>
				  <span  class='record_switch'> 
				    <button class='dofunc act_switch_element' to='prev'	title='切換上一件'> <i class="fa fa-chevron-left" aria-hidden="true"></i> 上一件 <b>z</b></button>
				    <button class='dofunc act_switch_element' to='next'	title='切換下一件'> 下一件 <i class="fa fa-chevron-right" aria-hidden="true"></i> <b>c</b></button>
				  </span>
				  <span  class='record_tasks'>  
				    <button class='dofunc' id='act_save_element_meta'	title='儲存單件資料'><i class="fa fa-floppy-o" aria-hidden="true"></i><b>x</b></button>	
				  </span>
				</header> 
				<div class='record_body'>
				  <div class='form_group meta_group_block _display' style='margin-top:5px;' >
				   
					<div class='field_set'> 
					  
					  <div class='data_col _flat'> 
					    <label class='data_field _must' > 影像編號 </label>
					    <div class='data_value _must' >   
						  <input type='text' class='_element _detail _variable _must' id='META-E-store_no' default='' readonly=true value='' />
					    </div>
					  </div>
					  
					 <div class='data_col _flat'> 
					    <label class='data_field _must' > 影像典藏號 </label>
					    <div class='data_value _must' >   
						  <input type='text' class='_element _detail _variable _must' id='META-E-file_store_id' default='' readonly=true value='' />
					    </div>
					  </div>
					  
					</div>
					
					
				    <div class='search_meta' > <!-- 資料欄位 -->
					  
					  <div class='data_col ' id='' > 
					    <label class='data_field '>圖檔名稱</label>
					    <div class='data_value'>   
					      <input type='text'  class='_element _detail _variable _update ' name="META-E-doname" id='META-E-doname'  placeholder='圖檔名稱' value=''  />
						</div>
					  </div>
					  
					  <div class='data_col' id='' > 
					    <label class='data_field '>圖檔類型</label>
					    <div class='data_value '> 
                          <?php if(isset( $fields_config['element']['dotype']) && isset($fields_config['element']['dotype']['pattern'])): ?>
						  <?php $value_sets = explode(';',$fields_config['element']['dotype']['pattern']);?>
						  <ul class='value_set' >
						  <?php foreach($value_sets as $item): ?> 
						  <li> <input type='radio' class='_element _detail _variable _update' value='<?php echo $item; ?>' name='META-E-dotype' ><?php echo $item; ?></li>
						  <?php endforeach; ?>
						  <li> 
						    <input type='radio' class='_element _detail _variable _update' value='_newa' name='META-E-dotype' >
						    <input type='text' class='_element _detail _variable' value='' name='META-E-dotype_newa' placeholder='新增類型' >   
						  </li>
						  </ul>
                          <?php else: ?>
                          <input type='text' class='_element _detail _variable _update' name="META-E-dotype" id='META-E-dotype' value='' /> 
						  <?php endif; ?>
						</div>
					  </div>
					  
					  <div class='data_col' id='' > 
					    <label class='data_field '>所屬族群</label>
					    <div class='data_value '> 
                          <input type='text' class='_volume ' name="META-V-ethnic" value='<?php echo $meta_collect['META-V-ethnic'];?>' readonly disabled /> 
						</div>
					  </div>
					  <div class='field_set'> 	  
						  <div class='data_col ' id='' > 
							<label class='data_field '>拍攝地點</label>
							<div class='data_value'>   
							  <input type='text'  class='_element _detail _variable _update ' name="META-E-location" id='META-E-location'  placeholder='拍攝地點' value=''  />
							</div>
						  </div>
						  <div class='data_col '> 
							<label class='data_field ' > 拍攝年代 </label>
							<div class='data_value _must' >   
							  <input type='text' class='_element _detail _variable _update' name='META-E-period' id='META-E-period' default='' value='' placeholder='拍攝年代' />
							</div>
						  </div>
						  <div class='data_col '> 
							<label class='data_field ' > 拍攝者 </label>
							<div class='data_value _must' >   
							  <input type='text' class='_element _detail _variable _update' name='META-E-creator' id='META-E-creator' default='' value='' placeholder='拍攝者' />
							</div>
						  </div>
					  </div> 
					  
					  <div class='data_col' id='' > 
					    <label class='data_field '>內容簡述</label>
					    <div class='data_value '>   
					      <textarea  class='_element _detail _variable _update _keeper' name="META-E-abstract" id='META-E-abstract' placeholder='內容簡述' value='' caserefer='META-E-abstract' ></textarea>
						</div>
					  </div>
					  
					  <div class='data_col' id='' > 
					    <label class='data_field '>備註</label>
					    <div class='data_value '>   
						  <input type='text'  class='_element _detail _variable _update ' name="META-E-remark" id='META-E-remark'  placeholder='備註' value=''  />
						</div>
					  </div>
					  
				    
					   
				      <!--
					  <div class='field_set'>   
						  <div class='data_col _flat'  title='開放檢索'> 
							<label class='data_field _necessary'>資料檢索</label>
							<div class='data_value'>   
							  <label class="switch">
								<input type="checkbox" class="boolean_switch _element _detail _variable _update _flag" name="META-E-_flag_open" id='META-E-_flag_open' data-save="1" data-default="0"  />
								<div class="slider round"></div>
							  </label>
							  <span>開放/不開放</span>
							</div>
						  </div>
						  
						  <div class='data_col _flat' id='' > 
							<label>影像閱覽</label>
							<div class='data_value'>   
							  <select class='_element _detail _variable _update' id='META-E-_view'  >
								<option value='開放'   >開放閱覽 </option>
								<option value='館內'   >館內閱覽 </option>
								<option value='調閱'   >到館調閱 </option>
								<option value='不開放' >不開放閱覽 </option>
							  </select>  
							</div>
						  </div>
						  
					  </div>
				      -->  
					</div>
					
				  </div>
				</div>  
				<footer class='record_footer'>
				  <div>
				    <label>編輯設定</label>
				    <select id='act_element_built_function' >
				      <option value='' selected disabled >功能選項</option>
					  <optgroup label='編輯作業'>
					    <option value='act_element_editlogs'  >調閱編輯紀錄</option>
					  </optgroup>
					  <optgroup label='移除作業'>
					    <option value='act_element_delete'  >刪除本件</option>
					  </optgroup>
				    </select>
				  </div>
				  <div class='edit_logs' >
					  <label>紀錄：最後更新</label>
					  <span class='_element _detail _variable' id='META-E-_timeupdate' ></span> 
					  by 
					  <span class='_element _detail _variable' id='META-E-_userupdate' ></span> 
				  </div>
				</footer>  
				
			  </div>
			  
			  
			  
		    </div>	
		  </div>
		  
		  
		  <!-- 數位檔按瀏覽模組 -->
		  <?php  //介面參數
		  $doplatform_size = isset($editor_config['uiconfig']['doplatform_module']) ? $editor_config['uiconfig']['doplatform_module']:[];
		  ?>
		  <div class='module_container _unselect _uikeep' 
		       id='doplatform_module' 
			   mode='asclose' move='0'  
			<?php if(count($doplatform_size)):?>   
			   style='<?php echo isset($doplatform_size['left']) ? 'left:'.$doplatform_size['left'].';':'';?><?php echo isset($doplatform_size['width']) ? 'width:'.$doplatform_size['width'].';':'';?>'
            <?php endif; ?> >
			
		    <div class='block_wrapper'>
		      
			  <div id='image_display_block'  >
				<header >
				  <span class='dooption_function'>
				    <span class='option md_anchor' ><i class="fa fa-thumb-tack" aria-hidden="true"></i><i class="fa fa-arrows-alt" aria-hidden="true"></i></span>
					
					<button class='dofunc ' id='act_dolist_admin' title='開啟列表'>  <i class="fa fa-list" aria-hidden="true"></i>/<i class="fa fa-upload" aria-hidden="true"></i>  </button>
					|
					<select id='dobj_folder_change' >
					  <option value='' selected disabled> 修改類別 </option>
					  <?php foreach($dobj_conf['dotypes'] as $dotype):?>
                      <option value='<?php echo $dotype;?>' ><?php echo $dotype;?></option>
					  <?php endforeach;?>
					</select>
				  </span>
				  <span class='dotarget_function'>
				    <select class='folder_selecter'  id='dobj_folder_select'  >
					  <option class='folder' id='_all' value='_all' > 所有類別 </option>
					  <?php foreach($dobj_conf['folders'] as $folder => $files):?>
					  <option class='folder' id='<?php echo $folder;?>' value='<?php echo $folder;?>' ><?php echo $folder;?> </option>
					  <?php endforeach;?>
				    </select>
					<select class='page_selecter'  scale='1' id='dobj_select_dom'>
					  <option value='' disabled selected> 數位圖檔 </option>
					  <?php foreach($dobj_conf['folders'] as $folder => $files):?>
					  <optgroup  label='<?php echo $folder;?>' >
						  <?php   foreach($files as $i => $file_conf):?>
					      <option class='pager' id='<?php echo $file_conf['file'];?>' value='<?php echo $file_conf['file'];?>' data-serial=<?php echo $i; ?> display=1 >P.<?php echo ($i+1);?> /  <?php echo $file_conf['file'];?> </option>
					      <?php endforeach;?>
					  </optgroup>
					  <?php endforeach;?>
				    </select>
					 
				  </span>
				  <span class='doswitch_function'>
					<button class='dofunc ' id='act_active_element_edit'  title='編輯影像資料'><i class="fa fa-edit" aria-hidden="true"></i><b>i</b></button>	
					|
					<button class='dofunc ' id='act_set_item_cover' title='設為封面'>  <i class="fa fa-bookmark" aria-hidden="true"></i> </button>
					|
					<button class='dofunc ' id='act_download_stored' title='下載原始圖檔'>  <i class="fa fa-download" aria-hidden="true"></i>  </button>
					|
					<button class='dofunc page_switch'  mode='dprev' title='前一頁'>  <i class="fa fa-step-backward" aria-hidden="true"></i><b><i class="fa fa-arrow-left" aria-hidden="true"></i></b></button>
					<button class='dofunc page_switch'  mode='dnext' title='後一頁'>  <i class="fa fa-step-forward" aria-hidden="true"></i></i><b><i class="fa fa-arrow-right" aria-hidden="true"></i></b> </button>
				  </span>
				  
				  <?php  //介面參數-顯示模式
				  $dodisplay_mode = isset($editor_config['uiconfig']['doscale_method']) ? $editor_config['uiconfig']['doscale_method']:'fitview';
				  $dodisplay_size = isset($editor_config['uiconfig']['scale_set']) ? $editor_config['uiconfig']['scale_set']:'100';
				  ?>		  
				  <span class='doscale_function' id='doscale_method' config='fitview' >
				    <span class='scale_waper'>
					  <input id='scale_set' type='range' min="70" max="300" value='<?php echo $dodisplay_size;?>' step="10" data-scale='1' />
					  <span  id='scale_info' >1.0</span>
					</span>
				  </span>
				</header>
				<div class='dorender' id='dobj_container' title=''></div>
				<footer>info </footer>
				<img id='dobj_loader' src="tool/svg-loaders/puff.svg" />
			  </div>
			</div>
		  </div> 
		  
		  
		  
		  
		  <!-- 數位檔案管理模組 -->
		  <div class='module_container' id='adfile_module' mode='asclose' move='0'  data-root='<?php echo $dobj_conf['root'];?>' data-folder='<?php echo $dobj_conf['folder'];?>' data-upload='' >
		    
			<div class='block_wrapper'>
			  
			  <!-- 影像列表 -->
			  <div class='module' id='dobj_file_block' >
			    <header>
				  <span class='option md_anchor' ><i class="fa fa-thumb-tack" aria-hidden="true"></i><i class="fa fa-arrows-alt" aria-hidden="true"></i></span>
				  <label>數位檔案管理</label>
				  <div class='method_manual'>
				    <label>管理模式:</label>
					<select class='md_methodsel'>
					  <option value='' disabled >選擇模式 </option>
					  <option value='dobjrecord' selected  > 檔案列表 </option>
					  <option value='dobjupload' > 檔案上傳 </option>
					</select>
				  </div>
				</header>
				<div class='work_block' > 
				  
				  <!-- 檔案列表管理 -->
				  <div class='md_method_dom' id='dobjrecord'  > 
				    <h2>
					  <table >
						<tr >
						  <th class='fsel' ><input type='checkbox' id='act_selall_dfile' /></th>
						  <th class='fnum'  >no</th>
						  <th class='ftype'  >類別</th>
						  <th class='fname' >檔名</th>
						  <th class='finfo' >資訊</th>
						  <th class='fedit'>檔</th>
						</tr>
					  </table>	
					</h2>
					<div class='workaround'>  
					  <table id='dobj_record_table' >
						<tbody id='do_list_container' >
						<?php if(isset($dobj_conf['files'])): ?>	
							<?php foreach($dobj_conf['files'] as $i => $file_conf):?>
							<tr class='file' data-order='<?php echo $i;?>' data-file='<?php echo $file_conf['file'];?>' >
							  <td class='fsel' ><input type='checkbox' name='fselect' class='act_selone_dfile' value='<?php echo $file_conf['file'];?>' ></td>
							  <td class='fnum' ><?php echo ($i+1);?>.</td>
							  <td class='ftype' ><?php echo isset($file_conf['dotype'])?$file_conf['dotype']:'';?></td>
							  <td class='fname' ><?php echo $file_conf['file'];?></td>
							  <td class='finfo' > <?php echo $file_conf['width'].'x'.$file_conf['height'];?> </td>
							  <td class='fedit'>
								<span class='option inlinefunc' edit='-1' >
								  <i class="fa fa-external-link act_adfile_downloaddo" aria-hidden="true" title='連結檔案'></i>
								  <i class='fa fa-pencil' aria-hidden='true' title='修改檔名'></i>
								  <i class='fa fa-save' aria-hidden='true'></i>
								</span>
							  </td>
							</tr>
							<?php endforeach;?>
						<?php endif;?>	
						</tbody>
					  </table>  
				    </div> 
				    <div class='workbatch'>
					  <span><i class="fa fa-check-square-o" aria-hidden="true"></i> 勾選批次處理 : </span>
					  <select id='act_adfile_conf_switch' prehead='adfile' >
					    <option value='initial' selected >選擇功能</option>
						<optgroup label='修改' >
					      <!-- <option value='rename'  > - 重新順號 </option>-->
						  <option value='reorder' > - 變更順序 </option>
					    </optgroup>
						<!--
						<optgroup label='輸出' >
					      <option value='download' disabled > - 打包下載 </option>
					    </optgroup>
						-->
						<optgroup label='刪除' >
						   <option value='delete'  > - 刪除勾選 </option>
					    </optgroup>
					  </select>:
					  <div class='function_config' mode='initial' >
					    <span class='funcgroup' id='adfile-initial' > [ 相關功能參數設定區 ] </span>
					    
						<span class='funcgroup' id='adfile-rename' style='display:none;'>
						  <input type='text'   name='file_name_prehead' id='file_name_prehead' placeholder='前墜檔名,預設為全宗號'  />
						  <input type='text'   name='file_name_startno' id='file_name_startno' placeholder='起始編號' />
						  <button id='act_adfile_rename'>重編</button>
						</span>
						
						<span class='funcgroup' id='adfile-reorder' style='display:none;'>
						  <button id='act_adfile_ordreset' ><i class="fa fa-repeat" aria-hidden="true"></i></button>
						  |
						  <button id='act_adfile_tofirst' ><i class="fa fa-step-backward" aria-hidden="true"></i></button> 
						  <button id='act_adfile_fordware' ><i class="fa fa-backward" aria-hidden="true"></i></button> 
						  <button id='act_adfile_backware' ><i class="fa fa-forward" aria-hidden="true"></i></button>
						  <button id='act_adfile_tolast' ><i class="fa fa-step-forward" aria-hidden="true"></i></button>
						  |
						  <i>或使用拖曳</i>
						  <button id='act_adfile_reorder' ><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
						</span> 
						<span class='funcgroup' id='adfile-download' style='display:none;'>
						  <input type='text'   name='file_name_download' placeholder='打包檔名'  />
						  <button >下載</button>
						</span>
						
						
						<span class='funcgroup' id='adfile-delete' style='display:none;'>
						  <span class='captcha' >
							  <input type="text" id='adfile_captcha_input' class=''  name="Turing" value="" size=5/>
							  <img src="tool/captcha/code.php" id="captcha_img">
							  <a class='reset_capture' href="#" onclick="document.getElementById('captcha_img').src = document.getElementById('captcha_img').src + '?' + (new Date()).getMilliseconds()" title='新驗證碼'><i class="fa fa-refresh" aria-hidden="true"></i></a>				
						  </span>  
						  <button  id='act_adfile_delete'>執行刪除</button>
						</span>
					  </div>
					  
					</div>
				  </div> <!-- end of dobjrecord -->
				  
				  
				  <!-- 檔案upload管理 -->
				  <div class='md_method_dom'  id='dobjupload' style='display:none;' >
				    <h2>
					  <div class='upload_finish'>
					    <input type='checkbox' id='act_selall_ufile' />
						新增檔案清單：
					    <span id='num_of_upload' title='上傳檔案數量' >…</span> /
					    <span id='execute_timer' title='上傳執行時間' >…</span> /
						<span id='complete_time' title='上傳完成時間' >…</span>
					  </div>
					  <div class='upload_process' title='' >
                        <button type='button' class='cancel' id='act_upl_delete'> <i class="fa fa-trash-o" aria-hidden="true"></i> 刪除 </button>
					    <button type='button' class='active' id='act_upl_import'> <i class="fa fa-hdd-o" aria-hidden="true"></i> 匯入 </button>
					  </div>
					</h2>
					<ul class='upload_list' id='upload_success' >
					  
					</ul>
					<h2>
					  <label>待上傳列表：<span id='num_of_queue' >..</span></label>
					  <div class='upload_action'>
						
						<span>類型：</span>
						<?php if(isset( $dobj_conf['dotypes']) && count($dobj_conf['dotypes'])): ?>
						  <select id='upload_do_type'>
						  <?php foreach($dobj_conf['dotypes'] as $dtype): ?> 
						    <option value='<?php echo $dtype?>'><?php echo $dtype; ?></option>
						  <?php endforeach; ?>
						  </select>
						  <input type='text' id='upload_do_type_add' value='' size='50' style='display:none;'/>
						<?php else: ?>
                          <input type='text' id='upload_do_type' value='' size='50' />
						<?php endif; ?>
                            
						</select>
						<button type='button' class='select blue' id='act_select_file'> 新增檔案 </button>
						<button type='button' class='active' id='act_active_upload' disabled=false  data-upload=''> 上傳 </button>
						<button type='button' class='cancel' id='act_clean_upload'> 清空 </button>
					  </div>
					</h2>
					<div class='upload_queue dropzone_sign' id='upload_dropzone' hasfile=0 ></div>
				  </div>
				</div>  
				
				<div class='admin_info' >
				  <i class="fa fa-info-circle" aria-hidden="true"></i>
				  <div class='minfo_block'>  
				    <div class='action_result' id='' alert='fail' >
				      <span>
					    <i class='execute'>相關資訊</i>
					    <i class='message'></i>
					  </span>
					<i class='acttime'></i>
				    </div>
				    <ul class='task_process' id='task_info' >
					</ul>
				  </div>
				</div>
			  </div>
			  
		    </div>
		  </div>
		  
		  <!-- 影像縮圖 -->
		  <div class='module_container _unselect' id='thumb_module' mode='asthumb' move='0'  data-root='<?php echo $dobj_conf['root'];?>' data-folder='<?php echo $dobj_conf['folder'];?>' data-upload='' >  
			<div class='block_wrapper'  >
		      
			  <div id='dobj_thumb_cover' >
			    <div id='cover_container'>
				  <img id='dobj_cover' src='thumb.php?src=<?php echo $dobj_conf['root'];?>thumb/<?php echo $dobj_conf['folder'];?>/<?php echo $meta_collect['META-V-cover_page'];?>'>
			    </div>
				<span id='cover_name'>
				  文物封面
				</span>
			  </div>
			  <div id='dobj_thumb_block' >
		         
				  <?php if(isset($dobj_conf['files'])): ?>
					<?php $i=1; ?>
					<?php foreach($dobj_conf['files'] as $i => $file_conf):?>
					<div class='thumb' data-folder='<?php echo isset($file_conf['dotype']) ? $file_conf['dotype'] : ''; ?>' p='<?php echo $file_conf['file'];?>'  >
					  <img data-src="thumb.php?src=<?php echo $dobj_conf['root'].'thumb/'.$dobj_conf['folder'].'/'.$file_conf['file']; ?>"  /> 
					  <i>P.<?php echo ++$i;?></i>
					</div>
					 <?php endforeach;?>
				  <?php endif; ?>	
				 
		      </div>
			</div>
		  </div>
		  
		  
		  <!-- 影像Loading  -->
		  <div id='main_page_loading'>
		    <span>
			<?xml version="1.0" encoding="utf-8"?><svg width="24px" height="24px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="uil-spin"><rect x="0" y="0" width="100" height="100" fill="none" class="bk"></rect><g transform="translate(50 50)"><g transform="rotate(0) translate(34 0)"><circle cx="0" cy="0" r="8" fill="#709a4e"><animate attributeName="opacity" from="1" to="0.1" begin="0s" dur="1s" repeatCount="indefinite"></animate><animateTransform attributeName="transform" type="scale" from="1.5" to="1" begin="0s" dur="1s" repeatCount="indefinite"></animateTransform></circle></g><g transform="rotate(45) translate(34 0)"><circle cx="0" cy="0" r="8" fill="#709a4e"><animate attributeName="opacity" from="1" to="0.1" begin="0.12s" dur="1s" repeatCount="indefinite"></animate><animateTransform attributeName="transform" type="scale" from="1.5" to="1" begin="0.12s" dur="1s" repeatCount="indefinite"></animateTransform></circle></g><g transform="rotate(90) translate(34 0)"><circle cx="0" cy="0" r="8" fill="#709a4e"><animate attributeName="opacity" from="1" to="0.1" begin="0.25s" dur="1s" repeatCount="indefinite"></animate><animateTransform attributeName="transform" type="scale" from="1.5" to="1" begin="0.25s" dur="1s" repeatCount="indefinite"></animateTransform></circle></g><g transform="rotate(135) translate(34 0)"><circle cx="0" cy="0" r="8" fill="#709a4e"><animate attributeName="opacity" from="1" to="0.1" begin="0.37s" dur="1s" repeatCount="indefinite"></animate><animateTransform attributeName="transform" type="scale" from="1.5" to="1" begin="0.37s" dur="1s" repeatCount="indefinite"></animateTransform></circle></g><g transform="rotate(180) translate(34 0)"><circle cx="0" cy="0" r="8" fill="#709a4e"><animate attributeName="opacity" from="1" to="0.1" begin="0.5s" dur="1s" repeatCount="indefinite"></animate><animateTransform attributeName="transform" type="scale" from="1.5" to="1" begin="0.5s" dur="1s" repeatCount="indefinite"></animateTransform></circle></g><g transform="rotate(225) translate(34 0)"><circle cx="0" cy="0" r="8" fill="#709a4e"><animate attributeName="opacity" from="1" to="0.1" begin="0.62s" dur="1s" repeatCount="indefinite"></animate><animateTransform attributeName="transform" type="scale" from="1.5" to="1" begin="0.62s" dur="1s" repeatCount="indefinite"></animateTransform></circle></g><g transform="rotate(270) translate(34 0)"><circle cx="0" cy="0" r="8" fill="#709a4e"><animate attributeName="opacity" from="1" to="0.1" begin="0.75s" dur="1s" repeatCount="indefinite"></animate><animateTransform attributeName="transform" type="scale" from="1.5" to="1" begin="0.75s" dur="1s" repeatCount="indefinite"></animateTransform></circle></g><g transform="rotate(315) translate(34 0)"><circle cx="0" cy="0" r="8" fill="#709a4e"><animate attributeName="opacity" from="1" to="0.1" begin="0.87s" dur="1s" repeatCount="indefinite"></animate><animateTransform attributeName="transform" type="scale" from="1.5" to="1" begin="0.87s" dur="1s" repeatCount="indefinite"></animateTransform></circle></g></g></svg>
		    </span>
		  </div>
		  
		 
		</div>
	  </div>
	</div>
	
	
	<!-- 框架外結構  -->
	<div class='system_message_area'>
	  <div class='message_block'>
		<div id='message_container'>
		  <div class='msg_title'><?php echo $page_info; ?></div>
		  <div class='msg_info'></div>
		</div>
		<div id='area_close'></div>
      </div>
	</div> 
	
	<!-- 顯示歷史 -->
	<div class='system_popout_area' id='meta_edit_logs' >
	    <div class='container'>
		  <div class='module_block' >
		    <header>
			  <div class='md_header'>
			    <span class='md_title'>編輯紀錄</span> 
			  </div>
			  <span class='area_close option' title='關閉'><i class="fa fa-times-circle" aria-hidden="true"></i></span>
			</header>
			<div class='md_contents'>
			  <table class='meta_edited_history'>
			    <tr class='logs_field'><td>日期</td><td>帳號</td><td>修改內容</td></tr>
			    <tbody id='meta_edit_record_block'>
			    </tbody>
			  </table>
			</div>
			<div class='md_footer'>
			  <div>
			    <span class='md_time'>  </span>
				From
				<span class='md_from'>  </span>
			  </div>
			  <div>
			    <span class='md_counter'>  </span>
			  </div>
			</div>
		  </div>
        </div>
	</div>
	
	
	<!-- 系統report -->
      <div class='system_feedback_area'>
        <div class='feedback_block'>
        <div class='feedback_header tr_like' >
          <span class='fbh_title'> 系統回報 </span>
          <a class='fbh_option' id='act_feedback_close' title='關閉' ><i class='mark16 pic_close'></i></a>
        </div>
        <div class='feedback_body' >
          <div class='fb_imgload'> 建立預覽中..</div>
          <div class='fb_preview'></div>
          <div class='fb_areasel'>
            <span>回報頁面:</span>
            <input type='radio' class='feedback_area_sel' name='feedback_area' value='system_body_block'>全頁面
            <input type='radio' class='feedback_area_sel' name='feedback_area' value='system_content_area'>中版面
            <input type='radio' class='feedback_area_sel' name='feedback_area' value='system_edit_area'>右版面
            <input type='radio' class='feedback_upload_sel' name='feedback_area' value='user_upload'><input type='file'  id='feedback_img_upload' >
          </div>
          <div class='fb_descrip'>
            <div class=''>
              <span class='fbd_title'>回報類型:</span>
              <input type='checkbox' class='feedback_type' name='fbd_type' value='資料問題' ><span >資料問題</span>，
              <input type='checkbox' class='feedback_type' name='fbd_type' value='系統問題' ><span >系統問題</span>，
              <input type='checkbox' class='feedback_type' name='fbd_type' value='使用問題' ><span >使用問題</span>，
              <input type='checkbox' class='feedback_type' name='fbd_type' value='建議回饋' ><span >建議回饋</span>，
              <input type='checkbox' class='feedback_type' name='fbd_type' value='其他' >其他:<input type='text' class='fbd_type_other' name='fbd_type_other' value='' >
            </div>
            <div class='fbd_title'>回報描述:</div>
            <textarea  class='feedback_content'  name='fbd_content'></textarea>
          </div>
        </div>
        <div class='feedback_bottom tr_like' >
          <a class='sysbtn btn_feedback' id='act_feedback_cancel' > <i class='mark16 pic_account_off'></i> 取 消 </a>
          <a class='sysbtn btn_feedback' id='act_feedback_submit' > <i class='mark16 pic_account_on'></i> 送 出 </a>		
        </div>
        </div>
      </div>      
	<!-- 系統Loading -->
    <div class='system_loading_area'>
	  <div class='loading_block' >
	    <div class='loading_string'> 系統處理中 </div>
		<div class='loading_image' id='sysloader'></div>
	    <div class='loading_info'>
		  <span >如果系統過久無回應，請按[ Esc ] 關閉 loading 版面，並重新操作剛才的動作.</span>
	    </div>
	  </div>
	</div>
  
    <div class='system_print_area' style='' id='print_dom'>
	  <div class='page_print_container'>
	    
		<h1>
		  <span>文物資料列印</span>
		  <span>
		    <button onclick="window.print()";>列印</button>
		    <button id='act_close_print_block'>關閉</button>
		  </span>
		</h1>
		<div class='print_group_block nonbreak'>
			<h2>
			  <span>文物基本資料</span>
			  <span>
			    列印<input type='checkbox' class='is_print' checked />
			  </span>
			</h2>
			<table class='print_table'>
			  <tr><th >文物編號 </th><td ><span class='print_value' id='P-V-store_id'></span><span class='print_value' id='P-store_orl'></span></td></tr>
			  <tr><th >全宗 </th><td class='print_value' id='P-V-fonds'></td></tr>
			  <tr><th >名稱 </th><td class='print_value' id='P-V-fonds'></td></tr>
			  <tr><th >類別 </th><td class='print_value' id='P-V-categories'></td></tr>
			  <tr><th >尺寸 </th><td class='print_value' id='P-V-size_info'></td></tr>
			  <tr><th >族群 </th><td class='print_value' id='P-V-ethnic'></td></tr>
			  <tr><th >所屬年代 </th><td class='print_value' id='P-V-period'></td></tr>
			  <tr><th >入藏年代 </th><td class='print_value' id='P-V-saved_year'></td></tr>
			  <tr><th >採集方式 </th><td class='print_value' id='P-V-acquire_type'></td></tr>
			  <tr><th >來源說明 </th><td class='print_value' id='P-V-acquire_info'></td></tr>
			  <tr><th >狀況代碼 </th><td class='print_value' id='P-V-status_code'></td></tr>
			  <tr><th >狀況簡述 </th><td class='print_value' id='P-V-status_descrip'></td></tr>
			  <tr><th >入庫時間 </th><td class='print_value' id='P-V-store_date'></td></tr>
			  <tr><th >存放地點 </th><td class='print_value' id='P-V-store_location'></td></tr>
			  <tr><th >架號 </th><td class='print_value' id='P-V-store_number'></td></tr>
			  <tr><th >箱號 </th><td class='print_value' id='P-V-store_boxid'></td></tr>
			  <tr><th >註銷資訊 </th><td ><span class='print_value' id='P-V-logout_flag'></span> : <span class='print_value' id='P-logout_descrip'></span></td></tr>
			  <tr><th >備註 </th><td class='print_value' id='P-V-remark'></td></tr>
			  <tr><th >影像數量 </th><td class='print_value' id='P-V-count_dofiles'></td></tr>
			</table>
		</div>
		<div class='print_group_block nonbreak'>
			<h2>
			  <span>研究引用</span>
			  <span>
			    列印<input type='checkbox' class='is_print' checked />
			  </span>
			</h2>
			<table class='print_table relate' id='print-research'>
			  <tbody class='print_template '>
				<tr><th >序號 </th><td class='print_value P-no'></td></tr>
				<tr><th >題目 </th>	<td class='print_value P-R-title'></td></tr>
				<tr><th >書籍期刊名稱 </th><td class='print_value P-R-source'></td></tr>
				<tr><th >作者 </th><td class='print_value P-R-author'></td></tr>
				<tr><th >發表年代 </th><td class='print_value P-R-pubyear'></td></tr>
				<tr><th >出版單位 </th><td class='print_value P-R-publisher'></td></tr>
			  </tbody> 
			</table>
		</div>
		
		<div class='print_group_block nonbreak'>
			<h2>
			  <span>展覽紀錄</span>
			  <span>
			    列印<input type='checkbox' class='is_print' checked />
			  </span>
			</h2>
			<table class='print_table relate' id='print-display' >
			  <tbody  class='print_template '>
				<tr><th >序號 </th><td class='print_value P-no'></td></tr>
				<tr><th >日期 </th><td class='print_value P-D-display_date'></td></tr>
				<tr><th >展覽主題 </th><td class='print_value P-D-display_topic'></td></tr>
				<tr><th >展覽地點 </th><td class='print_value P-D-display_place'></td></tr>
				<tr><th >展覽單位 </th><td class='print_value P-D-display_organ'></td></tr>
			  </tbody>
			</table>
		</div>
		
		<div class='print_group_block nonbreak' id='print_image_after'>
			<h2>
			  <span>異動紀錄</span>
			  <span>
			    列印<input type='checkbox' class='is_print' checked />
			  </span>
			</h2>
			<table class='print_table relate' id='print-movement' >
			  <tbody class='print_template '>
				<tr><th >序號 </th><td class='print_value P-no'></td></tr>
				<tr><th >類型 </th><td class='print_value P-M-move_type'></td></tr>
				<tr><th >地點 </th><td class='print_value P-M-move_location'></td></tr>
				<tr><th >說明 </th><td class='print_value P-M-move_reason'></td></tr>
				<tr><th >日期 </th><td class='print_value P-M-move_date'></td></tr>
				<tr><th >經手人 </th><td class='print_value P-M-move_handler'></td></tr>
			  </tbody> 
			</table>
		</div>
		
		<div class='print_group_block'>
			<h2>
			  <span>文物影像</span>
			  <span>
			    列印<input type='checkbox' class='is_print' checked />
			  </span>
			</h2>
			<ul id='print_images'>
			  <li class='print_template'></li>
			</ul>
	    </div>
		
		
	  </div>
	</div>
  
  </body>
</html>