(function($){
	var map_img = new Image;
	map_img.src = $('img#map_canvas')[0].src;
	if(map_img.width > 25){
		  adjustMap();
	}else{
		$(map_img).load(function() {
		  adjustMap();
		});
	}
	var hm = $('#map_canvas');
	$(window).resize(adjustMap);
	function adjustMap(){
		var actualWidth = 1900;
		var actualHeight = 1007;
		var mapWidth = $('.map').width();
		var mapHeight = $('.map').height();
		var tmpW = (actualWidth - mapWidth) / actualWidth;
		var tmpH = (actualHeight - mapHeight) / actualHeight;
		var xCoordsStr = jQuery('input[name=xcoordsStr]').val(); 
		var ycoordsStr = jQuery('input[name=ycoordsStr]').val();
		var dataIdStr = jQuery('input[name=dataId]').val();
		xCoords = xCoordsStr.split("|");
		yCoords = ycoordsStr.split("|");
		if(xCoords){
			dataId = dataIdStr.split("|");
			for(index in xCoords){
				xCoords[index] = xCoords[index] - xCoords[index] * tmpW;
				yCoords[index] = yCoords[index] - yCoords[index] * tmpH;
				var boxCoordsX = xCoords[index]-191;
				var boxCoordsY = yCoords[index]-334;

				if(boxCoordsY < 0){
					boxCoordsY= yCoords[index];
				}
				if(boxCoordsX < 0){
					boxCoordsX = xCoords[index];
				}
				var rightSide = parseInt(boxCoordsX)+363;
				if(rightSide > mapWidth){
					var temp = parseInt(rightSide - mapWidth)+5;
					boxCoordsX = boxCoordsX-temp;
				}

				var pin = "<span rel = "+dataId[index]+" id = 'pin_"+dataId[index]+"' class = 'savedPin'></span>";
				if($('span#pin_'+dataId[index]).length > 0)
					$('span#pin_'+dataId[index]).remove();
				$('div.map').append(pin);
				$('span#pin_'+dataId[index]).css({ top: yCoords[index]-19 + 'px', left: xCoords[index]-6 + 'px' }).show('fast');
				
				$('#pop_'+dataId[index]).css({ top: boxCoordsY + 'px', left: boxCoordsX + 'px' });
			}
		}
		$('span.savedPin').live('click',showDetail);
	}

	function showDetail(e){
		removeIframe();
		var mapWidth = $('.map').width();
		var pinId = $(this).attr('rel');
		var hmOffset = hm.offset();
		x = e.pageX-hmOffset.left-191;
		y = e.pageY-hmOffset.top-334;
		if(y < 0){
			y= e.pageY-hmOffset.top;
			flag = true;
		}
		if(x < 0){
			x = e.pageX-hmOffset.left;
			flag = true;
		}
		var rightSide = parseInt(x)+363;
		if(rightSide > mapWidth){
			var temp = parseInt(rightSide - mapWidth)+5;
			x = x-temp;
		}
		loaderX = e.pageX-hmOffset.left;
		loaderY =  e.pageY-hmOffset.top;
		if($('#pop_'+pinId).css('display') == 'none'){
			 $('#pop_'+pinId).css({ top: y + 'px', left: x + 'px' }).show('fast');
		}else{
			$('span.loader').css({ top: loaderY + 'px', left: loaderX + 'px' }).show('fast');
			
			var data = {
				'action' : 'plom_show_loc',
				'pinId'   :  pinId
			};
			var url = jQuery('input[name=ajaxUrl]').val();
			// Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.ajax({
				url : url,
				type: 'POST',
				data: data,
				success: function(response) {
					 jQuery('div.map').append(response);
					 jQuery('span.loader').hide();
					 jQuery('#pop_'+pinId).css({ top: y + 'px', left: x + 'px' }).show('fast');
					 jQuery('.popup_close').unbind('click').bind('click', removeIframe);
				}
			});
			$('span.loader').hide();
		}
	}
})(jQuery);

function removeIframe(){
	jQuery('div.map').find('div.popup_main').hide();
}

function removePin(){
	jQuery('div.map').find('div.popup_main').remove();
	jQuery('span.pin').hide();
}
