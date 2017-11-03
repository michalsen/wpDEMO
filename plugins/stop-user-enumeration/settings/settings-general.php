<?php
add_filter( 'wpsf_register_settings_sue_settings', 'sue_settings' );

function sue_settings( $wpsf_settings ) {
    // General Settings section
    $wpsf_settings[] = array(
	    'section_id'          => 'general',
	    'section_title'       => '',
	    'section_description' => '<p>' . esc_html__( 'Welcome to Stop User Enumeration, part of Fullworks WP VPS Security', 'stop-user-enumeration' ) . '</p>' .
	                             esc_html__('Fullworks WP VPS Security is built to help protect WP installations on VPS and Dedicated Servers, although you may use it happily on your shared hosting plans too.'
	                          ,'stop-user-enumeration') . '</p>',
	    'section_order'       => 5,
	    'fields'              => array(

            array(
                'id' => 'stop_rest_user',
                'title' => esc_html__('Stop REST API User calls','stop-user-enumeration'),
                'desc' => esc_html__('WordPress allows anyone to find users by API call, by checking this box the calls will be restricted to logged in users only. Only untick this box if you need to allow unfettered API access to users','stop-user-enumeration'),
                'type' => 'checkbox',
                'default' => 1
            ),
            array(
                'id' => 'log_auth',
                'title' => esc_html__('log attempts to AUTH LOG','stop-user-enumeration'),
                'desc' => sprintf(esc_html__('Leave this ticked if you are using %1$sFail2Ban%2$s on your VPS to block attempts at enumeration.%3$s If you are not running Fail2Ban or on a shared host this does not need to be ticked, however it normally will not cause a problem being ticked.'
	                ,'stop-user-enumeration'),
	                '<a href="http://www.fail2ban.org/wiki/index.php/Main_Page" target="_blank">','</a>','<br>'),
                'type' => 'checkbox',
                'default' => 1
            ),
            array(
                'id' => 'comment_jquery',
                'title' => esc_html__('Remove numbers from comment authors','stop-user-enumeration'),
                'desc' => esc_html__('This plugin uses jQuery to remove any numbers from a comment author name, this is because numbers trigger enumeration checking. You can untick this if you do not use comments on your site or you use a different comment method than standard',
	                'stop-user-enumeration'),
                'type' => 'checkbox',
                'default' => 1
            ),

        )
    );

    return $wpsf_settings;
}
