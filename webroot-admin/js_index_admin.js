/*    
  
  javascrip use jquery 
  rcdh 10 javascript pattenr rules v1
  
*/
 
  /***************************************************   
	IISPhoArchive # Index Page      
  ***************************************************/
  	
  $(window).on('load',function () {   //  || $(document).ready(function() {
    
	//-- 檢索
	var query_data = [];
	$('#act_search').click(function(){
	  
	  var field  = $('#search_field').val() ? $('#search_field').val() : 'kw';
	  var search = $('#search_string').val();
	  
	  if($(this).prop('disabled')){
	    alert("系統正在查詢中，請稍候..");
		return false;  
	  }
	  
	  if(!search.length){
		system_message_alert('error',"請輸入檢索條件");
        $('#search_string').focus();		
	    return false;
	  }
	  $(this).prop('disabled',true);
      
	  var accnum = $('#search_mode').val();
	  query_data.push(field+':'+search);
	  
	  // encode data
	  var passer_data  = encodeURIComponent(JSON.stringify(query_data));
	  location.href = "index.php?act=Archive/search/"+passer_data+'/'+accnum;
	});
	
	
	
	//-- chart
	
	var chart = JSON.parse($('#chart_data').text());
	 
	$('#chart_container').highcharts({
        chart: {
            type: 'area',
			spacingRight: 30,
			spacingLeft: 0,
			spacingBottom: 0
        },
        title: {
            text: null
        },
		legend:{
		    verticalAlign: "top"	
		},
        xAxis: {
			gridLineColor: "#dcdddd",
			endOnTick: true,
            categories: chart.x_category,
            tickmarkPlacement: 'on',
            title: {
                enabled: false
            },
			tickInterval: 3,
        },
        yAxis: {
			min:0,
			max:chart.y_max_value,
            title: {
                text: 'Space Usage'
            },
			tickAmount: 5,
            labels: {
                formatter: function () {
                    return (this.value / 1000) + 'TB';
                }
            },
			endOnTick: false,
			plotLines: [{
                color: 'red',
                width: 1,
                value: chart.y_max_value,
				zIndex:10,
                label: {
                    text: '空間上限',
                    align: 'right',
                    x: -10
                }
            },{
                color: '#2a83a2',
				dashStyle: 'dot',
                width: 1,
                value: chart.y_now_value,
                label: {
                    text: '目前用量',
                    align: 'right',
                    x: -10
                }
            }]
        },
        tooltip: {
            shared: true,
            valueSuffix: ' GB'
        },
        plotOptions: {
            area: {
                stacking: 'normal',
                lineColor: '#666666',
                lineWidth: 1,
                marker: {
                    lineWidth: 1,
                    lineColor: '#666666'
                },
				fillOpacity: 0.5
            },
			series: {
                marker: {
                    enabled: false
                }
            }
        },
        series: chart.x_data
		
    });
    
	
	//-- 檔案類型比例 
	var colors = Highcharts.getOptions().colors
    $('#photo_rate').highcharts({
        chart: {
            backgroundColor:"rgba(255,255,255,0)",
			plotBackgroundColor: null,
            plotBorderWidth: 0,
            plotShadow: false,
			spacingRight: 0,
			spacingLeft: 0,
			spacingBottom: 10
        },
        title: { text: null },
        tooltip: {
			
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                dataLabels: {
                    enabled: true,
                    distance: 10,
					formatter: function () {
					  return  this.key + ':' + this.y + '%' ;
                    }
                },
                startAngle: -90,
                endAngle: 90,
				center: ['50%', '100%']
            }
        },
        series: [{
			name: '空間比例',
            type: 'pie',
            innerSize: '30%',
            data: chart.pie_data
        }]
    });
	
	$("text:contains('Highcharts.com')").hide();
  
    /*===============================*/
	/*-- Announcement Function set --*/
	/*===============================*/
	
	$('#act_switch_post_mode').click(function(){
	  var more_flag = parseInt($('.billboard').attr('more'));	  
      $('.billboard').attr('more',parseInt(1 - more_flag));
	});
	
	
	$('.post').click(function(){
	  var dom = $(this);
	  var data_no = dom.attr('no');
	  
	  if(!parseInt(data_no)){
		system_message_alert('',"尚未選擇資料");  
	    return false;  
	  }
	  
	  $.ajax({
		  url: 'index.php',
		  type:'POST',
		  dataType:'json',
		  data: {act:'Main/getann/'+data_no},
		  beforeSend: 	function(){ system_loading();  },
		  error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		  success: 		function(response) {
			if(response.action){  
			  $(this).addClass('viewed');	
	          
			  var post = response.data;
			  
			  $('.ann_type').text(post.post_type);
			  $('.ann_title').text(post.post_title);
			  $('.ann_contents').html(Base64.decode(post.post_content));
			  $('.ann_time').text(post.post_time_start);
			  $('.ann_from').text(post.post_from);
			  $('.ann_counter').text(post.post_hits);
			  
			  $('.system_announcement_area').css('display','block');	
			}else{
			  system_message_alert('',response.info);
			}
		  },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });
	  
	});
	
	$('.ann_close').click(function(){
	  $('.viewed').removeClass('viewed');
	  $('.system_announcement_area').css('display','none');	
	});
	  
	//-- post emergency popout
	if( $(".post[popout='1']").length ){
	  $(".post[popout='1']:eq(0)").trigger('click');	
	}
  
  
  });