<?php
/*
 Plugin Name: Pin Locations on Map
 Description: This plugin is used to Pin locations with full detail on Map and display that Map on your website with shortcode [PLOM_DISPLAYMAP] at any place.
 Author: Arsh Sharma
 Version: 1.0
 Author URI: http://fitnessnit.com/
 Plugin URI: http://fitnessnit.com/
*/

function plom_files(){
		
	wp_enqueue_script('jquery');
	wp_enqueue_script('media-upload');
    wp_enqueue_script('thickbox');
    wp_enqueue_style('thickbox');
    
	$css_path = plugins_url('css/style.css', __FILE__);

	/* Register our Files. */
	wp_register_style( 'plomMapStylesheet', $css_path );
	wp_enqueue_style( 'plomMapStylesheet');		
	
	$js_path = plugins_url('js/plom_map.js', __FILE__);
	wp_register_script( 'plomMapScript', $js_path );
	wp_enqueue_script( 'plomMapScript');
}

function plom_files_front(){
	
	wp_enqueue_script('jquery');

	$css_path = plugins_url('css/style.css', __FILE__);
	$js_path = plugins_url('js/plom_front.js', __FILE__);
	
	/* Register our Files. */
	wp_register_style( 'plomMapStylesheet', $css_path );
	wp_enqueue_style( 'plomMapStylesheet');
	wp_register_script( 'plomMapScript', $js_path );
	wp_enqueue_script( 'plomMapScript');
}


// =========
// = HOOKS =
// =========

//add_action( 'init', 'plom_files_front' );
add_action( 'admin_menu','plomMap_init' );
add_action('admin_head', 'plom_files' );

function plomMap_init() {
	add_menu_page( 'Pin Locations on Map', 'Pin Locations on Map', 'publish_posts', 'plom_map', 'plomMap_menu' );
}

// function is used to show map and its functionality on backend
function plomMap_menu(){	
	global $wpdb;
	
	$img_path =   plugins_url('images/map.jpg', __FILE__);
	
	$instruction  = "<div class='updated below-h2' id='message' style = 'float:left;width:94%;'><p>Instructions : To save your address for any location just click on the appropriate map location, Fill the form and save it. </p><p> For showing the updated Map on website use shortcode : <strong>[PLOM_DISPLAYMAP]</strong>.</p> <p>For any more help contact : <a href = 'http://www.fitnessnit.com/contact-us/' target = '_BLANK'>Arsh Sharma</a></p><span class = 'donate_cont' style = 'float:right;'><form action='https://www.paypal.com/cgi-bin/webscr' method='post' target='_BLANK'>
<input type='hidden' name='cmd' value='_s-xclick'>
<input type='hidden' name='hosted_button_id' value='76HRTN4CYBJV8'>
<input type='image' src='https://www.paypalobjects.com/en_GB/i/btn/btn_donateCC_LG.gif' border='0' name='submit' alt='PayPal – The safer, easier way to pay online.'>
<img alt='' border='0' src='https://www.paypalobjects.com/en_GB/i/scr/pixel.gif' width='1' height='1'>
</form></span></div>";
	echo $instruction;

	$xCoords = $wpdb->get_results( "SELECT id,xCoords,ycoords FROM wp_plom_map" );
	foreach($xCoords as $key=>$value){
		$xcoordsStr .= $value->xCoords.'|';
		$ycoordsStr .= $value->ycoords.'|';
		$dataId .= $value->id.'|';
	}
	$xcoordsStr = trim($xcoordsStr,'|');
	$ycoordsStr = trim($ycoordsStr,'|');
	$dataId = trim($dataId,'|');
	
	echo "<div class  = 'map_container'>
			<img src = ".$img_path." id = 'map_canvas'>
			<span class = 'pin'></span>
			<span class = 'loader'></span>
			<input type = 'hidden' name = 'xcoordsStr' value ='".$xcoordsStr."' >
			<input type = 'hidden' name = 'ycoordsStr' value ='".$ycoordsStr."' >
			<input type = 'hidden' name = 'dataId' value ='".$dataId."' >
		</div>";
	
}

/// Function is trigger when activating the PLugin
function plom_activate(){
	global $wpdb;
	$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}plom_map (
			  id int(11) NOT NULL AUTO_INCREMENT,
			  img_path varchar(255) NOT NULL,
			  address1 varchar(100) NOT NULL,
			  addresss2 varchar(100) NOT NULL,
			  description text NOT NULL,
			  xCoords varchar(100) NOT NULL,
			  ycoords varchar(100) NOT NULL,
			  PRIMARY KEY (id)
			)";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}

// Function is used to show Map on front end
function plom_display_map(){
	plom_files_front();
	global $wpdb;
	$data = $wpdb->get_results( "SELECT * FROM wp_plom_map" );
	$img_path =   plugins_url('images/map.jpg', __FILE__);
	
	$xCoords = $wpdb->get_results( "SELECT id,xCoords,ycoords FROM wp_plom_map" );
	foreach($xCoords as $key=>$value){
		$xcoordsStr .= $value->xCoords.'|';
		$ycoordsStr .= $value->ycoords.'|';
		$dataId .= $value->id.'|';
	}
	$xcoordsStr = trim($xcoordsStr,'|');
	$ycoordsStr = trim($ycoordsStr,'|');
	$dataId = trim($dataId,'|');
	
	echo "<div class  = 'map' style = 'position:relative;'>
			<img src = ".$img_path." id = 'map_canvas'  style='width: 100%; max-height: 1007px;'>
			<span class = 'loader'></span>
			<input type = 'hidden' name = 'xcoordsStr' value ='".$xcoordsStr."' >
			<input type = 'hidden' name = 'ycoordsStr' value ='".$ycoordsStr."' >
			<input type = 'hidden' name = 'dataId' value ='".$dataId."' >
			<input type = 'hidden' name = 'ajaxUrl' value ='".admin_url('admin-ajax.php')."' >
		</div>";
}

// used to show the form in popup
function plom_add_pin(){
?>
<div class = 'nwgbp_form plom_form'>
	<form action = '' enctype='multipart/form-data' method='post' id = 'map_form'>
		<div class = 'formField'>
			<span>Address 1 *:</span>
			<textarea name = 'address1'></textarea>
		</div>
		<div class = 'formField'>
			<span>Address 2 *:</span>
			<textarea name = 'address2'></textarea>
		</div>
				
		<div class = 'formField'>
			<span>Upload Image : </span>
			<input class="upload_button" type="button" value="Upload Media" rel = 'advertisement' title="Upload Media" />
			<div class = 'loc_image'></div>
		</div>
		
		<div class = 'formField'>
			<input type = 'submit' name = 'save' value = 'save' id = 'save_form'>
			<span class = 'loader'></span>
		</div>
		<input type = 'hidden' name = 'xCoords' value = ''>
		<input type = 'hidden' name = 'yCoords' value = ''>
	</form>
	<a href = 'javascript:void(0)' id = 'closeNwgbp' class = 'closelink'></a>
	<span class = 'arrow'></span>
</div>
<?php
exit;	
}


function plom_edit_pin(){
	global $wpdb;
	if(isset($_REQUEST['pinId']) && !empty($_REQUEST['pinId'])){
		$where = 'where id = '.intval($_REQUEST['pinId']);
		$dataRes = $wpdb->get_results( "SELECT * FROM wp_plom_map ".$where ); ?>
		
		<div class = 'nwgbp_form plom_form'>
			<form action = '' enctype='multipart/form-data' method='post' id = 'map_form'>
				<div class = 'formField'>
					<span>Address 1 *:</span>
					<?php 
						if(isset($dataRes) && !empty($dataRes)){
							echo "<textarea name = 'address1'>".$dataRes[0]->address1."</textarea>";
						} ?>
				</div>
				<div class = 'formField'>
					<span>Address 2 *:</span>
					<?php 
						if(isset($dataRes) && !empty($dataRes)){
							echo "<textarea name = 'address2'>".$dataRes[0]->addresss2."</textarea>";
						}
					?>
				</div>
						
				<div class = 'formField'>
					<span>Upload Image : </span>
					<input class="upload_button" type="button" value="Upload Media" rel = 'advertisement' title="Upload Media" />
					
					<?php if(isset($dataRes[0]->img_path) && !empty($dataRes[0]->img_path)){ 
							$imageUrl = wp_get_attachment_url($dataRes[0]->img_path);
							echo "<div class = 'loc_image'>
									<div class='add'><img src=".$imageUrl." width=100><a href=javascript:void(0) class=delete-banner-item onclick='javascript:deleteLocImg(this);'></a></div>
									<input type = 'hidden' name = 'hd_imgpath' value = '".$imageUrl."'>
								</div>"; 
						}else{
							echo "<div class = 'loc_image'></div>";
						}
					?>
				</div>
				<div class = 'formField'>
					<input type = 'submit' name = 'save' value = 'save' id = 'save_form'>
					<?php if(isset($dataRes) && !empty($dataRes[0]->id)){ ?>
						<a href = 'javascript:void(0)' id = 'deletePin' class = 'deletePin' rel = "<?php echo $dataRes[0]->id; ?>">Delete</a>
					<?php } ?>
					<span class = 'loader'></span>
				</div>
				<input type = 'hidden' name = 'xCoords' value = '<?php if(isset($dataRes) && !empty($dataRes)){ echo $dataRes[0]->xCoords; }?>'>
				<input type = 'hidden' name = 'yCoords' value = '<?php if(isset($dataRes) && !empty($dataRes)){ echo $dataRes[0]->ycoords; }?>'>
				<?php if(isset($dataRes) && !empty($dataRes)){ ?>
					<input type = 'hidden' name = 'recId' value = '<?php echo $dataRes[0]->id; ?>'>
				<?php } ?>
			</form>
			<a href = 'javascript:void(0)' id = 'closeNwgbp' class = 'closelink'></a>
			<span class = 'arrow'></span>
		</div>
	<?php
	exit;
	}
}


function plom_save_pin(){
	global $wpdb;
	$from_data = array();
	parse_str($_REQUEST['form_data'], $from_data);

	if(!empty($from_data)){
		if((trim($from_data['address1']) != '') && (trim($from_data['address2']) != '')){
			
			$table = 'wp_plom_map';
			if(isset($from_data['loc_image']) && !empty($from_data['loc_image'])){
				$data = array('address1'=>trim($from_data['address1']), 'addresss2'=>trim($from_data['address2']), 'xCoords'=>trim($from_data['xCoords']),'ycoords'=>trim($from_data['yCoords']),'img_path'=>$from_data['loc_image']);				
			}else{
				$data = array('address1'=>trim($from_data['address1']), 'addresss2'=>trim($from_data['address2']), 'xCoords'=>trim($from_data['xCoords']),'ycoords'=>trim($from_data['yCoords']), 'img_path'=> '');	
			}
			if(isset($from_data['recId']) && !empty($from_data['recId'])){
				$where = array('id'=>$from_data['recId']);
				$wpdb->update($table,$data,$where);
				echo 'updated';
			}else{
				$wpdb->insert($table,$data);
				echo $last_id = $wpdb->insert_id;
			}
		}else {
			echo 'error';
		}
	}
	exit;
}

function plom_media_upload(){
	$response = array();
	if(!filter_var($_REQUEST['attachementUrl'], FILTER_VALIDATE_URL) === false){
		$attachment_Id = cam_get_image_id_from_url($_REQUEST['attachementUrl']);
		$file_attachment = get_post($attachment_Id);
		
		if (!empty($file_attachment)) {
			if($file_attachment->post_mime_type == 'image/jpeg' || $file_attachment->post_mime_type == 'image/pjpeg' || $file_attachment->post_mime_type == 'image/png' || $file_attachment->post_mime_type == 'image/gif'){
				$attachement_url = wp_get_attachment_url($attachment_Id);
			}
		}
	
		$response['attachment_id'] = $attachment_Id;
		$response['attachement_url'] = $attachement_url;
		$response['mime_type'] = $file_attachment->post_mime_type;
		$response['link'] = $file_attachment->post_excerpt;
		echo json_encode($response);
	}else{
		$response['message'] = "Something wrong occured, Please try again.";
		echo json_encode($response);
	}
	exit;
}


function plom_del_pin(){
	global $wpdb;
	if(isset($_REQUEST['pinId']) && !empty($_REQUEST['pinId'])){
		$pinId = intval($_REQUEST['pinId']);
		$table = 'wp_plom_map where id = '.$pinId;
		$wpdb->get_results('DELETE FROM '.$table);
		echo 'deleted';
		exit;
	}
}

function plom_show_loc(){
	if(($_REQUEST['pinId']) && !empty($_REQUEST['pinId'])){
		global $wpdb;
		$where = 'where id = '.intval($_REQUEST['pinId']);
		$dataRes = $wpdb->get_results( "SELECT * FROM wp_plom_map ".$where );
	}
	?>
	<div class="popup_main" id = 'pop_<?php echo $_REQUEST['pinId'];?>'>
		<div class="popup_top"></div>
		<div class="popup_middle">
			<div class="popup_close"></div>
			<?php
			if($dataRes[0]->img_path){
				$imageUrl = wp_get_attachment_url($dataRes[0]->img_path);
			?>
				<div class="popup_image">
					 <img src="<?php echo $imageUrl; ?>" alt="" width='325' height='195'/> 
				</div>
			<?php } ?>
			<div class="popoup_detail"> <span><?php echo $dataRes[0]->address1; ?></span> <small><?php echo $dataRes[0]->addresss2; ?></small> </div>
		</div>
		<div class="popup_bottom"></div>
		<span class = 'arrow'></span>
	</div>
	<?php
	exit;
}

add_shortcode( 'PLOM_DISPLAYMAP', 'plom_display_map' );
register_activation_hook( __FILE__, 'plom_activate');

add_action( 'wp_ajax_plom_add_pin', 'plom_add_pin' );
add_action( 'wp_ajax_plom_edit_pin', 'plom_edit_pin' );
add_action( 'wp_ajax_plom_save_pin', 'plom_save_pin' );
add_action( 'wp_ajax_plom_del_pin', 'plom_del_pin' );
add_action( 'wp_ajax_plom_show_loc', 'plom_show_loc' );
add_action( 'wp_ajax_plom_media_upload', 'plom_media_upload' );

?>
