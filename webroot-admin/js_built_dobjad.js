/* [ Admin Meta DOBJ Admin Function Set ] 數位檔案管理模組*/
  
  var dobjectconf   = {};  // 數位檔案設定
  
  function module_information( ModuleObj, ActionName, Message, Result){
      
      if(!ModuleObj.length){
		return false;
	  }

	  var infodom = ModuleObj.find('.action_result');
	  
	  switch(Result){
		case 'success': case 'fail  ':
		  infodom.attr('alert','success');
		  infodom.find('.execute').text(ActionName);
		  infodom.find('.message').text(Message); 
          infodom.find('.acttime').text( new Date().toLocaleString() );  		  
		  break;
		case 'reset  ': 
		default:  
		  infodom.attr('alert',''); 
		  infodom.find('.execute').text('模組資訊');
		  infodom.find('.message').text('');
		  infodom.find('.acttime').text('');  		  
		  break; 
	  }
  }
  
  
  
  
  $(window).on('load',function () {   //  || $(document).ready(function() {		
    
	/* == 影像調用函數 == */
	
	// 繪製影像
	
	//-- initial
	var do_container_w = 0;
	var do_container_h = 0;
	var dobj_default_center = 0;  // 數位物件顯示介面中心點
	
	var canvas_main;    // 主要物件
	
	var set_img_w = 0;  // 最後設定影像寬
	var set_img_h = 0;  // 最後設定影像長
	
	var set_left  = 0;  // 數位物件預設位置 left
	var set_top   = 50; // 數位物件預設位置  top
	
	var page_config = {};
	
	var do_move_start_point       = {};
	
	var img_object={'lx':0,'ly':0};
	
	
	//--讀取資料陣列
	var doqueue = [];
	
	//--當前頁面物件  
	var dobject = false;
	
	//@ 初始化 dobject
	function initial_dobject(PageName){
	  var dobject_template = {   
	    name : PageName,      //檔案名稱
	    path : '',	  		  //存取路徑   
	    size : {'ow':0,'oh':0,'vw':0,'vh':0}, //影像尺寸
	    coor : {'x':0,'y':0}, //位置
		viewpoints : [], 	  //超過視區時的切換點位 
		viewfocus:0,          //目前的視界點
		scal : 1,			  //縮放
	    item :[], 			  //頁面上的標籤(meta)
	    mark :[],             //頁面上的註記
        ready:false,		
	    tmpcanvas : false,    //暫存canvas 
		markcanvas: false,    //繪圖canvas 
		
	  };
	  return dobject_template;
	}
	
	//@-- get mouse location on dobjview 計算鼠標再畫面之位置
	function get_mouse_on_dobjview(evt){
	  var canvas = document.getElementById('dobj_canvas_main');
	  var rect   = canvas.getBoundingClientRect();
      var mouse_x = evt.clientX - rect.left;
	  var mouse_y = evt.clientY - rect.top;	
      return {'x':mouse_x,'y':mouse_y} ;
	}
	
	//@-- canvas mark object 圖標物件
	function DobjMark(LocX,LocY,Type){
	  
	  this.x = LocX || 0;
	  this.y = LocY || 0;
	  this.r = 10;
	  this.t = Type;
	  this.color = '#00ff00';
	  
      this.draw = function(ctx){ //create the draw function
        switch(Type){
		  case 'category': 
            ctx.beginPath();
		    ctx.arc(this.x,this.y,this.r,0,2*Math.PI ,false );
		    ctx.fillStyle = this.color;	
		    ctx.fill();
			break;
			
          default: 
		    //ctx.fillRect( mouse_x, mouse_y,20,20) //方形
	        //context.lineWidth = 1;
			//context.strokeStyle = "black";
			//context.stroke(); 
		    break;
		}
      };
	  
      this.update = function () { //create the function for update
        this.y--; //everytime this function runs, move the bubble up the y axis by one
        if (y < canvas.height) { //if the y axis of the bubble is less than the height of the canvas
            this.expired = true; //make the bubble disappear
        }
      };

      // HITTEST: Perform hit-test to determine if the passed in coordinate lies within 
      // the circle.
      // http://stackoverflow.com/questions/2212604/javascript-check-mouse-clicked-inside-the-circle-or-polygon
      this.hitTest = function (x, y) {
        var dx = x - this.x
	    var dy = y - this.y
        return dx * dx + dy * dy <= this.r * this.r
      }; 
	
	}
	
	
	
	
	//@ doloading alert 
	function dobj_loading(){
	  var loader_display = $('#dobj_loader').is(':visible') ? 'none':'block';
	  $('#dobj_loader').css('display',loader_display);
	}
	
	//@ initial canvas
	if($('#dobj_container').length){
	  
	  do_container_w = $('#dobj_container').innerWidth();
	  do_container_h = $('#dobj_container').innerHeight(); 
	  $('#dobj_container').append("<canvas id='dobj_canvas_main' width='"+do_container_w+"' height='"+do_container_h+"'/>");
	  canvas_main = document.getElementById('dobj_canvas_main').getContext('2d');
	  
	  // 綁滑鼠函數
	  $('#dobj_canvas_main').hover(
		function() {  //mouse enter
		  FLAG_MOUSE_ON_DOVIEW = true;  
		}, function() { //mouse leave
		  FLAG_MOUSE_ON_DOVIEW = false; 
		}
	  );
	  
	  // 綁定mark func
	  $('#dobj_canvas_main').click(function(event){
		
        if(!FLAG_MARK_ON_DOVJ){
		  return true  
	    }
		
		if(dobject.mark.length){
		  
		  var mouse = get_mouse_on_dobjview(event);
          var scale = dobject.size.vw/dobject.size.ow;
	      var hitloc = {'x':parseInt((mouse.x-dobject.coor.x)/scale),'y':parseInt((mouse.y-dobject.coor.y)/scale)}
		  
		  var ctx = dobject.tmpcanvas.getContext('2d');
          ctx.globalAlpha = 0.8;
	      ctx.fillStyle = "#00ff00";		
		  $.each(dobject.mark,function(i,mobj){
		    if(mobj.hitTest(hitloc.x,hitloc.y)){
			  mobj.color = 'red';
			}
	      });
          
		  if(FLAG_MOUSE_TO_MOVE) return true
		  draw_page();	  
		}   
	  });
	  
	  
	  // 顯示區域縮放
	  $('#doplatform_module').resizable({
		containment: ".main_content",
		handles: "se, sw",
		start: function( event, ui ) {  
		  FLAG_MOUSE_IN_USED = true;	
		},
		stop:function( event, ui ) {
		  FLAG_MOUSE_IN_USED = false;
		  resize_doplayform();
		},
	  });
	  
	  function resize_doplayform(){  
		//重算區塊
	    do_container_w = $('#dobj_container').innerWidth();
	    do_container_h = $('#dobj_container').innerHeight(); 
	    $('#dobj_canvas_main').attr({'width':do_container_w,'height':do_container_h});
	    canvas_main = document.getElementById('dobj_canvas_main').getContext('2d');

        //繪製影像
		if(!$('.page_selecter').val()) return true;
		dobj_loading(); 	
		draw_page();   
		dobj_loading();
	  }
	  
	  $(window).resize(function(event) {
		resize_doplayform()
	  });
	  
	}
	
	//@ load image to temp canvas
	function load_page(PageName){
       	  
      dobj_loading(); 
	  
	  // 初始化物件
	  dobject = initial_dobject(PageName);
	  
	  // 取得參數
	  var root =  $('#DATAROOT').data('set');
	  var folder =  $('#DOFOLDER').data('set');
	  
	  dobject.path	 = 'dobj.php?src='+root+'browse/'+folder+'/'+PageName;
      
	  return new Promise(function(resolve, reject){
        var img = new Image();
        img.onload = function(){
          
		  dobject.size.ow = img.width;
		  dobject.size.oh = img.height;
		
		  $('#dobj_canvas_temp').remove();
	      $('#dobj_container').append("<canvas id='dobj_canvas_temp' width='"+dobject.size.ow+"' height='"+dobject.size.oh+"' style='display:none;'/>");
	      
		  // draw page
		  var ctd = document.getElementById('dobj_canvas_temp').getContext('2d');
		  ctd.drawImage(img,0,0,dobject.size.ow,dobject.size.oh);
		  dobject.tmpcanvas = document.getElementById('dobj_canvas_temp');
		    
		  // 標示縮圖
		  $('.thumb.atpage').removeClass('atpage');
		  $('.thumb[p="'+PageName+'"]').addClass('atpage');
		  var prev_count = $('.thumb[p="'+PageName+'"]').prevAll().length;
		  var thumb_dim_h= $('.thumb[p="'+PageName+'"]').height()+9
		  var scrolltop  = prev_count*thumb_dim_h;
		  var scroll_container = $('#dobj_thumb_block').height();
		  
		  var padding_hold = parseInt(scroll_container/2)-50;
		  var scroll_to = (padding_hold < scrolltop) ? scrolltop-padding_hold : 0;
		  $('#dobj_thumb_block').scrollTop(scroll_to);
		  
		  dobj_loading();
		  dobject.ready = true;
		  resolve(dobject.path)
		  
        }
        img.onerror = function(){
          reject(dobject.path)   
        }
        img.src = dobject.path
      
	  });  // end of image load
	}
	
	
	//@ 計算影像位置並將物件繪入
	function draw_image_on_canvas(){
	   
	  do_container_w; //面板大小
	  do_container_h; //面板大小
		
	  var orl_img_w = dobject.size.ow;
	  var orl_img_h = dobject.size.oh;
		
	  var set_img_w = orl_img_w;
	  var set_img_h = orl_img_h;
	  
	  // set size
	  var print_method = $('#doscale_method').length ? $('#doscale_method').attr('config') : 'fitview';
	  
	  switch(print_method){
		  case 'fillview':   //填滿視區
		    if(orl_img_h > orl_img_w){
			  set_img_w = do_container_w;    
			  set_img_h = set_img_w * orl_img_h / orl_img_w;
			}else{
			  set_img_h = do_container_h; 
			  set_img_w = set_img_h * orl_img_w / orl_img_h ;
			}
		    break;
		  
		  case 'fitwidth': //等寬 
			set_img_w = do_container_w;    
			set_img_h = set_img_w * orl_img_h / orl_img_w;
			break;
			
		  case 'fitheight':
			set_img_h = do_container_h; 
			set_img_w = set_img_h * orl_img_w / orl_img_h ;
			break;
			
		  case 'fitview': default:
			if(orl_img_h > orl_img_w){
			  set_img_h = do_container_h; 
			  set_img_w = set_img_h * orl_img_w / orl_img_h ;
			}else{
			  set_img_w = do_container_w; 
			  set_img_h = set_img_w * orl_img_h / orl_img_w;			  
			}
			var rescale = 1;
			while( (set_img_h*rescale > do_container_h || set_img_w*rescale > do_container_w) && rescale>0){
			  rescale = rescale-0.01  	
			}
			set_img_h = parseInt(set_img_h*rescale);
			set_img_w = parseInt(set_img_w*rescale);
			break;
	  }
	 	
	  //--  scale  
	  var scale_rate  = parseFloat($('#scale_set').data('scale')); 
	  
	  set_img_h = set_img_h * scale_rate;
	  set_img_w = set_img_w * scale_rate;
	  
	  // count initial location
	  var loc_img_x = 0; // left
	  var loc_img_y = 0; // top
	  if(set_img_w < do_container_w){
		loc_img_x = parseInt((do_container_w - set_img_w) /2);
	  }else if(set_img_w > do_container_w){
		loc_img_x = do_container_w - set_img_w;
	  }
	  
	  if(dobject.coor.x===0 && dobject.coor.y===0){
		dobject.coor.x = loc_img_x;
	    dobject.coor.y = loc_img_y;  
	  }else{
		loc_img_x= dobject.coor.x;
	    loc_img_y= dobject.coor.y;
	  }
	  
	  dobject.size.vw = set_img_w;
	  dobject.size.vh = set_img_h;
	  dobject.viewpoints = [];
	  dobject.viewfocus = 0;
	  
	  if(set_img_h*set_img_w > do_container_w*do_container_h){
		
		var view_object_width  = set_img_w;
		var view_object_height = set_img_h;
		var pointer_x = 0;
		var pointer_y = 0;
		
		// 計算區塊數量
		var x_block_count = parseInt(view_object_width / do_container_w) + ((view_object_width%do_container_w)?1:0);
		var y_block_count = parseInt(view_object_height / do_container_h) + ((view_object_height%do_container_h)?1:0);
		
		for(var x=1 ; x<=x_block_count ; x++){
		  for(var y=0 ; y<y_block_count ; y++){
			pointer_x = do_container_w*x - view_object_width;
			pointer_x = ( pointer_x > 0 ) ? 0 : pointer_x; 
			
			// 調整
			pointer_y = 0-do_container_h*y + (y*10);
			pointer_y = pointer_y !=0 && ( pointer_y+view_object_height) < do_container_h ? do_container_h-view_object_height : pointer_y;
			
			dobject.viewpoints.push({'x':parseInt(pointer_x),'y':parseInt(pointer_y)});	
		  }	
		}
	  }
	  
	  // draw page
	  canvas_main.clearRect(0,0,do_container_w,do_container_h);
	  
	  // if has mark 
	  if(dobject.mark.length){
		//draw mark 
		var ctx = dobject.tmpcanvas.getContext('2d');
        ctx.globalAlpha = 0.8;
	    ctx.fillStyle = "#00ff00";		
		$.each(dobject.mark,function(i,mobj){
		  mobj.draw(ctx);
	    });
	  }
	  
	  // draw page
	  canvas_main.drawImage(dobject.tmpcanvas,loc_img_x,loc_img_y,set_img_w,set_img_h);	
	  
	  // draw mark tracker
	  if(dobject.markcanvas){
		canvas_main.drawImage(dobject.markcanvas,0,0,do_container_w,do_container_h);  
	  }
	  
	}
	
	//@-- load do to canvas
	function draw_page(PageName){
	  if(PageName && dobject.name != PageName){
		//-- 載入影像  
		load_page(PageName).then(function(successurl){
           draw_image_on_canvas(); 
		}).catch(function(errorurl){
		   system_message_alert("","影像載入失敗");
           return false;		   
		});
	  }else{
		draw_image_on_canvas();   
	  } 
	}
	
	
	//@-- set scale method  設定縮放模式
	$('a.scalfunc').click(function(){
	  
	  if(!dobject.ready) return false;
	  
	  var print_method = $('#doscale_method').length ? $('#doscale_method').attr('config') : 'fitview';
	  if(print_method == $(this).data('set')) return true;
	  $('#doscale_method').attr('config',$(this).data('set'));
	  $('#scale_set').data('scale',1).val(100).trigger('change');
	  dobject.coor={'x':0,'y':0}
	  draw_page();
	});
	
	//@-- change scale  設定縮放大小
	$('#scale_set').on('mousemove change blur',function(){
	  if(!dobject.ready) return false;	
	  var scale = Number( parseFloat($(this).val() /100).toFixed(1),2);	
	  $('#scale_info').text( Number( parseFloat($(this).val() /100).toFixed(1),2));
	  $(this).data('scale',scale);
	  draw_page();
	});
	
	//@-- wheel scale  滾輪縮放 
	$(document).on('mousewheel','#dobj_canvas_main',function(event, delta){
	  if(!dobject.ready || !FLAG_MOUSE_ON_DOVIEW){
		return false  
	  }
	  var ImgRate = parseInt($('#scale_set').val());
	  ImgRate = (delta<0) ? ImgRate-10 : ImgRate+10;
	  $('#scale_set').val(ImgRate).trigger('change')
	});
	
	//@-- mouse draggable start
	$(document).on('mousedown','#dobj_canvas_main',function(event, delta){ 
	  
	  if(!dobject.ready){
		return false; // 沒有圖片 
	  }
	  
	  if(!FLAG_MOUSE_ON_DOVIEW || FLAG_MOUSE_IN_USED ){
		return false  
	  }
	  
	  FLAG_MOUSE_TO_MOVE = true;
	  
	  var mouse = get_mouse_on_dobjview(event);
	  var canvas = document.getElementById('dobj_canvas_main');
      var rect   = canvas.getBoundingClientRect();
      do_move_start_point = {
        px: mouse.x,
        py: mouse.y,
		mx: event.clientX,
		my: event.clientY,
      };
	
	});
	
	
	//@-- mouse draggable start
	$(document).on('mouseup','.main_content',function(event, delta){ 
	  if(!FLAG_MOUSE_TO_MOVE){
		return false  
	  }
	  FLAG_MOUSE_TO_MOVE = false;
	  do_move_start_point = {};
	});
	
	
	//@-- mouse draggable move
	$(document).on('mousemove','#dobj_canvas_main',function(event, delta){ 
	  
	  if(!FLAG_MOUSE_TO_MOVE){
		return false  
	  }
	  
	  var x_move = event.pageX - do_move_start_point.mx;
	  var y_move = event.pageY - do_move_start_point.my;
	  
	  dobject.coor.x = parseInt(dobject.coor.x) + x_move;
	  dobject.coor.y = parseInt(dobject.coor.y) + y_move;
	  dobject.viewfocus = 0;
	  
	  // draw page
	  draw_page();
	  
	  var mouse = get_mouse_on_dobjview(event); 
      do_move_start_point = {
        px: mouse.x,
        py: mouse.y,
		mx: event.clientX,
		my: event.clientY,
      };
	  
	});
	
	

	//@-- folder 篩選資料夾
	$('.folder_selecter').change(function(){
	  
	  //cancel_pre_action();
	  var folder = $(this).val();
	  if(folder=='' || folder=='_all'){
		$('.thumb').removeClass('_hide');
	  }else{
		$('.thumb').addClass('_hide');
		if($(".thumb[data-folder='"+folder+"']").length){
		  $(".thumb[data-folder='"+folder+"']").removeClass('_hide');
		}else{
		  system_message_alert('','此資料夾無相關影像');
		}
	  }
	});
	
	 
	
	//@-- page selecter 跳頁
	$('.page_selecter').change(function(){
	  
	  if($('#act_dobj_edit_flag').prop('checked')){
		system_message_alert('','請先關閉編輯');
		return false;  
	  }  
	  //cancel_pre_action();
	  
	  // set page view mode 
	  $('#act_switch_view').attr('display',$(this).find('option:selected').attr('display'));
	  draw_page($(this).val());
	});
	
	
	//@-- page switch 換頁
	$('.page_switch').click(function(){
		
	  if($('#act_dobj_edit_flag').prop('checked')){
		system_message_alert('','請先關閉編輯');
		return false;  
	  }	
	  	
  	  var page_now 	= $('.page_selecter').val();
	  var pager_dom = '';
      var next_dom 	= '';
	  var next_point= dobject.viewfocus;
	  var next_folder = '';
	  
	  
	  
	  if( !$('option.pager:selected').length ){
		if($('.thumb').length){
		  $('.thumb:nth-child(1)').trigger('click');
		}else{
		  system_message_alert('','影像尚未讀取');	
		}
        return false;		
	  }
      
	  pager_dom = $('option.pager:selected');
	  pager_folder = $('option.pager:selected').parent();
	  
	  
	  switch($(this).attr('mode')){
		case 'dnext': 
		  next_dom = pager_dom.next('.pager'); 
		  next_folder = pager_folder.next();
		  break;
        
		case 'dprev': 
		  next_dom = pager_dom.prev('.pager'); 
		  next_folder = pager_folder.prev();
		  break;
        
        case 'vnext' : 
		  next_point++;
		  if(next_point < dobject.viewpoints.length ){
			dobject.coor =  dobject.viewpoints[next_point];
			dobject.viewfocus = next_point;
			canvas_main.clearRect(0,0,do_container_w,do_container_h);
	        canvas_main.drawImage(dobject.tmpcanvas,dobject.coor.x,dobject.coor.y,dobject.size.vw,dobject.size.vh);				
		    return true;
		  }
		  next_dom = pager_dom.next('.pager');
		  break;
		  
		case 'vprev' :  
		  next_point--;
		  if(next_point >= 0 ){
			dobject.coor =  dobject.viewpoints[next_point];
            dobject.viewfocus = next_point;
			canvas_main.clearRect(0,0,do_container_w,do_container_h);
	        canvas_main.drawImage(dobject.tmpcanvas,dobject.coor.x,dobject.coor.y,dobject.size.vw,dobject.size.vh);				
		    return true;
		  }
		  next_dom = pager_dom.prev('.pager');
		  break;
		  
	    default: return false; break;
	  }
	  
	  if(!next_dom.length){
		  
		if(next_folder.length){
		  
		  if($(this).attr('mode') == 'dnext'){
			next_dom = next_folder.find('option:first-child');	
		  }else if($(this).attr('mode') == 'dprev'){
			next_dom = next_folder.find('option:last-child');	  
		  }
		  
		  if(!next_dom.length){
			system_message_alert('','影像已達端點');
		    return false;   	  
		  }
		  
		}else{
		  system_message_alert('','影像已達端點');
		  return false;   	
		}
	  }
	  
	  $('.page_selecter').val(next_dom.val()).trigger('change');
	
	});
	
	
	//@-- 下載影像原始檔
	$('#act_download_stored').click(function(){
      var do_name = $('.page_selecter').val()	  
      if(!do_name){
		system_message_alert('','尚未選擇影像');  
	    return false;
	  }	  
	  
	  // get reference
	  var dataroot = $('meta#DATAROOT').data('set');  // 資料分類
	  var dofolder = $('meta#DOFOLDER').data('set');  // 檔案資料夾
	  
	  var recrod = $(this).parents('tr.file');
	  
	  //-- 解決 click 後無法馬上open windows 造成 popout 被瀏覽器block的狀況
	  newWindow = window.open("","_blank");
	     
	  $.ajax({
		  url: 'index.php',
		  type:'POST',
		  dataType:'json',
		  data: {act:'Meta/doprepare/'+dataroot+dofolder+'/'+do_name},
		  beforeSend: 	function(){ system_loading();  },
		  error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		  success: 		function(response) {
			if(response.action){  
			  newWindow.location.href = 'index.php?act=Meta/dodownload/'+response.data.dobjs.hash;
			}else{
			  newWindow.close();
			  system_message_alert('',response.info);
			}
		  },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });	
	  
	  
		
	});
	
	
	//@-- 設定為資料封面
	$('#act_set_item_cover').click(function(){
      var do_name = $('.page_selecter').val()	  
      if(!do_name){
		system_message_alert('','尚未選擇影像');  
	    return false;
	  }	  
	  
	  // get reference
	  var dataroot = $('meta#DATAROOT').data('set');  // 資料分類
	  var dofolder = $('meta#DOFOLDER').data('set');  // 檔案資料夾
	  
	  var recrod = $(this).parents('tr.file');
	  
	 
	  $.ajax({
		  url: 'index.php',
		  type:'POST',
		  dataType:'json',
		  data: {act:'Meta/dosetcover/'+dataroot+dofolder+'/'+do_name},
		  beforeSend: 	function(){ system_loading();  },
		  error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		  success: 		function(response) {
			if(response.action){  
			   $('#dobj_cover').attr('src','thumb.php?src='+dataroot+'thumb/'+dofolder+'/'+do_name);
			   system_message_alert('alert',"封面設定為 : "+do_name);
			}else{
			  system_message_alert('',response.info);
			}
		  },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });	
	  
	  
		
	});
  
	
	
	
	
	
	
	/* == 縮圖調用函數 == */
	
	//@-- Lazy Load
	$( "#dobj_thumb_block" ).scroll(function() {
	  $(this).lazyLoadXT();
	});
	
	$("#dobj_thumb_block").lazyLoadXT({edgeY:300});
	
	//@-- Query Lazy Load 
	$(document).on('lazyshow','.thumb', function () {
        /*
		var slot  = $(this).attr('slot');
		var accno = $(this).attr('accno');
		
		if(!parseInt(accno)  || slot=='-' ){
		  return false;	
		}
		loadQueryResultToSystem(accno,slot);*/
    }).lazyLoadXT({visibleOnly: false});
	
	//@-- click thumb
	$(document).on('click','.thumb',function(){
	  $('.page_selecter').val($(this).attr('p')).trigger('change');
	});
	
	
	//@-- jquery scroll bar thumb 區塊卷軸
    if($('#thumb_block_weaper').length){
		
		// 切換重新設定 scroll	
		var setting = {
		  autoReinitialise: true,
		  showArrows: false
		};
		// 設定 jScrollPane
		$('#thumb_block_weaper ').scrollbar();	 
    }
	
	
	/* == 影像列表函數 == */
	
	/*[ Dobj Record Function ]*/ // 數位檔案列表動作
	
	
	//@-- open dobj admin module
	$('#act_dolist_admin').click(function(){
	  $('#adfile_module').toggle();	
	});
	
	
	
	//-- record config switch
	$('#act_adfile_conf_switch').change(function(){
	  
	  // get reference
	  var dataroot = $('meta#DATAROOT').data('set');  // 資料分類
	  var dofolder = $('meta#DOFOLDER').data('set');  // 檔案資料夾
	  
	  var conf_mode = $(this).val();
	  if(!conf_mode){
		system_message_alert('','設定模式錯誤');  
	    return false; 	
	  }
	  
      var domid     = $(this).attr('prehead')+'-'+conf_mode;      
      if(!$('#'+domid).length){
		system_message_alert('','設定模式不存在');  
	    return false; 	  
	  }
	  
	  // 各設定動作欲載行為
	  switch(conf_mode){
		case 'rename': 
		  $('#file_name_prehead').val(dofolder); 
		  $('#file_name_startno').focus(); 
		  break;
		  
		case 'reorder':
          $( "#do_list_container" ).sortable({
			cancel: "tr:not(.sortable)"  
		  });
		  
		  // 設定勾選資料為可排序
		  $('.act_selone_dfile:checked').parents('tr.file').addClass('sortable');
		  
		  // 註冊排序啟動
		  $(document).on('change','.act_selone_dfile',function(){
			if($(this).prop('checked')){
			  $(this).parents('tr.file').addClass('sortable');	
			}else{
			  $(this).parents('tr.file').removeClass('sortable');		
			}  
		  });
		  
		  $( "#do_list_container" ).disableSelection();
   		  
		  break;
		  
		default:break;
	  }
	  
	  $('.funcgroup').hide();
	  $('#'+domid).show();
	  
	});
	
	
	
	
	//-- shift select all shift 多選
	var shift_select_start_dom = [];
	var shift_select_flag = 0;
	$(document).keydown(function(event){
	  shift_select_flag = event.key=='Shift' ? 1 : 0; 
	}); 
	$(document).keyup(function(event){
	  shift_select_flag = 0; 
	  shift_select_start_dom = false;
	});
	
	
	//-- record select dobj all 全選列表檔案
	if($('#act_selall_dfile').length){
	  $('#act_selall_dfile').change(function(){
		  
		if($(this).prop('checked') && !$('.act_selone_dfile').length){
		  system_message_alert('','尚未有新增檔案!!');
		  $(this).prop('checked',false);
          return false;		  
		}
		
		$('.act_selone_dfile').prop('checked',$(this).prop('checked')); 
	  
	  });
	}
	
	//-- record select dobj one 單選列表檔案
	$(document).on('click','.act_selone_dfile',function(){
	  
	  // 同步多選box
	  var select_all_fleg = $('.act_selone_dfile').length == $('.act_selone_dfile:checked').length ? true : false;
	  $('#act_selall_dfile').prop('checked',select_all_fleg);  	
	  
	  // 設定shift多選
	  if( $(this).prop('checked') ){
		
		if(!shift_select_start_dom){ // 第一個勾選
		  shift_select_start_dom = $(this);	
		}else{
		  
		  if(!shift_select_flag){
			return true;  
		  }
		  
          // 壓著shift &  已經勾選了第一個   		  
          var nowid = $(this).val();
		  var start = shift_select_start_dom.parents('tr');            
          var queue = [];
		  start.nextAll( "tr" ).each(function(){
			var checkbox = $(this).find("input[name='fselect']");  
			queue.push(checkbox);
            if(nowid == checkbox.val()){  
			  $.each(queue,function(i,dom){
				dom.prop('checked',true);  
			  });
			  queue = [];
			}
		  });
		}
	  }
	  
	});
	
	
	//-- record download 
	$(document).on('click','.act_adfile_downloaddo',function(){
	  
	  // get reference
	  var dataroot = $('meta#DATAROOT').data('set');  // 資料分類
	  var dofolder = $('meta#DOFOLDER').data('set');  // 檔案資料夾
	  
	  var recrod = $(this).parents('tr.file');
	  
	  //-- 解決 click 後無法馬上open windows 造成 popout 被瀏覽器block的狀況
	  newWindow = window.open("","_blank");
	     
	  $.ajax({
		  url: 'index.php',
		  type:'POST',
		  dataType:'json',
		  data: {act:'Meta/doprepare/'+dataroot+dofolder+'/'+recrod.data('file')},
		  beforeSend: 	function(){ system_loading();  },
		  error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		  success: 		function(response) {
			if(response.action){  
			  newWindow.location.href = 'index.php?act=Meta/dodownload/'+response.data.dobjs.hash;
			}else{
			  newWindow.close();
			  system_message_alert('',response.info);
			}
		  },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });	
	  
	});
	
	
	
	
	
	//-- record select to rename 重新命名勾選檔案
	$('#act_adfile_rename').click(function(){
	  
	  // get reference
	  var dataroot = $('meta#DATAROOT').data('set');  // 資料分類
	  var dofolder = $('meta#DOFOLDER').data('set');  // 檔案資料夾
	  
	  // check file header 
	  if(!$('#file_name_prehead').val()){
		system_message_alert('','請輸入共通檔名標頭');
		$('#file_name_prehead').focus();
		return false;  
	  }
	  
	  // check file header 
	  if(!$('#file_name_startno').val()){
		system_message_alert('','請輸入起始號碼');
		$('#file_name_startno').focus();
		return false;  
	  }
	  
	  var filehead = $('#file_name_prehead').val();
	  var startnum = $('#file_name_startno').val();
	  
	  // get select upfile
	  var target_files = $('.act_selone_dfile:checked').map(function(){return $(this).val(); }).get();
	  if( ! target_files.length ){
	    system_message_alert('',"尚未選擇檔案");
		return false;
	  }
	   
	   
	  // confirm to admin
	  if(!confirm("確定要重新命名所勾選的"+target_files.length+" 個數位檔案\n若遇到重複檔名將會把偵測到的檔案重新命名?")){
	    return false;  
	  }
	  
	  // encode data
	  var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(target_files)));
	  
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Meta/dorename/'+dataroot+dofolder+'/'+filehead+'/'+startnum+'/'+passer_data},
		beforeSend: function(){  system_loading(); },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			
			var info = '勾選之 '+response.data.dobjs.count+ " 筆資料已重新命名";
			
			$('#act_selall_dfile').prop('checked',false).trigger('change');
			module_information(upload_master_object,'更名',info,'success');
		    create_dobj_list();
		  
		  }else{
			module_information(upload_master_object,'更名',response.info,'fail');  
			system_message_alert('',response.info);
	      }
	    },
		complete:	function(){  }
      }).done(function(r) {  
	    system_loading(); 
	    $('#file_name_startno,#file_name_prehead').val('');
	  });  	  
	  
	});
	
	
	//-- record reset order change 復原順序
	$('#act_adfile_ordreset').click(function(){
	  
	  // confirm to admin
	  if(!confirm("確定要復原檔案順序\n檔案順序將還原至先前儲存之狀態!!")){
	    return false;  
	  }
	   
	  var order_change_detect = false;
	  $('#do_list_container').find('tr.file').each(function(i){
		if(String(i)==$(this).data('order')){
		   return true;	
		}  
		order_change_detect = true;
        return false;		
	  });
	  
	  if(order_change_detect){
		create_dobj_list();   
	  }else{
		system_message_alert('alert','未偵測到順序變更');  
	  }
	  $('#act_selall_dfile').prop('checked',false).trigger('change');
	  
	});
	
	//-- record order change forware 往後
	$('#act_adfile_fordware').click(function(){
	  
	  if(!$('.act_selone_dfile:checked').length){
		system_message_alert('','尚未勾選資料');
        return false;		
	  }
	  
	  // get select dfile
	  $('.act_selone_dfile:checked').each(function(){
		var recrod = $(this).parents('tr.file');
		if(recrod.prev('.file').length && !recrod.prev('.file').hasClass('sortable')){
		  recrod.clone().addClass('reorder').insertBefore(recrod.prev('.file'));
		  recrod.remove();
		}
	  })
	});
	
	//-- record order change backware 往前
	$('#act_adfile_backware').click(function(){
      
	  if(!$('.act_selone_dfile:checked').length){
		system_message_alert('','尚未勾選資料');
        return false;		
	  }
	  
	  // get select dfile
	  $($('.act_selone_dfile:checked').get().reverse()).each(function(){
		var recrod = $(this).parents('tr.file');
		if(recrod.next('.file').length && !recrod.next('.file').hasClass('sortable')){
		  recrod.clone().addClass('reorder').insertAfter(recrod.next('.file'));
		  recrod.remove();
		}
	  })
	});
	
	//-- record order change to first 排序到最前
	$('#act_adfile_tofirst').click(function(){
	  
	  if(!$('.act_selone_dfile:checked').length){
		system_message_alert('','尚未勾選資料');
        return false;		
	  }
	  
	  // get select dfile
	  $($('.act_selone_dfile:checked').get().reverse()).each(function(){
		var recrod = $(this).parents('tr.file');
		recrod.clone().addClass('reorder').prependTo('#do_list_container');
		recrod.remove();
	  })
	});
	
	//-- record order change to last 排序到最後
	$('#act_adfile_tolast').click(function(){
	  if(!$('.act_selone_dfile:checked').length){
		system_message_alert('','尚未勾選資料');
        return false;		
	  }
	  // get select dfile
	  $('.act_selone_dfile:checked').each(function(){
		var recrod = $(this).parents('tr.file');
		recrod.clone().addClass('reorder').appendTo('#do_list_container');
		recrod.remove();
	  })
	});
	
	
	
	//-- record all to reorder 儲存重新排序
	$('#act_adfile_reorder').click(function(){
	  
	  // get reference
	  var dataroot = $('meta#DATAROOT').data('set');  // 資料分類
	  var dofolder = $('meta#DOFOLDER').data('set');  // 檔案資料夾
	  
	  
	  var order_change_detect = false;
	  $('#do_list_container').find('tr.file').each(function(i){
		if(String(i)==$(this).data('order')){
		   return true;	
		}  
		order_change_detect = true;
        return false;		
	  });
	  
	  if(!order_change_detect){
		system_message_alert('alert','未偵測到順序變更'); 
		return false;
	  }
	  
	  // get select upfile
	  var target_files = $('.file').map(function(){return $(this).data('file'); }).get();
	  
	  if( ! target_files.length ){
	    system_message_alert('',"目前沒有任何檔案");
		return false;
	  }
	   
	  // confirm to admin
	  if(!confirm("確定要依據目前設定重新排序檔案?")){
	    return false;  
	  }
	  
	   // encode data
	  var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(target_files)));
	  
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Meta/doreorder/'+dataroot+dofolder+'/'+passer_data},
		beforeSend: function(){  system_loading(); },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){	
			var info = '資料夾:'+dofolder+ " 檔案順序已重新排列";
			module_information(upload_master_object,'排序',info,'success');
		    create_dobj_list();
		  }else{
			module_information(upload_master_object,'排序',response.info,'fail');  
			system_message_alert('',response.info);
	      }
	    },
		complete:	function(){  }
      }).done(function(r) { system_loading();  });  	  
	  
	});
	
	
	//-- record select to delete 刪除勾選檔案
	$('#act_adfile_delete').click(function(){
	  
	  // get reference
	  var dataroot = $('meta#DATAROOT').data('set');  // 資料分類
	  var dofolder = $('meta#DOFOLDER').data('set');  // 檔案資料夾
	  
	  // check recapture 
	  if(!$('#adfile_captcha_input').val()){
		system_message_alert('','請輸入驗證碼');
		$('#adfile_captcha_input').focus();
		return false;  
	  }
	  
	  // get select upfile
	  var target_files = $('.act_selone_dfile:checked').map(function(){return $(this).val(); }).get();
	  if( ! target_files.length ){
	    system_message_alert('',"尚未選擇檔案");
		return false;
	  }
	   
	  // confirm to admin
	  if(!confirm("確定要刪除所勾選的\n"+target_files.length+" 個數位檔案 ?")){
	    return false;  
	  }
	  
	   // encode data
	  var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(target_files)));
	  
	  
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Meta/dodele/'+dataroot+dofolder+'/'+passer_data+'/'+$('#adfile_captcha_input').val()},
		beforeSend: function(){  system_loading(); },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			
			var info = '勾選之 '+response.data.dobjs.count+ " 筆資料已刪除";
			
			$('#act_selall_dfile').prop('checked',false).trigger('change');
			module_information(upload_master_object,'刪除',info,'success');
		    create_dobj_list();
		  
		  }else{
			module_information(upload_master_object,'刪除',response.info,'fail');  
			system_message_alert('',response.info);
	      }
	    },
		complete:	function(){  }
      }).done(function(r) {  
	    system_loading(); 
	    $('#adfile_captcha_input').val('');
		$('#captcha_refresh').trigger('click');
		
	  });  	  
	  
	});
	
	
	//-- record save to package 
	$(document).on('click','#act_adfile_package',function(){
	  
	  // get reference
	  var dataroot = $('meta#DATAROOT').data('set');  // 資料分類
	  var dofolder = $('meta#DOFOLDER').data('set');  // 檔案資料夾
	  var projectno= 0;
	  var projectname = '';
	  
	  // check recapture 
	  if(!$('#file_save_package').val()){
		system_message_alert('','請選擇要存放的專案資料夾');
		$('#file_save_package').focus();
		return false;  
	  }
	  
	  projectno = $('#file_save_package').val();
	  projectname = $('#file_save_package').find('option:selected').html();
	  
	  // get select upfile
	  var target_files = $('.act_selone_dfile:checked').map(function(){return $(this).val(); }).get();
	  if( ! target_files.length ){
	    system_message_alert('',"尚未選擇檔案");
		return false;
	  }
	   
	  // confirm to admin
	  if(!confirm("確定要轉存所勾選的\n"+target_files.length+" 個檔案至專案 ["+projectname+"] ?")){
	    return false;  
	  }
	  
	   // encode data
	  var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(target_files)));
	  
	  
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Meta/dopackage/'+dataroot+dofolder+'/'+passer_data+'/'+projectno},
		beforeSend: function(){  system_loading(); },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			var info = '勾選之 '+response.data.dobjs.count+ " 以加入專案["+projectname+"]";
			$('#act_selall_dfile').prop('checked',false).trigger('change');
			module_information(upload_master_object,'收錄',info,'success');
		    
		  }else{
			module_information(upload_master_object,'收錄',response.info,'fail');  
			system_message_alert('',response.info);
	      }
	    },
		complete:	function(){  }
      }).done(function(r) {  
	    system_loading(); 
	    $('#file_save_package').val('');
	  });  	  
	  
	});
	
	
	
	
	
	/*[ Dbj Import Function ]*/ // 數位檔案匯入動作
	
	//-- initial
	var upload_process_fleg  = false;  
    var upload_master_object = $('#adfile_module');
	var upload_dropzone_dom  = $("div#upload_dropzone");
	
	//-- record select update all 全選上傳檔案
	if($('#act_selall_ufile').length){
	  $('#act_selall_ufile').change(function(){
		  
		if($(this).prop('checked') && !$('.act_selone_ufile').length){
		  system_message_alert('','尚未有新增檔案!!');
		  $(this).prop('checked',false);
          return false;		  
		}
		
		$('.act_selone_ufile').prop('checked',$(this).prop('checked')); 
	  
	  });
	}
	
	//-- record select update one 單選檔案
	$(document).on('click','.act_selone_ufile',function(){
	  var select_all_fleg = $('.act_selone_ufile').length == $('.act_selone_ufile:checked').length ? true : false;
	  $('#act_selall_ufile').prop('checked',select_all_fleg);  	
	});
	
	
	//-- record select to delete
	$('#act_upl_delete').click(function(){
	  
      // get value
	  var dom_record = $(this);
	  
	  // get select upfile
	  var target_files = $('.act_selone_ufile:checked').map(function(){return $(this).val(); }).get();
	  if( ! target_files.length ){
	    system_message_alert('',"尚未選擇檔案");
		return false;
	  }
	   
	  // confirm to admin
	  if(!confirm("確定要刪除所勾選的\n"+target_files.length+" 個已上傳的檔案 ?")){
	    return false;  
	  }
	  
	   // encode data
	  var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(target_files)));
	  
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Meta/upldel/'+passer_data},
		beforeSend: function(){  system_loading(); },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			
			var info = '已刪除勾選之 '+response.data.uplact.count+ " 筆資料, 並從目前新增清單中移除";
			alert(info);
			$.each(response.data.uplact.list,function(i,urno){
			  if(!$("li.tmpupl[urno='"+urno+"']").length) return true;
			  $("li.tmpupl[urno='"+urno+"']").empty().remove();
			});
			
			$('#act_selall_ufile').prop('checked',false).trigger('change');
			module_information(upload_master_object,'刪除',info,'success');
		  }else{
			module_information(upload_master_object,'刪除',response.info,'fail');  
			system_message_alert('',response.info);
	      }
	    },
		complete:	function(){  }
      }).done(function(r) {  system_loading(); });  	  
	  
		
	});
	
	
	
	
	//-- record select to import
	$('#act_upl_import').click(function(){
	  
      // get value
	  var dom_record = $(this);
	  
	  // get select upfile
	  var target_files = $('.act_selone_ufile:checked').map(function(){return $(this).val(); }).get();
	  if( ! target_files.length ){
	    system_message_alert('',"尚未選擇檔案");
		return false;
	  }
	   
	  // confirm to admin
	  if(!confirm("確定要匯入所勾選的\n"+target_files.length+" 個已上傳的檔案 ?")){
	    return false;  
	  }
	  
	   // encode data
	  var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(target_files)));
	  
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Meta/uplimport/'+passer_data},
		beforeSend: function(){  system_loading(); },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			
			var info = '勾選之 '+response.data.uplact.count+ " 筆資料, 處理匯入中..";
			
			// 註冊動作
			system_event_regist('TASK',response.data.uplact.task);  
			
			$.each(response.data.uplact.list,function(i,urno){
			  if(!$("li.tmpupl[urno='"+urno+"']").length) return true;
			  $("li.tmpupl[urno='"+urno+"']").find('.act_selone_ufile').remove().end().find('.usel').html('<i class="fa fa-refresh" aria-hidden="true"></i>');
			  system_event_regist('DOBJ',response.data.uplact.task+':'+urno);    
			});
			
			
			$('#act_selall_ufile').prop('checked',false).trigger('change');
			module_information(upload_master_object,'匯入',info,'success');
		    
			
		  }else{
			module_information(upload_master_object,'匯入',response.info,'fail');  
			system_message_alert('',response.info);
	      }
	    },
		complete:	function(){  }
      }).done(function(r) {  system_loading(); });  	  
	  
		
	});
	
	
	
	
	/*[ Dbj Upload Function ]*/  // 數位檔案上傳動作
	
	
	
	
	var upload = {};  //上傳資料包裝盒
	 
	var $dropZone =  upload_dropzone_dom.dropzone({
		
	    autoProcessQueue:false,
	    createImageThumbnails:false,
	    parallelUploads:2,
	    maxFiles:100,
	    url: "index.php?act=meta/upldobj/", 
	    clickable: "#act_select_file",          //設定 file select dom
	    paramName: "file",
	  
	    init: function() {
		  
		  this.on("addedfile", function(file) {
			  
			// Capture the Dropzone instance as closure.
			var _this = this;  
			  
			//file.fullPath
		    $('#complete_time').html('…');
			$('#act_active_upload').prop('disabled',false);
			
			upload_dropzone_dom.attr('hasfile','1'); 
			
			/***-- 建立刪除按鈕 --***/
			// Create the remove button
			var removeButton = Dropzone.createElement("<i class='mark16 pic_photo_upload_delete option upl_delete' title='刪除'></i>");

			// Listen to the click event
			removeButton.addEventListener("click", function(e) {
			  // Make sure the button click doesn't submit the form:
			  e.preventDefault();
			  e.stopPropagation();

			  // Remove the file preview.
			  _this.removeFile(file);
			  // If you want to the delete the file on the server as well,
			  // you can do the AJAX request here.
			
			});

			// Add the button to the file preview element.
			file.previewElement.appendChild(removeButton);
		  
		  });  
		},
		maxfilesreached: function(file){
		  system_message_alert("","到達上傳資料上限 100，若要增加檔案請清空後再重新加入");
		  //this.removeFile(file);
		},
		maxfilesexceeded: function(file){
		  this.removeFile(file);	
		},
	    sending: function(file, xhr, formData) {
		  // Will send the filesize along with the file as POST data.
		  formData.append("lastmdf", file.lastModified);
		},
	    success: function(file, response){
		  result = JSON.parse(response);
		  if(result.action){
		    $(file.previewElement).addClass('dz-success');
		    $('#num_of_upload').html($('.dz-success').length);			
		  }else{
		    $(file.previewElement).addClass('dz-error');	
		    $(file.previewElement).find('.dz-error-message').children().html(result.info);
		  }
	    },
	    complete: function(file){
		  //-- maxfilesreached maxfilesexceeded 等超過檔案上限也會觸發
		  if( upload_process_fleg && this.getQueuedFiles().length){
		    this.processQueue();
		  }
		},
	    queuecomplete:function(){
		  
		  if(!upload_process_fleg){
			return false;  
		  }
		  
		  // finish folder upload state  
		  $.ajax({
		    url: 'index.php',
		    type:'POST',
		    dataType:'json',
		    data: {act:'Meta/uplend/'+upload_master_object.data('upload')},
		    beforeSend: 	function(){ },
		    error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		    success: 		function(response) {
			  if(response.action){
			    
				system_message_alert('alert',"資料上傳已經完成，檔案列在暫存清單");	
				
				$('.upload_list').empty();
				$.each(response.data.queue,function(i,f){
				  var li = $('<li/>').addClass('tmpupl').attr({'urno':f.urno,'check':f['@check']});
                  li.append("<span class='usel'  ><input type='checkbox' class='act_selone_ufile' value='"+(f.urno)+"' > "+(++i)+". </span>");
                  li.append("<span class='uname' >"+f.name+"</span>");
                  li.append("<span class='utime' title='"+f._upload+"'>"+f._upload.substr(0,10)+"</span>");	
				  li.append("<span class='uinfo' >"+(f['@check']=='duplicate' ? '重複' : '<i class="fa fa-check" aria-hidden="true"></i>' )+"</span>");	
				  li.appendTo($('.upload_list'));
				});
				
				//system_event_regist('import',response.data);
			  
			  }else{
				system_message_alert('error',response.info);	  
			  }
		    },
		    complete:	function(){ }
		  }).done(function() { }); 
		  
		  
		  // set upload end time
		  var now = new Date(Date.now());
		  var formatted = now.getFullYear()+"/"+(parseInt(now.getMonth())+1)+"/"+now.getDate()+' '+now.getHours()+':'+now.getMinutes()+':'+now.getSeconds();   
		  $('#complete_time').html(formatted); 
		  
		  // finish upload
		  clearInterval(timer); 	    // 關閉計時器
		  upload_button_freeze(0); 	    // 打開上傳按鈕
		  upload = {};   			    // 清空上傳暫存資料
		  upload_process_fleg = false 	// 關閉 fleg
		  $('#num_of_queue').html(this.getQueuedFiles().length); //重計數量
		  
		}
	});
	
	//-- 啟動上傳
	$('#act_active_upload').click(function(){
	  
	  var button = $(this);
	  
	  if($(this).prop('disabled')){
		system_message_alert('','資料上傳中...');  
		return false;  
	  }	
	  
	  // 檢查上傳資料夾
	  if(!upload_master_object.data('folder')){
		system_message_alert('','上傳資料夾不可為空');
		return false;		
	  }
	  
	  
	  // 檢查數位檔案類型設定
	  if(!confirm("確定要將新增影像類型設定為『"+$('#upload_do_type').val()+"』?")){
		return false;  
	  }
	  
	  // initial upload package
	  upload = {'folder':upload_master_object.data('folder'),'list':[]};
	  upload.dotype = $('#upload_do_type').val();
	  
	  
	  $.each($dropZone[0].dropzone.getQueuedFiles(),function(i,file){
		var f={};
		f['name'] = file.name;
		f['type'] = file.type;
		f['size'] = file.size;
		f['lastmdf'] = file.lastModified;
		upload['list'][i] = f;
	  });
	  
	  // 待上傳檔案不可為空
	  if(!upload['list'].length){
		system_message_alert('','待上傳檔案不可為空');
	    return false;	
	  }
	  
	  $('#num_of_queue').html(upload['list'].length);
	  
	  var passer_data  = encodeURIComponent(JSON.stringify(upload));
	  //先與server溝通上傳資料以及檢測資料檔案
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Meta/uplinit/'+passer_data},
		  beforeSend: 	function(){ upload_button_freeze(1); upload_process_fleg=true;},
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
			
			if(response.action){
              
			  // 設定上傳位置
              upload_master_object.data('upload',upload_master_object.data('root')+response.data.upload.folder+'/'+response.data.upload.tmflag);	
			  
			  // 檢查是否重複檔案
			  $.each(response.data.upload.check,function(i,pho){
				if(pho.check=='double'){
				  $(".dz-preview:nth-child("+(i+1)+")").find('.dz-size').append("<span class='upl_double' title='重複'> - 重複</span>");
				}
			  });
			  
			  if($('.upl_double').length){
				if(confirm("發現重複檔案,請問是否要調整上傳清單?")){
				  upload_button_freeze(0);
				  return 1;  
				}   
			  }
			  
			  // active upload
			  upload['list'] 	  = [];
			  $dropZone[0].dropzone.options.url="index.php?act=Meta/upldobj/"+upload_master_object.data('upload')+'/'+encodeURIComponent(Base64M.encode(JSON.stringify(upload)));
			  $dropZone[0].dropzone.processQueue();
			  upload_timer();
			
			}else{
			  system_message_alert('error',response.info);
			}
	      },
		  complete:	function(){ }
	  }).done(function() { }); 
	  
	});
	
	
	//-- 清空上傳清單
	$('#act_clean_upload').click(function(){
	  $dropZone[0].dropzone.removeAllFiles( true );
	  $("#num_of_queue").html('…');
	  $("#upload_dropzone").attr('hasfile',0).empty();
	  $('#act_active_upload').prop('disabled',true);
	  $("#upload_do_type").val('');
	  
	});
	
	/*
	  --//等待放到上傳處理結束區
	  $("#num_of_upload")
	  $("#execute_timer").html('…');
	  $("#complete_time").html('…');
	*/
	
	
	//-- 上傳按鈕凍結
	function upload_button_freeze(option){
      if(option){
		$('#act_active_upload,#act_select_file').prop('disabled',true);  
	  }else{
		$('#act_active_upload,#act_select_file').prop('disabled',false);    
	  }
	}
	
	//-- 清空已上傳檔案
	$('#act_select_file' ).click(function(){
	  var dzObject = $dropZone[0].dropzone;
	  $.each(dzObject.getAcceptedFiles(),function(i,file){
		if(file.status == 'success'){
		  dzObject.removeFile(file);	  
		}
	  });
	});
	
	//-- 上傳計時器
	var timer;
	var totalSeconds = 0;
	function upload_timer(){
	  totalSeconds = 0;
      timer = setInterval(setTime, 1000);
      function setTime(){
        ++totalSeconds;
        $('#execute_timer').html(pad(parseInt(totalSeconds/60))+':'+pad(totalSeconds%60));
      }
      function pad(val){
        var valString = val + "";
        if(valString.length < 2){
          return "0" + valString;
        }else{
          return valString;
        }
      }
	}
  
  });

  //-- built dobj list  建構數位物件列表	
  function create_dobj_list(){
      
      var dataroot = $('meta#DATAROOT').data('set');  // 資料分類
	  var dofolder = $('meta#DOFOLDER').data('set');  // 檔案資料夾
	  
      $.ajax({
		  url: 'index.php',
		  type:'POST',
		  dataType:'json',
		  data: {act:'Meta/doconf/'+dataroot+dofolder},
		  beforeSend: 	function(){ system_loading();  },
		  error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		  success: 		function(response) {
			if(response.action){  
			  
			  $('#dobj_select_dom,#do_list_container,#dobj_thumb_block').empty(); 
			  $.each(response.data.dobjs,function(i,dobj){
				
                var selecter = $('<option/>').addClass('pager').attr({'id':dobj.file,'value':dobj.file,'data-serial':i,'display':1});
				selecter.html("P."+(i+1)+" / "+dobj.file);
				selecter.appendTo($('#dobj_select_dom'));
                
                var thumb = $('<div/>').addClass('thumb').attr('p',dobj.file);
				thumb.append("<img data-src='thumb.php?src="+dataroot+"thumb/"+dofolder+"/"+dobj.file+"' />");
				thumb.append("<i>P."+(i+1)+"</i>");
				thumb.appendTo($('#dobj_thumb_block'));
				
				
				var record = $('<tr/>').addClass('file').attr({'data-file':dobj.file,'data-order':i});
				record.append(" <td class='fsel' ><input type='checkbox' class='act_selone_dfile' name='fselect' value='"+dobj.file+"'></td>");
				record.append(" <td class='fnum' >"+(i+1)+"</td>");
				record.append(" <td class='fname' >"+dobj.file+"</td>");
				record.append(" <td class='finfo' >"+dobj.width+'x'+dobj.height+"</td>");
				record.append(" <td class='fedit' ><span class='option inlinefunc' edit='-1' ><i class='fa fa-pencil' aria-hidden='true' title='修改檔名'></i><i class='fa fa-save' aria-hidden='true'></i><i class='fa fa-external-link act_adfile_downloaddo' aria-hidden='true' title='下載檔案'></i></span></td>");
				record.appendTo($('#do_list_container'));		 
				
			  })
			  
			  $('#dobj_thumb_block').lazyLoadXT();
			  $('.thumb').lazyLoadXT({visibleOnly: false, checkDuplicates: false});
			  
			}else{
			  system_message_alert('',response.info);
			}
		  },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });  
	      
  }
  