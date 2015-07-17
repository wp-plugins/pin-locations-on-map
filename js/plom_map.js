(function($){
	var hm = $('#map_canvas');
	$('#map_canvas').live('click',openForm);
	insertedPoints();
	function openForm(e){
		removeIframe();
		var hmOffset = hm.offset();
		x = e.pageX-hmOffset.left;
		y = e.pageY-hmOffset.top;
		var iframeLocation = x;
		var popBoxWidth = 440;
		var tempX = parseInt(x)+440;
		if(tempX > 1900){
			iframeLocation = parseInt(x) - 440;
		}
		var data = {
				'action' : 'plom_add_pin'
			};
		// Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.ajax({
			url : ajaxurl,
			type: 'POST',
			data: data,
			beforeSend: function(){
				$('span.loader').css({ top: y-16 + 'px', left: x-11 + 'px' }).show('fast');
			},
			success: function(response) {
				$('span.loader').hide();
				$('div.map_container').append(response);
				$('.plom_form').css({ top: y-16 + 'px', left: iframeLocation + 'px' }).show('fast');
				
				$('span.pin').css({ top: y-16 + 'px', left: x-6 + 'px' }).show('fast');
				$('.plom_form').find('input[name = xCoords]').val(x);
				$('.plom_form').find('input[name = yCoords]').val(y);
				$('div.map_container').find('form').submit(submit_form);
				$('div.map_container').find('#closeNwgbp').unbind('click').bind('click', closeForm);
			}
		});
	}

	function insertedPoints(){
		var xCoords = [];
		var yCoords = [];
		var dataId = [];
		var xCoordsStr = $('input[name=xcoordsStr]').val();
		var ycoordsStr =  $('input[name=ycoordsStr]').val();
		var dataIdStr = $('input[name=dataId]').val();
		
		if(xCoordsStr && ycoordsStr && dataIdStr){
			xCoords = xCoordsStr.split("|");
			yCoords = ycoordsStr.split("|");
			dataId = dataIdStr.split("|");
			for(index in xCoords){
				var pin = "<span rel = "+dataId[index]+" id = 'pin_"+dataId[index]+"' class = 'savedPin'></span>";
				$('div.map_container').append(pin);
				$('span#pin_'+dataId[index]).css({ top: yCoords[index]-16 + 'px', left: xCoords[index]-6 + 'px' }).show('fast');
			}
		}
	}

	$('span.savedPin').live('click',editDetail);
	function editDetail(e){
		var pinId = $(this).attr('rel');
		
		removeIframe();
		var hmOffset = hm.offset();
		x = e.pageX-hmOffset.left;
		y = e.pageY-hmOffset.top;
		
		var iframeLocation = x;
		var popBoxWidth = 440;
		var tempX = parseInt(x)+440;
		if(tempX > 1900){
			iframeLocation = parseInt(x) - 440;
		}
		
		var data = {
				'action' : 'plom_edit_pin',
				'pinId'   :  pinId
			};
		// Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.ajax({
			url : ajaxurl,
			type: 'POST',
			data: data,
			beforeSend: function(){
				$('span.loader').css({ top: y-16 + 'px', left: x-11 + 'px' }).show('fast');
			},
			success: function(response) {
				$('span.loader').hide();
				$('div.map_container').append(response);
				$('.plom_form').css({ top: y + 'px', left: iframeLocation + 'px' }).show('fast');
				$('div.map_container').find('form').submit(submit_form);
				$('div.map_container').find('#closeNwgbp').unbind('click').bind('click', closeForm);
				$('div.map_container').find('#deletePin').unbind('click').bind('click', deleteLocation);
			}
		});
	}

})(jQuery);


jQuery('.upload_button').live('click', function() {
	tb_show('', 'media-upload.php?TB_iframe=true');
});

window.send_to_editor = function(html) {
	var htmlDv = '<div class = "hdAttData" style = "display:none;">'+html+'</div>';
	jQuery('body').append(htmlDv);
	var mediaUrl = jQuery('body').find('div.hdAttData').last().find('a').attr('href');
	var data = {
		'attachementUrl': mediaUrl,
		'action'        : 'plom_media_upload'
	};
	jQuery.post(ajaxurl, data, function(response) {
		if(response){
			var data = jQuery.parseJSON(response);
			if(!data.message){
				if(data.mime_type == 'image/jpeg' || data.mime_type == 'image/pjpeg' || data.mime_type == 'image/png') {
					var imgURL = data.attachement_url;
					var img = jQuery('<img src="'+imgURL+'"/>').load(function(){
							appendLocAttachment(response);
					});
				}else {
					alert('You can only add an image for location.');
				}
			}else{
				alert('Something wrong occured, Please try again.');
			}
		}
	});	
	tb_remove();	
}

function appendLocAttachment(response){
	var data = jQuery.parseJSON(response);
	jQuery('.loc_image').html("<div class='add'><a href='javascript:void(0);'><img src="+data.attachement_url+" width=100></a><a href=javascript:void(0) class=delete-banner-item onclick=\'deleteLocImg(this);'\></a><input type=hidden name=loc_image value="+data.attachment_id+ "></div>");
	tb_remove();
}

/*
* Function used to delete uploaded image
*/

function deleteLocImg(obj){
	jQuery(obj).parent().remove();
}


function removeIframe(){
	jQuery('div.map_container').find('.plom_form').remove();
}

function removePin(id){
	if(id != null){
		jQuery('div.map_container').find('.plom_form').hide();
		jQuery('span#pin_'+id).remove();
	}else{
		jQuery('div.map_container').find('.plom_form').remove();
		jQuery('span.pin').hide();
	}
}

function attachPin(id,x,y){
		var pin = "<span rel = "+id+" class = 'savedPin' id = 'pin_"+id+"'></span>";
		jQuery('div.map_container').append(pin);
		jQuery('span#pin_'+id).css({ top: y-16 + 'px', left: x-6 + 'px' }).show('fast');
}

function submit_form(){
	var form_data = jQuery("form#map_form").serialize();
	var xCoords = jQuery("form#map_form").find('input[name=xCoords]').val();
	var yCoords = jQuery("form#map_form").find('input[name=yCoords]').val();
	
	var data = {
				'action' : 'plom_save_pin',
				'form_data'   :  form_data
			};
	// Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.ajax({
			url : ajaxurl,
			type: 'POST',
			data: data,
			beforeSend: function(){
				jQuery("form#map_form").find('input[type=submit]').attr('disabled', true);
			},
			success: function(response) {
				if(response == 'updated'){
					alert('Location Updated Successfully.');
					jQuery('div.map_container').find('.plom_form').remove();
				}else if(response == 'error'){
					alert('Please fill the required fields.');
					jQuery("form#map_form").find('input[type=submit]').attr('disabled', false);
				}else{
					alert('Location Saved Successfully.');
					jQuery('div.map_container').find('.plom_form').remove();
					jQuery('span.pin').hide();
					attachPin(response, xCoords, yCoords);
				}
			}
		});
	return false;
}




function closeForm(){
	jQuery('div.map_container').find('.plom_form').remove();
	jQuery('span.pin').hide();
};

function deleteLocation(){
	if (confirm("Are you sure you want to delete this?")) {
		
		var pinId = jQuery(this).attr('rel');
		var data = {
				'action' : 'plom_del_pin',
				'pinId'   :  pinId
			};
		
		// Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.ajax({
			url : ajaxurl,
			type: 'POST',
			data: data,
			beforeSend: function(){
				jQuery("form#map_form").find('input[type=submit]').attr('disabled', true);
			},
			success: function(response) {
				if(response == 'deleted'){
					alert('Location deleted Successfully.');
					jQuery('div.map_container').find('.plom_form').remove();
					removePin(pinId);
				}else{
					alert('Something went wrong. Please try again later.');
				}
			}
		});
	}
};



