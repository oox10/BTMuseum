/* [ Admin Meta Admin Function Set ] */
	
$(window).on('load',function () {   //  || $(document).ready(function() {		
	
	
	/***------------------------***/
	/*   [ META PAGE FUNCTION ]   */
	/***------------------------***/
	/* == 詮釋資料管理
	   - 資料列表
       - 資料搜尋
       - 批次處理
       - 卷宗管理	   
	*/
	
	
	/***== [ META LIST AD FUNCTION ] ==***/ //資料列表管理函數
	//------------------------------------------------------------------------------------------------------
	
	
	
	/*== Meta Search function Set : 資料搜尋 ==*/
	
	//@-- reset object function : 重新設定搜尋方式
	$( ".search_input").unbind( "keydown keyup" );
    $(".act_search").click(function(){});
	
	//-- data record filter  : 篩選類型選單
	$("input[type='radio'][name='record_type']").click(function(){
	  if($(this).prop('checked')){
		var record_flag = $(this).val();
		location.search='?act=Built/index/'+record_flag;
	  }	
	});
	
	//@-- datepicker initial : 篩選日期選單
	$("#filter_date_start,#filter_date_end").datepicker({
	    dateFormat: 'yy-mm-dd',
	    onClose: function(dateText, inst) { 
	      if(/\d{4}-\d{2}-\d{2}$/.test(dateText)){
		    $(this).val(dateText);
		  }
	    } 
	});
	
	// 全宗單選
	$('.zname').click(function(){
	  $('.zname').removeClass('selected');
	  $(".zselect").prop('checked',false);
	  $(this).addClass('selected').prev().prop('checked',true);
	});
	
	
	// 取得檢索設定
	function get_search_condition(){
	  // 檢索條件
	  var search = {};
	  
	  // 搜尋
	  search['search'] = [];
	  search['search'].push({'field':'_all','value':$('#filter_search_terms').val(),'attr':'+'});
	  $('.fspackage').not( "._template" ).each(function(){
		var condition = {};
        condition.field = $(this).find('.search_field').val();
        condition.value = $(this).find('.search_terms').val();
		condition.attr  = $(this).find('.search_attr').val();
		search['search'].push(condition);
	  });
	  
	  // 勾選搜尋層級
	  if($('.typesel').length){
		if($('.typesel')[0].tagName=='INPUT'){
		  search['data_type'] = $("input[name='data_type']:checked").val();   	
		} 
	  }
	  
	  // 篩選是否註銷
	  if($('.logoutsel').length){
		if($('.logoutsel')[0].tagName=='INPUT'){
		  search['logout'] = $("input[name='logout_flag']").prop('checked') ? 1 : 0;   	
		} 
	  }
	  
	  // 勾選後分類
	  search['pquery'] = {};
	  $('.pqterm:checked').each(function(){
		var pfield = $(this).attr('name');
        var pterm  = $(this).val();
        if(typeof search['pquery'][pfield] == 'undefined') search['pquery'][pfield] = [];
		search['pquery'][pfield].push(pterm);
	  })
	  
	  return search;
	}
	
	//-- search submit  : 搜尋資料
	$('#filter_submit').click(function(){
	   var search = get_search_condition();
	   location.href = 'index.php?act='+$('.inthis').attr('id')+'/index/'+$('.record_pageing').val()+'/'+encodeURIComponent(Base64M.encode(JSON.stringify(search)));
	});
	
	//-- search filter : 後分類單選
	$('.term_info > span').click(function(){
      var main_dom = $(this).parents('li');
	  $('.pqterm').prop('checked',false);
	  main_dom.find('input').prop('checked',true);
	  $('#filter_submit').trigger('click');
	})
	
	//-- reset_filter
	$('#reset_filter').click(function(){
  	  $('.zselect').prop('checked',true);
	  $('.mlimit').prop('checked',false);
	  $('#filter_date_start,#filter_date_end,#filter_search_terms').val('');
	  $('li.fspackage').remove();
	  $('.pqterm').prop('checked',false);
	  $("input[name='logout_flag']").prop('checked',false);
	});
	
	
	//-- add field search condition
	$('#fsconditionadd').click(function(){
	  var fsdom = $('li.fspackage._template').clone().removeClass('_template');
	  fsdom.appendTo('#fsconditions');
	});
	
	//-- del field search condition
	$(document).on('click','.act_remove_fspackage',function(){
	  $(this).parents('li.fspackage').remove();
	});
	
	
	/*== Meta Pager function Set : 資料分頁函數 ==*/
	
	//@-- unbind default pager : 取消預設分頁方式
	$(document).off('click','.page_to');  
	
	//@-- pager clicker 換頁
	$('.page_to').click(function(){
	  if(!$(this).attr('page')){
	    return false;
	  }	
	  var link = location.search.replace('/#.*?$/','').split('/');
	  link[2] = $(this).attr('page');
	  location.search = link.join('/');
	});
	
	//@-- page jump
	$('.page_jump').change(function(){
	  if(!$(this).val()){
	    return false;
	  }	
	  var link = location.search.replace('/#.*?$/','').split('/');
	  link[2] = $(this).val();
	  location.search = link.join('/');
	});
	
	//@-- page size change : 分頁模式切換
	$('.record_pageing').change(function(){
	  $('#filter_submit').trigger('click');	
	});
	
	
	
	/***== [ META FOLDER AD FUNCTION ] ==***/ //資料夾管理介面
	//------------------------------------------------------------------------------------------------------
	
	
	//-- create new folder 新增資料夾
	$('#act_create_folder').click(function(){
	  
      if(!$("li.rsettag[data-folder='myfolder']").length){
		system_message_alert('','尚未開放');  
	    return false;
	  }
	  if($("li.rsettag[mode='init']").length){
		system_message_alert('','請先儲存先前新增之資料夾');  
	    return false;
	  }  
	  if($('li.rsettag').length >4 ){
		system_message_alert('','超過資料夾上限!!');  
	    return false;  
	  }
	  var newfoldertag = $("li.rsettag[data-folder='myfolder']").clone();
      newfoldertag.attr({'mode':'init','data-folder':'newfolder'}).removeClass('_atthis');
	  newfoldertag.find('label').prop('contenteditable',true);
	  newfoldertag.insertBefore($(this));
	});
	
	//-- attach orl folder 進入資料夾
	$(document).on('click','.act_attach_folder',function(){
	  var main_tags = $(this).parents('li.rsettag');
	  
	  if(main_tags.attr('mode')!='save'){
		return true;
	  }
	  
	  if($('.record_folder#'+main_tags.data('folder')).length){
		$('.record_folder').hide();  
		$('.record_folder#'+main_tags.data('folder')).show();  
	  }
	  
	  // active ajax
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Meta/folderatt/'+main_tags.data('folder')},
		beforeSend: function(){  system_loading(); },
		error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		success: 	function(response) {
		  if(response.action){
			$('.rsettag._atthis').removeClass('_atthis');
	        main_tags.addClass('_atthis');
		  }else{
			system_message_alert('',response.info);
		  }
		},
		complete:	function(){  }
	  }).done(function(r) {  system_loading(); }); 
	  
	});
	
	//-- remove id from folder 移出資料夾
	$(document).on('click','.act_folder_out',function(){
	  
	  var main_tag   = $('.rsettag._atthis');
	  var main_record= $(this).parents('.data_record');
	  
	  var folder     = {};
	  folder['ticket']  = main_tag.attr('data-folder'); 
	  folder['name']    = main_tag.find('label').text(); 
	  folder['records'] = [main_record.attr('no')]; 
	  
	  var paser_data = encodeURIComponent(Base64M.encode(JSON.stringify(folder)));
	  
	  // active ajax
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Meta/folderout/'+paser_data},
		beforeSend: function(){  system_loading(); },
		error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		success: 	function(response) {
		  if(response.action){
			main_tag.find('.set_counter').text(response.data.folder.count);
		    main_record.remove();
		  }else{
			system_message_alert('',response.info);
		  }
		},
		complete:	function(){  }
	  }).done(function(r) {  system_loading(); }); 
	  
	});
	
	
	
	//-- active folder 針對資料夾動作處理  insert / create
	$(document).on('click','.act_active_folder',function(){
	  var main_tag   = $(this).parents('li.rsettag');
	  var folder     = {};
	  folder['ticket']  = main_tag.attr('data-folder'); 
	  folder['name']    = main_tag.find('label').text(); 
	  folder['records'] = []; 
	  
	  var paser_data = encodeURIComponent(Base64M.encode(JSON.stringify(folder)));
	  
	  switch( main_tag.attr('mode')){
		
		case 'init':  //儲存新資料夾
         
		  // active ajax
		  $.ajax({
			url: 'index.php',
			type:'POST',
			dataType:'json',
			data: {act:'Meta/foldernew/'+paser_data},
			beforeSend: function(){  system_loading(); },
			error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
			success: 	function(response) {
			  if(response.action){
				
				// 新增資料列表區塊
                if(!$('#myfolder').length){
				  system_message_alert('','模組尚未開放');	
				  return true;
				}                  
                
				var new_folder_dom = $('#myfolder').clone() 				
                new_folder_dom.attr('id',response.data.folder.ticket).css('display','none');
                new_folder_dom.find('.data_result').empty();
				new_folder_dom.find('.folder_remark').val('');
				new_folder_dom.appendTo('.record_body');
				
				// 新增批次處理選單項目
                $('#folder_list').append("<option value='folder' f='"+response.data.folder.ticket+"'>加入 - "+response.data.folder.name+"</option>");
                
				// 更改標籤狀態                
				main_tag.attr({'mode':'save','data-folder':response.data.folder.ticket});
				main_tag.find('label').prop('contenteditable',false);
				main_tag.find('.set_counter').text(0);
				
				
			  }else{
				system_message_alert('',response.info);
			  }
			},
			complete:	function(){  }
		  }).done(function(r) {  system_loading(); }); 
		  break; 
		
        case 'save':  //勾選資料 放入 / 移出
          
		  if( !$('#'+folder['ticket']).length ){
			system_message_alert("","資料夾不存在");  
		  }
		  
		  // 取得目前勾選資料
		  var master_records = $('.record_folder:visible');
		  if(!master_records.find('.act_selector:checked').length){
			system_message_alert("","尚未勾選資料");    
		  }
		  
		  folder['records']= master_records.find('.act_selector:checked').map(function(){return $(this).val(); }).get();
		  paser_data = encodeURIComponent(Base64M.encode(JSON.stringify(folder)));
		  
		  var active = (main_tag.hasClass('_atthis')) ? 'folderout':'folderadd';
		  var method = (main_tag.hasClass('_atthis')) ? '移出':'加入';
		  
		  if(!confirm("請問確認要將勾選之 "+folder['records'].length+" 筆資料 "+method+"《"+folder['name'] +"》嗎?")){
			return false;  
		  }
		  
		  // active ajax
		  $.ajax({
			url: 'index.php',
			type:'POST',
			dataType:'json',
			data: {act:'Meta/'+active+'/'+paser_data},
			beforeSend: function(){  system_loading(); },
			error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
			success: 	function(response) {
			  if(response.action){
				
				var record_table = $('#'+folder['ticket']).find('.data_result');
				var record_count = record_table.find('.act_selector').length;
				
				if(active=='folderadd'){
				  $.each(response.data.folder.newadd,function(i,sid){
				    var folder_new_record = master_records.find(".data_record[no='"+sid+"']").clone();
				    folder_new_record.find('.act_selector').prop('checked',false);
					folder_new_record.find('.act_meta_getin').before("<button type='button' class='cancel act_folder_out' title='移出資料夾' ><i class='fa fa-ban' aria-hidden='true'></i></button>");
				    record_table.append(folder_new_record);
				  });
				}else{
				  $.each(folder['records'],function(i,sid){
				    master_records.find(".data_record[no='"+sid+"']").remove();
				  });	
				}
				
				main_tag.find('.set_counter').text(response.data.folder.count);
				system_message_alert('alert','已將勾選之'+folder['records'].length+'筆資料 '+method+' ['+folder['name']+']')
			  
			  }else{
				system_message_alert('',response.info);
			  }
			},
			complete:	function(){  }
		  }).done(function(r) {  system_loading(); }); 
		  break; 
		  
		  
		  
		  
		  break;
		  
		
		case 'dele':  //執行刪除 
		  
		  if(!confirm("確定要刪除此工作區\n確定後資料夾將會被移除!!?")){
			return false;  
		  }
		  
		  // active ajax
		  $.ajax({
			url: 'index.php',
			type:'POST',
			dataType:'json',
			data: {act:'Meta/folderdel/'+paser_data},
			beforeSend: function(){  system_loading(); },
			error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
			success: 	function(response) {
			  if(response.action){
				
				// 移除選單項目
                $('#folder_list').find("option[value='folder'][f='"+folder['ticket']+"']").remove();
				
				// 移除資料列表區塊
				if( $('#'+folder['ticket']).length ){
				  $('#'+folder['ticket']).remove(); 
				}                  
                // 移除標籤                
				main_tag.remove();
				
				$('#default_tag').find('label').trigger('click');
				
				
			  }else{
				system_message_alert('',response.info);
			  }
			},
			complete:	function(){  }
		  }).done(function(r) {  system_loading(); }); 
		  break; 
		  
		  break;
		
		default: 
		  system_message_alert('','未知的資料夾功能');
		  break;  
	  }
	  
	});
	
	
	
	
	//-- remove old folder 取消資料夾
	$(document).on('click','.act_remove_folder',function(){
	  
	  var main_tag   = $(this).parents('li.rsettag');
	  var folder     = {};
	  folder['name'] = main_tag.find('label').text(); 
	  
	   switch( main_tag.attr('mode')){
		
		case 'init':  //刪除尚未儲存的資料夾
		  if(!confirm("確定要刪除新增資料夾??")){
			return false;  
		  }
		  main_tag.remove();
		  break;
		
		case 'save':  //刪除已儲存的資料夾，轉變狀態
		  main_tag.attr('mode','dele');
		  break;
		  
		case 'dele':  //復原刪除
          main_tag.attr('mode','save');
		  break		
		  
		default: 
		  system_message_alert('','未知的資料夾功能');
		  break;  
		
	   }
	  
	  
	});
	
    //-- auto save work remark 自動儲存目前工作備註 
	$(document).on('change','.folder_remark',function(){
      var main_tag   = $('.rsettag._atthis');
	  var folder     = {};
	  folder['ticket']  = main_tag.attr('data-folder'); 
	  folder['remark']  = $(this).val(); 
      
	  var paser_data = encodeURIComponent(Base64M.encode(JSON.stringify(folder)));
	  
	  // active ajax
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Meta/foldernote/'+paser_data},
		beforeSend: function(){ },
		error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		success: 	function(response) {
		  if(!response.action){
			system_message_alert('',response.info);
		  }
		},
		complete:	function(){  }
	  }).done(function(r) {  }); 
	  
	});
	
	 
	
	
	/***== [ META BATCH AD FUNCTION ] ==***/ //批次資料管理介面
	//------------------------------------------------------------------------------------------------------
	
	//-- record select all 全選本頁
	if($('.act_select_all').length){
	 
	  $('.act_select_all').change(function(){
		var master_table = $(this).parents('.record_list');	  
		master_table.find('.act_selector').prop('checked',$(this).prop('checked')); 
	  });
	
	}
	
	//-- record select one 單選本頁
	$('.act_selector').click(function(){
	  var master_table = $(this).parents('.record_list');
	  var select_all_fleg = $('.act_selector').length == $('.act_selector:checked').length ? true : false;
	  master_table.find('.act_select_all').prop('checked',select_all_fleg);  	
	});
 
	
	
	//-- select batch function
	$('#act_execute_batch').click(function(){
	  
	  if(!$('#act_record_batch_to').val()){
		system_message_alert('','尚未選擇執行工作');  
	    return false;
	  }
	  
	  var act_action = $('#act_record_batch_to').val();
	  var act_name   = $("#act_record_batch_to").find("option[value='"+act_action+"']").html();
	  var act_info   = $("#act_record_batch_to").find("option[value='"+act_action+"']").attr('title');
	  var master_records = $('.record_folder:visible');
	  var records    = master_records.find('.act_selector:checked').map(function(){return $(this).val(); }).get();
	  
	  switch(act_action){
		
		case 'folder': 
		  if(!records.length){
		    system_message_alert('','尚未選擇資料');  
	        return false;
	      }
		  var folder_id = $('#act_record_batch_to').find('option:selected').attr('f');
	      if($("li.rsettag[data-folder='"+folder_id+"']").length){
			$("li.rsettag[data-folder='"+folder_id+"']").find('.act_active_folder').trigger('click');  
		  }
		  
		  
		  break;
		
		case 'export':  //資料匯出
          if(!records.length){
		    system_message_alert('','尚未選擇資料');  
	        return false;
	      }
		  
		  $('#module_batch_export').find('#export_selected_count').html(records.length).end().show();
		  break;
		
		case 'import':
		  meta_batch_upload_module_initial();
          $('#module_batch_upload').show();  		
		  break;
		  
		case 'print':
		  if(!records.length){
		    system_message_alert('','尚未選擇資料');  
	        return false;
	      }
		  
		  $('#module_batch_printout').find('#printout_selected_count').html(records.length).end().show();
		  
		  break;
		
		default: system_message_alert('','功能尚未開放'); break;
		
		
		
	  }
	   
	  return false;
	 
	  
	  
	  // confirm to admin
	  if(!confirm("確定要對勾選 [ "+records.length+" ] 筆資料執行 : "+act_name+"?,\n這將會使得 "+act_info+"")){
	    return false;  
	  }
	  
	  var paser_data = encodeURIComponent(Base64M.encode(JSON.stringify(records)));
	  
	  
	  if(act_action=='export'){
		
	  }else{
		  // active ajax
		  $.ajax({
			url: 'index.php',
			type:'POST',
			dataType:'json',
			data: {act:'Meta/batch/'+paser_data+'/'+act_action},
			beforeSend: function(){  system_loading(); },
			error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
			success: 	function(response) {
			  if(response.action){
				system_message_alert('alert','已成功執行:'+act_name+' / '+response.data.batch+' 筆');
				$('#act_record_batch_to').val('');
				$('.act_selector').prop('checked',false);
				var batch_set = act_action.split('/');
				$.each(records,function(i,no){
				  $(".data_record[no='"+no+"']").find('.status._variable.'+batch_set[0]).attr('data-flag',batch_set[1]);	
				});
			  }else{
				system_message_alert('',response.info);
			  }
			},
			complete:	function(){  }
		  }).done(function(r) {  system_loading(); });    
	  }	


      // reset
	  $('#act_record_batch_to').val('');
	  
	});
	
	
	/* [Meta Batch Export] */ 
	
	
	//-- batch export select data //勾選資料批次匯出
	$('#act_meta_batch_export').click(function(){
		
		if(!$('.act_selector:checked').length){
		  system_message_alert('','尚未勾選匯出資料');
		  return false;	
		}
		if(!$('._efield:checked').length){
		  system_message_alert('','尚未勾選匯出欄位');
		  return false;		
		}
		
		var records    = $('.act_selector:checked').map(function(){return $(this).val(); }).get();
		var efields    = {};
		efields['volume']  = $('._efield._member._volume:checked').map(function(){return $(this).val(); }).get();
		efields['element'] = $('._efield._member._element:checked').map(function(){return $(this).val(); }).get();
		
		var paser_objects = {
		  'records':records,
          'efields':efields
		};
	 
		
		var paser_data = encodeURIComponent(Base64M.encode(JSON.stringify(paser_objects)));
		 
		//-- 解決 click 後無法馬上open windows 造成 popout 被瀏覽器block的狀況
	    //  newWindow = window.open("","_blank");
		$.ajax({
		  url: 'index.php',
		  type:'POST',
		  dataType:'json',
		  data: {act:'Meta/batchexport/'+paser_data},
		  beforeSend: 	function(){ system_loading();  },
		  error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		  success: 		function(response) {
			if(response.action){  
			  window.location.href = 'index.php?act=Meta/getexport/'+response.data.batch.fname;
			  //newWindow.location.href = 'index.php?act=Meta/getexport/'+response.data.batch.fname;
			}else{
			  //newWindow.close();
			  system_message_alert('',response.info);
			}
		  },
		  complete:		function(){   }
	    }).done(function() { system_loading();   });		
		
	})
	
	
	//-- select all efields
	$('.act_select_all_efield').change(function(){
	  var chkstatus = $(this).prop('checked');
      var target_level = $(this).attr('level');
      if(target_level=='_all'){
		$('input._efield').prop('checked',chkstatus);   
	  }else{
		$('input._efield[name^="'+target_level+'"]').prop('checked',chkstatus);   
	  }
	  var checked_all  =  $('input._efield').length == $('input._efield:checked').length ? true:false;
	  $('.act_select_all_efield[level="_all"]').prop('checked',checked_all);
	})
	
	//-- select master checker
	$('input._efield._member').change(function(){
	  var select_master = $(this).hasClass('_volume')  ? 'volume' : 'element';  
	  var checked_master = $('input._efield._member._'+select_master).length == $('input._efield._member._'+select_master+':checked').length ? true : false;
	  $('.act_select_all_efield[level="'+select_master+'"]').prop('checked',checked_master);
	  var checked_all =  $('input._efield').length == $('input._efield:checked').length ? true:false;
	  $('.act_select_all_efield[level="_all"]').prop('checked',checked_all);  
	});
	
	
	
	/* [Meta Batch PrintOut] */ 
	
	
	//-- batch export select data //勾選資料批次列印
	$('#act_meta_batch_print_rander').click(function(){
		
		if(!$('.act_selector:checked').length){
		  system_message_alert('','尚未勾選列印資料');
		  return false;	
		}
		
		var records    = $('.act_selector:checked').map(function(){return $(this).val(); }).get();
		var pfields    = $('._pfield:checked').map(function(){  if(!$(this).prop('disabled')) return $(this).val(); }).get();
		var hasthumb   = $('#act_printout_thumb').prop('checked') ? 1 : 0;
		
		var paser_objects = {
		  'records':records,
          'pfields':pfields,
		  'rowseach':$('#printout_records').val(),
		  'hasthumb':hasthumb
		};
	 
		var paser_data = encodeURIComponent(Base64M.encode(JSON.stringify(paser_objects)));
		
		window.open('index.php?act=Meta/printout/'+paser_data,"_blank");	
	})
	
	
	//-- select print field
	$('input._pfield').change(function(){
	  // 建立資料預覽
	  $('#printout_fields_set').empty();  
	  $('input._pfield:checked').each(function(){
		if($(this).prop('disabled')) return true;   
		var printfield = $('tr.data_field._sample').clone().removeClass('_sample');
		printfield.find('td.dfield').html($(this).attr('title')+':');
		printfield.find('td.dvalue').html($(this).attr('title')+'欄位內容');
		printfield.appendTo($('#printout_fields_set'));
	  });
	});
	
	//-- set print thumb
	$('#act_printout_thumb').change(function(){
	  if($(this).prop('checked')){
		$('#print_thumb').show();  
	  }else{
		$('#print_thumb').hide();    
	  }
	});
	
	
	
	
	
	
	
	
	
	
	/* [Meta Batch Upload] */
	
	//-- upload batch file module initial 初始化批次上傳模組
	function meta_batch_upload_module_initial(){
	  $('#module_batch_upload').data({'upload':'','revise':''}).attr('flag-upload',''); 
	  $('#meta_excel_select').val('');
	  $('#upload_result').find('span').empty();
	  $('.meta_check_rsl,#meta_check_list').empty();
	  $('#upload_license').attr('check',''); 
	}
	
	//-- upload batch meta file (excel)
	$(document).on('click','#act_upload_batch_excel',function(){  
	  
	  // get id
	  var data_class = $('#zong_class').length ? $('#zong_class').data('value') : '';
	  var active_dom = $(this);
	   
	  if( ! data_class){
	    system_message_alert('',"資料錯誤");
		return false;
	  }
	  
	  // check file 
	  var upload_selecter = $('#meta_excel_select').val();
	  var file_name   = upload_selecter.split('\\').pop();
	  if(!file_name){
		system_message_alert('','尚未選擇檔案');    
		return false;		
	  }
	  
	  if( /\.(xls|xlsx)$/gi.test(file_name)===false ){
		system_message_alert('','檔案格式錯誤，請使用excel檔案');  
		$('#meta_excel_select').val('');
	    return false;	
	  }
	  
	  system_loading();
	  var action = $('#file_upload_form').attr('action');
	  $('#file_upload_form').attr('action',action+'/'+data_class);
	  var FormObj = document.getElementById('file_upload_form'); 
	  FormObj.submit();
	  $('#file_upload_form').attr('action',action);
	  
	  meta_batch_upload_module_initial();
	  
	});
	
	
	//-- execute batch file checker
    $('#act_check_uploaded').click(function(){
	  
	  var active_dom = $(this);
	  var upload_key = $('#module_batch_upload').data('upload');
	  if(!upload_key.length){
		system_message_alert('','尚未上傳資料');
        return false;		
	  }
	  
	  $('#module_batch_upload').data('revise','');
	  
	  
	  $.ajax({
		  url: 'index.php',
		  type:'POST',
		  dataType:'json',
		  data: {act:'Meta/batchcheck/'+upload_key},
		  beforeSend: 	function(){  active_loading(active_dom,'initial'); },
		  error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		  success: 		function(response) {
			if(response.action){  
			  
			  if(typeof response.data.check == 'undefined'){
				system_message_alert('','回傳資料錯誤'); 
			  }
			  
			  $('#meta_check_list').empty();
			  
			  $('#upload_license').attr('check',response.data.license);
			  $.each(response.data.check,function(checktype,checkresult){
				if(Array.isArray(checkresult)){
				  
				  if(!checkresult.length) return true;	  
				  
				  $('#num_'+checktype).text(checkresult.length);
				  $.each(checkresult,function(index,result){
					var record = $('<li/>').addClass("revise "+checktype);
					var descrip = [
				      "<span class='no' >"+(index+1)+".</span>",
					  "<span class='sheet'>"+result.sheet+"</span>",
					  "<span class='cell'>"+result.cellid+" / "+result.column+" </span>",
					  "<span class='desc'>"+result.descrip+"</span>"	
					];
					record.append("<div class='review'>"+descrip.join('')+"</div>")
					record.append("<div class='content'>"+result.content+"</div>")
				    record.appendTo($('#meta_check_list'));
				  });
				 
				}else{
				  $('#num_'+checktype).text(checkresult);	
				}
			  
			  });
			  $('#module_batch_upload').data('revise',response.data.fname);
			}else{
			  system_message_alert('',response.info);
			}
		  },
		  complete:		function(){   }
	  }).done(function(r) { active_loading(active_dom, r.action);   });	
	  
    });
	
	
	//-- download revise excel
	$('#act_get_revisefile').click(function(){
	  
	  var upload_key = $('#module_batch_upload').data('revise');
	  if(!upload_key.length){
		system_message_alert('','尚未執行查驗');
        return false;		
	  }	
	   
	  $.ajax({
		  url: 'index.php',
		  type:'POST',
		  dataType:'json',
		  data: {act:'Meta/batchrevise/'+upload_key},
		  beforeSend: 	function(){ system_loading();  },
		  error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		  success: 		function(response) {
			if(response.action){  
			  window.location.href = 'index.php?act=Meta/getexport/'+response.data.revise.fname;
			}else{
			  system_message_alert('',response.info);
			}
		  },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });	
	  
	});
	
	
	
	//-- execute batch import
	$('#act_execute_import').click(function(){
	  
	  var active_dom = $(this);
	  var upload_key = $('#module_batch_upload').data('upload');
	  if(!upload_key.length){
		system_message_alert('','尚未上傳資料');
        return false;		
	  }
	  
	  var upload_key = $('#module_batch_upload').data('revise');
	  if(!upload_key.length){
		system_message_alert('','尚未執行查驗');
        return false;		
	  }	
	   
	  $.ajax({
		  url: 'index.php',
		  type:'POST',
		  dataType:'json',
		  data: {act:'Meta/batchimport/'+upload_key},
		  beforeSend: 	function(){ system_loading();  },
		  error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		  success: 		function(response) {
			if(response.action){  
			  system_message_alert('alert',"已更新:"+response.data.batch.update+'筆資料');
			  $('#module_batch_upload').attr('flag-upload',response.data.batch.update);
			}else{
			  system_message_alert('',response.info);
			}
		  },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });	
	  
	});
	
	
	//-- download renew batch excel
	$('#act_renew_batch_file').click(function(){
	  
	  var upload_key = $('#module_batch_upload').data('revise');
	  if(!upload_key.length){
		system_message_alert('','尚未執行查驗');
        return false;		
	  }	
	  
	  var upload_status = $('#module_batch_upload').attr('flag-upload');
	  if( upload_status=='' || parseInt(upload_status)==0){
		system_message_alert('','資料未更新');
        return false;		
	  }	
	   
	  $.ajax({
		  url: 'index.php',
		  type:'POST',
		  dataType:'json',
		  data: {act:'Meta/batchrenew/'+upload_key},
		  beforeSend: 	function(){ system_loading();  },
		  error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		  success: 		function(response) {
			if(response.action){  
			  window.location.href = 'index.php?act=Meta/getexport/'+response.data.batch.fname;
			}else{
			  system_message_alert('',response.info);
			}
		  },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });	
	  
	});
	
	
	//-- download renew batch excel
	$('#act_renew_batch_skip').click(function(){
	  
	  var upload_key = $('#module_batch_upload').data('upload');
	  if(!upload_key.length){
		system_message_alert('','尚未執行查驗');
        return false;		
	  }	
	  $.ajax({
		  url: 'index.php',
		  type:'POST',
		  dataType:'json',
		  data: {act:'Meta/batchskip/'+upload_key},
		  beforeSend: 	function(){ system_loading();  },
		  error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		  success: 		function(response) {
			if(response.action){  
			 window.location.href = 'index.php?act=Meta/getexport/'+response.data.revise.fname;
			}else{
			  system_message_alert('',response.info);
			}
		  },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });	
	  
	});
	
	
	//-- 取消批次處理
	$('#act_meta_batch_cancel').click(function(){
	  var upload_key = $('#module_batch_upload').data('upload');
	  if(upload_key.length){
        if(!confirm("確定要放棄目前的更新作業?")){
		  return false;	
		}
	  }	
	  meta_batch_upload_module_initial();
	});
	
	
	//-- 關閉批次處理
	$('#act_meta_batch_finish').click(function(){
	  meta_batch_upload_module_initial();
	  $('#module_batch_upload').hide();
	});
	

	//-- execute task : 編輯資料 
	$('.act_meta_getin').click(function(){
	  var task_dom = $(this).parents('.data_record');
	  var data_no  = task_dom.attr('no');
	  
	  if(!data_no.length){
		system_message_alert('','尚未選擇資料');
        return false;		
	  }
	  window.open('index.php?act=Meta/editor/'+data_no);	  
	});
	 
	
	
	$('.area_close').click(function(){
	  $(this).parents('.system_popout_area').hide();
      resetmoduleform($(this).data('form'));
	});
	
	function resetmoduleform(FormClass){
	  if(FormClass){
		var bind_form = FormClass;
        $('.'+bind_form+'._variable').each(function(){
		  if( $(this).hasClass('_update')){
			if($(this).attr('type') !='checkbox' && $(this).attr('type') !='radio'){
			  $(this).val('');  	
			}else{
			  $(this).prop('checked',false);	
			}
		  }else{
			$(this).html('');   
		  }
		});
	  }
	}
	
	
	/***== [ META Zong AD FUNCTION ] ==***/ //全宗資料管理介面
	//------------------------------------------------------------------------------------------------------
	
	
	//-- volume create //新增卷
	$('#act_volume_create').click(function(){
	  
	  $.ajax({
		  url: 'index.php',
		  type:'POST',
		  dataType:'json',
		  data: {act:'Meta/volumenew/'},
		  beforeSend: 	function(){ system_loading();  },
		  error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		  success: 		function(response) {
			if(response.action){  
			  window.open('index.php?act=Meta/editor/'+response.data.volume.system_id);	  
			}else{
			  system_message_alert('',response.info);
			}
		  },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });	
      
	});
	
	
	
	
	
	//-- volume edit : cancel edit  //取消 volume 編輯
	$('#act_volumn_edit_exit').click(function(){
	  if($('._volume._modify').length){
		if(!confirm("目前資料已變更，請問要放棄目前編輯的資料嗎?")){
		  return false;	
		}  
	  }
	  resetmoduleform('_volume');
	  $('#module_volume_editer').hide();
	});
	
	
	//-- volume edit : create volume  // 新增volumn
	$('#act_volumn_edit_create').click(function(){
	  
	  // get volumn meta
	  var volume = {};
	  var field_check = true;
	  var data_type = $(this).data('class');
	  
	  if($('#VOLUME-title_main').val().match(/@SAMPLE/)){
		system_message_alert('','請先移除標題範例標籤"@SAMPLE"');
		return false;  
	  }
	  
	  $('._volume._update').each(function(){
		if($(this).attr('type')=='checkbox' || $(this).attr('type')=='radio'){
		  var vfiled = $(this).attr('name');
		  var vvalue = $("input[name='"+vfiled+"']:checked").map(function(){return $(this).val(); }).get().join(';');  	
		}else{
		  var vfiled = $(this).attr('id');   
          var vvalue = $(this).val();  	
		}
		
		if($(this).parents('.data_value').prev().hasClass('_necessary') && vvalue==''){
		  system_message_alert('','請填寫必備欄位!!');
		  $(this).focus();	
		  field_check = false;
		  return false;
		}
		volume[vfiled] =  vvalue;
	  });
	  
	  if(!field_check){
		return false;  
	  }
	  
	  var paser_data = encodeURIComponent(Base64M.encode(JSON.stringify(volume)));
	  
      $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Meta/'+data_type+'/'+paser_data},
		beforeSend: function(){  system_loading(); },
		error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		success: 	function(response) {
		  if(response.action){
			location.href= 'index.php?act=Meta/editor/'+response.data.renew.syid;
		  }else{
			system_message_alert('',response.info);
		  }
		},
		complete:	function(){  }
	  }).done(function(r) {  system_loading(); });  
      
	});
	
	
	
	
}); /*** end of html load ***/
  
  
  //-- loading page call function  //上傳回傳檔案呼叫函數
  function process_batch_upload(SubmitReturn){
	
	var response = JSON.parse(SubmitReturn);
	
    if(!response){
	  system_message_alert('','上傳執行失敗');
      return false;	  
	}
	  
	if(!response.action){
	  system_message_alert('',response.info);
      return false;		
	}  
	
	$('#uploaded_file').html(response.data.file);
	$('#uploaded_size').html(' ('+response.data.size+') ');
	$('#module_batch_upload').data('upload',response.data.save);
	  
	system_loading(); 
  }
  
	
	   
	
	
	
  