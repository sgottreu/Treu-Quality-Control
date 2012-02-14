<?php
/*
Plugin Name: Treu Quality Control
Plugin URI: http://www.treutech.com/wordpress/quality-control-plugin
Description: Passing quality control info from the host site to BaseCamp
Author: Scott Gottreu
Version: 1.0
Author URI: http://www.treutech.com
*/

/*
Copyright (C) 2012 Scott gottreu

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

*/

add_action('admin_menu', 'treu_quality_control_admin_menu');

register_uninstall_hook( __FILE__, 'treu_quality_control_uninstall' );

function treu_quality_control_admin_menu() {
	add_options_page('Treu Quality Control Options', 'Treu Quality Control Plugin', 'manage_options', 'quality-control-basecamp-plugin', 'treu_quality_control_plugin_options');
}

function treu_quality_control_plugin_options() {

      //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    // Read in existing option value from database
    
    $api = get_option( 'qc_basecamp_api' );
    $companyId = get_option( 'qc_basecamp_company' );
    $subdomain = get_option('qc_basecamp_subdomain');
    

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    
    if( isset($_POST['submit-hidden']) && $_POST['submit-hidden'] == 'Y' ) {
        // Read their posted value
        
        $api = $_POST[ 'qc_basecamp_api' ];
		$companyId = $_POST['qc_basecamp_company' ];
        $subdomain = $_POST['qc_basecamp_subdomain' ];
        

        // Save the posted value in the database
        update_option( 'qc_basecamp_api', $api );
        update_option( 'qc_basecamp_company', $companyId );
        update_option( 'qc_basecamp_subdomain', $subdomain );        
        
        // Put an settings updated message on the screen

?>
        <div class="updated"><p><strong><?php _e('Settings saved.', 'menu-test' ); ?></strong></p></div>
<?php

    }
?>
    <style>
        input[type="text"]  {
            background-color:#ddd;
            border-color: #AAA;
        }
    </style>
    <div class="wrap">

        <h2><?php echo __( 'Treu Quality Control Plugin Settings', 'menu-test' );?></h2>
		
		<h3><?php require_once('bookmark.htm');?></h3>
		
        <form name="prefixes" method="post" action="">
        <input type="hidden" name="submit-hidden" value="Y">
        
        <p>
            <strong>Basecamp API:</strong><br>
        <input type="text" name="qc_basecamp_api" value="<?php echo $api; ?>" size="40" class="qc_basecamp_input">
        </p>
        <p>
            <strong>Basecamp Company Id:</strong><br>
        <input type="text" name="qc_basecamp_company" value="<?php echo $companyId; ?>" size="10" class="qc_basecamp_input">
        </p>
        <p>
            <strong>Basecamp Subdomain:</strong><br>
        http://<input type="text" name="qc_basecamp_subdomain" value="<?php echo $subdomain; ?>" size="10" class="qc_basecamp_input">.basecamphq.com
        </p>
                
        <hr>
        <p class="submit">
        <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
        </p>
        
        </form>
        
        
    </div>

<?php
}


function treu_quality_control_uninstall()
{
	// Delete associated options
	delete_option( 'qc_basecamp_api' );
	delete_option( 'qc_basecamp_company' );
	delete_option( 'qc_basecamp_subdomain' );        

}