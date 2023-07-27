<LINK rel="stylesheet" type="text/css" href="style.css">
<?php

$server= "localhost";
$user= "calendar";
$database= "calendar";

/* Accessing MYSQL-Server */

MYSQL_CONNECT($server, $user, $password) or die ( "<H3>Server unreachable</H3>");
MYSQL_SELECT_DB($database) or die ( "<H3>Database non existent</H3>");

//------------------------------------------------------------
//	Set the first and last days of month
//------------------------------------------------------------

$iMonth = trim($_GET["month"]);
if ($iMonth > 12 or $iMonth < 1){ $iMonth = date('n'); }

$iYear = trim($_GET["year"]);
if ($iYear > 2010 or $iYear < 2005){ $iYear = date('Y'); }

$iCalendarID = trim($_GET["calid"]);

//find the first day of month
$datFirstDayOfMonth = $iMonth . "/1/" . $iYear;
$iFirstDayOfMonth = date('w', strtotime($datFirstDayOfMonth));

//add one month
//$datOneMonthAhead = DateAdd("m", 1, datFirstDayOfMonth)

//find the days in month
$iDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $iMonth, $iYear);

//find the last day of month
$datLastDayOfMonth = $iMonth . "/" . $iDaysInMonth . "/" . $iYear;
$iLastDayOfMonth = date('w', $datLastDayOfMonth);

//find number of days in first and last weeks
$iDaysInFirstWeek = 7 - $iFirstDayOfMonth;
$iDaysInLastWeek = $iLastDayOfMonth; //iLastDayOfMonth is an integer representation of the 

//find number of middle weeks
$iDaysInMiddleWeeks = $iDaysInMonth - $iDaysInFirstWeek - $iDaysInLastWeek;
$iMiddleWeeks = $iDaysInMiddleWeeks/7;
$iWeeksInMonth = $iMiddleWeeks + 2;

$iCellHeight = 90 /$iWeeksInMonth;

print "<style>\n";
print ".DAYCELLS\n";
print "{HEIGHT: $iCellHeight%;}\n";
print "</style>\n";
//------------------------------------------------------------
//	Done setting days
//------------------------------------------------------------

?>
<html>
<head>
<title>Event Calendar</title>
<script language="Javascript">
<!--
function launchDetails(i){
	var windowURL = "events.php?mode=view&eventid=" + i;
	var windowName = "events";
	var windowFeatures = "menubar=no,height=500,width=600,scrollbars=yes"
	window.open(windowURL, windowName, windowFeatures);
	return;
}
function launchCreateRequest(){
	var windowURL = "events.php?mode=create&calid=<?php print $iCalendarID; ?>";
	var windowName = "events";
	var windowFeatures = "menubar=no,height=500,width=600,scrollbars=yes"
	window.open(windowURL, windowName, windowFeatures);
	return;
}
function launch(url, options){
	var windowURL = url;
	var windowName = "childwindow";
	var windowFeatures = options;
	//var windowFeatures = "menubar=no,height=500,width=600,scrollbars=yes"
	window.open(windowURL, windowName, windowFeatures);
	return;
}
function changeCalendar(cbo){
	window.location.href = "calendarview.php?<?php print "month=$iMonth&year=$iYear"; ?>&calid=" + cbo.value;
}
// -->
</script>
</head>
<body onload="moveEvents();">
<?php
//Response.Write "<div class=WATERMARK id=MonthWatermark>" & MonthName(iMonth) & "</div>"
print "<table width=\"100%\"><tr>";
//------------------------------------------------------------
//	List available calendars
//------------------------------------------------------------
$sqlCal = "SELECT CalendarID, CalendarName FROM Calendar";
$rsCal=MYSQL_QUERY($sqlCal);

//default is all calendars - not the first calendar
//If iCalendarID = "" Then iCalendarID = rsCal.Fields("CalendarID").Value

print "<td>&nbsp;";

//write calendar drop down
print "<a href=\"#\" onclick=\"moveEvents();\">Calendar Name:</a>";
print "<select name=CalendarID onchange=\"return changeCalendar(this);\">";
print "<option value=00>All Calendars</option>";

while($rowCal  =  mysql_fetch_assoc($rsCal)){
	$tmpCalendarID = $rowCal['CalendarID'];
	$sSelected = "";
    if ($iCalendarID == $tmpCalendarID){ $sSelected = " SELECTED"; }
    $sCalendarName = $rowCal['CalendarName'];
    print "<option value=$tmpCalendarID $sSelected>$sCalendarName</option>";
}

//mysql_free_result($rowCal);


print "</select>";
print "</td>";
print "<td>&nbsp;";
print "</td>";

//write Create Request
print "<td>&nbsp;";
//If Session("ValidUser") = 1 Then
print "<a href=# style=\"text-decoration:none;color:black\" onclick=\"return launchCreateRequest();\" onmouseover=\"this.style.color='blue'\" onmouseout=\"this.style.color='black'\">Create Request</a>";
//End If
print "</td>";

//write Printable Version
print "<td>&nbsp;";
print "<a target=\"_new\" href=\"printablecalendar.php?month=$iMonth&year=$iYear&calid=$iCalendarID\" style=\"text-decoration:none;color:black\" onmouseover=\"this.style.color='blue'\" onmouseout=\"this.style.color='black'\">Printable Version</a>";
print "</td>";

//write year
print "<td>&nbsp;";
$iLastYear = $iYear - 1;
$iNextYear = $iYear + 1;
print "<a href=\"calendarview.php?month=$iMonth&year=$iLastYear\" style='text-decoration:none'><<<</a>";
print "Year: $iYear";
print "<a href=\"calendarview.php?month=$iMonth&year=$iNextYear\" style='text-decoration:none'>>>></a>";
print "</td>";

//write Page Info
print "<td>&nbsp;";
print "<a href=# style=\"text-decoration:none;color:black\" onclick=\"return launch('pageinfo.php', 'menubar=no,height=500,width=600,scrollbars=no');\" onmouseover=\"this.style.color='blue'\" onmouseout=\"this.style.color='black'\">Page Info</a>";
print "</td>";
print "</tr></table>";
print "<hr>";

//------------------------------------------------------------
//	Write navbar (month names)
//------------------------------------------------------------
print "<table class=MONTHS>";
print "<tr>";

for ($i=1; $i <= 12; $i++){
    $monthname = monthname($i);
    print "<td>";
    if ($i == $iMonth){
      print "<span class=MONTHNAMES style='color:red'>";
      print $monthname;
      print "</span>";
      	
    } else {
      print "<a href=\"calendarview.php?month=$i&year=$iYear&calid=$iCalendarID\" onmouseover=\"this.className='MONTHNAMESBOLD';\" onmouseout=\"this.className='MONTHNAMES';\" class=MONTHNAMES>";
      print $monthname;
      print "</a>";
	}
	print "</td>";
}
print "</tr>";
print "</table>";


print "<hr>";

//------------------------------------------------------------
//	Write day names
//------------------------------------------------------------
print "<table class=BASETABLE id=calendartable onresize=\"moveEvents();\">";
print "<tr id=weekdayrow>";
for ($i = 1; $i <= 7; $i++){
    $dayname = dayname($i);
	print "<td class=DAYNAMES>$dayname</td>";
}
print "</tr>";

$iDay = 1;

//write each row
for ($i = 1; $i <= $iWeeksInMonth; $i++){
    print "<tr id=week$i>";
    switch($i){
      case "1":
        RenderFirstWeek(); break;
      case "2":
        RenderMiddleWeek(); break;
      case "3":
        RenderMiddleWeek(); break;
      case "4":
        RenderMiddleWeek(); break;
      case "5":
        if ($iMiddleWeeks == 5){
          RenderMiddleWeek();
        } else {
          RenderLastWeek();
        }
        break;
      case "6":
        if ($iMiddleWeeks == 5){
          RenderMiddleWeek();
        } else {
          RenderLastWeek();
        }
        break;
    }
        print "</tr>";
}

print "</table>";

//------------------------------------------------------------
//	Get the events for this calendar
//------------------------------------------------------------
//object to hold levels
//Set d = CreateObject("Scripting.Dictionary")
// associative array
$d = array();

//$sBetween = "'$datFirstDayOfMonth' AND '$datLastDayOfMonth 11:59 PM'";
$ts1 = strtotime($datFirstDayOfMonth);
$ts2 = strtotime($datLastDayOfMonth);
$ts3 = strtotime($datLastDayOfMonth) + 86399;
$sBetween = "FROM_UNIXTIME($ts1) AND FROM_UNIXTIME($ts3)";

if ($iCalendarID == "" OR $iCalendarID == "00"){
	$sqlEvents = "SELECT DISTINCT e.EventID, e.EventName, e.EventDescr, e.EventStart, e.EventEnd, e.ConfidenceFactor, e.Status FROM Calendar c LEFT JOIN Event e ON c.CalendarID = e.CalendarID WHERE e.EventStart BETWEEN $sBetween OR e.EventEnd BETWEEN $sBetween OR e.EventStart < FROM_UNIXTIME($ts1) AND e.EventEnd > FROM_UNIXTIME($ts2)";
 } else {
    $sqlEvents = "SELECT DISTINCT e.EventID, e.EventName, e.EventDescr, e.EventStart, e.EventEnd, e.ConfidenceFactor, e.Status FROM Calendar c LEFT JOIN Event e ON c.CalendarID = e.CalendarID WHERE c.CalendarID = $iCalendarID AND (((e.EventStart BETWEEN $sBetween) OR (e.EventEnd BETWEEN $sBetween)) OR (e.EventStart < FROM_UNIXTIME($ts1) AND e.EventEnd > FROM_UNIXTIME($ts2)))";
}

//catch december events
if ($iMonth == 12){
    $iMonth2 = 1;
	$iYear2 = $iYear + 1;
} else {
	$iMonth2 = $iMonth + 1;
	$iYear2 = $iYear;
}

// get the events
$rsEvents=MYSQL_QUERY($sqlEvents);

$sScript = "";

if (mysql_num_rows($rsEvents) > 0){

//write out events
while ($rowEvents = mysql_fetch_assoc($rsEvents)){
    $iEventID = $rowEvents['EventID'];
	$sEventName = $rowEvents['EventName'];
	$sEventStart = $rowEvents['EventStart'];
	$sEventEnd = $rowEvents['EventEnd'];

	//first day of event
	$iStartDay = date("d", strtotime($sEventStart));
	//first week of event

    $iStartWeek = DateDiff("ww", $datFirstDayOfMonth, $sEventStart) + 1;

	//number of days event lasts
	$ts1 = strtotime($sEventStart);
	$ts2 = strtotime($sEventEnd);
	$iDays = ($ts2 - $ts1) / 86400;
	
	//color for confidence factor
	$sColor = GetColor($rowEvents['ConfidenceFactor']);

    /* TODO
    If rsEvents.Fields("Status").Value = "Not Approved" Then
		'set another color
		sColor = "blue"
	End If
    */
	//------------------------------------------------------------
	//	generated javascript
	//------------------------------------------------------------
	//this is javascript with the id of this event
	$sObject = "document.all.item(\"Event$iEventID\").style";
	//this returns the left of the day
	$sLeft = "getLeft('day$iStartDay');";
	//this returns the width of one cell * number of days of event
	$iDaysPlusOne = $iDays + 1;
	$sWidth = "getWidth('day$iStartDay', $iDaysPlusOne);";

    if ((strtotime($sEventStart) - strtotime($datFirstDayOfMonth)) < 0){
	//If DateDiff("d", datFirstDayOfMonth, sEventStart) < 0 Then
		//rewrite for custom left - different month
		$sLeft = "getLeft('previousday$iStartDay');";
		//rewrite for custom width - different month
		$sWidth = "getWidth('previousday$iStartDay', $iDaysPlusOne);";
    }

	//this returns the top of the row
    $iLevel = Check($sEventStart, $sEventEnd);
    $ssTop = "top_of_table + getTopOfRow('week$iStartWeek') + (number_height + (event_height * $iLevel));";

    AddToList($sEventStart, $sEventEnd, $iLevel);

    //javascript for hyperlink
    $sJScript = "onmouseover=\"this.style.cursor='hand';this.style.color='blue'\" onmouseout=\"this.style.cursor='default';this.style.color='black'\" onclick=\"return launchDetails('$iEventID')\"";

    if ($iDays == 0){
		//write out one day event
		print "<span style='background-color:$sColor' id=Event$iEventID class=EVENT $sJScript>$sEventName</span>";
		$sScript = $sScript . $sObject . ".top=" . $ssTop . $sObject . ".left=" . $sLeft . $sObject . ".width=" . $sWidth;
	} else {
		//more than one, check to see if it spans over multiple calendar weeks
		$iWeekSpan = DateDiff("ww", $sEventStart, $sEventEnd);

		if ($iWeekSpan == 0){
            //write out multiple day event contained in one week
			print "<span style='background-color:$sColor' id=Event$iEventID class=EVENT $sJScript>$sEventName</span>";
			$sScript = $sScript . $sObject . ".top=" . $ssTop . $sObject . ".left=" . $sLeft . $sObject . ".width=" . $sWidth;
		} else {
			$iBalance = 0;
			//render events in n number of weeks
            for ($iW = 0; $iW <= $iWeekSpan; $iW++){
             	//is the start week in this month?
                if ($iStartWeek < 1){
                    $iStartWeek++;
                    $iBalance++;
                } else {
					//rewrite for custom top
                    $iTemp = $iStartWeek + $iW - $iBalance;
					$ssTop = "top_of_table + getTopOfRow('week$iTemp') + (number_height + (event_height * $iLevel));";
					$iTemp = "";
					
					//custom object name
					$sObject = "document.all.item(\"Event$iEventID-$iW\").style";

					//quit processing if in to next month

                    if (($iStartWeek + $iW - $iBalance) > $iWeeksInMonth){ break; }

                    switch($iW){
                    	case 0:
							//first part of event
							print "<span style='border-right:none;background-color:$sColor' id=Event$iEventID-$iW class=EVENT $sJScript>$sEventName->>></span>";
							//rewrite for custom width - 8 - daynumber
							$iDays2 = 6 - date('w', strtotime($sEventStart));
							$iDays3 = $iDays2 + 1;
							$sWidth = "getWidth('day$iStartDay', $iDays3);";
							$sScript = $sScript . $sObject . ".top=" . $ssTop . $sObject . ".left=" . $sLeft . $sObject . ".width=" . $sWidth;
							break;
						case $iWeekSpan:
							//last part of event
							print "<span style='border-left:none;background-color:$sColor' id=Event$iEventID-$iW class=EVENT $sJScript><<<-$sEventName</span>";
							//rewrite for custom width - daynumber
							$iDays2 = date('w', strtotime($sEventEnd)) + 1;
       						$sWidth = "getWidth('day$iStartDay', $iDays2);";
							//rewrite for custom left
							$iStartDay2 = date('j', strtotime($sEventEnd)) - $iDays2 + 1;
							
							if ($iStartDay2 < 0){
                                //rewrite for custom left
								$iStartDay2 = date('w', (DateAdd("d", ($iStartDay2 - 1), $sEventEnd)));

                                    $tsFirstDay = strtotime($datFirstDayOfMonth);
                                    $tsSecondDay = strtotime($sEventStart);
                                    if (($tsSecondDay - $tsFirstDay) < 0){
										$bLeftDontChange = 1;
										$sLeft = "getLeft('previousday$iStartDay2');";
										//rewrite for custom width - different month
										$iTemp = $iDays + 1;
										$sWidth = "getWidth('previousday$iStartDay', $iTemp);";
									}
							}
							
							if ($bLeftDontChange <> 1){
                                $sLeft = "getLeft('day$iStartDay2');";
    							$bLeftDontChange = 0;
                                $sScript = $sScript . $sObject . ".top=" . $ssTop . $sObject . ".left=" . $sLeft . $sObject . ".width=" . $sWidth;
                            }
                            break;
						default:
							//middle part of event
							print "<span style='border-left:none;border-right:none;background-color:$sColor' id=Event$iEventID-$iW class=EVENT $sJScript><<<-$sEventName->>></span>";
							//rewrite for custom width - always 7 because this is a middle week
							$iDays2 = 7;
							$sWidth = "getWidth('day$iStartDay', $iDays2);";
							//rewrite for custom left - any Sunday of month

                            $iStartDay2 = date('w', (DateAdd("d",8 - $iFirstDayOfMonth , $datFirstDayOfMonth)));
							
							$sLeft = "getLeft('day$iStartDay2');";
							$sScript = $sScript . $sObject . ".top=" . $ssTop . $sObject . ".left=" . $sLeft . $sObject . ".width=" . $sWidth;
					}
				}
			}
		}
	}
//end while loop thru records
}
//end big if
}
?>
<script language="Javascript">
<!--
function moveEvents(){
	var top_of_table = document.all.item("calendartable").offsetTop;
	var number_height = document.all.item("daynumber1").offsetHeight;
	var event_height = 13;
	number_height = number_height - event_height;
	//document.all.item("calendartable").style.height = 700;
	//var t_height = screen.availHeight;
	//document.all.item("MonthWatermark").style.top = t_height / 2;

	<?php print $sScript; ?>
}
function getTopOfRow(week){
	var top_of_row = document.all.item(week).offsetTop;
	return top_of_row;
}
function getLeft(x){
	var xx = document.all.item(x);
	return xx.offsetLeft + 10;
}
function getWidth(x, days){
	var xx = document.all.item(x);
	var a = (days * xx.clientWidth);
	return a;
}
//-->
</script>
</body>
</html>

<?php

// PHP FUNCTIONS

function RenderFirstWeek(){
    global $iDay, $iFirstDayOfMonth, $datFirstDayOfMonth;
    for ($ii = 1; $ii <= 7; $ii++){
        if ($iFirstDayOfMonth+1 > $ii){
			//write previous months days
			$tsFirstDay = strtotime($datFirstDayOfMonth);
			$seconds = ($iFirstDayOfMonth + 1 - $ii) * 86400;
			$pDay = date('j', $tsFirstDay - $seconds);
			//$pDay = date('j', (DateAdd("d",($ii - $iFirstDayOfMonth),$datFirstDayOfMonth)));
            print "<td class=FILLCELLS valign=top id=previousday$pDay><div class=DAYNUMBER id=previousdaynumber$pDay>$pDay</div></td>";
		} else {
			print "<td class=DAYCELLS valign=top id=day$iDay><div class=DAYNUMBER id=daynumber$iDay>$iDay</div></td>";
			$iDay++;
		}
	}
}

function RenderMiddleWeek(){
    global $iDay;
	for ($ii = 1; $ii <=7; $ii++){
		print "<td class=DAYCELLS valign=top id=day$iDay><div class=DAYNUMBER>$iDay</div></td>";
		$iDay++;
	}
}

function RenderLastWeek(){
    global $iDay, $iDaysInMonth;
	for ($ii = 1; $ii <=7; $ii++){
        if ($iDaysInMonth >= $iDay){
			print "<td class=DAYCELLS valign=top id=day$iDay><div class=DAYNUMBER>$iDay</div></td>";
        } else {
			//write post months days
            $pDay = $iDay - $iDaysInMonth;
			print "<td class=FILLCELLS valign=top id=previousday$pDay><div class=DAYNUMBER id=previousdaynumber$pDay>$pDay</div></td>";
        }
		$iDay++;
	}
}

function GetColor($ConfidenceFactor){
    switch($ConfidenceFactor){
        case "High":
            $GetColor = "lightgreen"; break;
		case "Medium":
			$GetColor = "yellow"; break;
		case "Low":
			$GetColor = "red"; break;
		case "None":
			$GetColor = "white"; break;
        default:
            $GetColor = "white"; break;
    }
    return $GetColor;
}
function Check($sEventStart, $sEventEnd){
    global $d;
	$iLevelCheck = 1;
    for ($ii = $sEventStart; $ii <= $sEventEnd; $ii++){
        $Level = $d[$ii];
        if ($Level >= $iLevelCheck){
            $iLevelCheck = $Level + 1;
        }
	}
	return $iLevelCheck;
}
function AddToList($sEventStart, $sEventEnd, $Level){
    global $d;
    for ($i=$sEventStart; $i <= $sEventEnd; $i++){
		$sKey = $i;
        $d[$sKey] = $Level;
	}

}

//should be in footer
function monthname($i){
    switch($i){
        case "1":
            $monthname = "January"; break;
        case "2":
            $monthname = "February"; break;
        case "3":
            $monthname = "March"; break;
        case "4":
            $monthname = "April"; break;
        case "5":
            $monthname = "May"; break;
        case "6":
            $monthname = "June"; break;
        case "7":
            $monthname = "July"; break;
        case "8":
            $monthname = "August"; break;
        case "9":
            $monthname = "September"; break;
        case "10":
            $monthname = "October"; break;
        case "11":
            $monthname = "November"; break;
        case "12":
            $monthname = "December"; break;
        default:
            $monthname = "N/A"; break;
    }

    return $monthname;
}

function dayname($i){
    switch($i){
        case "1":
            $dayname = "Sunday"; break;
        case "2":
            $dayname = "Monday"; break;
        case "3":
            $dayname = "Tuesday"; break;
        case "4":
            $dayname = "Wednesday"; break;
        case "5":
            $dayname = "Thursday"; break;
        case "6":
            $dayname = "Friday"; break;
        case "7":
            $dayname = "Saturday"; break;
        default:
            $dayname = "N/A"; break;
    }

    return $dayname;
}

function DateAdd($interval, $number, $date) {

    $date_time_array = getdate($date);
    $hours = $date_time_array['hours'];
    $minutes = $date_time_array['minutes'];
    $seconds = $date_time_array['seconds'];
    $month = $date_time_array['mon'];
    $day = $date_time_array['mday'];
    $year = $date_time_array['year'];

    switch ($interval) {

        case 'yyyy':
            $year+=$number;
            break;
        case 'q':
            $year+=($number*3);
            break;
        case 'm':
            $month+=$number;
            break;
        case 'y':
        case 'd':
        case 'w':
            $day+=$number;
            break;
        case 'ww':
            $day+=($number*7);
            break;
        case 'h':
            $hours+=$number;
            break;
        case 'n':
            $minutes+=$number;
            break;
        case 's':
            $seconds+=$number;
            break;
    }
       $timestamp= mktime($hours,$minutes,$seconds,$month,$day,$year);
    return $timestamp;
}
function DateDiff($interval, $datefrom, $dateto, $using_timestamps = false) {
  /*
    $interval can be:
    yyyy - Number of full years
    q - Number of full quarters
    m - Number of full months
    y - Difference between day numbers
      (eg 1st Jan 2004 is "1", the first day. 2nd Feb 2003 is "33". The datediff is "-32".)
    d - Number of full days
    w - Number of full weekdays
    ww - Number of full weeks
    h - Number of full hours
    n - Number of full minutes
    s - Number of full seconds (default)
  */

  if (!$using_timestamps) {
    $datefrom = strtotime($datefrom, 0);
    $dateto = strtotime($dateto, 0);
  }
  $difference = $dateto - $datefrom; // Difference in seconds

  switch($interval) {

    case 'yyyy': // Number of full years

      $years_difference = floor($difference / 31536000);
      if (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom), date("j", $datefrom), date("Y", $datefrom)+$years_difference) > $dateto) {
        $years_difference--;
      }
      if (mktime(date("H", $dateto), date("i", $dateto), date("s", $dateto), date("n", $dateto), date("j", $dateto), date("Y", $dateto)-($years_difference+1)) > $datefrom) {
        $years_difference++;
      }
      $datediff = $years_difference;
      break;

    case "q": // Number of full quarters

      $quarters_difference = floor($difference / 8035200);
      while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($quarters_difference*3), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
        $months_difference++;
      }
      $quarters_difference--;
      $datediff = $quarters_difference;
      break;

    case "m": // Number of full months

      $months_difference = floor($difference / 2678400);
      while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($months_difference), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
        $months_difference++;
      }
      $months_difference--;
      $datediff = $months_difference;
      break;

    case 'y': // Difference between day numbers

      $datediff = date("z", $dateto) - date("z", $datefrom);
      break;

    case "d": // Number of full days

      $datediff = floor($difference / 86400);
      break;

    case "w": // Number of full weekdays

      $days_difference = floor($difference / 86400);
      $weeks_difference = floor($days_difference / 7); // Complete weeks
      $first_day = date("w", $datefrom);
      $days_remainder = floor($days_difference % 7);
      $odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?
      if ($odd_days > 7) { // Sunday
        $days_remainder--;
      }
      if ($odd_days > 6) { // Saturday
        $days_remainder--;
      }
      $datediff = ($weeks_difference * 5) + $days_remainder;
      break;

    case "ww": // Number of full weeks

      $datediff = floor($difference / 604800);
      break;

    case "h": // Number of full hours

      $datediff = floor($difference / 3600);
      break;

    case "n": // Number of full minutes

      $datediff = floor($difference / 60);
      break;

    default: // Number of full seconds (default)

      $datediff = $difference;
      break;
  }

  return $datediff;

}


?>

