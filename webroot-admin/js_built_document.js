  /* [ Admin Meta Print Type Built Function Set ] */
	
  $(window).on('load',function () {   //  || $(document).ready(function() {		
	
	/*[ Module Config ]*/
	
	//-- module action logs  : 模組動作訊息
	// ModuleObj : 模組 JQOBJ
	// ActionName : 執行動作
	// ActionMessage : 執行動作訊息
	// Result : 是否成功 success/fail/reset
	
	//-- move action 1 : switch moduel draggable
	$('.md_anchor').mousedown(function(){
	  var module_dom = $(this).parents('.module_container');
      var new_move_flag = parseInt(module_dom.attr('move')) ? 0 : 1;   
	  //set move flag
	  if(new_move_flag){
		module_dom.draggable({ handle: "header",disabled:false});  
	  }else{
		module_dom.draggable( "destroy" );    
	  }
	  module_dom.attr('move',new_move_flag);
	});
	
	
	//-- move action 2 : disabled moduel draggable
	$('.md_anchor').mouseup(function(){
	  var module_dom = $(this).parents('.module_container');
      var new_move_flag = 0;   
	  //set move flag
	  module_dom.draggable( "destroy" );    
	  module_dom.attr('move',new_move_flag);
	  
	});
	
	
	//-- admin module control 各模組模式切換
	$('.module_display_type').click(function(){  
	  var now_mode   = $(this).attr('mode');
      var relate_dom = $(this).attr('dom');	  
	  var set_mode = '';
	  if($(this).find('a#'+now_mode).next().length){
		set_mode = $(this).find('a#'+now_mode).next().attr('id');  
	  }else{
		set_mode = $(this).find('a:nth-child(1)').attr('id');  
	  }
	  $(this).attr('mode',set_mode);
	  $('#'+relate_dom).attr({'mode':set_mode,'style':''});
	});
	
	
	//-- method action 1 : switch moduel method 
	$('.md_methodsel').change(function(){
	  
	  var module_dom = $(this).parents('.module_container');
      var new_md_method = $(this).val();
	  
	  if(!module_dom.find('.md_method_dom#'+new_md_method).length){
		system_message_alert('','模組模式錯誤');
        $(this).val('');
        return false; 		
	  }
	  
	  $('.md_method_dom').css('zIndex',0).hide();
	  module_dom.find('.md_method_dom#'+new_md_method).css('zIndex',1).show();
	});
	
	
	$('.area_close').click(function(){
	  $(this).parents('.system_popout_area').hide();
      resetmoduleform($(this).data('form'));
	});
	
	
	/***-------------------------***/
	/* [ BUILT CONTENTS FUNCTION ] */
	/***-------------------------***/
	
	var historyinput  = {};  // 儲存上次的內容
	
	//@-- stop hot key 快速鍵取消
	$(document).keyup(function(event){
	  if(event.keyCode=='18'){  // alt left
		FLAG_SYSTEM_HOTKEY_START = false;
  		$('body').attr('hotkey','0');
		$('#record_master').focus();
	  }  
	  //if(event.key=='r') FLAG_SYSTEM_HOTKEY_START = false;	 
	});
	
	// 鼠鍵操作模式  :  屬標需在影像顯示區
	$(document).keydown(function(event){
	  
	  if(!FLAG_MOUSE_ON_DOVIEW) return true;
	  if(FLAG_SYSTEM_HOTKEY_START) return true;
	  
	  switch(event.key){	
	    // 切換影像
		case 'b': //上一區 
		  $(".page_switch[mode='vprev']").length ? $(".page_switch[mode='vprev']").trigger('click') : ''; 
		  $('.dobj_container').focus(); //防止填入b
		  break  
		case 'n': //下一區
		  $(".page_switch[mode='vnext']").length ? $(".page_switch[mode='vnext']").trigger('click') : ''; 
		  $('.dobj_container').focus(); //防止填入n
		  break  
		default:break;
	  }
	  
	  return true	
	});
	
	// 鍵盤模式 : 需使用功能鍵
	$(document).keydown(function(event){
	  
	  if(event.keyCode=='18'){  // alt left
	    FLAG_SYSTEM_HOTKEY_START = true;
        $('body').attr('hotkey','1');
		return false; // 防止瀏覽器  alt  鍵控制
	  }
	  
	  if(!FLAG_SYSTEM_HOTKEY_START) return true;  
	  
	  switch(event.key){
		
		case '1':case '2':case '3':case '4':case '5': // 切換編輯介面
		  $('.module_container').css('z-index','11');
		  if($('.module_container:nth-child('+event.key+')').length){
			$('.module_container:nth-child('+event.key+')').css('z-index','15');   
		  }
		  break;
		
		// 切換影像
		case 'b': case 'ArrowLeft':  $(".page_switch[mode='vprev']").length ? $(".page_switch[mode='vprev']").trigger('click') : ''; break  //上一區
		case 'n': case 'ArrowRight': $(".page_switch[mode='vnext']").length ? $(".page_switch[mode='vnext']").trigger('click') : ''; break  //下一區
		
		// 切換單件
		case 'z'  : case 'ArrowUp'  :  //上一件
		  if(FLAG_SYSTEM_EDITOR_LEVEL==2){
			$(".act_switch_element[to='prev']").trigger('click');  
		  }else{
			$(".act_switch_editor[to='prev']").trigger('click');
		  }
		  break;  
		
		case 'c': case 'ArrowDown': //下一件
		  if(FLAG_SYSTEM_EDITOR_LEVEL==2){
			$(".act_switch_element[to='next']").trigger('click');  
		  }else{
			$(".act_switch_editor[to='next']").trigger('click');
		  }
		  break;
		
		case 'e': // 讀取單件
          if(FLAG_SYSTEM_EDITOR_LEVEL==1){
            $('#volume_forms_switcher').find('li[data-group="element_list"]').trigger('click');
			if($('.data_result').find('tr.data_record').length){
			  $('.data_result').find('tr.data_record:first-child').trigger('click');
			}
		  }
		  break;
		  
		case 'i': // 新增單件
          $('#volume_forms_switcher').find('li[data-group="element_list"]').trigger('click');
		  if(FLAG_SYSTEM_EDITOR_LEVEL==1){
			$('#act_create_category').trigger('click');
		  }else{
			$('#act_newa_element_meta').trigger('click');  
		  }		
		  break;
		
		case 'a': //apply value 根據欄位設定應該的值 
		  var $focused = $(':focus');
          if($focused.hasClass('_variable')){
			switch($focused.attr('id')){
			  case 'META-E-domap_from': $('#META-E-domap_from').val($('.page_selecter').val()); break;
			  case 'META-E-domap_end':  $('#META-E-domap_end').val($('.page_selecter').val()); break;
			  default: break;
			}
		  } 
          break;
		
		
		case 'd':  // 下一行 
	      
		  if($(':focus').length){
			  var $focused = $(':focus');
              if($focused.hasClass('cell_form') && $focused.parents('tr.data_record').length){
				
				main_raw = $focused.parents('tr.data_record'); 
				var field_now = $focused.attr('name');
				if(main_raw.next() && main_raw.next().find("._update[name='"+field_now+"']").length){
				  var next_raw = main_raw.next();
				  next_raw.find("._update[name='"+field_now+"']").focus();
				}else if(main_raw.parents('.data_result').length  ){
				  var next_raw = main_raw.parents('.data_result').find('tr:first-child');
                  next_raw.find("._update[name='"+field_now+"']").focus(); 				  
				}
				
			  }else if($focused.parents('.field_set').length){
				main_raw = $focused.parents('.field_set');
				main_raw.next().find('._update')[0].focus();
			  }else if($focused.parents('.data_col').length){
				main_raw = $focused.parents('.data_col');
				main_raw.next().find('._update')[0].focus();
			  }
		  }
		  break;
		
         
		case 'x': // 儲存目前資歷
		  if($('#act_save_element_meta').is(':visible')){
			$('#act_save_element_meta').trigger('click');  
		  }else if($('#act_save_volume_meta').is(':visible')){
			$('#act_save_volume_meta').trigger('click');  
		  }
		  break;
		
		case 'w': // 切換建檔form
		  var formswitcher = (FLAG_SYSTEM_EDITOR_LEVEL==1) ? $('#volume_forms_switcher') : $('#element_forms_switcher');
		  if(formswitcher.find('li._atthis').length && formswitcher.find('li._atthis').next().length){
			formswitcher.find('li._atthis').next().trigger('click');  
		  }else{
			formswitcher.find('li:nth-child(1)').trigger('click');  
		  }
		  break;
		  
		case 'q': // 離開建檔form  目前僅提供件層級關閉
		  if(FLAG_SYSTEM_EDITOR_LEVEL==2){
			$('#act_close_element_editor').trigger('click');  
		  }
		  break;
		
		case 'l': console.log(canvas.toObject()); break; 
	  
	  }
	  
	  return false
	});
	
	
	//@-- meta group switcher 切換欄位群組	
	$('.meta_group_sel').click(function(){
	  
	  var meta_group_block = $(this).data('group');
       
	  if($(this).hasClass('_atthis')){
		return false; 
	  }
	  
	  // focus group 
	  var domcontainer = $(this).parents('.record_body');
	  if(meta_group_block=='_all'){
		domcontainer.find('.meta_group_block').addClass('_display');  
	  }else{
		domcontainer.find('._display').removeClass('_display');
        domcontainer.find('#'+meta_group_block).addClass('_display');		
	  }
	  domcontainer.find('.meta_group_sel').removeClass('_atthis');
	  $(this).addClass('_atthis');
	  
	});
	
	
	//@-- cheage editor target  切換建檔主體資料  
	$('.act_switch_editor').click(function(){
	  
	  var editor_index = $('#SYSTEMID').data('set');
	  var switch_method= $(this).attr('to');
	  var act_object   = $(this);
	 
	  // encode data
	  var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(editor_ui_config_get())));
      
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Meta/volumeswitch/'+editor_index+'/'+switch_method+'/'+passer_data},
		beforeSend: function(){  active_loading(act_object,'initial'); },   // 因為video load 會將  event peading
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			system_loading();  
			location.href = 'index.php?act=Meta/editor/'+response.data;
		  }else{
			system_message_alert('',response.info);
	      }
	    },
		complete:	function(){  }
      }).done(function(r) {  active_loading(act_object , r.action );  });
	  
	});
	
	
	//-- download task
	$('#act_task_downlaod').click(function(){
      var task_no    = $('#taskid').data('refer');
	  window.open('index.php?act=Built/export/'+task_no);
	});
	
	
	//-- switch config block
	$('#act_editor_setting,#act_close_setting').click(function(){
	  if( $('.advance_conf').is(':visible')){
		$('.advance_conf').hide();  
	  }else{
		$('.advance_conf').show();    
	  }
	});
	
	
	
	
	/* == 資料編輯函數 == */
	
	// 重新綁定資料檢查
	
	var editor_keeper = false;
	
	$('._variable').off('keyup change blur');
	$('._update').on('keyup change blur',function(){
	  var field_name = $(this).attr('id') ? $(this).attr('id') : $(this).attr('name');
	  var form_value = $(this).val();
	  var form_store = {};
	  
	  if(field_name.match(/\-V\-/)){
		form_store = typeof data_orl['volume'] !=='undefined' ? data_orl['volume'] : {} ;
	  }else if(field_name.match(/\-E\-/)){
		form_store = typeof data_orl['element']!=='undefined' ? data_orl['element'] : {} ; 
	  }
      
	  if(typeof form_store[field_name] === 'undefined' ){
		form_store[field_name] = '';  
	  }
	  
	  if( form_store[field_name] !== form_value ){
	    $(this).addClass('_modify');    
	  }else{
	    if($(this).hasClass('_modify')){
		  $(this).removeClass('_modify');    
		}
	  }
	  
	  //@ 放棄參考資料
	  if(form_value=='' && $(this).parents('.data_col').hasClass('_refer')){
		$(this).parents('.data_col').removeClass('_refer');   
	  }
	  
	  //@ focus 後移除hold標籤
	  if($(this).hasClass('_hold')){
		$(this).removeClass('_hold').next('.act_holder_givup').remove();  
	  }
	  
	  //@ editor tool : every 5 second keep unsave content // 編輯暫存器
	  var editor_main = $('#VOLUMEID').data('set');
	  var editor_hash  = location.hash.replace(/#/,'');
	  var passer_key   = editor_main+'@'+editor_hash;
	  
	  if( $('._modify').length && !editor_keeper){
		
		editor_keeper = setInterval(function(){  
		  
		  var value_keep = {};
		  $('._keeper._modify').each(function(){
			value_keep[$(this).attr('id')] = $(this).val();
		  });
		  
		  if(!Object.keys(value_keep).length) return false;
		  
		  // encode data
		  var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(value_keep)));
		  
		  // active ajax
		  $.ajax({
			url: 'index.php',
			type:'POST',
			dataType:'json',
			data: {act:'Meta/keeper/push/'+passer_key+'/'+passer_data},
			beforeSend: function(){  },
			error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
			success: 	function(response) {
			   
			},
			complete:	function(){  }
		  }).done(function(r) {});  
		}, 5000);
	  
	  }else if(!$('._modify').length && editor_keeper){
		// active ajax
		$.ajax({
			url: 'index.php',
			type:'POST',
			dataType:'json',
			data: {act:'Meta/keeper/clean/'+passer_key},
			beforeSend: function(){  },
			error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
			success: 	function(response) {
			  clearInterval(editor_keeper);
              editor_keeper = false;     
			},
			complete:	function(){  }
		}).done(function(r) {});
	  }
	  
	});
	
	
	//@-- 參考資料預設為全選狀態
	$("._update").on('focus',function(){
	  if($(this).parents('.data_col').hasClass('_refer')){
		$(this).select();
	  }
	});
	
	//@-- 放棄暫存資料
	$(document).on('click','.act_holder_givup',function(){
	  
	  var meta_field = $(this).prev('._update').attr('id');
	  var meta_level = $(this).attr('level')
	  
	  if( typeof data_orl[meta_level]  !=='undefined' &&  
	      typeof data_orl[meta_level][meta_field] !=='undefined' ){  
		  $('#'+meta_field).val(data_orl[meta_level][meta_field]);
		  $('#'+meta_field).removeClass('_hold');
	  }
	  $(this).remove();
	
	})
	
	
	//@-- 重新設定欄位  
	function initial_metadata_editer(MetaClass){
      $('.'+MetaClass+'._variable').each(function(){
		if($(this).hasClass('_update')){
		  if($(this).attr('type')=='checkbox' || $(this).attr('type')=='radio'){
            $(this).prop('checked',false); 
		  }else{
			$(this).val('');  
		  }	
		}else{
		  $(this).html('');	
		}
	  });
	}
	
	
	
	//-- get data to editer  // 從server取得資料並放入編輯區
	function insert_data_to_volume_form(DataObj){
	  
	  $('._modify').removeClass('_modify');
	  $('._refer').removeClass('_refer');
	  $('._hold').removeClass('_hold');
	  
	  $.each(DataObj,function(field,mval){
			
		if(field=='META-V_flag_open' || field=='META-E_flag_open' ){
		  $("input#"+field).prop('checked',(parseInt(mval) ? true : false) );    
		}else{  
		  
		  // 一般欄位編輯
		  if($("._variable[id='"+field+"']").length){
			
			if($("._variable[id='"+field+"']").hasClass('_update') || $("._variable[id='"+field+"']")[0].tagName=='INPUT' ){
			  $("._variable[id='"+field+"']").val(mval); 
			}else{
			  $("._variable[id='"+field+"']").html(mval); 	
			}  
		  
		  }else{
			// 多選項欄位
			if($("._variable[name='"+field+"']").length){
				
			  $("._variable[name='"+field+"']").prop('checked',false);	
				
			  var val_array = mval.split(';');  
			  $.each(val_array,function(i,term){
				$("._variable[name='"+field+"'][value='"+term+"']").prop('checked',true); 
			  });
			
			}
		  }
		
		}
	  });
	}  
	
	
	
	//-- get data to editer  // 從server取得資料並放入編輯區
	function insert_data_to_element_form(MasterDom,DataObj){
	   
	  $('._modify').removeClass('_modify');
	  $('._refer').removeClass('_refer');
	  $('._hold').removeClass('_hold');
	  
	  var dom_record  = $('._target');
	   
	  $.each(DataObj,function(field,mval){	
	    
		if( field=='META-E-_flag_open' ){
		  
		  $("input#"+field).prop('checked',(parseInt(mval) ? true : false) );    
		
		}else{
		  
		  // 一般欄位編輯
		  if(MasterDom.find("._element._variable[name='"+field+"']").length){
           			
            var tag_dom = MasterDom.find("._element._variable[name='"+field+"']");
			
			if(tag_dom.attr('type')=='checkbox' || tag_dom.attr('type')=='radio'){
			  MasterDom.find("._element._variable[name='"+field+"']").prop('checked',false);	
			  var val_array = mval.split(';');  
			  $.each(val_array,function(i,term){
			    MasterDom.find("._element._variable[name='"+field+"'][value='"+term+"']").prop('checked',true); 
			  });
			}else{
			  if(tag_dom[0].tagName=='INPUT' || tag_dom[0].tagName=='SELECT' || tag_dom[0].tagName=='TEXTAREA'){
			    MasterDom.find("._element._variable[name='"+field+"']").val(mval); 	
			  }else{
			    MasterDom.find("._element._variable[name='"+field+"']").html(mval); 	
			  }
			}
			
		  }else if(MasterDom.find("._element._variable[id='"+field+"']").length){
			var tag_name = MasterDom.find("._element._variable[id='"+field+"']")[0].tagName;
			if(tag_name=='INPUT' || tag_name=='SELECT' || tag_name=='TEXTAREA' ){
			  MasterDom.find("._element._variable[id='"+field+"']").val(mval); 	
			}else{
			  MasterDom.find("._element._variable[id='"+field+"']").html(mval); 	
			}
		  }
		  
		  
		  // 若為空值，則檢測是否需要帶入芋仔資料
		  if(mval==''){
			
			
			// 上層參照
			if( MasterDom.find("._element._variable[id='"+field+"']").length && 
			    MasterDom.find("._element._variable[id='"+field+"']")[0].hasAttribute("parentrefer") ){
			  //尚未實作
			}
			
			
			// 他件參照
			if( MasterDom.find("._element._variable[id='"+field+"']").length && 
			    MasterDom.find("._element._variable[id='"+field+"']")[0].hasAttribute("caserefer") ){
					
			  var refer_field = MasterDom.find("._element._variable[id='"+field+"']").attr('caserefer');			  
			  if(typeof historyinput[refer_field] !== typeof undefined && historyinput[refer_field]!='' ){
				var refer_value = ''; 
                if(MasterDom.find("._element._variable[id='"+field+"']")[0].hasAttribute("linkdom")){  // 影像參照需特殊處理
				  var target_pager = $("option.pager[value='"+historyinput[refer_field]+"']"); 
				  if( target_pager.next().length){
					refer_value = target_pager.next().attr('value');
				  } 	
				}else{
				  refer_value = historyinput[refer_field];
				}
			    MasterDom.find("._element._variable[id='"+field+"']").val(refer_value).parents('.data_col').addClass('_refer');	
			  }
			}
		  
		    // 自我參照
			if( MasterDom.find("._element._variable[id='"+field+"']").length && 
			    MasterDom.find("._element._variable[id='"+field+"']")[0].hasAttribute("selfrefer")){
			  var refer_field = MasterDom.find("._element._variable[id='"+field+"']").attr('selfrefer');			  
			  if(typeof DataObj[refer_field] !== typeof undefined && DataObj[refer_field]!='' ){
				MasterDom.find("._element._variable[id='"+field+"']").val(DataObj[refer_field]).parents('.data_col').addClass('_refer'); 
			  }
			}
		  }
		  
		
		  
		}
	  });  
	
	}
	
	
	/* == 資料編輯欄位工具 == */
	
	//@-- 確認資料是否變更
	function check_meta_modify(){
	  if($('._modify').length){
		if(!confirm("尚有資料變更未儲存，請問要放棄變更資料嗎?")){
		  return false	
		}  
	  }
	  return true;
	}
	
	
	//-- check page_num value
	function check_page_num(){
	  var pnum_s = parseInt($('#page_num_start').val()) ? parseInt($('#page_num_start').val()):0;
	  var pnum_e = parseInt($('#page_num_end').val()) ? parseInt($('#page_num_end').val()):0;
	  pnum_s+=1;
	  pnum_e+=1;
	  
	  if(pnum_s > pnum_e){
		$('#page_num_checked').attr('check','0').text('設定錯誤');
		return false;		
	  }
	  var pager_s = $('option.pager:nth-child('+pnum_s+')');  
	  
	  if(!pager_s.length){
		$('#page_num_checked').attr('check','0').text('起始頁頁面不存在');
		return false;	
	  }
	  
	  var pager_e = $('option.pager:nth-child('+pnum_e+')');  
	  if(!pager_e.length){
		$('#page_num_checked').attr('check','0').text('結束頁頁面不存在');
		return false;	
	  }
	  $('#page_num_checked').attr('check','1').text('');
	}
	
	//--check page num
	$('.page_num').on('keyup change',function(){
	  check_page_num();	
	});
	
	
	/***== [ VOLUME EDIT FUNCTION ] ==***/
	
	//-- ethnic select
	$('select._ethnic_main').change(function(){
	  var main_ethnic = $(this).val()
      $('ul.ethnic_assist li').hide();
	  if($("li[ethnic='"+main_ethnic+"']").length){
		$("li[ethnic='"+main_ethnic+"']").css('display','flex');  
	  }
	});
	
	//-- ethnic other select
	$('select._ethnic_sub').change(function(){
	  var sub_ethnic = $(this).val()
      $(this).next().hide(); 
	  if($(this).val()=='其他'){
		 $(this).next().val('').show();  
	  }
	});
	
	if($('select._ethnic_sub').val()){
	  $('ul.ethnic_assist li:nth-child(1)').css('display','flex');
	}
	
	$('#META-V-store_location').change(function(){
	  var store = $(this).val();
      if(store=='_newa') $(this).hide().next().show().focus();
	});
	
	//@-- 跳轉列印頁面
	
	$('#act_close_print_block').click(function(){
	  $('.print_group_block').show();
	  $('.print_table.relate').find('tbody:not(.print_template)').remove();
	  $('body').removeClass('print_mode'); 
	});
	
	$('#act_print_volume_meta').click(function(){
	  
	  var volumn_id = $("#VOLUMEID").data('set');
	  var act_object = $(this)
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Meta/volumeprint/'+volumn_id},
		beforeSend: function(){  active_loading(act_object,'initial'); },   // 因為video load 會將  event peading
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			var data_print =  response.data.meta;
			
			// 輸入列印介面
			$.each(data_print['source'],function(mf,mv){
			  var print_id = mf.replace(/^META/,'P');
			  if($('#'+print_id).length){
				$('#'+print_id).html(mv);  
			  }
			});
			
		    var counter=0;
			$.each(data_print['research'],function(i,record){
			  var rdom = $('#print-research').find('tbody.print_template').clone();
			  counter++;
			  $.each(record,function(rf,rv){
				rdom.find('.P-no').html(counter+'.');  
			    var print_class = rf.replace(/^META/,'P');
			    if(rdom.find('.'+print_class).length){
				  rdom.find('.'+print_class).html(rv);  
			    }
			  });			  
			  rdom.removeClass('print_template').appendTo($('#print-research'));
			});
			$('#print-research').parents('.print_group_block').find('.is_print').prop('checked',counter ? true : false);
			
			var counter=0;
			$.each(data_print['display'],function(i,record){
			  var rdom = $('#print-display').find('tbody.print_template').clone();
			  counter++;
			  $.each(record,function(rf,rv){
				rdom.find('.P-no').html(counter+'.');  
			    var print_class = rf.replace(/^META/,'P');
			    if(rdom.find('.'+print_class).length){
				  rdom.find('.'+print_class).html(rv);  
			    }
			  });			  
			  rdom.removeClass('print_template').appendTo($('#print-display'));
			});
			$('#print-display').parents('.print_group_block').find('.is_print').prop('checked',counter ? true : false);
			
			var counter=0;
			$.each(data_print['movement'],function(i,record){
			  var rdom = $('#print-movement').find('tbody.print_template').clone();
			  counter++;
			  $.each(record,function(rf,rv){
				rdom.find('.P-no').html(counter+'.');  
			    var print_class = rf.replace(/^META/,'P');
			    if(rdom.find('.'+print_class).length){
				  rdom.find('.'+print_class).html(rv);  
			    }
			  });			  
			  rdom.removeClass('print_template').appendTo($('#print-movement'));
			});
			$('#print-movement').parents('.print_group_block').find('.is_print').prop('checked',counter ? true : false);
			
			
			
			$('body').addClass('print_mode'); 
			 
			 
		  }else{
			system_message_alert('',response.info);
	      }
	    },
		complete:	function(){  }
      }).done(function(r) {  active_loading(act_object , r.action );  });
	  
	  
	   
	});
	
	
	
	
	
	//@-- 新增詮釋資料
	$('#act_create_volume_meta').click(function(){
	  system_loading();
	  act_volume_create();
	});
	
	
	//-- save volume modify
	$('#act_save_volume_meta').click(function(){
	  
	  // get value
	  var data_class    = $('#DATACLASS').data('set');
	  var data_store_no = $('#META-V-store_no').val();
	  
	  if( !data_store_no ){
	    system_message_alert('',"資料錯誤");
		return false;
	  }
	  
	  var modify_data  = {};
	  var meta_checked = true;
	  var act_object   = $(this);
	  
	  // option active checked  // 檢查按鈕是否在可執行狀態
	  if( act_object.prop('disabled') ){
	    return false;
	  }
	  
	  // get volume value
	  $('._volume._update').each(function(){
	    
		if( !$(this).hasClass("_flag")  &&  !$(this).is(":visible")) return true;
		
		
		if($(this)[0].tagName=='INPUT' && ( $(this).attr('type')=='checkbox' || $(this).attr('type')=='radio')){
		  
		  if($(this).hasClass('_flag')){
			var field_name = $(this).attr('name');
		    modify_data[field_name] = $(this).prop('checked') ? 1 : 0;  
		  }else{
			var field_name = $(this).attr('name');
		    if(typeof modify_data[field_name] == 'undefined') modify_data[field_name] = [];
			
			if($(this).prop('checked')){
		      var select_value = $(this).val()=='_newa' ? $(this).next().val() : $(this).val();
			  if(select_value){
				modify_data[field_name].push(select_value); 
			  }
			}
		  }
		
		}else{
		  var field_name  = typeof $(this).attr('id') != 'undefined' ? $(this).attr('id') : $(this).attr('name');
	      var field_value = $(this).val();
		  modify_data[field_name] = field_value;
		}
		
		if( ($(this).hasClass('_must')  || $(this).parents('.data_value').prev().hasClass('_must')) && field_value=='' ){  
		  $(this).focus();
		  system_message_alert('',"請填寫必要欄位(．標示)");
		  meta_checked = false;
		  return false;
		}
	  });
	  
	  
	  if(!meta_checked) return false; 
	 
	  
	  // encode data
	  var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(modify_data)));
      
      // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Meta/volumesave/'+data_store_no+'/'+passer_data},
		beforeSend: function(){  active_loading(act_object,'initial'); },   // 因為video load 會將  event peading
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			var data_load =  response.data.meta;
			data_orl['volume'] = data_load.source;
			insert_data_to_volume_form(data_load.source);
		  }else{
			
			if(response.data.save){
			  $.each(response.data.save,function(mf,err){
				if($('#'+mf).length){
				  $('#'+mf).focus();	
				}else if($("_update[name='"+mf+"']").length){
				  $('#'+mf).focus();				  
				}
				system_message_alert('',err);
			    return false;
			  })	
			}else{
			  system_message_alert('',response.info);	
			}
			
			
	      }
	    },
		complete:	function(){  }
      }).done(function(r) {  active_loading(act_object , r.action );  });
	});
	
	
	//@-- volume create  新增卷
	
	function act_volume_create(){
	  
	  if(!check_meta_modify()) return false;  
	  
	  $.ajax({
		  url: 'index.php',
		  type:'POST',
		  dataType:'json',
		  data: {act:'Meta/volumenew/'},
		  beforeSend: 	function(){ system_loading();  },
		  error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		  success: 		function(response) {
			if(response.action){  
			  location.href='index.php?act=Meta/editor/'+response.data.volume.system_id;	  
			}else{
			  system_message_alert('',response.info);
			}
		  },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });	
	  
	}
	
	
	//@-- volume delete 刪除卷
	function act_volume_delete(){
	  
	  if(!$('#SYSTEMID').data('set')){
		system_message_alert("資料錯誤，請重新整理頁面!!");  
	    return false;
	  }
	  
	  if(!confirm("刪除文物資料將導致建立之影像資訊與數位檔案清空!\n確定要刪除本筆文物資料嗎?"))  return false;   
	  
	    /* var person = prompt("Please enter your name:", "Harry Potter");
		if (person == null || person == "") {
			txt = "User cancelled the prompt.";
		} else {
			txt = "Hello " + person + "! How are you today?";
		}
		document.getElementById("demo").innerHTML = txt;*/
	  
	  
	  $.ajax({
		  url: 'index.php',
		  type:'POST',
		  dataType:'json',
		  data: {act:'Meta/volumedele/'+$('#SYSTEMID').data('set')},
		  beforeSend: 	function(){ system_loading();  },
		  error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		  success: 		function(response) {
			if(response.action){  
			  window.close(); 
			}else{
			  system_message_alert('',response.info);
			}
		  },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });
	  
	  
	  
	}
	
	
	
	
	
	
	/***== [ CATEGORY FUNCTION ] ==***/
	
	//@--create category element
	$('#act_create_category').click(function(){
	  var new_element = $('tr._record_template').clone();
	  new_element.removeClass('_record_template').attr('no','_addnew');
	  new_element.appendTo('.data_result');
	  $('#record_member').css('display','flex');
	});
	
	
	//@--delete category element
	$(document).on('click','.act_element_delete',function(){
	  var main_dom = $(this).parents('tr.data_record');
	  var checkempty = main_dom.find('._update').map(function(){ return $(this).val() ? 1 :0  }).get().reduce((a, b) => a + b, 0);
	  if(checkempty && !confirm("確定要刪除此資料?")){
		return false;  
	  }
	  main_dom.remove();
	});
	
	
	
	
	
	
	/***== [ ELEMENT FUNCTION ] ==***/
	
	
	//@-- switch item meta
	$('.act_switch_element').click(function(){
	  
	  if(!check_meta_modify()) return false;  
	  
	  var target_dom = '';
      var moveto_dom = '';
 	  
      if(!$('._target').length){
		system_message_alert('','尚未選擇資料'); 
		return false;
	  }
	  target_dom = $('._target');  
	  
	  switch($(this).attr('to')){
		case 'prev': moveto_dom = target_dom.prev('.data_record'); break;	
	    case 'next': 
		  moveto_dom = target_dom.next('.data_record'); 
		  // 暫存歷史紀錄，僅適用於下一件與新增
		  historyinput=data_orl['element'];
		  break;
		default:system_message_alert('','發生問題，請洽管理者');  
	  }
	  
	  if(!moveto_dom.length){
		system_message_alert('','資料已達端點');
        return false;		
	  }
	  moveto_dom.trigger('click');
	});
	
	
	
	
	//-- image active editor 
	$('#act_active_element_edit').click(function(){
		
	  var do_file = $('.page_selecter').val()	  
      if(!do_file){
		system_message_alert('','尚未選擇影像');  
	    return false;
	  }	  	
	  
	  var myRe = /(BM\d+)\-(\d+)\.(jpg|png)/;
      var item = myRe.exec(do_file);
	  
	  var volume_no  = item[1];
	  var element_no = item[1]+'-'+item[2]; 
	  var element_dotype = $(".thumb[p='"+do_file+"']").attr("data-folder");
	  
	  
	  $(".meta_group_sel[data-group='element_list']").trigger('click');
	  
	  if($("._element_read[id='"+element_no+"']").length){
		$("._element_read[id='"+element_no+"']").trigger('click');
	  }else{	  
		
		var meta = {};
		meta['file_no']  = item[2];
		meta['dotype']   = element_dotype;
		meta['doformat'] = item[3];
		
		var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(meta)));
	    
		$.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Meta/itemnewa/'+volume_no+'/'+passer_data},
		  beforeSend: 	function(){ system_loading();  },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){  
			  
			  var new_element = $('tr._record_template').clone();
			  new_element.removeClass('_record_template').attr({'no':response.data.newa.store_no,'id':element_no});
			  new_element.find("td[field='META-E-store_no']").html(element_no);
			  new_element.find("td[field='META-E-dotype']").html(element_dotype);
			  new_element.find("td[field='META-E-doname']").html("新增影像目錄");
			  new_element.find("td[field='META-E-doformat']").html(item[3]);
			  new_element.appendTo('.data_result').trigger('click'); 
			  
		    }else{
			  system_message_alert('',response.info);
		    }
	      },
		  complete:		function(){   }
	    }).done(function() { system_loading();   });
		
	  }
	   
		
	});
	
	
	
	
	
	
	//--read item data
	$(document).on('click','._element_read',function(){
	 
	  // get value
	  var data_no   = $(this).attr('no');
	  var volumn_id = $("#VOLUMEID").data('set')
	  var main_dom  = $(this);
	  
	  // active ajax
	  if( ! data_no ){
	    system_message_alert('',"資料錯誤");
		return false;
	  }
	  
	  initial_metadata_editer('_element._detail');
	  
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Meta/itemread/'+volumn_id+'/'+data_no},
		  beforeSend: 	function(){ system_loading();  },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){  
			  
			  $('._target').removeClass('_target');
			  main_dom.addClass('_target');
			
			  var data_load =  response.data.meta;
			  data_orl['element'] = data_load.source;
			
			  // 將資料輸入介面
			  insert_data_to_element_form($('#record_member'),data_orl['element']);
			  insert_data_to_element_form(main_dom,data_orl['element']);
			  
			  // 設定建檔介面
			  $('#record_member').css('display','flex');
			  FLAG_SYSTEM_EDITOR_LEVEL = 2;
			
			  //開啟影像
			  if($('#META-E-store_no').val()){
		        $('.page_selecter').val($('#META-E-store_no').val()+'.jpg').trigger('change');   
	          }
			  
			  //填入暫存內容
			  $.each(data_load.buffer,function(id,tmp){
			    if($('#'+id).length){
				  $('#'+id).val(tmp).addClass('_hold').after('<a class="option act_holder_givup" level="element"  title="放棄暫存資料"><i class="fa fa-times-circle" aria-hidden="true"></i></a>');
			    }
			  });
			  
			  location.hash = data_load.source['META-E-store_no'];
			  
		    }else{
			  system_message_alert('',response.info);
		    }
	      },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });
	  
	});
	
	
	//@-- save data modify - item
	$('#act_save_element_meta').click(function(){
	  
	  // get value
	  var master_no   = $('#VOLUMEID').data('set');
	  var main_dom    = $('._target');
	  var element_no  = $('._target').attr('no');
	  
	  if( !element_no || !master_no){
	    system_message_alert('',"資料錯誤");
		return false;
	  }
	  
	  var element_meta  = {};
	  var act_object    = $(this);
	  var element_check = true;
	  
	  // option active checked  // 檢查按鈕是否在可執行狀態
	  if( act_object.prop('disabled') ){
	    return false;
	  }
	  
	  // get value
	  $('._element._detail._update').each(function(){
	    if($(this)[0].tagName=='INPUT' && ( $(this).attr('type')=='checkbox' || $(this).attr('type')=='radio')){
		  if($(this).hasClass('_flag')){
			var field_name = $(this).attr('name');
		    element_meta[field_name] = $(this).prop('checked') ? 1 : 0;  
		  }else{
			var field_name = $(this).attr('name');
		    if(typeof element_meta[field_name] == 'undefined') element_meta[field_name] = [];
			
			if($(this).prop('checked')){
		      var select_value = $(this).val()=='_newa' ? $(this).next().val() : $(this).val();
			  if(select_value){
				element_meta[field_name].push(select_value); 
			  }
			}
		  
		  }
		}else{
		  var field_name  = $(this).attr('id');
	      var field_value = $(this).val();
		  element_meta[field_name] = field_value;
		}
		
		if( ($(this).parents('.data_value').prev().hasClass('_must') || $(this).hasClass('_must')) && field_value=='' ){  
		  $(this).focus();
		  system_message_alert('',"請填寫必要欄位 ( * 標示)");
		  element_check = false;
		  return false;
		}
		
	  });
	  
	  if(!element_check) return false; 
	  
	  // encode data
	  var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(element_meta)));
       
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Meta/itemsave/'+master_no+'/'+element_no+'/'+passer_data},
		beforeSend: function(){  active_loading(act_object,'initial'); },   // 因為video load 會將  event peading
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			 
			
			if(element_no=='_addnew' && typeof response.data.newa.source_id !== 'undefined'){
			  main_dom.attr('no',response.data.newa.store_no); 
			}
			
			$('._target').removeClass('_target');
			
			main_dom.attr('mode','view');
			main_dom.addClass('_target');
			
			var data_load =  response.data.meta;
			data_orl['element'] = data_load.source;
			
			insert_data_to_element_form(main_dom,data_orl['element']);
			insert_data_to_element_form($('#record_member'),data_orl['element']);
            
			if(typeof response.data.save.updated.dotype != 'undefined'){
			  if(!$('#dobj_folder_change').find("option[value='"+response.data.save.updated.dotype+"']").length){
				$('#dobj_folder_change').append("<option value='"+response.data.save.updated.dotype+"' >"+response.data.save.updated.dotype+"</option>");
			  }
			  $('#dobj_folder_change').val(response.data.save.updated.dotype).trigger('change');
			}
			
			$('#record_member').css('display','flex'); 	
			  			
		  }else{
			system_message_alert('',response.info);
	      }
	    },
		complete:	function(){  }
      }).done(function(r) {  active_loading(act_object , r.action );  });
	  
	  
	});
	
	//@-- iterm function execute
	$('#act_element_built_function').change(function(){
	  switch($(this).val()){
		case 'act_element_delete'  : act_element_delete(); break;  
	    case 'act_element_editlogs': act_get_editlogs($('#META-E-store_no').val()); break;
	    default: system_message_alert('',"尚未開放"); break;
	  }
      $(this).val('');	  
	});
	
	
	//@-- close element editer
	$('#act_close_element_editor').click(function(){
	  if(!check_meta_modify()) return false;  
	  var editer_dom = $(this).parents('.data_record_block');
	  var edit_state = editer_dom.attr('status')	
      switch(edit_state){
		case 'editing':  break;
		default:break;  
	  }
	  editer_dom.css('display','none');
	  initial_metadata_editer('_element._detail');
	  FLAG_SYSTEM_EDITOR_LEVEL = 1;
	  location.hash = '';
	});
	
	
	
    //@-- item delete
	function act_element_delete(){
	 
	  // get value
	  var master_no   = $('#META-V-store_no').val();
	  var main_dom    = $('._target');
	  var element_no  = $('._target').attr('no');
	  
	  if( !element_no || !master_no){
	    system_message_alert('',"資料錯誤");
		return false;
	  }
	  
	  var element_meta  = {};
	  var act_object    = $(this);
	  var element_check = true;
	  
	  if(!confirm("確定要刪除本件資料??!!")){
		return false;  
	  }
	  
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Meta/itemdele/'+master_no+'/'+element_no},
		beforeSend: function(){ system_loading() },   // 因為video load 會將  event peading
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			data_orl['element'] = [];
			main_dom.remove();
			$('#act_close_element_editor').trigger('click'); //close editor
			system_message_alert('alert',"資料已經移除");
		  }else{
			system_message_alert('',response.info);
	      }
	    },
		complete:	function(){  }
      }).done(function(r) {  system_loading()  });
	  
	  
    }
    
	
	
	//@-- item editlogs 
	function act_get_editlogs(data_no){
	  
	  // get value
	  if( ! data_no ){
		system_message_alert('',"資料錯誤");
		return false;
	  }
	  
	  $('#meta_edit_record_block').empty();
	  
	  
	   // active ajax
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Meta/history/'+data_no},
		beforeSend: function(){  system_loading(); },
		error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		success: 	function(response) {
		  if(response.action){
			if(!response.data.length){
			  system_message_alert('alert',"無編輯紀錄");
			  return false;
			} 
			 
			$.each(response.data,function(i,log){
			  var record = $("<tr/>");
			  record.append("<td>"+log.time+"</td>");
			  record.append("<td>"+log.editor+"</td>");
			  
			  var modify = "";
			  $.each(log.fields,function(mf,mv){
				if(mf.match(/^\_/)) return true;
				modify = modify+"<div>"+mf+" => "+mv+"</div>";  
			  });
			  
			  record.append("<td>"+modify+"</td>");
			  record.appendTo($('#meta_edit_record_block'));
			  
			}); 
			$('#meta_edit_logs').show();
			
		  }else{
			system_message_alert('',response.info);
		  }
		},
		complete:	function(){  }
	  }).done(function(r) {  system_loading(); });
	  $(this).val('');
	}
	
	
	
	
	
	//initial volume meta &  item meta
	//初始化編輯介面
	if($('#VOLUMEID').length){  
	  
	  var volume_id    = $('#VOLUMEID').data('set');
	  
	  if(!volume_id){
		system_message_alert('','初始化資料失敗');  
	    return false;
	  }
	  
      // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Meta/volumeread/'+volume_id},
		beforeSend: function(){    },   // 因為video load 會將  event peading
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			
			var data_load =  response.data.meta;
			data_orl['volume'] = data_load.source;
			
			// insert research
			$.each(data_load.research,function(srno,research){
			  var record = $('.research_template').clone();
			  record.removeClass('research_template').attr({'mode':'view','no':srno});
			  record.find('.research_rno').text(srno);
			  record.find('._vresearch._update').val('');
			  record.find('.research_descrip').text(research['META-R-title']+' / '+research['META-R-pubyear']+' / '+research['META-R-author']);
			  record.appendTo('#research_contents');  	
			});
			
			 
			$.each(data_load.movement,function(smno,movement){
			  var record = $('.movement_template').clone();
			  record.removeClass('movement_template').attr({'mode':'view','no':smno});
			  record.find('.movement_no').text(smno);
			  record.find('._vmovement._update').val('');
			  $.each(movement,function(mf,mv){
                  record.find("._variable[name='"+mf+"']").map(function(){
				    if($(this).hasClass('_update')){
					  $(this).val(mv);
					  if($(this)[0].tagName=='SELECT' && !$(this).find("option[value='"+mv+"']").length){
						$(this).next().css('display','inline-block').end().hide();  
					  }
					}else{
					  $(this).text(mv);  
				    }
				  });
			  }); 
              record.appendTo('#movement_contents');  	
			});
			
			$.each(data_load.display,function(sdno,display){
			  var record = $('.display_template').clone();
			  record.removeClass('display_template').attr({'mode':'view','no':sdno});
			  record.find('.display_no').text(sdno);
			  record.find('._vdisplay._update').val('');
			  $.each(display,function(mf,mv){
                  record.find("._variable[name='"+mf+"']").map(function(){
				    if($(this).hasClass('_update')){
					  $(this).val(mv);
					  if($(this)[0].tagName=='SELECT' && !$(this).find("option[value='"+mv+"']").length){
						$(this).next().css('display','inline-block').end().hide();  
					  }
					}else{
					  $(this).text(mv);  
				    }
				  });
			  }); 
              record.appendTo('#display_contents');  	
			});
			 
		  }else{
			system_message_alert('',response.info);
	      }
	    },
		complete:	function(){  }
      }).done(function(r) {    });
	  
	  //-- initial account data  //帶有參數的網址連結資料
	  if(document.location.hash.match(/^#.+/)){
		  $target = $("tr.data_record[no='"+location.hash.replace(/^#/,'')+"']");
		  if($target.length){
			$('#volume_forms_switcher').find('li[data-group="element_list"]').trigger('click');  
			if( !$target.hasClass( '_target' )){
			  $target.trigger('click'); //初始化進入由  _element_read 啟動	
			}
		  }else{
			system_message_alert('','查無資料');
		  }
	  }else{
		  var address_path = document.location.search.split('/');	
		  if(typeof address_path[3] !='undefined' && parseInt(address_path[3])){
			$target = $("tr.data_record[no='"+address_path[3]+"']");
			if($target.length){ 
			  $('#volume_forms_switcher').find('li[data-group="element_list"]').trigger('click');  
			  if( !$target.hasClass( '_target' )){
				$target.trigger('click');	//初始化進入由  _element_read 啟動	
			  }
			}else{
			  system_message_alert('','查無資料');
			}  
		  }	
	  } 
	}
	
	
	
	
	/***== [ VOLUME RESEARCH FUNCTION ] ==***/
	
	//@-- insert new research 
	$('#act_create_research').click(function(){
	  var research = $('.research_template').clone();
      research.removeClass('research_template').attr({'mode':'edit','no':'_addnew'});
	  research.find('.research_rno').text('new');
	  research.find('._vresearch._update').val('');
	  research.appendTo('#research_contents');
	});
	
	
	
	//@-- open record editor
    $(document).on('click','.act_research_option',function(){
	  
	  var volume_id = $('#VOLUMEID').data('set')
	  var main_dom = $(this).parents('.research_record')
      var record_mode_now = main_dom.attr('mode');
      var record_mode_to  = record_mode_now=='view' ? 'edit' : 'view';
	 
	  switch(record_mode_to){
		case 'view':  //save
          
		  var research = {};
		  main_dom.find('._vresearch._update').each(function(){
			research[$(this).attr('name')] = $(this).val();
		  });
		  
		  // encode data
	      var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(research)));
          
		  // active ajax
		  $.ajax({
			url: 'index.php',
			type:'POST',
			dataType:'json',
			data: {act:'Meta/vrsave/'+volume_id+'/'+main_dom.attr('no')+'/'+passer_data},
			beforeSend: function(){  system_loading()    },   // 因為video load 會將  event peading
			error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
			success: 	function(response) {
			  if(response.action){
				main_dom.attr('no',response.data.save);
			    main_dom.find('.research_descrip').text(response.data.read['META-R-title']+' / '+response.data.read['META-R-pubyear']+' / '+response.data.read['META-R-author']);
			  }else{
				system_message_alert('',response.info);
			  }
			},
			complete:	function(){  }
		  }).done(function(r) {  system_loading()    });
		  break;
		  
        case 'edit': //read	
		  // active ajax
		  $.ajax({
			url: 'index.php',
			type:'POST',
			dataType:'json',
			data: {act:'Meta/vrread/'+volume_id+'/'+main_dom.attr('no')},
			beforeSend: function(){  system_loading()    },   // 因為video load 會將  event peading
			error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
			success: 	function(response) {
			  
			  if(response.action){
				
				$.each(response.data.read,function(rf,rv){
                  main_dom.find("._variable[name='"+rf+"']").map(function(){
				    if($(this).hasClass('_update')){
					  $(this).val(rv);
				    }else{
					  $(this).text(rv);  
				    }
				  });
			    }); 
			  }else{
				system_message_alert('',response.info);
			  }
			},
			complete:	function(){  }
		  }).done(function(r) {  system_loading()    });
		  break;
	  }
	  main_dom.attr('mode',record_mode_to);
    });
	
	
	//@-- open record editor
    $(document).on('click','.act_redearch_delete',function(){
	  
	  var volume_id = $('#VOLUMEID').data('set')
	  var main_dom = $(this).parents('.research_record')
      
	  
	  if(main_dom.attr('no')=='_addnew'){
		main_dom.remove();
        return true;		
	  }
	  
	  if(!confirm("確認要刪除本項引用紀錄?")){
		return false;		
	  }
	  
	  // active ajax
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Meta/vrdele/'+volume_id+'/'+main_dom.attr('no')},
		beforeSend: function(){  system_loading()    },   // 因為video load 會將  event peading
		error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		success: 	function(response) {
		  if(response.action){
			main_dom.remove();
		  }else{
			system_message_alert('',response.info);
		  }
		},
		complete:	function(){  }
	  }).done(function(r) {  system_loading()    });
	 
	});
	
	
	/***== [ VOLUME MOVEMENT FUNCTION ] ==***/
	
	//@-- insert new research 
	$('#act_create_movement').click(function(){
	  if($(".movement_record[no='_addnew']").length > 2){
		system_message_alert('',"請先儲存新增紀錄");  
	    return false;
	  }
	  var research = $('.movement_template').clone();
      research.removeClass('movement_template').attr({'mode':'edit','no':'_addnew'});
	  research.find('.movement_no').text('new');
	  research.find('._vmovement._update').prop({'disabled':false,'readonly':false});
	  research.prependTo('#movement_contents');
	});
	
	//@-- add new move type
	$(document).on('change','.act_movement_type_sel',function(){
	  if($(this).val() == '_new'){
		$(this).hide().next().show().focus();  
	  }
	})
	
	//@-- open record editor
    $(document).on('click','.act_movement_option',function(){
	  
	  var volume_id = $('#VOLUMEID').data('set')
	  var main_dom = $(this).parents('.movement_record')
      var record_mode_now = main_dom.attr('mode');
      var record_mode_to  = record_mode_now=='view' ? 'edit' : 'view';
	 
	  switch(record_mode_to){
		case 'view':  //save
          
		  var movement = {};
		  var checked  = true;
		  main_dom.find('._vmovement._update').each(function(){
			if(!$(this).is(":visible")) return true;
			if($(this).is(":visible") && $(this).val()==''){
			  $(this).focus();
			  checked = false;
			  return false;
			}
			movement[$(this).attr('name')] = $(this).val();
		  });
		  
		  if(!checked){
			system_message_alert('','所有欄位皆須填寫!!');  
		    return false;
		  }
		  
		  // encode data
	      var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(movement)));
          
		  // active ajax
		  $.ajax({
			url: 'index.php',
			type:'POST',
			dataType:'json',
			data: {act:'Meta/vmsave/'+volume_id+'/'+main_dom.attr('no')+'/'+passer_data},
			beforeSend: function(){  system_loading()    },   
			error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
			success: 	function(response) {
			  if(response.action){
				main_dom.attr('no',response.data.save.sno);
				main_dom.find('.movement_no').text(response.data.save.num);
				main_dom.find('._vmovement._update').prop({'disabled':true,'readonly':true});
			  }else{
				system_message_alert('',response.info);
			  }
			},
			complete:	function(){  }
		  }).done(function(r) {  system_loading()    });
		  break;
		  
        case 'edit': //read	
		  // active ajax
		  $.ajax({
			url: 'index.php',
			type:'POST',
			dataType:'json',
			data: {act:'Meta/vmread/'+volume_id+'/'+main_dom.attr('no')},
			beforeSend: function(){  system_loading()    },    
			error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
			success: 	function(response) {
			  
			  if(response.action){
				$.each(response.data.read,function(rf,rv){
                  main_dom.find("._variable[name='"+rf+"']").map(function(){
				    if($(this).hasClass('_update')){
					  if($(this)[0].tagName=='SELECT' && !$(this).find("option[value='"+rv+"']").length){
						$(this).next().css('display','inline-block').end().hide();  
					  }
				      $(this).val(rv).prop({'disabled':false,'readonly':false});   
					}else{
					  $(this).text(rv);  
				    }
				  });
			    }); 
			  }else{
				system_message_alert('',response.info);
			  }
			
			},
			complete:	function(){  }
		  }).done(function(r) {  system_loading()    });
		  break;
	  }
	  main_dom.attr('mode',record_mode_to);
    });
	
	
	//@-- open record editor
    $(document).on('click','.act_movement_delete',function(){
	  
	  var volume_id = $('#VOLUMEID').data('set')
	  var main_dom = $(this).parents('.movement_record')
      
	  if(main_dom.attr('no')=='_addnew'){
		main_dom.remove();
        return true;		
	  }
	  
	  if(!confirm("確認要刪除本項異動紀錄?")){
		return false;		
	  }
	  
	  // active ajax
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Meta/vmdele/'+volume_id+'/'+main_dom.attr('no')},
		beforeSend: function(){  system_loading()    },   // 因為video load 會將  event peading
		error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		success: 	function(response) {
		  if(response.action){
			main_dom.remove();
		  }else{
			system_message_alert('',response.info);
		  }
		},
		complete:	function(){  }
	  }).done(function(r) {  system_loading()    });
	 
	});
	
	
	
	
	
	/***== [ VOLUME DISPLAY FUNCTION ] ==***/
	
	//@-- insert new research 
	$('#act_create_display').click(function(){
	  if($(".display_record[no='_addnew']").length > 2){
		system_message_alert('',"請先儲存新增紀錄");  
	    return false;
	  }
	  var research = $('.display_template').clone();
      research.removeClass('display_template').attr({'mode':'edit','no':'_addnew'});
	  research.find('.display_no').text('new');
	  research.find('._vdisplay._update').prop({'disabled':false,'readonly':false});
	  research.prependTo('#display_contents');
	});
	
	 
	
	//@-- open record editor
    $(document).on('click','.act_display_option',function(){
	  
	  var volume_id = $('#VOLUMEID').data('set')
	  var main_dom = $(this).parents('.display_record')
      var record_mode_now = main_dom.attr('mode');
      var record_mode_to  = record_mode_now=='view' ? 'edit' : 'view';
	 
	  switch(record_mode_to){
		case 'view':  //save
          
		  var record = {};
		  var checked  = true;
		  main_dom.find('._vdisplay._update').each(function(){
			if(!$(this).is(":visible")) return true;
			if($(this).is(":visible") && $(this).val()==''){
			  $(this).focus();
			  checked = false;
			  return false;
			}
			record[$(this).attr('name')] = $(this).val();
		  });
		  
		  if(!checked){
			system_message_alert('','所有欄位皆須填寫!!');  
		    return false;
		  }
		  
		  
		  // encode data
	      var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(record)));
          
		  // active ajax
		  $.ajax({
			url: 'index.php',
			type:'POST',
			dataType:'json',
			data: {act:'Meta/vdsave/'+volume_id+'/'+main_dom.attr('no')+'/'+passer_data},
			beforeSend: function(){  system_loading()    },   
			error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
			success: 	function(response) {
			  if(response.action){
				main_dom.attr('no',response.data.save.sno);
				main_dom.find('.display_no').text(response.data.save.num);
				main_dom.find('._vdisplay._update').prop({'disabled':true,'readonly':true});
			  }else{
				system_message_alert('',response.info);
			  }
			},
			complete:	function(){  }
		  }).done(function(r) {  system_loading()    });
		  break;
		  
        case 'edit': //read	
		  // active ajax
		  $.ajax({
			url: 'index.php',
			type:'POST',
			dataType:'json',
			data: {act:'Meta/vdread/'+volume_id+'/'+main_dom.attr('no')},
			beforeSend: function(){  system_loading()    },    
			error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
			success: 	function(response) {
			 
			  if(response.action){
				$.each(response.data.read,function(rf,rv){
                  main_dom.find("._variable[name='"+rf+"']").map(function(){
				    if($(this).hasClass('_update')){
					  if($(this)[0].tagName=='SELECT' && !$(this).find("option[value='"+rv+"']").length){
						$(this).next().css('display','inline-block').end().hide();  
					  }
				      $(this).val(rv).prop({'disabled':false,'readonly':false});   
					}else{
					  $(this).text(rv);  
				    }
				  });
			    }); 
			  }else{
				system_message_alert('',response.info);
			  }
			
			},
			complete:	function(){  }
		  }).done(function(r) {  system_loading()    });
		  break;
	  }
	  main_dom.attr('mode',record_mode_to);
    });
	
	
	//@-- open record editor
    $(document).on('click','.act_display_delete',function(){
	  
	  var volume_id = $('#VOLUMEID').data('set')
	  var main_dom = $(this).parents('.display_record')
      
	  if(main_dom.attr('no')=='_addnew'){
		main_dom.remove();
        return true;		
	  }
	  
	  if(!confirm("確認要刪除本項展覽紀錄?")){
		return false;		
	  }
	  
	  // active ajax
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Meta/vddele/'+volume_id+'/'+main_dom.attr('no')},
		beforeSend: function(){  system_loading()    },   // 因為video load 會將  event peading
		error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		success: 	function(response) {
		  if(response.action){
			main_dom.remove();
		  }else{
			system_message_alert('',response.info);
		  }
		},
		complete:	function(){  }
	  }).done(function(r) {  system_loading()    });
	 
	});
	
	
	//@-- ui config setting 介面設定取得
	function editor_ui_config_get(){
	  // 紀錄介面設定
	  var uiconfig = {
		'volume_forms_switcher': $('#volume_forms_switcher').find('.meta_group_sel._atthis').attr('data-group'), 
		'doplatform_module':{
		  'left': $('#doplatform_module').css('left'),
          'width': $('#doplatform_module').css('width'),
		},
		'doscale_method':$('#doscale_method').attr('config'),
		'scale_set':$('#scale_set').val(),
	  };	
	  	
	  return uiconfig;
	}
	
	//@-- ui reset 重新設定介面
	function act_uiconfig_reset(){
	  $('#doplatform_module').css({'left':'','width':'','height':''});
	  $('#doscale_method').attr('config','fitview');
	  $('#scale_set').val('100');
	  $(window).trigger('resize');
	}
	
	//@-- editor function execute
	$('#act_meta_built_function').change(function(){
	  switch($(this).val()){
		case 'act_uiconf_reset'  : act_uiconfig_reset(); break;  
		case 'act_meta_editlogs' : act_get_editlogs($('#META-V-store_no').val()); break;
		case 'act_volume_create' : act_volume_create(); break;
		case 'act_volume_delete' : act_volume_delete(); break;
		default: system_message_alert('',"尚未開放"); break;
	  }
      $(this).val('');	  
	});
	
	
	 act_uiconfig_reset();
	
  }); /*** end of html load ***/
    

	//-- admin record active data print //列印函數
	(function() {
    var beforePrint = function() {
	  $('.is_print').each(function(){
		if(!$(this).prop('checked')){
		  $(this).parents('.print_group_block').hide();	
		}  
		  
	  })
    };
    var afterPrint = function() {
      $('.print_group_block').show();
	  $('.print_table.relate').find('tbody:not(.print_template)').remove();
	  $('body').removeClass('print_mode'); 
    };

    if (window.matchMedia) {
        var mediaQueryList = window.matchMedia('print');
        mediaQueryList.addListener(function(mql) {
			if ( mql.matches ) {
                beforePrint();
            } else {
                afterPrint();
            }
        });
    }
    window.onbeforeprint = beforePrint;
    window.onafterprint = afterPrint;
    }());
     
	//-- image load 輔助函數-1
	$.createCache = function( requestFunction ) {
		var cache = {};
		return function( key, callback ) {
			if ( !cache[ key ] ) {
				cache[ key ] = $.Deferred(function( defer ) {
					requestFunction( defer, key );
				}).promise();
			}
			return cache[ key ].done( callback );
		};
	};
	  
	//-- image load 輔助函數  -2
    $.loadImage = $.createCache(function( defer, url ) {
		var image = new Image();
		function cleanUp() {
			image.onload = image.onerror = null;
		}
		defer.then( cleanUp, cleanUp );
		image.onload = function() {
			defer.resolve( url );
		};
		image.onerror = defer.reject;
		image.src = url;
    });
	
	
	
	
	//-- sse event regist 任務處理
	
	var TaskEvent = {};
	function system_event_regist(even_type,PaseID){
	  
		if(!PaseID.length){
		  return false;	
		}

		switch(even_type){
		  
		  case 'TASK':
			
			// regist
			TaskEvent[PaseID] = new EventSource("event.php?task="+PaseID);
			TaskEvent[PaseID].onmessage = function(event) { console.log(event.data); };
		  
			// 起始訊息
			system_event_alert({"task":PaseID, "info":"資料上傳已經完成，檔案轉置中 "},'load'); 
			
			// 註冊主體訊息函數
			TaskEvent[PaseID].addEventListener('PROCESSING', function(e) {
			  var data = JSON.parse(e.data);
			  $("li[task='"+data.task+"']").find('.progress').html(data.progress);
			}, false);
			
			// 註冊主體完成函數
			TaskEvent[PaseID].addEventListener('TASKFINISH', function(e) {
			  var data = JSON.parse(e.data);
			  system_event_alert(data,'done');
			  TaskEvent[PaseID].close();
			  create_dobj_list(); // 重建檔案列表
			}, false);
			
			break;
		  
		  case 'DOBJ':
			
			// regist
			var pasid = PaseID.split(':')
			TaskEvent[PaseID] = new EventSource("event.php?task="+pasid[0]+'&dobj='+pasid[1]);
			TaskEvent[PaseID].onmessage = function(event) { console.log(event.data); };
			
			// 註冊主體訊息函數
			TaskEvent[PaseID].addEventListener('PROCESSING', function(e) {
			  var data = JSON.parse(e.data);
			  $("li[task='"+data.task+"']").find('.target').html(data.target+'..');
			}, false);
			
			// 註冊單件處理函數
			TaskEvent[PaseID].addEventListener('DOIMPORTED', function(e) {
			  var data = JSON.parse(e.data);
			  
			  if(!$("li.tmpupl[urno='"+pasid[1] +"']").length) return true;
			  $("li.tmpupl[urno='"+pasid[1] +"']").empty().remove();
			  
			  TaskEvent[PaseID].close();
			
			}, false);
			
			break;
		  
		  
		  case 'package':
			system_event_alert({"task":task_no, "info":"資料匯出打包中.."},'load');
			TaskEvent[task_no].addEventListener('_PROCESSING', function(e) {
			  var data = JSON.parse(e.data);
			  $("li[task='"+data.task+"']").find('.progress').html(data.progress);
			}, false);
			
			TaskEvent[task_no].addEventListener('_PHO_EXPORT', function(e) {
			  var data = JSON.parse(e.data);
			  system_event_alert(data,'link');
			  TaskEvent[task_no].close();
			}, false);
			
			break;	
			
		  default:break;	  
		}
	}
  
    
	//-- alert event message
	function system_event_alert(data,type){
	  type = type!='' ? type : 'alert'; 

	  var DOM = $("<li/>");
	  switch(type){
	    case 'load'  : DOM.attr('task',data.task).html(data.info+" <span class='progress'>0 / 0</span> / <span class='target'> </span>"); break;
	    case 'link'  : DOM.addClass('download_link').attr('data-href',data.href).html("資料打包完成，請點選下載 ("+data.count+")"); break;  
	    case 'done'  : DOM.html("上載資料已匯入系統 ("+data.count+")"); break;  
	    default: DOM.html(data.info); break;
	  }
	  $('#task_info').find('li').hide().end().prepend(DOM);
	}
    
	
	
	
   
	
	
  