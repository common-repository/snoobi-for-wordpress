<?php
/**
 * Takes care of fetching and showing the web analytics data
 * @package Snoobi for Wordpress
 **/
require_once("SnoobiDatetime.php");

class SnoobiReport
{

	public function __construct()
	{
	}

	/**
	 * Method for drawing the report UI
	 *
	 */
	public function drawUi()
	{

            ?>
<div id="wrap">

    <?php
    if( strlen( get_option('snoobi_account') )<2 || SnoobiConfig::oauthOk()===false )
    {
        echo '<h3>You need to configure some fancy settings before wieving reports dude. <a href="admin.php?page=snoobi_wp_config" class="button add-new-h2"> Go to settings page</a></h3>';
        return;
    }

    $SnoobiFunction = new SnoobiFunctions();

    // Check if account tracking has started and we have at least one visit
    $total = $SnoobiFunction->getTotalVisits();
    
    if( $total===null || !isset( $total->visits->{"$"} ) || $total->visits->{"$"}==0 )
    {
        echo '<table align="center" width="100%" border="1">';
        echo '<tr>';
        echo '<td align="left" width="100">';
        echo '<img src="'.get_site_url().'/wp-content/plugins/snoobi-for-wordpress/img/chart_icon.jpg">';
        echo '</td>';
        echo '<td align="left">';
        echo '<h2>Whoops, we couldn\'t find any visits yet!</h2>';

        if( get_option('snoobi_disabled')==1 )
        {
            echo '<p>Your Snoobi tracking seems to be disabled. Please go to<a href="?page=snoobi_wp_config"> settings page </a>and enable Snoobi tracking and click around your blog / site. Soon after that you should see some numbers in your web analytics report.</p>';
        }
        else
        {
            echo '<p>Your Snoobi tracking is however in place and active. You should see some numbers in your report soon. You can also click around your blog / site to generate some traffic to be shown in web analytics report.</p>';
        }
        
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        return;
    }
    
    // Check if the interval is set
    if( isset( $_GET['sd'] ) )
    {
        $SnoobiFunction->startDate->setDatetime($_GET['sd'], "FI" );
    }
    else
    {
        $SnoobiFunction->startDate->setDatetime( date("Y-m-d", mktime(date("H"), date("i"), date("s"), date("m")-1, date("j"), date("Y") ) ), "EN" ); // Defaults one month back
    }

    if( isset( $_GET['ed'] ) )
    {
        $SnoobiFunction->endDate->setDatetime($_GET['ed'], "FI" );
    }
    else
    {
        $SnoobiFunction->endDate->setDatetime( date("Y-m-d"), "EN" );
    }

    echo '<table align="center" width="100%" border="1">';
    echo '<tr>';
    echo '<td align="left" width="100">';
    echo '<img src="'.get_site_url().'/wp-content/plugins/snoobi-for-wordpress/img/chart_icon.jpg">';
    echo '</td>';
    echo '<td align="left">';
    echo '<form name="SnoobiChangeInterval" id="SnoobiChangeInterval" method="GET">';
    echo '<h2 style="display:inline">Web analytics report - '.get_option('snoobi_account').' - <input type="text"  class="addDatepicker" style="display:inline; font-size:18px; width:100px" name="sd" value="'.$SnoobiFunction->startDate->getDatetime("j.n.Y").'"> - <input type="text" class="addDatepicker" style="display:inline; font-size:18px; width:100px" name="ed"  value="'.$SnoobiFunction->endDate->getDatetime("j.n.Y").'"><input class="button" type="submit" style="width: 120px; margin-bottom: 10px; margin-left: 10px;" value="Apply new interval"></h2>';
    echo '<input type="hidden" name="page" value="'.$_GET['page'].'">';
    echo '</form>';
    echo '</td>';
    echo '<td align="left" width="220">';

    echo '<img style="margin-bottom: 5px;" src="'.get_site_url().'/wp-content/plugins/snoobi-for-wordpress/img/small_logo.png">';
    echo '</td>';

    echo '</tr>';
    echo '</table>';

    ?>
<table class="wp-list-table widefat fixed" width="90%" align="center">
    <thead>
    <tr>
        <th scope="col" width="90%">
            <span>Totals</span></th>
        <th scope="col"></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td align="center">

            <div id="chart-container-1"></div>
        </td>
    </tr>
    </tbody>
</table>
    <br />
<table class="wp-list-table widefat fixed" width="90%" align="center">
    <thead>
    <tr>
        <th scope="col" width="95%">
            <span>Entries</span></th>
        <th scope="col"></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td align="center">
            <div id="pie-container"></div>
        </td>
    </tr>
    </tbody>
</table>

<br />
<table class="wp-list-table widefat fixed" width="90%" align="center">
    <thead>
    <tr>
        <th scope="col">
            <span>The most popular blogs / pages</span></th>
            <th scope="col">
                <span>Visits</span>
            </th>
            <th scope="col">
                <span>Bounce rate</span>
            </th>
    </tr>
    </thead>
    <tbody>

    <?php
        $pages = $SnoobiFunction->getPages();

        if($pages!==null)
        {

            foreach($pages as $page)
            {
    ?>
    <tr>
        <td align="left">
            <?php echo $page->{'@name'}; ?>
        </td>
        <td align="left">
            <?php echo $page->visits->{'$'}; ?>
        </td>
        <td align="left">
            <?php echo $page->bounceRate->{'$'}; ?> %
        </td>
    </tr>
    <?php
            }
        }
        else
        {
            ?>
    <tr>
        <td align="left">
            There seems to be no pageviews during the selected interval
        </td>
    </tr>
    <?php

        }
    ?>
    </tbody>
</table>

<br />
<table class="wp-list-table widefat fixed" width="90%" align="center">
    <thead>
    <tr>
        <th scope="col">
            <span>The most popular search phrases</span></th>
            <th scope="col">
                <span>Visits</span>
            </th>
            <th scope="col">
                <span>Bounce rate</span>
            </th>
            <th scope="col">
                <span>Avg. pageviews / visit</span>
            </th>
    </tr>
    </thead>
    <tbody>

    <?php
        $searchterms = $SnoobiFunction->getSearchterms();
        
        if(is_array($searchterms) && count($searchterms)>0)
        {
            foreach($searchterms as $term)
            {
    ?>
    <tr>
        <td align="left">
            <?php echo $term->{'@name'}; ?>
        </td>
        <td align="left">
            <?php echo $term->visits->{'$'}; ?>
        </td>
        <td align="left">
            <?php echo $term->bounceRate->{'$'}; ?> %
        </td>
        <td align="left">
            <?php echo $term->pageViewsPerVisit->{'$'}; ?>
        </td>

    </tr>
    <?php
            }
        }
        else
        {
            ?>
        
    <tr>
        <td align="left">
            No entries from seearch engines during the selected interval
        </td>
    </tr>
<?php
        }
?>
    </tbody>
</table>

</div>
<?php
        }


        public static function initJqueries(){
                $SnoobiFunction = new SnoobiFunctions();

                // Check if the interval is set
                if( isset( $_GET['sd'] ) )
                {
                    $SnoobiFunction->startDate->setDatetime($_GET['sd'], "FI" );
                }
                else
                {
                    $SnoobiFunction->startDate->setDatetime( date("Y-m-d", mktime(date("H"), date("i"), date("s"), date("m")-1, date("j"), date("Y") ) ), "EN" ); // Defaults one month back
                }

                if( isset( $_GET['ed'] ) )
                {
                    $SnoobiFunction->endDate->setDatetime($_GET['ed'], "FI" );
                }
                else
                {
                    $SnoobiFunction->endDate->setDatetime( date("Y-m-d"), "EN" );
                }

                $visitors   = $SnoobiFunction->getVisitors();
                $visits     = $SnoobiFunction->getVisits();
                $pageviews  = $SnoobiFunction->getPageviews();

                $entry = $SnoobiFunction->getEntries();

                $xAxis = array();
                $yAxisVisitors = array();

                if($visitors!==null)
                {

                    foreach($visitors as $key=>$data)
                    {
                        array_push($xAxis,$data->{'@startDate'});
                        if($data->visitors->{'$'}){
                            array_push($yAxisVisitors,$data->visitors->{'$'});
                        }
                        else
                        {
                            array_push($yAxisVisitors,0);
                        }
                    }
                }

                $yAxisVisits = array();
                //debug($visits);

                if($visits!==null)
                {
                    foreach($visits as $key=>$data)
                    {
                        if($data->visits->{'$'}){
                            array_push($yAxisVisits,$data->visits->{'$'});
                        }
                        else
                        {
                            array_push($yAxisVisits,0);
                        }
                    }
                }

                $yAxisPageviews = array();

                if($pageviews!==null)
                {
                    foreach($pageviews as $key=>$data)
                    {
                        if($data->pageViews->{'$'}){
                            array_push($yAxisPageviews,$data->pageViews->{'$'});
                        }
                        else
                        {
                            array_push($yAxisPageviews,0);
                        }
                    }

                ?>
               <script type="text/javascript">

              var chart1;
              var pie2;
              $(document).ready(function() {

                  chart1 = new Highcharts.Chart({
                     chart: {
                        renderTo: 'chart-container-1',
                        defaultSeriesType: 'line'
                     },
                     title: {
                        text: 'Here\'s your KPIs. Enjoy!'
                     },
                     xAxis: {
                        type: 'datetime',
                        dateTimeLabelFormats:
                        {
                            day: ' %d. %b '
                        }
                     },
                     yAxis: {
                        title: {
                           text: '',
                        },
                        min: 0
                     },
                     series: [{
                        name: 'Visitors',
                        data: <?php echo json_encode( $yAxisVisitors ) ;?>,

                        pointStart: Date.UTC(<?php echo $SnoobiFunction->startDate->getDatetime("Y"); ?>,<?php echo $SnoobiFunction->startDate->getDatetime("n")-1; ?>,<?php echo $SnoobiFunction->startDate->getDatetime("d"); ?>),
                        pointInterval: 24 * 3600 * 1000 // one day
                     },
                     {
                        name: 'Visits',
                        data: <?php echo json_encode( $yAxisVisits ) ;?>,

                        pointStart: Date.UTC(<?php echo $SnoobiFunction->startDate->getDatetime("Y"); ?>,<?php echo $SnoobiFunction->startDate->getDatetime("n")-1; ?>,<?php echo $SnoobiFunction->startDate->getDatetime("d"); ?>),
                        pointInterval: 24 * 3600 * 1000 // one day
                     },
                     {
                        name: 'Pageviews',
                        data: <?php echo json_encode( $yAxisPageviews ) ;?>,

                        pointStart: Date.UTC(<?php echo $SnoobiFunction->startDate->getDatetime("Y"); ?>,<?php echo $SnoobiFunction->startDate->getDatetime("n")-1; ?>,<?php echo $SnoobiFunction->startDate->getDatetime("d"); ?>),
                        pointInterval: 24 * 3600 * 1000 // one day
                     }
                    ]
                  });

                   pie1 = new Highcharts.Chart({
                      chart: {
                         renderTo: 'pie-container',
                         margin: [50, 0, 0, 0],
                         plotBackgroundColor: 'none',
                         plotBorderWidth: 0,
                         plotShadow: false
                      },
                      title: {
                         text: 'Entries to your site / blog'
                      },
                      subtitle: {
                         text: ''
                      },
                      tooltip: {
                         formatter: function() {
                            return '<b>'+ this.series.name +'</b><br/>'+
                               this.point.name +': '+ Math.round(this.percentage*10)/10 +' %';
                         }
                      },
                       series: [{
                         type: 'pie',
                         name: 'Entries',
                         innerSize: '15%',
                         data: [
                            { name: 'Referring websites', y: <?php echo $entry->links->visits->{'$'}; ?>, color: '#4572A7' },
                            { name: 'Advertisement', y: <?php echo $entry->ads->visits->{'$'}; ?>, color: '#AA4643' },
                            { name: 'Search engines', y: <?php echo $entry->searchEngines->visits->{'$'}; ?>, color: '#89A54E' },
                            { name: 'Social media', y: <?php echo $entry->communities->visits->{'$'}; ?>, color: '#80699B' },
                            { name: 'Direct', y: <?php echo $entry->typingUrl->visits->{'$'}; ?>, color: '#E6A74B' },
                         ],
                         dataLabels: {
                            enabled: true,
                            color:  '#000000',
                            connectorColor: '#000000'
                         }
                      }]
                  });

                 jQuery('.addDatepicker').datepicker({
                    showButtonPanel: true,
                    dateFormat: 'd.m.yy',
                    maxDate: 'd.m.yy'
                 });


              });
              </script>
              <?php
              /* No pageviews during selcted interval */
                }
        }
}