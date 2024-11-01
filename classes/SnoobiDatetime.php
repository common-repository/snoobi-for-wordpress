<?php
/**
 * Class for performing the API calls
 * Built-in cache functionality
 * @package Snoobi for Wordpress
 **/
if( !defined('DATE_W3Cf') ) define( 'DATE_W3Cf', 'Y-m-d\TH:i:s');
class SnoobiDatetime
{

	const XML = "Y-m-d\TH:i:s";
	
	public $timestamp;

	public $timezone;
	public $datestamp;

	function __construct( $timestring='', $format='EN', $timezone="Europe/Helsinki")
	{
		if( strlen($timestring) )
			if( !$this->setDatetime( $timestring, $format ) )
				throw new Exception("Datetime was formatted incorrectly $timestring in $format");

		$this->timezone = $timezone;
	}

	function setDatetime($aikaP,$format="EN")
	{
		if(is_null($aikaP))
		{
			$this->timestamp = 0;
			$this->datestamp = 0;
			return true;
		}

		if(strtoupper($format)=="EN"){
			if(is_object($aikaP))
			{
				$aikaP = date("Y-m-d H:i:s", $aikaP->timestamp);
			}

			$palat = explode(" ",$aikaP);
			$pvm = isset($palat[0]) ? $palat[0] : '0000-00-00';
			$klo = isset($palat[1]) ? $palat[1] : null;

			$palatPvm = explode("-",$pvm);
			$vuosi = $palatPvm[0];
			$kk = isset( $palatPvm[1] ) ? $palatPvm[1] : null;
			$pv = isset( $palatPvm[2] ) ? $palatPvm[2] : null;

 			$palatKello = explode(":",$klo);
			$tunnit = $palatKello[0];
			$min = isset($palatKello[1]) ? $palatKello[1] : null;
			$sek = isset($palatKello[2]) ? $palatKello[2] : null;

			if(substr_count($pvm,"-")<2 || strlen($vuosi)<4 || strlen($kk)>2 || strlen($pv)>2){
				return false;
			}
		}
		elseif(strtoupper($format)=="SQL"){
			if(is_object($aikaP))
			{
				$aikaP = date("Y-m-d H:i:s", $aikaP->timestamp);
			}

			$pvm=substr($aikaP,0,8);
			$klo=substr($aikaP,8,6);

			$vuosi = substr($pvm,0,4);
			$kk = substr($pvm,4,2);
			$pv = substr($pvm,6,2);

			// ------ Parsitaan kello ja tsekataan samalla onko kelloa annettu -----
			if(!$tunnit = substr($klo,0,2)) $tunnit=0;
			if(!$min = substr($klo,2,2)) $min=0;
			if(!$sek = substr($klo,4,2)) $sek=0;

			$klo="$tunnit:$min:$sek";

			// ------ Tarkistetaan ett� p�iv�m��r� on annettu validissa SQL-formaatissa -------
			if(substr_count($this->pvm,".")>0 || substr_count($this->pvm,"-")>0 || !is_numeric($vuosi) || !is_numeric($kk) || !is_numeric($pv)){
				return false;
			}
		}
		elseif(strtoupper($format)=="FI"){
			if(is_object($aikaP))
			{
				$aikaP = date("d.m.Y H:i:s", $aikaP->timestamp);
			}
			
		 	$palat = explode(" ",$aikaP);
			$pvm = $palat[0];
			$klo = isset($palat[1]) ? $palat[1] : null;

			$palatPvm = explode(".",$pvm);
			$vuosi = $palatPvm[2];
			$kk = $palatPvm[1];
			$pv = $palatPvm[0];

 			$palatKello = explode(":",$klo);
			$tunnit = $palatKello[0];
			$min = isset($palatKello[1]) ? $palatKello[1] : null;
			$sek = isset($palatKello[2]) ? $palatKello[2] : null;

			// ------ Tarkistetaan ett� p�iv�m��r� on annettu validissa FI-formaatissa -------
			if(substr_count($pvm,".")<2 || strlen($vuosi)<4 || strlen($kk)>2 || strlen($pv)>2 || !is_numeric($vuosi) || !is_numeric($kk) || !is_numeric($pv)){
				return false;
			}
		}
		elseif(strtoupper($format)=="DMY"){
	        $vuosi = "20".substr($aikaP,4,2);
	        $kk = substr($aikaP,2,2);
	        $pv = substr($aikaP,0,2);

	        $tunnit = 0;
	        $min = 0;
	        $sek = 0;
		}
		elseif(strtoupper($format)=="LOG"){
		 	$palat = explode(" ",$aikaP);
			$pvm = $palat[0];
			$klo = $palat[1];
			$palatPvm = explode("-",$pvm);
			$vuosi = $palatPvm[2];
			switch($palatPvm[1]){
				case "Jan":
					$kk = "01";
				break;
				case "Feb":
					$kk = "02";
				break;
				case "Mar":
					$kk = "03";
				break;
				case "Apr":
					$kk = "04";
				break;
				case "May":
					$kk = "05";
				break;
				case "Jun":
					$kk = "06";
				break;
				case "Jul":
					$kk = "07";
				break;
				case "Aug":
					$kk = "08";
				break;
				case "Sep":
					$kk = "09";
				break;
				case "Oct":
					$kk = "10";
				break;
				case "Nov":
					$kk = "11";
				break;
				case "Dec":
					$kk = "12";
				break;
			}
			$pv = $palatPvm[0];

 			$palatKello = explode(":",$klo);
			$tunnit = $palatKello[0];
			$min = $palatKello[1];
			$sek = $palatKello[2];
		
			if(strlen($pv)==1) $pv = "0".$pv;
	        if(is_string($pv)) $pv = (int) $pv;
	        if(is_string($tunnit)) $tunnit = (int) $tunnit;
			if(strlen($kk)==1) $kk = "0".$kk;
 			if(is_string($kk)) $kk = (int) $kk;
		}
		elseif(strtoupper($format)=="YMDHIS"){	
			$vuosi = substr($aikaP,0,4);
	        $kk = substr($aikaP,4,2);
	        $pv = substr($aikaP,6,2);
	        $tunnit = substr($aikaP,8,2);
	        $min = substr($aikaP,10,2);
	        $sek = substr($aikaP,12,2);			
		}
		elseif(strtoupper($format)=="TO_DAYS") {
			$this->timestamp = 1;
			$this->datestamp = 0;						
			$this->addInterval( $aikaP-719528 ); // MySQL starts from year 0, PHP from year 1970 -> 719528 days between
			return true;
		}
		elseif(strtoupper($format)=="DATE_W3CF" || strtoupper($format)=='XML' ){
			if(!strpos($aikaP, 'T'))
			{
				$date = $aikaP;
				$time = '00:00:00';
			} 
			else
				list($date, $time) = explode('T',$aikaP);
			
			//-- Date --
			list($vuosi, $kk, $pv) = explode('-', $date);
			
			if ( !$vuosi || !$kk || !$pv )
				return false;
				
			list($tunnit, $min, $sek) = explode(':', substr($time, 0, 8));
		}

		if(!$tunnit) $tunnit=0;
		if(!$min) $min=0;
		if(!$sek) $sek=0;

		if($kk=="00") $kk=0;
		if($pv=="00") $pv=0;
		if($vuosi=="0000") $kk=0;

		if($kk=="0" && $pv=="0" && $kk=="0")
			{
			$this->timestamp=false;
			$this->datestamp=false;
			return true;
		}

		$this->timestamp=mktime((int)$tunnit,(int)$min,(int)$sek,(int)$kk,(int)$pv,(int)$vuosi);
		$this->datestamp=mktime(0,0,0,(int)$kk,(int)$pv,(int)$vuosi);
		return true;

	}

	function getDatetime($stringFormat="Y-m-d H:i:s", $timezone=null){
		$defaultTimezone = "Europe/Helsinki";

		// If we want to use different timezone this time, set it.
		if( strlen($timezone) )
			$_timezone = $timezone;
		else
			$_timezone = $this->timezone;

		// if timezone
		if( $_timezone != $defaultTimezone )
		{
			$defaultTimezone = date_default_timezone_get();
			date_default_timezone_set($_timezone);
		}

		if( !$this->timestamp || $this->timestamp < 0 )
		{
			$_date = false;
		}
		else
		{
			$_date = date($stringFormat,$this->timestamp);
		}

		// return timezone to default.
		if( $_timezone != $defaultTimezone )
			date_default_timezone_set($defaultTimezone);

		return $_date;
	}

        function addInterval($days,$months="0",$years="0",$hours="0",$minutes="0",$seconds="0"){

		$months = round($months, 2);
		if($months<1 && $months>0){
			$paivia = 30*$months;
			$days = $days+ceil($paivia);
		}

		if($this->timestamp<=0) return false;
		$current = getdate($this->timestamp);
		if(!is_numeric($days)) $days=0;
		if(!is_numeric($months)) $months=0;
		if(!is_numeric($years)) $years=0;
		if(!is_numeric($hours)) $hours=0;
		if(!is_numeric($minutes)) $minutes=0;
		if(!is_numeric($seconds)) $seconds=0;
		$this->timestamp=mktime($current["hours"]+$hours,$current["minutes"]+$minutes,$current["seconds"]+$seconds,$current["mon"]+$months,$current["mday"]+$days,$current["year"]+$years);
	}

	function getToDays(){
		if(!$this->timestamp ||$this->timestamp<0) return false;
		if(!function_exists("toDays")) require_once("math.functions.inc");
		return toDays($this->getDatetime(),"en");
	}

	function getMinutesToPresent($allowNegative = false) {
		if(!$this->timestamp ||$this->timestamp<0) return false;
		 $difInMinutes = ($this->timestamp - time()) / 60;
		 if (!$allowNegative && $difInMinutes < 0) $difInMinutes=0;
		 return round($difInMinutes);
	}

	function setToPreviousSunday()
	{
		if(!$this->timestamp ||$this->timestamp<0) return false;
		$this->addInterval(-$this->getDatetime('N'),0,0,23,59,59 );
	}

	function setToPreviousMonday(){
		if(!$this->timestamp ||$this->timestamp<0) return false;
		$this->setDatetime(date("Y-m-d H:i:s",strtotime('last Monday', $this->timestamp)));
		return true;
	}

	function setToPreviousWeekDay($weekDay)
	{
		if(!$this->timestamp ||$this->timestamp < 0) return false;

		$this->setDatetime(date("Y-m-d H:i:s",strtotime("last {$weekDay}", $this->timestamp)));

		return true;
	}

	function setToPreviousMonthsFirstDay(){
		$this->setDatetime("1.".$this->getDatetime("n.Y"),"FI");
		$this->addInterval(0,-1);
		return true;
	}

	function isMonthsLastDay(){
		if(date("t",$this->timestamp)==$this->getDatetime("d")){
			return true;
		}
		else return false;
	}	

        function setToPreviousMonthsLastDay()
	{
		$daysInThisMonth = date("t", $this->timestamp);

		if($daysInThisMonth == date("d", $this->timestamp))
		{
			$this->addInterval(-$daysInThisMonth);
		}
		else
		{
			$this->addInterval(0, -1);
		}

		$daysInThisMonth = date("t", $this->timestamp);
		$this->setDatetime($daysInThisMonth . "." . $this->getDatetime("n.Y"), "FI");

		return true;
	}

	function setToThisMonthsLastDay(){
		$daysInThisMonth = date("t",$this->timestamp);
		$this->setDatetime($daysInThisMonth.".".$this->getDatetime("n.Y")." 23:59:59","FI");		
		return true;
	}

        function setToThisMonthsFirstDay(){
		$this->setDatetime("1.".$this->getDatetime("n.Y"),"FI");
		return true;
	}
	
	function getMonth(){
		return date("n",$this->timestamp);
	}

        function getYear(){
		return date("Y",$this->timestamp);
	}

	function getDay(){
		return date("j",$this->timestamp);
	}

	function getHour(){
		return date("H",$this->timestamp);
	}

	function getWeek(){
		return date("W",$this->timestamp);
	}

	function getMinutes(){
		return date("i",$this->timestamp);
	}

	function getSeconds(){
		return date("s",$this->timestamp);
	}
	
	function isLeapYear(){
		if((($this->getYear() % 4) == 0) && ((($this->getYear() % 100) != 0) || (($this->getYear() % 400) == 0)))
			return true;
		else return false;
	}
	
	function getGmtDatetime($stringFormat="Y-m-d H:i:s"){
		if(!$this->timestamp ||$this->timestamp<0) return false;
		return gmdate($stringFormat,$this->timestamp);
	}

        function setToNextQuarterOfHour(){
		if(!$this->timestamp ||$this->timestamp<0) return false;
		$this->setDatetime($this->getDatetime("d.m.Y H:i:00"),"fi"); // set seconds to full minute
		$minutes = $this->getMinutes();
		
		if($minutes%15==0)
			$minutes++; // If full quarter, add one minute for the next loop
			
		while($minutes%15 != 0){
			$minutes++;
		}
		$this->addInterval(0,0,0,0,$minutes-$this->getMinutes());
	}

	function setToNow()
	{
		return $this->setDatetime(date('Y-m-d H:i:s'));
	}

        function setToQuarterStart()
	{
		$this->checkDateIsSet();
		$this->setToThisMonthsFirstDay();
		$this->addInterval(0,-($this->getMonth() % 3)); //Go back 
	}

	function setToPreviousQuarterEnd(){
		$this->setToQuarterStart();
		$this->addInterval(0,0,0,0,0,-1); //1 second back
	}


	function setToYearStart()
	{
		$this->checkDateIsSet();
		$this->setDatetime($this->getYear().'-01-01');
	}

	function setToPreviousYearEnd()
	{
		$this->setToYearStart();
		$this->addInterval(0,0,0,0,0,-1); //1 second back
	}

	private function checkDateIsSet()
	{
		if(!$this->timestamp)
		{
			trigger_error("coreDatetime object was not initialized, using now", E_USER_NOTICE);
			$this->setToNow();
		}
	}

	public static function now()
	{
		$instance = new self;
		$instance->setToNow();
		return $instance;
	}

	function __destruct(){
		unset($this->timestamp);
		unset($this->datestamp);
	}
}