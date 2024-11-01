<?php
/*
Plugin Name: WordPress PayPal Donation
Plugin URI: http://thomas.stachl.me/plugins/wordpress-paypal-donation/
Description: This plugin adds a donate button to posts and sidebar.
Version: 1.01
Author: Thomas Stachl, Alberto Buschettu 
Author URI: http://thomas.stachl.me/


    Copyright 2008 Thomas Stachl  (email : thomas@stachl.me)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if( isset($_GET['resource']) && !empty($_GET['resource'])) {
	# base64 encoding
	$resources = array(
		'paypal.gif' =>
		'R0lGODlhEAAQALMAAP///8z//8zM/8zMzJnMzJmZzGaZzGaZmW'.
		'ZmmTNmmTNmZgAzZgAAM////wAAAAAAACH5BAEHAA0ALAAAAAAQ'.
		'ABAAAARasMlFq5XYalpws8lRIAmVENnCLAngIuoCJBOlCC5QVP'.
		'NXITmBwhSgWRCDwqHEOvQ2MVarYIQ2AYMEszLIYQXUqglg0Jq1'.
		'NcqhSPOkKLq2O41EzzFB+V1y3ksiADs='.
		'');
 
	if(array_key_exists($_GET['resource'], $resources)) {
 
		$content = base64_decode($resources[ $_GET['resource'] ]);
 
		$lastMod = filemtime(__FILE__);
		$client = ( isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false );
		// Checking if the client is validating his cache and if it is current.
		if (isset($client) && (strtotime($client) == $lastMod)) {
			// Client's cache IS current, so we just respond '304 Not Modified'.
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastMod).' GMT', true, 304);
			exit;
		} else {
			// Image not cached or cache outdated, we respond '200 OK' and output the image.
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastMod).' GMT', true, 200);
			header('Content-Length: '.strlen($content));
			header('Content-Type: image/' . substr(strrchr($_GET['resource'], '.'), 1) );
			echo $content;
			exit;
		}
	}
}
 
function wpdon_get_resource_url($resourceID) {
 
	return trailingslashit( get_bloginfo('url') ) . '?resource=' . $resourceID;
}

// Localization
load_plugin_textdomain('wordpress-paypal-donation');

// Global plugin url
$wpdon_plugin_url = trailingslashit( get_bloginfo('wpurl') ).PLUGINDIR.'/'. dirname( plugin_basename(__FILE__) );

// Hook for adding menupage
if ( is_admin() ) {
	add_action('admin_menu', 'wpdon_add_page');
}

// Function for above hook
function wpdon_add_page() {
	global $wp_version;
	if ( current_user_can('edit_posts') && function_exists('add_submenu_page') ) {
 
		$menutitle = '';
		if ( version_compare( $wp_version, '2.6.999', '>' ) ) {
			$menutitle = '<img src="' . wpdon_get_resource_url('paypal.gif') . '" alt="" />' . ' ';
		}
		$menutitle .= __('PayPal Donation', 'wordpress-paypal-donation');
 
		add_submenu_page('options-general.php', __('WordPress Paypal Donation', 'wordpress-paypal-donation'), $menutitle, 9, __FILE__, 'wpdon_options');
		$plugin = plugin_basename(__FILE__); 
		add_filter( 'plugin_action_links_' . $plugin, 'wpdon_actions' );
	}
}

// Adds an action link to the plugins page
function wpdon_actions($links) {
	$settings_link = '<a href="options-general.php?page=wordpress-paypal-donation/wp-paypal-donation.php">' . __('Settings') . '</a>';
	array_unshift( $links, $settings_link );
 
	return $links;
}

function wpdon_options() {
	global $wpdon_plugin_url;
	
    if(isset($_POST['action'])) {
		if(!eregi("^[a-z0-9]+([-_\.]?[a-z0-9])+@[a-z0-9]+([-_\.]?[a-z0-9])+\.[a-z]{2,4}$",$_POST["business"])) {
			echo '<div id="message" class="updated fade"><p><strong>'.__('Email is not valid!', 'wordpress-paypal-donation')."</strong></p></div>";
		} else {				
			if (!$_POST['image']) $_POST['image'] = "http://www.paypal.com/en_US/i/btn/btn_donate_LG.gif";
			
			$settings = array (
				'business'			=> $_POST['business'],
				'item_name'			=> $_POST['item_name'],
				'return'			=> $_POST['return'],
				'cancel_return'		=> $_POST['cancel_return'],
				'amount'			=> $_POST['amount'],
				'currency_code'		=> $_POST['currency_code'],
				'image'				=> $_POST['image'],
				'page_style'				=> $_POST['page_style']
			);
			update_option('wordpress_paypal_donation', $settings);

			echo "<div id=\"message\" class=\"updated fade\"><p><strong>WordPress PayPal Donation plugin options updated.</strong></p></div>";
		}
    }

	$settings = get_option('wordpress_paypal_donation');
	
	$business = $settings['business'];
	$item_name = $settings['item_name'];
	$return = $settings['return'];
	$cancel_return = $settings['cancel_return'];
	$amount = $settings['amount'];
	$currency_code = $settings['currency_code'];
	$image = $settings['image'];
	$page_style = $settings['page_style'];
	include 'currency.php';

    $xml = new SimpleXMLElement($string);
    $options = '<select name="currency_code">';
    
    foreach ($xml->currency as $currency) {
        $options .= "<option value='$currency->currencycode'";
        if ($currency_code == $currency->currencycode) $options .= ' selected="selected"';
        $options .= ">".$currency->currencycode." | ".substr($currency->title,0,24)."</option>";
    }
    
    $options .= "</select>";

	$actionurl=$_SERVER['REQUEST_URI'];
?>
<div class="wrap" style="max-width:950px !important;"> 
	<h2><?php _e('WordPress PayPal Donation', 'wordpress-paypal-donation'); ?></h2> 
				
	<div id="poststuff" style="margin-top:10px;"> 
		<div id="sideblock" style="float:right;width:220px;margin-left:10px;"> 
				 <h3><?php _e('Information', 'wordpress-paypal-donation'); ?></h3> 
				 <div id="dbx-content" style="text-decoration:none;"> 
					 <img src="<?php echo $wpdon_plugin_url ?>/images/browser_16x16.png"><a style="text-decoration:none;" href="http://thomas.stachl.me/2008/11/30/tutorials/wordpress-paypal-donation/"> <?php _e('Plugin Home', 'wordpress-paypal-donation'); ?></a><br /><br /> 
					 <img src="<?php echo $wpdon_plugin_url ?>/images/notes_16x16.png"><a style="text-decoration:none;" href="http://thomas.stachl.me/2008/11/30/tutorials/wordpress-paypal-donation/#comments"> <?php _e('Plugin Comments', 'wordpress-paypal-donation'); ?></a><br /><br /> 
					 <img src="<?php echo $wpdon_plugin_url ?>/images/wallpaper_16x16.png"><a style="text-decoration:none;" href="http://wordpress.org/extend/plugins/wordpress-paypal-donation/"> <?php _e('Rate Plugin', 'wordpress-paypal-donation'); ?></a><br /><br /> 
				     <img src="<?php echo $wpdon_plugin_url ?>/images/map_16x16.png"><a style="text-decoration:none;" href="http://thomas.stachl.me/plugins/"> <?php _e('My WordPress Plugins', 'wordpress-paypal-donation'); ?></a><br /><br /> 
					 <br /> 
					 <p align="center"><img src="<?php echo $wpdon_plugin_url ?>/images/tools_128x128.png" width="128" height="128"></p> 
					 <p><img src="<?php echo $wpdon_plugin_url ?>/images/mail_16x16.png"><a style="text-decoration:none;" href="http://thomas.stachl.me/kontakt/"> <?php _e('Contact me', 'wordpress-paypal-donation'); ?></a></p> 
		 		</div> 
		 	</div> 
 
	 <div id="mainblock" style="width:710px"> 
	 
		<div class="dbx-content"> 
		 	<form action="<?php echo $actionurl; ?>" method="post"> 
					<input type="hidden" name="action" value="update" />
					<?php wp_nonce_field('update-options'); ?>
					<h3><?php _e('Options', 'wordpress-paypal-donation'); ?></h3>		
                    <p><?php _e('Enter the default settings for your PayPal Donation account.', 'wordpress-paypal-donation'); ?></p>
                    <fieldset>
                    <legend><strong><?php _e('General options', 'wordpress-paypal-donation'); ?></strong></legend>
                        <table width="450" border="0" cellpadding="0" cellspacing="0" class="form-table">
                            <tr valign="top">
                              <td width="200"><input type="text" name="business" value="<?php echo $business; ?>" />
                                <label for="business"> <?php _e('Email', 'wordpress-paypal-donation'); ?></label></td>
                              <td width="250"><input type="text" name="item_name" value="<?php echo $item_name; ?>" />
                                <label for="item_name"> <?php _e('Title', 'wordpress-paypal-donation'); ?></label></td>
                            </tr>
                        </table>
                    </fieldset>
					<br />					
					<br />
					<fieldset>		
                    <legend><strong><?php _e('Where do go', 'wordpress-paypal-donation'); ?></strong></legend>
                        <table width="450" border="0" cellpadding="0" cellspacing="0" class="form-table">
                            <tr valign="top">
                              <td width="200"><input type="text" name="return" value="<?php echo $return; ?>" />
                                <label for="return"> <?php _e('Return URL', 'wordpress-paypal-donation'); ?></label></td>
                              <td width="250"><input type="text" name="cancel_return" value="<?php echo $cancel_return; ?>" />
                                <label for="cancel_return"> <?php _e('Cancel URL', 'wordpress-paypal-donation'); ?></label></td></tr>
 <tr valign="top">                               
                                                              <td width="250"><input type="text" name="page_style" value="<?php echo $page_style; ?>" />
                                <label for="page_style"> <?php _e('Page Style', 'wordpress-paypal-donation'); ?></label></td>
                                
                            </tr>
                        </table>
                    </fieldset>
					<br />					
					<br />
					<fieldset>		
                    <legend><strong><?php _e('How much', 'wordpress-paypal-donation'); ?></strong></legend>
                        <table width="450" border="0" cellpadding="0" cellspacing="0" class="form-table">
                            <tr valign="top">
                              <td width="200"><input type="text" name="amount" value="<?php echo $amount; ?>" />
                                <label for="amount"> <?php _e('Amount', 'wordpress-paypal-donation'); ?></label></td>
                              <td width="250"><?php echo $options; ?>
                                <label for="currency_code"> <?php _e('Currency', 'wordpress-paypal-donation'); ?></label></td>
                            </tr>
                        </table>
                    </fieldset>
					<br />					
					<br /> 				
                    <fieldset>
                    <legend><strong><?php _e('Image', 'wordpress-gallery-slideshow'); ?></strong></legend>
                    <table width="450" border="0" cellpadding="0" cellspacing="0" class="form-table">
                        <tr valign="top">
                            <td width="200"><input type="text" name="image" value="<?php echo $image; ?>" />
                                <label for="image"> <?php _e('Image', 'wordpress-paypal-donation'); ?></label></td>
                            <td width="250"><img src="<?php echo $image; ?>" /></td>
                        </tr>
                    </table>
                    </fieldset>
					<br />
                    <br />
					<div class="submit"><input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" /></div> 
			</form> 
		</div> 
		<br/><br/>
	 </div> 
 
	</div> 
	
<h5><?php _e('WordPress PayPal Donation plugin by ', 'wordpress-paypal-donation'); ?><a href="http://thomas.stachl.me/">Thomas Stachl</a></h5> 
</div> 
<?
}

function wordpress_paypal_donation_create($attr) {
	global $post;
	global $wpdon_plugin_url;

	$settings = get_option('wordpress_paypal_donation');
	
	$business = $settings['business'];
	$item_name = $settings['item_name'];
	$return = $settings['return'];
	$cancel_return = $settings['cancel_return'];
	$amount = $settings['amount'];
	$currency_code = $settings['currency_code'];
	$image = $settings['image'];
	$page_style = $settings['page_style'];
	
	extract(shortcode_atts(array(
		'email'			=> $business,
		'title'			=> $item_name,
		'return_url'	=> $return,
		'cancel_url'	=> $cancel_return,
		'amount'		=> $amount,
		'ccode'			=> $currency_code,
		'image'			=> $image,
	), $attr));
	
	$output  = "<form class='donate' method='post' action='https://www.paypal.com/cgi-bin/webscr'>";
	$output .= "		<input type='hidden' value='$amount' name='amount'/>";
	$output .= "		<input type='hidden' value='_xclick' name='cmd'/>";
	$output .= "		<input type='hidden' value='$email' name='business'/>";
	$output .= "		<input type='hidden' value='$title' name='item_name'/>";
	$output .= "		<input type='hidden' value='1' name='no_shipping'/>";
	$output .= "		<input type='hidden' value='$return_url' name='return'/>";
	$output .= "		<input type='hidden' value='$cancel_url' name='cancel_return'/>";
	$output .= "		<input type='hidden' value='$ccode' name='currency_code'/>";
	$output .= "		<input type='hidden' value='$page_style' name='page_style'/>";
	$output .= "		<input type='hidden' value='0' name='tax'/>";
	$output .= "		<input type='image' alt='PayPal - The safer, easier way to pay online' name='submit' style='border: 0pt none ;' src='$image'/>";
	$output .= "</form>";
	
	return $output;
}
add_shortcode('donate', 'wordpress_paypal_donation_create');

function wordpress_paypal_donation($attr = '') {
	$result = '';
	$attributes = explode('&', $attr);
		foreach($attributes as $attribute){
			if($attribute){
				// find the position of the first '='
				$i = strpos($attribute, '=');
				// if not a valid format ('key=value) we ignore it
				if ($i){
					$key = substr($attribute, 0, $i);
					$val = substr($attribute, $i+1); 
					$result[$key]=$val;
				}
			}
		}
	echo wordpress_paypal_donation_create($result);
}

function wpdon_uninstall(){
	delete_option('wordpress_paypal_donation');
}
register_deactivation_hook( __FILE__, 'wpdon_uninstall' );

function wpdon_install(){
	$settings = array (
		'business'			=> 'you@yourdomain.com',
		'item_name'			=> 'Donate for me',
		'return'			=> 'http://yourdomain.com',
		'cancel_return'		=> 'http://yourdomain.com',
		'amount'			=> '1.00',
		'currency_code'		=> 'USD',
		'image'				=> 'https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif',
	);
		
	if(!get_option('wordpress_paypal_donation')){
		add_option('wordpress_paypal_donation', $settings);
	}
}
register_activation_hook( __FILE__, 'wpdon_install' );

?>