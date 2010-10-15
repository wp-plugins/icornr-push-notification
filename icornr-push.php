<?php
/*
Plugin Name: iCornr Push Notification
Plugin URI: http://www.icornr.com/wpplugin
Description: Sending push notifications to your iPhone users with iCornr when publishing new posts.
Version: 1.0
Author: iCornr
Author URI: http://www.icornr.com
License: GPL2
*/
?>
<?php
/*  Copyright 2010  iCornr  (email : info@icornr.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
	/* Runs when plugin is activated */
	register_activation_hook(__FILE__,'icornrpush_install'); 

	/* Runs on plugin deactivation*/
	register_deactivation_hook( __FILE__, 'icornrpush_remove' );

	function icornrpush_install() {
		/* Creates new database field */
		add_option("icornr_push_id", '', '', 'yes');
		add_option("icornr_push_blogid", '', '', 'yes');
		add_option("icornr_push_enabled", '0', '', 'yes');
	}

	function icornrpush_remove() {
		/* Deletes the database field */
		delete_option('icornr_push_id');
		delete_option('icornr_push_blogid');
		delete_option('icornr_push_enabled');
	
	}

	add_action('publish_post', 'push_on_new_post');
	
	function push_on_new_post() {
	
		if (get_option('icornr_push_enabled') == 'on'){
		
			// This is the message that will be pushed to the iphone appended after the iCornr title
			// the total message can be max 80 characters.
			$message = urlencode("New post: ".$_POST['post_title']);

    		$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_POST, true); 
			//curl_setopt($ch, CURLOPT_POSTFIELDS, $receipt); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_URL, 'https://www.icornr.com/push.php?blogid='.
				get_option('icornr_push_blogid').'&message='.$message.'&pushid='.get_option('icornr_push_id')); 
			$response = curl_exec($ch);
	
			// Need response?
		}
	}
?>
<?php
	if ( is_admin() ){

		/* Call the html code */
		add_action('admin_menu', 'icornrpush_admin_menu');

		function icornrpush_admin_menu() {
			add_options_page('iCornr Push', 'iCornr Push', 'administrator',
				'icornrpush', 'icornrpush_html_page');
		}
	}
?>
<?php
	function icornrpush_html_page() {
		?>
		<div style="width:600px;">
		<h2>iCornr Push Notification</h2>
		
		<div>
		<p><b>Important:</b> To use push notification with your iCornr account it must have VIP status.</p>
		
		<p>To enable push notification with your iCornr you need to set a few values here.
		You can find out these values by logging in to your iCornr account and visiting your
		iCornr with the 'My iCornr' link from the menu. </p>
		
		</div>
		
		<form method="post" action="options.php">
		<?php wp_nonce_field('update-options'); ?>

		<table width="620">
			<tr valign="top">
				<th width="120" align="left" valign="top" scope="row">Enabled:</th>
				<td width="500" valign="top">
					<?php
						$check = "";
						if(get_option('icornr_push_enabled') == "on"){ 
							$check = 'checked="checked"'; 
						}
						
						
					?>
				
					<input type="checkbox" name="icornr_push_enabled" value="on"
						id="icornr_push_enabled" <?php echo($check)?> ">
					</br>
				</td>
			</tr>
			<tr valign="top">
				<th width="120" align="left" valign="top" scope="row">Enter blog id:</th>
				<td width="500" valign="top">
					<input name="icornr_push_blogid" style="width:100px;" type="text" id="icornr_push_blogid"
					value="<?php echo get_option('icornr_push_blogid'); ?>" /> (Your blog id is typically a number, ex. '10')
					</br>
				</td>
			</tr>
			<tr valign="top">
				<th width="120" align="left" valign="top" scope="row">Enter push id:</th>
				<td width="500" valign="top">
					<input name="icornr_push_id" style="width:300px;" type="text" id="icornr_push_id"
					value="<?php echo get_option('icornr_push_id'); ?>" />
					</br>
				</td>
			</tr>
			<tr>
				<th width="120" align="left" valign="top" scope="row">Test:</th>
				<td width="500" valign="top">
					<?php
					$ch = curl_init(); 
					curl_setopt($ch, CURLOPT_POST, true); 
					//curl_setopt($ch, CURLOPT_POSTFIELDS, $receipt); 
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_URL, 'https://www.icornr.com/push.php?op=test&blogid='.get_option('icornr_push_blogid').'&message=test&pushid='.get_option('icornr_push_id')); 
					$response = curl_exec($ch);
					
					if ($response == "Test ok! Push notification possible."){
						?>
						<span style="color:green;">
						<?php
					}
					else{
						?>
						<span style="color:red;">
						<?php
					}
					
					echo($response);
					?>
					</span>
					<br/><br/>
					If you get 'Invalid host: xxx.xxx.xxx' it means that you must log into your iCornr account and
					set this host in the "Push" tab for 'Push host'.
				</td>
			</tr>
			
		</table>

		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="icornr_push_id,icornr_push_blogid,icornr_push_enabled" />

		<p>
		<input type="submit" value="<?php _e('Save Changes') ?>" />
		</p>

		</form>
		</div>
	<?php
	}
?>