<?php
/**
 * @package Snoobi for Wordpress
 */
/*
Plugin Name: Snoobi for Wordpress
Plugin URI: http://www.snoobi.com/snoobi-for-wordpress
Description: With this plugin you can easily install Snoobi Analytics and fetch reports from it.
Author: Snoobi Corp. / Hannu Pölönen
Version: 1.0
Author URI: http://www.snoobi.com
*/

/*
 * Inits the Snoobi plugin
 */
function snoobi_wp_init()
{

    $pluginDir = WP_PLUGIN_DIR.'/snoobi-for-wordpress';
    
    define('SNOOBI_WP_CACHE_DIR', $pluginDir.'/wp-cache' );
    define('SNOOBI_WP_CONSUMER_KEY', 'c1f6c4b3eb436d4c61fd1fb4e02a05b8643f0411' );
    define('SNOOBI_WP_CONSUMER_SECRET', 'a9d0e17b1653af6113468791a1071a6982a48ab5' );
    define('SNOOBI_WP_CALLBACK_URL', get_site_url().'/wp-admin/admin.php?page=snoobi_wp_oauth');
    define('SNOOBI_WP_REQUEST_TOKEN_URL', 'https://rest.snoobi.com/oauth/requesttoken?oauth_callback='.urlencode(SNOOBI_WP_CALLBACK_URL));
    define('SNOOBI_WP_ACCESS_TOKEN_URL', 'https://rest.snoobi.com/oauth/accesstoken');
    define('SNOOBI_WP_REST_API_URL','https://rest.snoobi.com');

    require_once("classes/SnoobiConfig.php");
    require_once("classes/SnoobiReport.php");
    require_once("classes/SnoobiOauth.php");
    require_once("classes/SnoobiFunctions.php");
}

add_action('init', 'snoobi_wp_init');

function snoobi_wp_check_oauth()
{


    if( isset( $_GET['page'] ) && $_GET['page'] == 'snoobi_wp_oauth')
    {
        if( isset( $_GET['truncate'] ) && $_GET['truncate']==true )
        {
            update_option( 'snoobi_oauth_token', null );
            update_option( 'snoobi_oauth_token_secret', null );
        }
        // Wp init not ran so we need to hack this one
        if( !defined( 'SNOOBI_WP_CALLBACK_URL' ) ) define('SNOOBI_WP_CALLBACK_URL', get_site_url().'/wp-admin/admin.php?page=snoobi_wp_oauth');

        $Config = new SnoobiConfig();
        $Config->handleOauth();
    }


    if( isset( $_GET['page'] ) && $_GET['page'] == 'snoobi_wp_oauth_step_1')
    {
        print_r($_GET);
        die();
    }
}

add_action('wp_loaded', 'snoobi_wp_check_oauth');

if ( is_admin() )
{
    wp_enqueue_script( 'jquery-google', 'http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js');
    wp_enqueue_script( 'jquery-ui','https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js' );
    wp_enqueue_script( 'highcharts', get_site_url().'/wp-content/plugins/snoobi-for-wordpress/Highcharts/js/highcharts.js');

    wp_enqueue_style( 'jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/base/jquery-ui.css' );

    /* Trigger the admin init */
    add_action('admin_menu', 'snoobi_wp_init_admin');
}


/* Init the admin stuff */
function snoobi_wp_init_admin()
{
    global $submenu, $current_user, $snoobi_wp_hooks;

    $snoobi_wp_hooks = array();
    
    /* Set the menu and the sub pages */
    $snoobi_wp_hooks["snoobi-wp-splash"] = add_menu_page( 'Snoobi Analytics', 'Web Analytics', 'read', 'snoobi-wp-menu', array('SnoobiReport','drawUi'), get_site_url().'/wp-content/plugins/snoobi-for-wordpress/img/Snoobi_b.gif') ;

    $snoobi_wp_hooks["snoobi-wp-config"] = add_submenu_page(
            'snoobi-wp-menu',
            'Snoobi configuration',
            'Snoobi configuration',
            'read',
            'snoobi_wp_config',
            array('SnoobiConfig','drawUi')
            );
}


function snoobi_wp_contextual_help($contextual_help, $screen_id, $screen) {

	global $snoobi_wp_hooks;

        switch ($screen_id)
	{
            case $snoobi_wp_hooks["snoobi-wp-splash"]:
                $contextual_help = 'Here\'s your Snoobi Analytics report. Enjoy!';
            break;

            case $snoobi_wp_hooks["snoobi-wp-config"]:
		$contextual_help = 'Define settings for Snoobi Analytics tracking and repotring';
            break;
	}
	return $contextual_help;
}

add_filter('contextual_help',  'snoobi_wp_contextual_help', 10, 3);

function snoobi_wp_charts()
{
    if( isset( $_GET['page'] ) && ( $_GET['page']=='snoobi_wp_report' || $_GET['page']=='snoobi-wp-menu' ) && SnoobiConfig::oauthOk()===true )
    {
        SnoobiReport::initJqueries();
    }
}

add_action('admin_head','snoobi_wp_charts');

function debug($what){
    echo "<textarea cols=100 rows=60>";
    print_r($what);
    echo "</textarea>";
    die();
}

/* Set the script to the end of the page */

function snoobi_wp_tracking()
{
    $account = get_option('snoobi_account');
    if( strlen( $account ) >1 && get_option('snoobi_disabled')!=1)
    {
    ?>
        <script type="text/javascript" src="http://eu1.snoobi.com/snoop.php?tili=<?php echo $account; ?>">
        </script>
    <?php
    }
}

add_action('wp_footer', 'snoobi_wp_tracking');

// Remove and truncate all of the settings
function snoobi_wp_deactivate()
{
    SnoobiConfig::removeAllOptions();
}

register_deactivation_hook('snoobi-for-wordpress/snoobi-for-wp.php', 'snoobi_wp_deactivate' );