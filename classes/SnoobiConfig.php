<?php
/**
 * Handles configuration page
 * @package Snoobi for Wordpress
 **/
class SnoobiConfig
{

	public function __construct()
	{
	}

	/**
	 * Method for drawing the configuration page
	 *
	 */
	public function drawUi()
	{
            if( isset($_POST['save']) )
            {
                update_option('snoobi_account',$_POST['snoobi_account']);

                $msg = "Snoobi configration saved";

                if( isset($_POST['snoobi_disabled']) && $_POST['snoobi_disabled']==1 )
                {
                    update_option('snoobi_disabled',1);
                }
                else
                {
                    update_option('snoobi_disabled',0);
                    $msg .= ". Snoobi is now collecting stuff from your site / blog.";

                }

                if( isset($_POST['snoobi_use_cache']) && $_POST['snoobi_use_cache']==1 )
                {
                    update_option('snoobi_use_cache',1);
                    if( self::initCache() === false)
                    {
                        $error = "Could not create / write to cache dir <i>".SNOOBI_WP_CACHE_DIR."</i>";
                    }
                }
                else
                {
                    update_option('snoobi_use_cache',0);
                }

            }

            if( isset($_POST['reset']) )
            {
                self::truncateAllOptions();

                $msg = "Snoobi disabled & settings resetted";
                if( self::clearCache() === false )
                {
                    $error = "Cache claring failed. Make sure httpd can write to <i>".SNOOBI_WP_CACHE_DIR."</i>!";
                }

            }

            if( isset($_POST['clear-cache']) )
            {
                self::clearCache();

                $msg .= "Cache cleared";
            }

            $snoobi_oauth_token = get_option('snoobi_oauth_token');
            $current_snoobi_account = get_option("snoobi_account");

            if( isset($_GET['msg']) && $_GET['msg']=='oauth_configured_ok')
            {
                $msg = 'Congrats my friend, you are connected to Snoobi! In order to start tracking your blog / site, choose your Snoobi account and save the settings.';
            }

            ?>
<div id="wrap">
    <h2>Snoobi web analytics - settings</h2>

    <?php
        
           if( isset($msg) ) echo '<p style="display:inline; color:green">'.$msg.'</p><br><br>';
           if( isset($error) ) echo '<p style="display:inline; color:red">'.$error.'</p><br><br>';

    ?>
    <?php
    if( self::oauthOk()===true )
    {
            $SnoobiFunction = new SnoobiFunctions();
            $accounts = $SnoobiFunction->getAccounts();

            if($accounts===null)
            {
                self::truncateAllOptions();
                ?>
                <p style="display:inline; color:red">Whoops! We couldn't find any Snoobi-accounts to connect your with. If you're sure that you have access to at least one Snoobi account, please re-connect with Snoobi by clicking the button below.</p><br><br>
                <a class="button add-new-h2" href="?page=snoobi_wp_oauth">Re-connect with Snoobi</a>

    <?php
                return;
            }
    ?>
<form name="snoobi-config" id="snoobi-config-form" method="POST">
<table class="wp-list-table widefat fixed">
    <thead>
    <tr>
        <th width="230px" scope="col">
            <span>Snoobi web analytics settings</span></th>
        <?php
            if( get_option('snoobi_disabled')==1 )
           {
               echo "<h3>IMPORTANT! Your Snoobi tracking is disabled at the moment. Data will not be collected from your site when Snoobi is disabled!</h3>";
           }
           ?>
        <th scope="col" width="230px"></th>
        <th scope="col"></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>
            <label for="snoobi_account">Name of the Snoobi account</label>
        </td>
        <td colspan="2">

 
                <?php

                    // No need to select drop down if privileges to only one account
                    $readonly = "";
                    if( count($accounts)==1 ) $readonly="readonly";

                   echo '<select name="snoobi_account" '.$readonly.'>';


                    foreach( $accounts as $account=>$alias )
                    {
                        if( $account==$current_snoobi_account )
                        {
                            $selected = 'SELECTED';
                        }
                        else
                        {
                            $selected = '';
                        }

                        echo '<option value="'.$account.'" '.$selected.' >'.$alias.'</option>';
                    }
                ?>

            </select>
        </td>
    </tr>
    <input type="hidden" name="snoobi_use_cache" id="snoobi_use_cache" value="0"
    <tr>
        <td>
            <label for="snoobi_disabled">
                Status of Snoobi tracking
                <?php
                $_checked_1 = "";
                $_checked_0 = "";

                if(get_option('snoobi_disabled')==1)
                {
                    $_checked_1 = "CHECKED";
                    $_checked_0 = "";
                }
                else
                {
                    $_checked_1 = "";
                    $_checked_0 = "CHECKED";
                }
                ?>
            </label>
        </td>
        <td colspan="2">
            <?php
                $checked = "";
                if(get_option('snoobi_disabled')) $checked = "CHECKED";
             ?>

            <input type="radio" name="snoobi_disabled" id="snoobi_disabled_0" value="0" <?php echo $_checked_0; ?> > Enabled
            <input type="radio" name="snoobi_disabled" id="snoobi_disabled_1" value="1" <?php echo $_checked_1; ?> > Disabled
        </td>
    </tr>
    <tr>
        <td>
            <input type="submit" name="save" value="Save configuration">
        </td>
        <td>
            <input type="submit" name="reset" value="Disable & reset">
        </td>

        <td>
            <?php
            /*
            <input type="submit" name="clear-cache" value="Clear cache">
             *
             */
            ?>
        </td>
    </tr>

    </tbody>
</table>
</form>
    <?php
    }

if( self::oauthOk()===false && isset($_GET['msg']) && $_GET['msg']=='oauth_error')
{
    echo '<h2 style="color:red">OAuth process failed! Make sure you server setup meets the requirements.</h2>';
}
?>
<br />

<table class="wp-list-table widefat fixed">
    <thead>
    <tr>
        <th width="200px" scope="col" id="bands-head-1">
            <span>OAuth status</span></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>
        <?php
            if( self::oauthOk()===false )
            {
                echo 'Hey dude, you need to grant privilege for Wordpress to access your Snoobi account(s). You can do it by clicking the button below. It\'s all done with cool security thingy called <a href="http://oauth.net/" target="_blank">OAuth </a>. Your Snoobi credentials are not saved in Wordpress.';

                echo '<a class="button add-new-h2" href="?page=snoobi_wp_oauth" style="display:block; padding:10px; text-align:center; font-weight: bold; width: 140px; margin-top:6px;">Connect with Snoobi</a>';
                ?>
            <br /><br />

                <?php

                global $current_user;
                get_currentuserinfo();

                echo '<h2 style="margin-bottom:0px; margin-top:30px;">Don\'t have Snoobi account yet?<br /> No worries, get your free trial by typing your email ';
                ?>
                <form action="https://signup.snoobi.com/signup.php?action=request" method="post" id="get-your-own-snoobi-today" target="_blank" style="display:inline">
                <input value="SNOOBI_WP_TRIALS" name="campaign" id="campaign" type="hidden" />
                <input value="en" name="language" id="language" type="hidden" />
                <input name="email" id="email" type="text" value="<?php echo $current_user->user_email; ?>" style="display:inline; width: 170px" />
                <input id="submit" type="submit" name="submit" class="button add-new-h2" value="and by clicking this" style="display:inline">
                </form>
  <?php
            }
            else
            {
                echo 'You have succesfully connected Snoobi to Wordpress';
            }
        ?>
            <br /><br />
        </td>
    </tr>
    </tbody>
</table>

</div>
            <?php
	}

	public function handleOauth()
	{

            if( isset( $_SERVER['HTTPS'] ) )
            {
                $protocol = "https";
            }
            else
            {
                $protocol = "http";
            }

            $redirAddress = $protocol.'://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'].'?page=snoobi_wp_config';

            // Check do we have token and secret already

            if( $this->oauthOk()===true )
            {
                if( isset( $_SERVER['HTTPS'] ) )
                {
                    $protocol = "https";
                }
                else
                {
                    $protocol = "http";
                }

                wp_redirect($redirAddress.'&msg=oauth_aleady_ok');
                die();
            }
            else
            {
                // Reset tokens and other stuff
                if( !isset($_GET['oauth_token']) ) self::removeAllOptions();


                $OAuth  = new SnoobiOAuth();
                $result = $OAuth->getAccessTokenAndSecret();

                if( $result !== true)
                {
                    if( !isset( $_GET['erc'] ) )
                    {
                        $erc=0;
                    }
                    else
                    {
                        $erc=$_GET['erc'];
                    }

                    ++$erc;
                    die();
                }
                else
                {
                    wp_redirect($redirAddress.'&msg=oauth_configured_ok');
                    die();
                }
            }
        }

        private function clearCache()
        {
            if( $handle = @opendir( SNOOBI_WP_CACHE_DIR ) )
            {
                while (false !== ($file = @readdir($handle))) {

                    if( $file==="." || $file===".." ) continue;
                    
                    $toDel = SNOOBI_WP_CACHE_DIR."/".$file;
                    @unlink($toDel);
                }
                @closedir($handle);
            }
            return true;
        }

        private function initCache()
        {
            if( @is_dir( SNOOBI_WP_CACHE_DIR ) === false)
            {
                if( @mkdir( SNOOBI_WP_CACHE_DIR ) === false ){
                    return false;
                }
            }
            return true;
        }

        public function oauthOk()
        {
            if( strlen( get_option('snoobi_oauth_token') )>10 &&  strlen( get_option('snoobi_oauth_token_secret'))>10 )
            {
                return true;
            }
            return false;
        }

        public function truncateAllOptions()
        {
                update_option('snoobi_account','');
                update_option('snoobi_username','');
                update_option('snoobi_pass','');
                update_option('snoobi_disabled','1');
                update_option('snoobi_oauth_token', null);
                update_option('snoobi_oauth_token_secret', null);
                update_option('snoobi_request_token', null);
                update_option('snoobi_request_token_received', null);
                update_option('snoobi_use_cache', null);
        }

        public function removeAllOptions()
        {
                delete_option('snoobi_account');
                delete_option('snoobi_username');
                delete_option('snoobi_pass');
                delete_option('snoobi_disabled');
                delete_option('snoobi_oauth_token');
                delete_option('snoobi_oauth_token_secret');
                delete_option('snoobi_request_token');
                delete_option('snoobi_request_token_received');
                delete_option('snoobi_use_cache');
        }
}