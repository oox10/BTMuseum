/* [ Admin Field Function Set ] */
	
  
  $(window).on('load',function () {   //  || $(document).ready(function() {		
	
	
	// group leave function
	$(document).on('click','.act_save_field_format',function(){
	  
	  var main_dom = $(this).parents('tr');
	  var mfno = main_dom.attr('no');
	  var format = {};
	  
	  main_dom.find('._format').each(function(){
		if($(this).attr('name')=='pattern'){
		  format[$(this).attr('name')] = $(this).val();	
		}else{
		  format[$(this).attr('name')] = $(this).prop('checked') ? 1 : 0;	
		}
	  })
	  
	  // encode data
	  var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(format)));
	  
	   
      $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Field/save/'+mfno+'/'+passer_data},
		beforeSend: function(){  system_loading(); },
		error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		success: 	function(response) {
		  if(response.action){
			system_message_alert('alert',"欄位設定已更新");
		  }else{
			system_message_alert('',response.info);
		  }
		},
		complete:	function(){  }
	  }).done(function(r) {   system_loading();   });
	  
	  // remove 
		
	}); 
	
	
  });	
  
  
  