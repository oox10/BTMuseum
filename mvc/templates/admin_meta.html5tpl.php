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
	
	<!-- Self -->
	<link rel="stylesheet" type="text/css" href="theme/css/css_default.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_main.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_meta_admin.css" />
	
	<script type="text/javascript" src="js_library.js"></script>
	<script type="text/javascript" src="js_admin.js"></script>
	<script type="text/javascript" src="js_meta_admin.js"></script>
	
	
	<!-- PHP -->
	<?php
	$user_info 		= isset($this->vars['server']['data']['user']) 		? $this->vars['server']['data']['user'] 	: array('user'=>array('user_name'=>'Anonymous'),'group'=>array());
	
	$data_filter    = isset($this->vars['server']['data']['filter']['submit']) 	? $this->vars['server']['data']['filter']['submit'] : array('search'=> [],'data_type'=>'collection','logout'=>0 );  
	$data_termhit   = isset($this->vars['server']['data']['filter']['termhit']) 	? $this->vars['server']['data']['filter']['termhit'] : array();  
	
	$data_list  	= isset($this->vars['server']['data']['search']['list']) 	? $this->vars['server']['data']['search']['list'] : array();  
	
	$data_count 	= isset($this->vars['server']['data']['search']['count']) 	? $this->vars['server']['data']['search']['count'] : 0;  
	$data_pageing 	= isset($this->vars['server']['data']['search']['range'])    ? $this->vars['server']['data']['search']['range'] : '1-50';
	$data_start 	= isset($this->vars['server']['data']['search']['start'])    ? $this->vars['server']['data']['search']['start'] : 1;
	
	
	$data_pterm 	= isset($this->vars['server']['data']['search']['pterm'])    ? $this->vars['server']['data']['search']['pterm'] : [];
	// 後分類欄位設定
	$post_query_fields = [
	  'list_store_type'=>'類別',
	  //'list_categories'=>'類別',
	  'list_ethnic'=>'族群',
	  'list_status_code'=>'狀況代碼',
	  'list_store_location'=>'存放位置',
	  'savedyear'=>'入藏年代',
	  'list_dotype'=>'影像類型'
	];
	
	$page_conf  	= isset($this->vars['server']['data']['page'])    ? $this->vars['server']['data']['page'] : array();
	
	//資料庫欄位設定
	$dbfield_conf   = isset($this->vars['server']['data']['dbfield']) 		? $this->vars['server']['data']['dbfield']: ['volume'=>[],'element'=>[]];  
	
	//綜合模組頁面訊息
	$module_info 		= isset($this->vars['server']['info']) ? $this->vars['server']['info'] : '';
    
	
    $meta_edit_flag = intval($user_info['user']['user_roles']['R00']) || intval($user_info['user']['user_roles']['R02']) ? 1 : 0;     // 是否可修改資料
	
	//user folder   
	$user_folders   = isset($this->vars['server']['data']['folder']) 		? $this->vars['server']['data']['folder']:[];  
	 
	
	
	?>
  </head>
  
  
  <body>
	<div class='system_main_area'>
	  <div class='system_manual_area'>
	  <?php include('area_admin_manual.php'); ?>
	  </div>
	  <div class='system_content_area wide_mode'>
        <div class='tool_banner' >
		  <ol id='system_breadcrumbs' typeof="BreadcrumbList" >
		  </ol>
		  <span class='account_option tool_right'>
		    <div class='account_info'>
			  <span id='acc_mark'><i class='m_head'></i><i class='m_body'></i></span>
			  <span id='acc_string'> 
			    <i class='acc_name'><?php echo $user_info['user']['user_name']; ?></i>
			    <i class='acc_group'><?php echo $user_info['user']['user_group']; ?></i>
			  </span>
			  <span id='acc_option'><a class='mark16 pic_more'></a> </span>
			</div>
		    <div class='account_control arrow_box'>
			  <ul class='acc_option_list'>
			    <li >
				  <label title='目前群組'> <i class="fa fa-university" aria-hidden="true"></i> 群組 </label>
				  <select id='acc_group_select'>
				    <?php foreach($user_info['group'] as $gset): ?>  
				    <option value='<?php echo $gset['id']?>' <?php echo $gset['now']?'selected':'' ?> > <?php echo $gset['name']; ?></option>
				    <?php endforeach; ?>
				  </select>
				</li>
				<li> 
				  <label> <i class="fa fa-user-secret" aria-hidden="true"></i> 角色 </label>
				  <span>
				    <?php foreach($user_info['group'] as $gid => $gset): ?>  
				    <?php if($gset['now']) echo join(',',$gset['roles']); ?>
				    <?php endforeach; ?>
				  </span> 
				</li>
				<li>
				  <label> <i class="fa fa-clock-o" aria-hidden="true"></i> 登入</label>
				  <span> <?php echo $user_info['login']; ?></span>
				</li>
			  </ul>
			  <div class='acc_option_final'>
			    <span id='acc_logout'> 登出 </span>
			  </div>
		    </div>
		  </span>
		</div>
		
		<div class='topic_banner'>
		  <div class='topic_header'> 
		    <div class='topic_title'> 資料管理模組 </div>
			<div class='topic_descrip'> 詮釋資料編輯與數位檔案管理 </div>
		  </div>
		  <div class='module_filter'>
		    <div class='filter_query'>
			  	<div class='filter_search_block'>
				  <ul id='fsconditions'>
				    <li class='fspackage _template'>
					  <select class='search_attr'>
					    <option value='+'> AND </option>
						<option value=' '> OR </option>
						<option value='-'> NOT </option>
					  </select>
					  <select class='search_field'>
					    <option value='store_no'>典藏號:</option>
						<option value='title'>名稱:</option>
						<option value='ethnic'>所屬族群:</option>
						<option value='store_information'>儲存位置:</option>
						<option value='remark'>備註:</option> 
						<option value='dotype'>影像類型:</option> 
					  </select>
					  <input type='text' class='search_terms'  value='' placeholder='搜尋條件' />  
					  <a class='option act_remove_fspackage' title='刪除搜尋條件'><i class="fa fa-times" aria-hidden="true"></i></a>
					</li>
				    <li class='fsdefault'><input type='text' id='filter_search_terms'  value='<?php echo isset($data_filter['search'][0]) ? $data_filter['search'][0]['value']:''; ?>' placeholder='輸入搜尋關鍵字' /></li>
				    <?php if(count($data_filter['search'])) array_shift($data_filter['search']); ?>
					<?php if(count($data_filter['search'])): ?>
					<?php   foreach($data_filter['search'] as $search ): ?>
					<li class='fspackage'>
					  <select class='search_attr'>
					    <option value='+' <?php echo $search['attr']=='+'?'selected':'' ?> > AND </option>
						<option value=' ' <?php echo $search['attr']==' '?'selected':'' ?>> OR </option>
						<option value='-' <?php echo $search['attr']=='-'?'selected':'' ?>> NOT </option>
					  </select>
					  <select class='search_field'>
					    <option value='store_id' 	<?php echo $search['field']=='store_id'?'selected':'' ?>  >典藏號:</option>
						<option value='title'		<?php echo $search['field']=='title'?'selected':'' ?>  >名稱:</option>
						<option value='ethnic'		<?php echo $search['field']=='ethnic'?'selected':'' ?>  >所屬族群:</option>
						<option value='store_information'	<?php echo $search['field']=='store_information'?'selected':'' ?>  >儲存位置:</option>
						<option value='remark'		<?php echo $search['field']=='remark'?'selected':'' ?>  >備註:</option> 
						<option value='dotype'		<?php echo $search['field']=='dotype'?'selected':'' ?>  >影像類型:</option> 
					  </select>
					  <input type='text' class='search_terms'   placeholder='搜尋條件' value="<?php echo $search['value']; ?>"/>  
					  <a class='option act_remove_fspackage' title='刪除搜尋條件'><i class="fa fa-times" aria-hidden="true"></i></a>
					</li>
					<?php   endforeach; ?>
					<?php endif; ?>
					
					
				  </ul>
				  <a class='option' id='fsconditionadd' title='新增搜尋條件'><i class="fa fa-plus" aria-hidden="true"></i></a>
				</div>
				<span class='filter_option' >  
				  <button id='filter_submit'  type='button' class='active'><i class="fa fa-search" aria-hidden="true"></i> 篩選 </button> 
				  <a id='reset_filter' class='option' > <i class="fa fa-refresh" aria-hidden="true"></i> 清空條件</a>
				</span>
			</div>
			<ul class='filter_set'>
			  <li>
			    <label id=''>層級篩選：</label>
				<input type='radio' class='typesel' name='data_type' value='collection'	<?php echo $data_filter['data_type']=='collection' ? 'checked':''?> /> <span class='zname'>文物</span>
				<input type='radio' class='typesel' name='data_type' value='element'	<?php echo $data_filter['data_type']=='element' ? 'checked':''?> /> <span class='zname'>影像</span>
			  </li>
			  <li>
			    <label id=''>註銷篩選：</label>
				<input type='checkbox' class='logoutsel' name='logout_flag' value='1'	<?php echo $data_filter['logout']=='1' ? 'checked':''?> /> <span class='zname'>已註銷文物</span>
			  </li>
			  <!--
			  <li>
			    <label>管理篩選：</label>
				<input type='radio' class='handler' name='creater'  value='_all' checked  >所有資料 
				<input type='radio' class='handler' name='creater'  value='_self' >我的資料
			  </li>
			  
			  <li>
			    <label>日期篩選：</label>
				<span class='input_date' ><input type='text' id='filter_date_start' placeholder='日期-起' size='10' value='<?php echo isset($data_filter['search']['date_start']) ? $data_filter['search']['date_start'] : '';  ?>' /><i class="fa fa-calendar" aria-hidden="true"></i></span>
				<span class='input_date' ><input type='text' id='filter_date_end'   placeholder='日期-迄' size='10' value='<?php echo isset($data_filter['search']['date_end']) ? $data_filter['search']['date_end'] : '';  ?>' /><i class="fa fa-calendar" aria-hidden="true"></i></span>
			  </li>
			  -->
			</ul>
			
		  </div>
		  
		</div>
		
		<div class='main_content' >
		  <!-- 資料列表區 -->
		  <div class='data_record_block' id='record_selecter' >
		    <div class='record_header'>
			  <ul id='record_set_switcher'>
			    <li class='rsettag _atthis' data-folder='search' mode='save' id='default_tag' > 
				  <label class='act_attach_folder' >資料清單</label> 
				</li>
				<?php if(count($user_folders)): ?>
				<?php   foreach($user_folders as $fid=>$folder): ?>
				<li class='rsettag' data-folder='<?php echo $fid;?>' mode='save' >
				  <a class='option act_remove_folder'>
				    <i class="fa fa-times" aria-hidden="true" title='刪除資料夾'></i>
				    <i class="fa fa-reply" aria-hidden="true" title='還原刪除'></i>
				  </a>
				  <label class='act_attach_folder' > <?php echo $folder['name'];?> </label>
				  <a class='option act_active_folder' >
				    <i class='set_counter'  ><?php echo count($folder['result']);?></i>
				    <i class="fa fa-cart-arrow-down" aria-hidden="true" title='加入資料夾'></i>
					<i class="fa fa-arrow-up" aria-hidden="true" title='移出資料夾'></i>
                    <i class="fa fa-floppy-o" aria-hidden="true" title='儲存' ></i>
				  </a>
				</li>  
				<?php   endforeach; ?>
				<?php endif; ?>
				 
				<li class='rsetadd' id='act_create_folder' title='新增工作區' ><a class='option'><i class="fa fa-plus" aria-hidden="true"></i></a></li>
			  </ul>
			  
			  <span class='record_option'>
			    <label>批次處理：</label>
				<select id='act_record_batch_to'>
				  <optgroup label='匯出勾選' >
				    <option value='export'   title='匯出勾選資料' >批次匯出</option>
					<option value='import'   title='回傳更新'     disabled >批次匯入-尚未開放</option>
					<option value='print' title='勾選匯出報表' >列印報表</option>
				  </optgroup>
				  <optgroup label='創建群組' id='folder_list' >
				    <?php if(count($user_folders)): ?>
				    <?php   foreach($user_folders as $fid=>$folder): ?>
				    <option value='folder' f='<?php echo $fid;?>' >加入 - <?php echo $folder['name']; ?> </option>
				    <?php   endforeach; ?>
				    <?php endif; ?>
				  </optgroup>
				  <!--
				  <?php if($meta_edit_flag): ?>
				  <optgroup label='資料設定' >
				    <option value='logout/1' title='將勾選文物設定為註銷狀態' >批次註銷</option>
					<option value='open/0' title='檢索系統中找不到目前勾選的資料' >資料關閉</option>
				    <option value='open/1' title='檢索系統中可以查詢目前勾選的資料' >資料開啟</option>
					<option value='view/開放' title='數位檔案開放於網路上閱覽' >開放閱覽</option>
				    <option value='view/不開放' title='不提供數位檔案閱覽'>不開放閱覽</option>
				  </optgroup>
				  <?php endif; ?>
				  -->
				  
				</select>
				<button type='button' class='active' id='act_execute_batch'>執行</button>
			  </span>
			</div> 
			<div class='record_body'>
		      
			    <div class='record_folder' id='search' > <!-- 主要資料表 --> 
			  
				  <div class='record_control'>
					<span class='record_limit'>  
					  每頁 :
					  <select class='record_pageing' >
						<option value='1-20'   <?php echo $data_pageing=='1-20'? 'selected':''; ?> > 20 </option>
						<option value='1-50'   <?php echo $data_pageing=='1-50'? 'selected':''; ?> > 50 </option>
						<option value='1-100'  <?php echo $data_pageing=='1-100'? 'selected':''; ?> > 100 </option>
						<option value='_all' <?php echo $data_pageing=='_all'? 'selected':''; ?> > 全部 </option>
					  </select> 筆
					  / 共 <span> <?php echo $data_count; ?></span>  筆
					</span>
					<span class='record_pages'>
					  <a class='page_tap page_to' page='<?php echo $page_conf['prev'];?>' > &#171; </a>
					  <span class='page_select'>
					  <?php foreach($page_conf['list'] as $p=>$limit ): ?>
					  <a class="page_tap <?php echo $p==$page_conf['now'] ? 'page_now':'page_to'; ?>" page="<?php echo $limit;?>" ><?php echo $p; ?></a>
					  <?php endforeach; ?>
					  </span>
					  <a class='page_tap page_to' page='<?php echo $page_conf['next'];?>' > &#187; </a>
					  ，跳至
					  <select class='page_jump'>
						<optgroup label="首尾頁">
						  <option value='<?php echo $page_conf['top'];?>' >首頁</option>
						  <option value='<?php echo $page_conf['end'];?>' >尾頁</option>
						</optgroup>
						<optgroup label="-">
						  <?php foreach($page_conf['jump'] as $p=>$limit ): ?>
						  <option value="<?php echo $limit; ?>"  <?php echo $p==$page_conf['now'] ? 'selected':''; ?> ><?php echo 'P.'.$p; ?></option>
						  <?php endforeach; ?>
						</optgroup>					  
					  </select>
					</span>
				  </div>
				  <div class='record_content' >
					  <div class='record_arrange'>
					  <?php foreach($data_pterm as $tfild => $terms): ?>
						<?php if(count($terms)): ?>
						<h1>
						  <span><?php echo isset($post_query_fields[$tfild]) ? $post_query_fields[$tfild] : '欄位統計'; ?> </span>
						  <i>：<?php echo count($terms); ?> 項</i>
						</h1>
						<ul class='term_filter'>
						<?php foreach($terms as $t): ?>  
						  <li class='term_info ' >
							<input type='checkbox' 
								   class='pqterm'  
								   name='<?php echo $tfild?>' 
								   value='<?php echo $t['key'];?>' 
								   <?php echo isset($data_filter['pquery'][$tfild])&&in_array($t['key'],$data_filter['pquery'][$tfild]) ?  'checked':''?>   
							> 
							<span><?php echo $t['key'];?></span>
							<i><?php echo $t['doc_count'];?></i>
						  </li>
						<?php endforeach; ?>
						</ul>
						<?php endif; ?>
					  <?php endforeach; ?>
					  </div>
				  
					  <table class='record_list' id='tasks_list'>
						<tr class='data_field'>
						  <td title='no'	><input type='checkbox' class='act_select_all'  >no.</td>
						  <td title='縮圖'	>縮圖</td>
						  <td title=''		>典藏號</td>
						  <td title=''		>名稱</td>
						  <td title=''		>存放位置</td>
						  <td title=''		>圖檔</td>
						  <td title=''		>入庫時間</td>
						  <td title=''		align=center ><button type='button' class='active' id='act_volume_create' ><i class="fa fa-plus" aria-hidden="true"></i></option></td>
						</tr>
						<tbody class='data_result' mode='list' >   <!-- list / search--> 
						<?php foreach($data_list as $i=>$data): ?>  
						  
						  <?php if($data['_source']['data_type']=='collection'): ?>
						  <tr class='data_record' collection='<?php echo $data['_source']['collection']; ?>'  no='<?php echo $data['_id'];?>' status='' >
							<?php //處理搜尋標示 
							$data_display = $data['_source'];
							$pattern = array();
							if(count($data_termhit)){    
							  foreach($data_termhit as $term){
								$pattern = '@('.preg_quote($term).')@u';  
							  }		
							}
							foreach($data_display as $key => $meta){
							  if(is_array($meta)) $meta = join('；',$meta);  
							  $data_display[$key] = count($pattern) ? preg_replace($pattern,'<hit>\\1</hit>',$meta) : $meta;
							}
							?>
							<td class='meta_no'><input type='checkbox' class='act_selector' value='<?php echo $data['_id'];?>'> <?php echo $i+$data_start;?> </td>
							<td class='meta_thumb'><img src='thumb.php?src=<?php echo $data['_source']['zong'];?>/browse/<?php echo $data['_source']['collection'];?>/<?php echo $data['_source']['collection'];?>-002.jpg' /></td>
							<td class='meta_id'><?php echo $data_display['store_id'];?></td>
							<td class='meta_title'><?php echo $data_display['title'];?></td>
							<td class='meta_store'><?php echo $data_display['store_location'];?> / <?php echo $data_display['store_number'];?> / <?php echo $data_display['store_boxid'];?></td>
							<td class='meta_donum'><?php echo $data_display['count_dofiles'];?></td>
							<td class='meta_saved'><?php echo $data_display['store_date'];?></td>
							<td class='meta_option'>
							  <button type='button' class='active act_meta_getin' flag-editable='<?php echo $meta_edit_flag;?>'>
								<span class='act_editable'  ><i class="fa fa-pencil" aria-hidden="true"></i> 編輯</span> 
								<span class='act_viewable'  ><i class="fa fa-eye" aria-hidden="true"></i> 檢視</span>
							  </button>
							</td>			   
						  </tr> 
						  
						  
						  <?php else: //影像 ?>
						  <tr class='data_record' collection='<?php echo $data['_source']['collection']; ?>'  no='<?php echo $data['_id'];?>' status='' >
							<?php //處理搜尋標示 
							$data_display = $data['_source'];
							$pattern = array();
							if(count($data_termhit)){    
							  foreach($data_termhit as $term){
								$pattern = '@('.preg_quote($term).')@u';  
							  }		
							}
							foreach($data_display as $key => $meta){
							  if(is_array($meta)) $meta = join('；',$meta);  
							  $data_display[$key] = count($pattern) ? preg_replace($pattern,'<hit>\\1</hit>',$meta) : $meta;
							}
							?>
							<td class='meta_no'><input type='checkbox' class='act_selector' value='<?php echo $data['_id'];?>'> <?php echo $i+$data_start;?> </td>
							<td class='meta_thumb'><img src='thumb.php?src=<?php echo $data['_source']['zong'];?>/browse/<?php echo $data['_source']['collection'];?>/<?php echo $data['_source']['identifier'];?>.jpg' /></td>
							<td class='meta_id'><?php echo $data['_source']['store_id'];?></td>
							<td class='meta_title'><?php echo $data['_source']['title'];?></td>
							<td class='meta_store'><?php echo $data['_dbsource']['collection']['store_location'];?> / <?php echo $data['_dbsource']['collection']['store_number'];?> / <?php echo $data['_dbsource']['collection']['store_boxid'];?></td>
							<td class='meta_donum'> 1 </td>
							<td class='meta_saved'><?php echo $data['_dbsource']['collection']['store_date'];?></td>
							<td class='meta_option'>
							  <button type='button' class='active act_meta_getin' flag-editable='<?php echo $meta_edit_flag;?>'>
								<span class='act_editable'  ><i class="fa fa-pencil" aria-hidden="true"></i> 編輯</span> 
								<span class='act_viewable'  ><i class="fa fa-eye" aria-hidden="true"></i> 檢視</span>
							  </button>
							</td>			   
						  </tr> 
						  <?php endif; ?>
											
						<?php endforeach; ?>
						</tbody>
					  </table>
				  
				  </div>
				  <div class='record_control'>
					<span class='record_result'>  
					  顯示 <span> <?php echo $data_pageing; ?> </span> /
					  共 <span> <?php echo $data_count; ?></span>  筆
					</span>
					<span class='record_pages'>
					  <a class='page_tap page_to' page='<?php echo $page_conf['prev'];?>' > &#171; </a>
					  <span class='page_select'>
					  <?php foreach($page_conf['list'] as $p=>$limit ): ?>
					  <a class="page_tap <?php echo $p==$page_conf['now'] ? 'page_now':'page_to'; ?>" page="<?php echo $limit;?>" ><?php echo $p; ?></a>
					  <?php endforeach; ?>
					  </span>
					  <a class='page_tap page_to' page='<?php echo $page_conf['next'];?>' > &#187; </a>
					  ，跳至
					  <select class='page_jump'>
						<optgroup label="首尾頁">
						  <option value='<?php echo $page_conf['top'];?>' >首頁</option>
						  <option value='<?php echo $page_conf['end'];?>' >尾頁</option>
						</optgroup>
						<optgroup label="-">
						  <?php foreach($page_conf['jump'] as $p=>$limit ): ?>
						  <option value="<?php echo $limit; ?>"  <?php echo $p==$page_conf['now'] ? 'selected':''; ?> ><?php echo 'P.'.$p; ?></option>
						  <?php endforeach; ?>
						</optgroup>					  
					  </select>
					</span>
				  </div>
				  
				</div><!-- end of main record set-->  
                <!-- work folder -->
				<?php if(count($user_folders)): ?>
				<?php   foreach($user_folders as $fid=>$folder): ?>
				<?php    $data_start = 0; ?>
				<div class='record_folder' id='<?php echo $fid; ?>' >  
				  <textarea class='folder_remark' placeholder='工作備註'><?php echo $folder['remark'];?></textarea>
				  
				  <table class='record_list in_folder' >
						<tr class='data_field'>
						  <td title='no'	><input type='checkbox' class='act_select_all'  >no.</td>
						  <td title='縮圖'	>縮圖</td>
						  <td title=''		>典藏號</td>
						  <td title=''		>名稱</td>
						  <td title=''		>存放位置</td>
						  <td title=''		>圖檔</td>
						  <td title=''		>入庫時間</td>
						  <td title=''		align=center > </td>
						</tr>
						<tbody class='data_result' mode='list' >   <!-- list / search--> 
						<?php foreach($folder['result'] as $i=>$data): ?>  
						  
						  <?php if($data['_source']['data_type']=='collection'): ?>
						  <tr class='data_record' collection='<?php echo $data['_source']['collection']; ?>'  no='<?php echo $data['_id'];?>' status='' >
							<?php //處理搜尋標示 
							$data_display = $data['_source'];
							$pattern = array();
							if(count($data_termhit)){    
							  foreach($data_termhit as $term){
								$pattern = '@('.preg_quote($term).')@u';  
							  }		
							}
							foreach($data_display as $key => $meta){
							  if(is_array($meta)) $meta = join('；',$meta);  
							  $data_display[$key] = count($pattern) ? preg_replace($pattern,'<hit>\\1</hit>',$meta) : $meta;
							}
							?>
							<td class='meta_no'><input type='checkbox' class='act_selector' value='<?php echo $data['_id'];?>'> <?php echo $i+$data_start;?> </td>
							<td class='meta_thumb'><img src='thumb.php?src=<?php echo $data['_source']['zong'];?>/browse/<?php echo $data['_source']['collection'];?>/<?php echo $data['_source']['collection'];?>-002.jpg' /></td>
							<td class='meta_id'><?php echo $data_display['store_id'];?></td>
							<td class='meta_title'><?php echo $data_display['title'];?></td>
							<td class='meta_store'><?php echo $data_display['store_location'];?> / <?php echo $data_display['store_number'];?> / <?php echo $data_display['store_boxid'];?></td>
							<td class='meta_donum'><?php echo $data_display['count_dofiles'];?></td>
							<td class='meta_saved'><?php echo $data_display['store_date'];?></td>
							<td class='meta_option'>
							  <button type='button' class='cancel act_folder_out' title='移出資料夾' >
								<i class="fa fa-ban" aria-hidden="true"></i>
							  </button>
							  <button type='button' class='active act_meta_getin' flag-editable='<?php echo $meta_edit_flag;?>'>
								<span class='act_editable'  ><i class="fa fa-pencil" aria-hidden="true"></i> 編輯</span> 
								<span class='act_viewable'  ><i class="fa fa-eye" aria-hidden="true"></i> 檢視</span>
							  </button>
							</td>			   
						  </tr> 
						  
						  
						  <?php else: //影像 ?>
						  <tr class='data_record' collection='<?php echo $data['_source']['collection']; ?>'  no='<?php echo $data['_id'];?>' status='' >
							<?php //處理搜尋標示 
							$data_display = $data['_source'];
							$pattern = array();
							if(count($data_termhit)){    
							  foreach($data_termhit as $term){
								$pattern = '@('.preg_quote($term).')@u';  
							  }		
							}
							foreach($data_display as $key => $meta){
							  if(is_array($meta)) $meta = join('；',$meta);  
							  $data_display[$key] = count($pattern) ? preg_replace($pattern,'<hit>\\1</hit>',$meta) : $meta;
							}
							?>
							<td class='meta_no'><input type='checkbox' class='act_selector' value='<?php echo $data['_id'];?>'> <?php echo $i+$data_start;?> </td>
							<td class='meta_thumb'><img src='thumb.php?src=<?php echo $data['_source']['zong'];?>/browse/<?php echo $data['_source']['collection'];?>/<?php echo $data['_source']['identifier'];?>.jpg' /></td>
							<td class='meta_id'><?php echo $data['_source']['store_id'];?></td>
							<td class='meta_title'><?php echo $data['_source']['title'];?></td>
							<td class='meta_store'><?php echo $data['_dbsource']['collection']['store_location'];?> / <?php echo $data['_dbsource']['collection']['store_number'];?> / <?php echo $data['_dbsource']['collection']['store_boxid'];?></td>
							<td class='meta_donum'> 1 </td>
							<td class='meta_saved'><?php echo $data['_dbsource']['collection']['store_date'];?></td>
							<td class='meta_option'>
							  <button type='button' class='active act_meta_getin' flag-editable='<?php echo $meta_edit_flag;?>'>
								<span class='act_editable'  ><i class="fa fa-pencil" aria-hidden="true"></i> 編輯</span> 
								<span class='act_viewable'  ><i class="fa fa-eye" aria-hidden="true"></i> 檢視</span>
							  </button>
							</td>			   
						  </tr> 
						  <?php endif; ?>
											
						<?php endforeach; ?>
						</tbody>
					  </table>
				  
				</div>
				<?php   endforeach; ?>
				<?php endif; ?>
				<!-- end of work folder -->				

				
		    </div>
		  </div>
		  
		</div>
	  </div>
	</div>
	
	
	<!-- 框架外結構  -->
	<div class='system_message_area'>
	  <div class='message_block'>
		<div id='message_container'>
		  <div class='msg_title'><?php echo $module_info; ?></div>
		  <div class='msg_info'></div>
		</div>
		<div id='area_close'></div>
      </div>
	</div> 
	
	
	<!-- 批次匯出設定 -->
	<div class='system_popout_area' id='module_batch_export'    >
	    <div class='container'>
		  <div class='module_block' >
		    <h1>
			  <div class='md_header'>
			    <span class='md_title'>批次匯出模組 </span>
                <span >匯出目前勾選之 <i id='export_selected_count' >0</i> 筆資料</span>
				
			  </div>
			  <span class='area_close option' title='關閉'><i class="fa fa-times-circle" aria-hidden="true"></i></span>
			</h1>
			<div class='md_contents' id='batch_export_workaround' >
			  <h1>設定匯出欄位</h1>
			  <table id='export_field_setting' >
			    <tr>
				  <td><input type='checkbox' class='act_select_all_efield _master' level='_all' checked > 全部欄位</td>
				  <td></td>  
				</tr>
			    <tr>
				  <td><input type='checkbox' class='act_select_all_efield _efield _volume _master' level='volume' checked > 卷層級欄位</td>
				  <td class='fields'>
				    <?php if(isset($dbfield_conf['volume'])): ?>
					<ul class='field_list'>
                    <?php foreach($dbfield_conf['volume'] as $field_name => $field_conf): ?> 
					<?php  if($field_conf['descrip']=='' || !$field_conf['can_export']) continue; ?>  
					  <li><input type='checkbox' class='_efield _volume _member' name='volume_export_fields' value='<?php echo $field_name;?>' checked><?php echo $field_conf['descrip'];?></li>
					<?php endforeach;?> 
				    </ul>
				    <?php endif; ?>
				  </td>
				</tr>
			    <tr>
				  <td><input type='checkbox' class='act_select_all_efield _efield _element _master' level='element' checked> 件層級欄位</td>
				  <td class='fields'>
				    <?php if(isset($dbfield_conf['element'])): ?>
					<ul class='field_list'>
                    <?php foreach($dbfield_conf['element'] as $field_name => $field_conf): ?> 
                    <?php  if($field_conf['descrip']=='' || !$field_conf['can_export']) continue; ?>  
					  <li><input type='checkbox' class='_efield _element _member' name='element_export_fields' value='<?php echo $field_name;?>' checked><?php echo $field_conf['descrip'];?></li>
					<?php endforeach;?> 
				    </ul>
				    <?php endif; ?>
				  </td>
				</tr>
			  </table>
			  <h1>設定匯出格式</h1>
			  <div>
			    <select id='batch_export_format'>
				  <option value='xlsx' selected > Excel XLSX </option>
				</select>
			  </div>
			  
			  
			</div>
			<div class='md_footer'>
			  <div>
			    
			  </div>
			  <div>
				<button type="button" class="cancel" id="act_meta_batch_cancel">取消匯出</button>
				<button type="button" class="active" id="act_meta_batch_export">執行匯出</button>
			  </div>
			</div>
		  </div>
        </div>
	</div>
	
	<!-- 批次列印設定 -->
	<div class='system_popout_area' id='module_batch_printout'    >
	    <div class='container'>
		  <div class='module_block' >
		    <h1>
			  <div class='md_header'>
			    <span class='md_title'>批次列印模組 </span>
                <span >列印目前勾選之 <i id='printout_selected_count' >0</i> 筆資料</span>
				
			  </div>
			  <span class='area_close option' title='關閉'><i class="fa fa-times-circle" aria-hidden="true"></i></span>
			</h1>
			<div class='md_contents' id='batch_printout_workaround' >
			  <h1>設定列印欄位</h1>
			  <table id='printout_field_setting' >
			    <tr>
				  <td>列印頁面設定</td>
				  <td>
				    <div>是否列印影像：<input type='checkbox' id='act_printout_thumb' checked > </div>
					<hr/>
					<div>
					  每頁資料筆數：
					  <select id='printout_records' >
					    <option value=1> 每頁 1 筆 </option>
					    <option value=2> 每頁 2 筆 </option>
					    <option value=3> 每頁 3 筆 </option>
					    <option value=4> 每頁 4 筆 </option>
					    <option value=5 selected> 每頁 5 筆 </option>
					    <option value=6> 每頁 6 筆 </option>
					    <option value=7> 每頁 7 筆 </option>
					    <option value=8> 每頁 8 筆 </option>
					    <option value=9> 每頁 9 筆 </option>
                        <option value=10> 每頁 10 筆 </option>
					    <option value=11> 每頁 11 筆 </option>
					    <option value=12> 每頁 12 筆 </option>	
                        <option value=12> 每頁 13 筆 </option>	
                        <option value=12> 每頁 14 筆 </option>	
                        <option value=12> 每頁 15 筆 </option>	 						
					  </select>
					</div>
				  </td>  
				</tr>
			    <tr>
				  <td>列印欄位設定</td>
				  <td class='fields'>
				    <?php if(isset($dbfield_conf['volume'])): ?>
					<ul class='field_list'>
                    <?php foreach($dbfield_conf['volume'] as $field_name => $field_conf): ?> 
					<?php  if($field_conf['descrip']=='' || !$field_conf['can_printout']) continue; ?>  
					  <li>
						<input type='checkbox' 
						       class='_pfield' 
							   name='volume_printout_fields' 
							   title='<?php echo $field_conf['descrip'];?>'
							   value='<?php echo $field_name;?>' 
						       <?php echo in_array($field_conf['descrip'],['文物典藏號','文物名稱','文物類別','所屬族群','入藏年代']) ? 'checked' : '';?>
							   <?php echo in_array($field_conf['descrip'],['文物典藏號','文物名稱']) ? "disabled title='必輸出欄位'" : '';?>
						>
						  <?php echo $field_conf['descrip'];?>
					  </li>
					  
					<?php endforeach;?> 
				    </ul>
				    <?php endif; ?>
				  </td>
				</tr>
			    <tr>
				  <td colspan=2>
				  列印格式預覽
				  </td>
				</tr>
				<tr>
				  <td colspan=2>
				    <div class='printout_sample'>
				      <div id='print_thumb'><img src='theme/image/sample.jpg'></div>
				      <div id='print_fields'>
					    <table>
						  <tr class='data_title'><td colspan=2> 076-D-00-0000 / 銀項鍊</td></tr>
						  <tr class='data_field _sample'><td class='dfield'> </td><td class='dvalue'>  </td></tr> 
						  <tbody id='printout_fields_set'>
						    <tr class='data_field'><td class='dfield'> 文物類別 :</td><td class='dvalue'> 飾品 </td></tr> 
						    <tr class='data_field'><td class='dfield'> 所屬族群 :</td><td class='dvalue'> 原住民族 </td></tr> 
						    <tr class='data_field'><td class='dfield'> 入藏年代 :</td><td class='dvalue'> 1985 </td></tr> 
						  </tbody>
						</table>
					  </div>
				    </div>
				  </td>
				</tr>
			  </table>
			</div>
			<div class='md_footer'>
			  <div>
			    
			  </div>
			  <div>
				<button type="button" class="active" id="act_meta_batch_print_rander">生成列印頁面</button>
			  </div>
			</div>
		  </div>
        </div>
	</div>
	
	<!-- 批次更新 -->
	<div class='system_popout_area' id='module_batch_upload'  data-upload=''  data-revise=''  flag-upload='' >
	    <div class='container'>
		  <div class='module_block' >
		    <h1>
			  <div class='md_header'>
			    <span class='md_title'>資料批次更新模組 </span> 
			  </div>
			  <span class='area_close option' title='關閉'><i class="fa fa-times-circle" aria-hidden="true"></i></span>
			</h1>
			<div class='md_contents' id='batch_upload_workaround' >
			  <h1> 步驟一：選擇要上傳的excel</h1>
			  <div class='batch_file_upload'>
				<div id='upload_form'  >
				  <form class='file_upload_area' id='file_upload_form' action="index.php?act=Meta/batchupload/" method="post" enctype="multipart/form-data" target="upload_target" >
				    <input id='meta_excel_select' name="file" type="file" placeholder='請選擇要上傳的excel檔案' />
				  </form> 
				  <iframe id="upload_target" name="upload_target" src="loader.php" ></iframe>  
				</div>
				<button id='act_upload_batch_excel' title='執行上傳' ><i class="fa fa-upload" aria-hidden="true"></i> 上傳批次檔案 </button>
			  </div>
			  
			  <h1> 
			    <span>步驟二：執行資料檢查：</span>
				<div id='upload_result'>
				  <span id='uploaded_file'></span>
				  <span id='uploaded_size'></span>
				</div>
				<button id='act_check_uploaded' ><i class="fa fa-eye" aria-hidden="true"></i> 檢查上傳內容 </button>
			  </h1>
			  <div class='batch_file_checker'>
				<div class="progress progress-large" id='upload_check_process' >
				  <span style="width: 0%">0%</span>
				</div>
				<table id='upload_check_table'>
				  <tr>
					<td> 權限 </td>
					<td> 新增 </td>
					<td> 刪除 </td>
					<td> 碰撞 </td>
					<td> 修改 </td>
				    <td> 錯誤 </td>
				    <td> 總數 </td>
				  </tr>
				  <tr>
					<td >
					  <span class='' id='upload_license' check='' >
					    <i class="fa fa-check" aria-hidden="true"></i> 
					    <i class="fa fa-times" aria-hidden="true"></i>					     
					  </span>
					</td>
					<td ><span class='meta_check_rsl' id='num_insert' >  </span></td>
					<td ><span class='meta_check_rsl' id='num_delete' >  </span></td>
					<td ><span class='meta_check_rsl' id='num_conflict' >  </span></td>
					<td ><span class='meta_check_rsl' id='num_modify' >  </span></td>
					<td ><span class='meta_check_rsl' id='num_fail' >  </span></td>
					<td ><span class='meta_check_rsl' id='num_total' >  </span></td>
				  </tr>
				</table>
			  </div>  
			  
			  <h1>
			    <span>
				  步驟三：資料處理紀錄 
				  <button id='act_get_revisefile'><i class="fa fa-file-excel-o" aria-hidden="true"></i> 下載查驗結果 </button>
				</span>
				<span>
				  <button id='act_execute_import'><i class="fa fa-repeat" aria-hidden="true"></i> 執行資料更新 </button>
				</span>
			  </h1>
			  <ul class='batch_file_analysis' id='meta_check_list'>
			    
			  </ul>
			  
			</div>
			<div class='md_footer'>
			  <div>
			    <span>批次更新結果</span> 
				<button id='act_renew_batch_file'><i class="fa fa-file-excel-o" aria-hidden="true"></i> 重新下載更新後批次資料 </button>
			    <button id='act_renew_batch_skip'><i class="fa fa-file-excel-o" aria-hidden="true"></i> 下載檢測有問題部分</button>
			  </div>
			  <div>
				<button type="button" class="cancel" id="act_meta_batch_cancel">取消</button>
				<button type="button" class="active" id="act_meta_batch_finish">完成</button>
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
  
  </body>
</html>