<?php
/**
 * Class for performing the API calls
 * Built-in cache functionality
 * @package Snoobi for Wordpress
 **/

require_once("OAuth.php");
require_once("SnoobiCurl.php");

class SnoobiApiClientCache
{

        private $cacheDir;
        private $cacheEnabled = false;

        public $consumer;
        public $signatureMethod;

        public function __construct()
        {
            $this->consumer = new OAuthConsumer(SNOOBI_WP_CONSUMER_KEY, SNOOBI_WP_CONSUMER_SECRET);
            $this->signatureMethod = new OAuthSignatureMethod_HMAC_SHA1();
        }

        /*
         * Sets the cache dir and checks that folder is writable & readable
         *
         */
        public function cacheDir( $cacheDir )
        {
            // Check that the directory exists
            if( !is_dir($cacheDir) )
            {
                $this->disableCache();
                throw new Exception("CACHE-ERROR: Cache directory $cacheDir does not exist");
            }

            // Write random file to the directory and check that it's writable
            $file = $cacheDir."/snbcahce_".mktime()."_".rand(1,  getrandmax());
            $fh = fopen($file,'x');

            if($fh === false)
            {
                $this->disableCache();
                throw new Exception("CACHE-ERROR: Cannot create file to cache directory. Tried to create $file");
            }

            if(fwrite($fh,"c1") === false)
            {
                $this->disableCache();
                throw new Exception("CACHE-ERROR: Cannot write to cache directory. Tried to write $file");
            }

            fclose($fh);
            unlink($file);
            $this->cacheDir = $cacheDir;
            $this->enableCache();
        }

        private function hashFile( $service )
        {
            $filehash = md5( $service );
            return $this->cacheDir."/".$filehash;
        }

        public function disableCache()
        {
            $this->cacheEnabled = false;
        }

        public function enableCache()
        {
            $this->cacheEnabled = true;
        }

        /**
	 * Method to receive data from service
	 * @return string
	 */
	public function fetchResults( $serviceUrl )
	{
                $serviceUrl = trim( $serviceUrl );

                #die($serviceUrl);
                
                if( $this->cacheEnabled===true )
                {
                    // Check if the cache file exists
                    $hashFile = $this->hashFile( $serviceUrl );
                    $cacheContents = @file_get_contents( $hashFile );

                    if( !$cacheContents )
                    {
                        $response = $this->fetchData( $serviceUrl );

                        $fh = fopen($hashFile,'x');
                        if($fh === false)
                        {
                            $this->disableCache();
                            throw new Exception("CACHE-ERROR: Cannot create file to cache directory. Tried to create $hashFile");
                        }

                        if(fwrite($fh,$response) === false)
                        {
                            $this->disableCache();
                            throw new Exception("CACHE-ERROR: Cannot write to cache directory. Tried to write $file");
                        }

                        fclose($fh);

                        return $response;
                    }
                    else
                    {
                        return $cacheContents;
                    }
                }
                else
                {
                    $response = $this->fetchData( $serviceUrl );
                }
                return $response;
	}

        public function fetchData( $serviceUrl )
        {
            $token = new OAuthConsumer( get_option( 'snoobi_oauth_token' ), get_option('snoobi_oauth_token_secret'), NULL);
            $api = OAuthRequest::from_consumer_and_token($this->consumer, $token, "GET", $serviceUrl, array());
            $api->sign_request($this->signatureMethod, $this->consumer, $token);
            $toHeader = $api->to_header();
            $results = SnoobiCurl::get_curl_response($toHeader, $serviceUrl, false);
            return $results;
        }
}