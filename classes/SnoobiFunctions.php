<?php
/**
 * Helper functions to fetch data from Snoobi
 * @package Snoobi for Wordpress
 **/

require_once("SnoobiApiClientCache.php");
require_once("SnoobiDatetime.php");

class SnoobiFunctions
{

	protected $api=null;
	protected $oauth=null;
        protected $account = null;

        private $token;
	private $domain;

        public $startDate;
        public $endDate;

	/**
	 * Create's new instance out of rest API client
	 */
	public function __construct()
	{
            $this->account = get_option( 'snoobi_account' );

            $this->startDate = new SnoobiDatetime( date("Y-m-d", mktime(date("H"), date("i"), date("s"), date("m")-1, date("j"), date("Y") ) ) , "EN" ); // Defaults one month back
            $this->endDate = new SnoobiDatetime( date("Y-m-d"), "EN" );
	}

	/**
	 * Init the api only once
	 */
	private function initApi()
	{
            if( $this->api == null )
            {
                $this->api = new SnoobiApiClientCache();
                if( get_option('snoobi_use_cache')==1 )
                {
                    $this->api->cacheDir( SNOOBI_WP_CACHE_DIR );
                    $this->api->enableCache();
                }
            }
            return true;
        }


        public function getTotalVisits()
	{
                $this->initApi();

                $yesterday = new SnoobiDatetime(date("Y-m-d"));
                $start = new SnoobiDatetime('2004-01-01');

                $jsonTs = $this->api->fetchResults( SNOOBI_WP_REST_API_URL.'/report/mainreport/summary.json?account='.$this->account.'&sd='.$start->getDatetime("Y-m-d").'&ed='.$yesterday->getDatetime("Y-m-d"));

                $resultsTs = json_decode( $jsonTs );

                if( !isset( $resultsTs[0]->report->mainReport->summary )) return null;
                $dataTs = $resultsTs[0]->report->mainReport->summary;

                return $dataTs;

        }
        public function getAccounts()
	{
                $this->initApi();
                $results = $this->api->fetchResults( SNOOBI_WP_REST_API_URL.'/user/accounts.json?limit=10' );

                list($object) = json_decode( $results );
                $simpleArray = array();
                if( !isset( $object->user->accountPrivileges->privilege )) return null;

                $privileges = $object->user->accountPrivileges->privilege;


                if( is_array($privileges) && count($privileges)>0 )
                {
                    foreach($privileges as $obj)
                    {
                        $account = $obj->account->{'$'};
                        $alias = $obj->alias->{'$'};
                        $simpleArray[$account] = $alias;
                    }
                }
                else
                {
                    $account = $privileges->account->{'$'};
                    $alias = $privileges->alias->{'$'};
                    $simpleArray[$account] = $alias;
                }

                if( count($simpleArray)==0 ) return null;

                return $simpleArray;

	}

        public function getVisitors()
        {
            $this->initApi();
            $jsonTs = $this->api->fetchResults(  SNOOBI_WP_REST_API_URL.'/report/mainreport/timeseries.json?account='.$this->account.'&sd='.$this->startDate->getDatetime("Y-m-d").'&ed='.$this->endDate->getDatetime("Y-m-d").'&view=visitors');
            $resultsTs = json_decode($jsonTs);

            if( !isset( $resultsTs[0]->report->mainReport->timeSeries->timeUnit )) return null;

            $dataTs = $resultsTs[0]->report->mainReport->timeSeries->timeUnit;


            return $dataTs;
        }

        public function getPageviews()
        {
            $this->initApi();
            $jsonTs = $this->api->fetchResults( SNOOBI_WP_REST_API_URL.'/report/mainreport/timeseries.json?account='.$this->account.'&sd='.$this->startDate->getDatetime("Y-m-d").'&ed='.$this->endDate->getDatetime("Y-m-d").'&view=pageviews');
            $resultsTs = json_decode($jsonTs);

            if( !isset( $resultsTs[0]->report->mainReport->timeSeries->timeUnit )) return null;

            $dataTs = $resultsTs[0]->report->mainReport->timeSeries->timeUnit;

            return $dataTs;
        }

        public function getVisits()
        {
            $this->initApi();
            $jsonTs = $this->api->fetchResults( SNOOBI_WP_REST_API_URL.'/report/mainreport/timeseries.json?account='.$this->account.'&sd='.$this->startDate->getDatetime("Y-m-d").'&ed='.$this->endDate->getDatetime("Y-m-d").'&view=visits&groupby=day');
            $resultsTs = json_decode($jsonTs);

            if( !isset( $resultsTs[0]->report->mainReport->timeSeries->timeUnit )) return null;

            $dataTs = $resultsTs[0]->report->mainReport->timeSeries->timeUnit;
            return $dataTs;
        }

        public function getEntries()
        {
            $this->initApi();
            $jsonTs = $this->api->fetchResults( SNOOBI_WP_REST_API_URL.'/report/mainreport/entrymethods.json?account='.$this->account.'&sd='.$this->startDate->getDatetime("Y-m-d").'&ed='.$this->endDate->getDatetime("Y-m-d"));
            $resultsTs = json_decode($jsonTs);

            if( !isset( $resultsTs[0]->report->mainReport->entryMethods )) return null;

            $dataTs = $resultsTs[0]->report->mainReport->entryMethods;
            return $dataTs;
        }

        public function getPages ()
        {
            $this->initApi();
            $jsonTs = $this->api->fetchResults( SNOOBI_WP_REST_API_URL.'/report/mainreport/pages.json?account='.$this->account.'&sd='.$this->startDate->getDatetime("Y-m-d").'&ed='.$this->endDate->getDatetime("Y-m-d").'&limit=10');
            $resultsTs = json_decode($jsonTs);

            if( !isset( $resultsTs[0]->report->mainReport->pages->title )) return null;

            $dataTs = $resultsTs[0]->report->mainReport->pages->title;

            return $dataTs;
        }

        public function getSearchterms ()
        {
            $this->initApi();
            $jsonTs = $this->api->fetchResults( SNOOBI_WP_REST_API_URL.'/report/mainreport/searchterms.json?account='.$this->account.'&sd='.$this->startDate->getDatetime("Y-m-d").'&ed='.$this->endDate->getDatetime("Y-m-d").'&limit=10');
            $resultsTs = json_decode($jsonTs);
            
            if( !isset( $resultsTs[0]->report->mainReport->searchTerms->searchTerm )) return null;

            $dataTs = $resultsTs[0]->report->mainReport->searchTerms->searchTerm;
            return $dataTs;
        }

        public function  __destruct()
        {
        }
}