<?php
/**
 * OAuth wrapper
 * @package Snoobi for Wordpress
 **/

require_once("OAuth.php");
require_once("SnoobiCurl.php");

class SnoobiOAuth
{
    private $requestToken = '';

    
    public function  __construct()
    {
        session_start();
    }
    
    public function getAccessTokenAndSecret()
    {
        try
        {
            /*
             * As in any oAuth related communication, create new instance of an OAuth class with our consumer credentials
             */
            $consumer = new OAuthConsumer( SNOOBI_WP_CONSUMER_KEY, SNOOBI_WP_CONSUMER_SECRET, null);

            if( $this->getRequestToken() === null )
            {
                $req = OAuthRequest::from_consumer_and_token($consumer, NULL, "POST", SNOOBI_WP_REQUEST_TOKEN_URL);
                $req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, NULL);

                $toHeader = $req->to_header();
                $response = SnoobiCurl::get_curl_response($toHeader, SNOOBI_WP_REQUEST_TOKEN_URL);
                parse_str($response, $oauth);

                $this->setRequestToken( $oauth['oauth_token_secret'] );
                wp_redirect($oauth['authentification_url'].'?oauth_token='.$oauth['oauth_token']);
                die();
            }
            else
            {

                $token = new OAuthConsumer($_GET['oauth_token'], $this->getRequestToken());

                $acc = OAuthRequest::from_consumer_and_token($consumer, $token, "POST", SNOOBI_WP_ACCESS_TOKEN_URL);
                $acc->set_parameter("oauth_verifier", $_GET['verifier_token']);
                $acc->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, $token);


                $toHeader = $acc->to_header();
                $response = SnoobiCurl::get_curl_response($toHeader, SNOOBI_WP_ACCESS_TOKEN_URL);
                parse_str($response, $oauth);

                /* Save the tokens as options */
                update_option( 'snoobi_oauth_token', $oauth['oauth_token'] );
                update_option( 'snoobi_oauth_token_secret', $oauth['oauth_token_secret'] );
                update_option( 'snoobi_request_token', null );
                return true;
            }
        }
        catch(OAuthException $E)
        {
            return $E->debugInfo["body_recv"];
        }
    }
    
    private function setRequestToken( $token )
    {
        $this->requestToken = $token;
        update_option('snoobi_request_token', $token);
        $_SESSION['requestToken'] = $token;
    }

    private function getRequestToken()
    {
        $this->requestToken = "";
        if( get_option('snoobi_request_token') ) $this->requestToken = get_option('snoobi_request_token');
        if( isset( $this->requestToken ) && strlen( $this->requestToken )>3 ) return $this->requestToken;
        return null;
    }
    public function  __destruct() 
    {
    }
}
