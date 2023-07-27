<%@ Language=VBScript %>
<!-- #include file="header.asp" -->
<%

'------------------------------------------------------------
'	Set the first and last days of month
'------------------------------------------------------------
iMonth = Request.QueryString("month")
If iMonth > 12 OR iMonth < 1 Then
	iMonth = Month(Date)
Else
	iMonth = Request.QueryString("month")
End If
iYear = Request.QueryString("year")
If iYear = "" Then iYear = Year(Date)
iCalendarID = Request.QueryString("calid")

'find the first day of month
datFirstDayOfMonth = iMonth & "/1/" & iYear
iFirstDayOfMonth = Weekday(datFirstDayOfMonth)

'add one month
datOneMonthAhead = DateAdd("m", 1, datFirstDayOfMonth)

'find the difference to calculate number of days in month
iDaysInMonth = DateDiff("d", datFirstDayOfMonth, datOneMonthAhead)

'find the last day of month
datLastDayOfMonth = iMonth & "/" & iDaysInMonth & "/" & iYear
iLastDayOfMonth = Weekday(datLastDayOfMonth)

'find number of days in first and last weeks
iDaysInFirstWeek = 8 - iFirstDayOfMonth
iDaysInLastWeek = iLastDayOfMonth 'iLastDayOfMonth is an integer representation of the weekday

'find number of middle weeks
iDaysInMiddleWeeks = iDaysInMonth - iDaysInFirstWeek - iDaysInLastWeek
iMiddleWeeks = iDaysInMiddleWeeks/7

iWeeksInMonth = iMiddleWeeks + 2

iCellHeight = 90 /iWeeksInMonth
Response.Write "<style>" & vbCrLf
Response.Write ".DAYCELLS" & vbCrLf
Response.Write "{HEIGHT: " & iCellHeight & "%;}" & vbCrLf
Response.Write "</style>" & vbCrLf
'------------------------------------------------------------
'	Done setting days
'------------------------------------------------------------

%>
<html>
<head>
<title>Event Calendar</title>
<script language="Javascript">
<!--
function launchDetails(i){
	var windowURL = "events.asp?mode=view&eventid=" + i;
	var windowName = "events";
	var windowFeatures = "menubar=no,height=500,width=600,scrollbars=yes"
	window.open(windowURL, windowName, windowFeatures);
	return;
}
function launchCreateRequest(){
	var windowURL = "events.asp?mode=create&calid=<% = iCalendarID %>";
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
	window.location.href = "calendarview.asp?month=<% = iMonth%>&year=<% = iYear%>&calid=" + cbo.value;
}
// -->
</script>
</head>
<body onload="moveEvents();">
<%
'Response.Write "<div class=WATERMARK id=MonthWatermark>" & MonthName(iMonth) & "</div>"
Response.Write "<table width=""100%""><tr>"
'------------------------------------------------------------
'	List available calendars
'------------------------------------------------------------
sqlCal = "SELECT CalendarID, CalendarName FROM Calendar"
Set rsCal = CreateObject("ADODB.Recordset")
rsCal.Open sqlCal, conn

'default is all calendars - not the first calendar
'If iCalendarID = "" Then iCalendarID = rsCal.Fields("CalendarID").Value

Response.Write "<td>&nbsp;"
'write calendar drop down
Response.Write "<a href=""#"" onclick=""moveEvents();"">Calendar Name:</a>"
Response.Write "<select name=CalendarID onchange=""return changeCalendar(this);"">"
Response.Write "<option value=00>All Calendars</option>"
Do Until rsCal.EOF
	tmpCalendarID = rsCal.Fields("CalendarID").Value
	sSelected = ""
	If CStr(iCalendarID) = CStr(tmpCalendarID) Then sSelected = " SELECTED"
	sCalendarName = rsCal.Fields("CalendarName").Value	Response.Write "<option value=" & tmpCalendarID & sSelected & ">" & sCalendarName & "</option>"	rsCal.MoveNext
Loop
Response.Write "</select>"
Set rsCal = Nothing
Response.Write "</td>"

Response.Write "<td>&nbsp;"
'If Session("ValidUser") = 0 Then
'	'write Sign Up
'	Response.Write "<a href=# style=""text-decoration:none;color:black"" onclick=""return launch('signup.asp', 'menubar=no,height=250,width=600,scrollbars=no');"" onmouseover=""this.style.color='blue'"" onmouseout=""this.style.color='black'"">Sign Up</a>"
'Else
'	'write Modify User
'	Response.Write "<a href=# style=""text-decoration:none;color:black"" onclick=""return launch('signup.asp', 'menubar=no,height=250,width=600,scrollbars=no');"" onmouseover=""this.style.color='blue'"" onmouseout=""this.style.color='black'"">Modify User</a>"
'End If
Response.Write "</td>"

'write Create Request
Response.Write "<td>&nbsp;"
If Session("ValidUser") = 1 Then
	Response.Write "<a href=# style=""text-decoration:none;color:black"" onclick=""return launchCreateRequest();"" onmouseover=""this.style.color='blue'"" onmouseout=""this.style.color='black'"">Create Request</a>"
End If
Response.Write "</td>"

'write Printable Version
Response.Write "<td>&nbsp;"
Response.Write "<a target=""_new"" href=""printablecalendar.asp?month=" & iMonth & "&year=" & iYear & "&calid=" & iCalendarID & """ style=""text-decoration:none;color:black"" onmouseover=""this.style.color='blue'"" onmouseout=""this.style.color='black'"">Printable Version</a>"
Response.Write "</td>"

'write year
Response.Write "<td>&nbsp;"
Response.Write "<a href=""calendarview.asp?month=" & iMonth & "&year=" & (iYear - 1) & """ style='text-decoration:none'><<<</a>"
Response.Write "Year: " & iYear
Response.Write "<a href=""calendarview.asp?month=" & iMonth & "&year=" & (iYear + 1) & """ style='text-decoration:none'>>>></a>"
Response.Write "</td>"

'write Page Info
Response.Write "<td>&nbsp;"
Response.Write "<a href=# style=""text-decoration:none;color:black"" onclick=""return launch('pageinfo.asp', 'menubar=no,height=500,width=600,scrollbars=no');"" onmouseover=""this.style.color='blue'"" onmouseout=""this.style.color='black'"">Page Info</a>"
Response.Write "</td>"


Response.Write "</tr></table>"

Response.Write "<hr>"

'------------------------------------------------------------
'	Write navbar (month names)
'------------------------------------------------------------
Response.Write "<table class=MONTHS>"
Response.Write "<tr>"
For i = 1 To 12
	Response.Write "<td>"
	If Not (i = CInt(iMonth)) Then
		Response.Write "<a href=""calendarview.asp?month=" & i & "&year=" & iYear & "&calid=" & iCalendarID & """ onmouseover=""this.className='MONTHNAMESBOLD';"" onmouseout=""this.className='MONTHNAMES';"" class=MONTHNAMES>"
	Else
		Response.Write "<span class=MONTHNAMES style='color:red'>"
	End If
	Response.Write MonthName(i)
	If Not (i = CInt(iMonth)) Then
		Response.Write "</a>"
	Else
		Response.Write "</span>"
	End If
	Response.Write "</td>"
Next
Response.Write "</tr>"
Response.Write "</table>"


Response.Write "<hr>"
'------------------------------------------------------------
'	Write day names
'------------------------------------------------------------
Response.Write "<table class=BASETABLE id=calendartable onresize=""moveEvents();"">"
'Response.Write "<table>"
Response.Write "<tr id=weekdayrow>"
For i = 1 To 7
	Response.Write "<td class=DAYNAMES>" & WeekDayName(i) & "</td>"
Next
Response.Write "</tr>"

iDay = 1

'write each row
For i = 1 To iWeeksInMonth
	Response.Write "<tr id=week" & i & ">"
	Select Case i
		Case 1
			RenderFirstWeek()
		Case 2, 3, 4
			RenderMiddleWeek()
		Case 5, 6
			If iMiddleWeeks = 5 Then
				RenderMiddleWeek()
			Else
				RenderLastWeek()
			End If
	End Select
	Response.Write "</tr>"
Next
%>
</table>
<%
'------------------------------------------------------------
'	Get the events for this calendar
'------------------------------------------------------------
'object to hold levels
Set d = CreateObject("Scripting.Dictionary")

'sBetween = "'" & iMonth & "/1/" & iYear & "' AND '" & iMonth2 & "/1/" & iYear2 & "'"
sBetween = "'" & datFirstDayOfMonth & "' AND '" & datLastDayOfMonth & " 11:59 PM'"

If iCalendarID = "" OR iCalendarID = "00" Then
	sqlEvents = "SELECT DISTINCT e.EventID, e.EventName, e.EventDescr, e.EventStart, e.EventEnd, e.ConfidenceFactor, e.Status FROM Calendar c LEFT JOIN Event e ON c.CalendarID = e.CalendarID LEFT JOIN Server s ON e.EventID = s.EventID WHERE e.EventStart BETWEEN " & sBetween & " OR e.EventEnd BETWEEN " & sBetween & " OR e.EventStart < '" & datFirstDayOfMonth & "' AND e.EventEnd > '" & datLastDayOfMonth & "'"
Else
	sqlEvents = "SELECT DISTINCT e.EventID, e.EventName, e.EventDescr, e.EventStart, e.EventEnd, e.ConfidenceFactor, e.Status FROM Calendar c LEFT JOIN Event e ON c.CalendarID = e.CalendarID LEFT JOIN Server s ON e.EventID = s.EventID WHERE c.CalendarID = " & iCalendarID & " AND (((e.EventStart BETWEEN " & sBetween & ") OR (e.EventEnd BETWEEN " & sBetween & ")) OR (e.EventStart < '" & datFirstDayOfMonth & "' AND e.EventEnd > '" & datLastDayOfMonth & "'))"
End If

'catch december events
If iMonth = 12 Then
	iMonth2 = 1
	iYear2 = iYear + 1
Else
	iMonth2 = iMonth + 1
	iYear2 = iYear
End If

''''sqlEvents = "SELECT DISTINCT e.EventID, e.EventName, e.EventDescr, e.EventStart, e.EventEnd, e.ConfidenceFactor, e.Status, s.ServerEnv FROM Calendar c LEFT JOIN Event e ON c.CalendarID = e.CalendarID LEFT JOIN Server s ON e.EventID = s.EventID WHERE c.CalendarID = " & iCalendarID & " AND ((e.EventStart BETWEEN " & sBetween & ") OR (e.EventEnd BETWEEN " & sBetween & ")) OR (e.EventStart < '" & datFirstDayOfMonth & "' AND e.EventEnd > '" & datLastDayOfMonth & "')"
'sqlEvents = "SELECT DISTINCT e.EventID, e.EventName, e.EventDescr, e.EventStart, e.EventEnd, e.ConfidenceFactor, e.Status FROM Calendar c LEFT JOIN Event e ON c.CalendarID = e.CalendarID LEFT JOIN Server s ON e.EventID = s.EventID WHERE c.CalendarID = " & iCalendarID & " AND ((e.EventStart BETWEEN " & sBetween & ") OR (e.EventEnd BETWEEN " & sBetween & ")) OR (e.EventStart < '" & datFirstDayOfMonth & "' AND e.EventEnd > '" & datLastDayOfMonth & "')"
Set rsEvents = CreateObject("ADODB.Recordset")
rsEvents.Open sqlEvents, conn

sScript = ""

'write out events
Do Until rsEvents.EOF
	iEventID = rsEvents.Fields("EventID").Value
	sEventName = rsEvents.Fields("EventName").Value
	sEventStart = FormatDateTime(rsEvents.Fields("EventStart").Value, 2)
	sEventEnd = rsEvents.Fields("EventEnd").Value
	'If rsEvents.Fields("ServerEnv").Value <> "" Then
	'	sEventName = sEventName & "(" & rsEvents.Fields("ServerEnv").Value & ")"
	'End If

	'first day of event
	iStartDay = DatePart("d", sEventStart)
	'first week of event
	iStartWeek = DateDiff("ww", datFirstDayOfMonth, sEventStart) + 1
	'number of days event lasts
	iDays = DateDiff("d", sEventStart, sEventEnd)
	'color for confidence factor
	sColor = GetColor(rsEvents.Fields("ConfidenceFactor").Value)

	If rsEvents.Fields("Status").Value = "Not Approved" Then
		'set another color
		sColor = "blue"
	End If
	'------------------------------------------------------------
	'	generated javascript
	'------------------------------------------------------------
	'this is javascript with the id of this event
	sObject = "document.all.item(""Event" & iEventID & """).style"
	'this returns the left of the day
	sLeft = "getLeft('day" & iStartDay & "');"
	'this returns the width of one cell * number of days of event
	sWidth = "getWidth('day" & iStartDay & "', " & (iDays + 1) & ");"
	'Response.Write "kl=" & DateDiff("d", datFirstDayOfMonth, sEventStart)
	If DateDiff("d", datFirstDayOfMonth, sEventStart) < 0 Then
		'rewrite for custom left - different month
		sLeft = "getLeft('previousday" & iStartDay & "');"
		'rewrite for custom width - different month
		sWidth = "getWidth('previousday" & iStartDay & "', " & (iDays + 1) & ");"
		'Response.Write "hello"
	End If

	'this returns the top of the row
	'ssTop = "getTop('week" & iStartWeek & "');"
iLevel = Check(sEventStart, sEventEnd)
ssTop = "top_of_table + getTopOfRow('week" & iStartWeek & "') + (number_height + (event_height * " & iLevel & "));"
AddToList sEventStart, sEventEnd, iLevel



'javascript for hyperlink
sJScript = "onmouseover=""this.style.cursor='hand';this.style.color='blue'"" onmouseout=""this.style.cursor='default';;this.style.color='black'"" onclick=""return launchDetails('" & iEventID & "')"""

	If iDays = 0 Then
		'write out one day event
		Response.Write "<span style='background-color:" & sColor & "' id=Event" & iEventID & " class=EVENT " & sJScript & ">" & sEventName & "</span>"
		sScript = sScript & sObject & ".top=" & ssTop & sObject & ".left=" & sLeft & sObject & ".width=" & sWidth
	Else
		'more than one, check to see if it spans over multiple calendar weeks
		iWeekSpan = DateDiff("ww", sEventStart, sEventEnd)

		If iWeekSpan = 0 Then
			'write out multiple day event contained in one week
			Response.Write "<span style='background-color:" & sColor & "' id=Event" & iEventID & " class=EVENT " & sJScript & ">" & sEventName & "</span>"
			sScript = sScript & sObject & ".top=" & ssTop & sObject & ".left=" & sLeft & sObject & ".width=" & sWidth
		Else
			iBalance = 0
			'render events in n number of weeks
			For iW = 0 To iWeekSpan
				'is the start week in this month?
				If iStartWeek < 1 Then
					iStartWeek = iStartWeek + 1
					iBalance = iBalance + 1
				Else
					'rewrite for custom top
					ssTop = "top_of_table + getTopOfRow('week" & (iStartWeek + iW - iBalance)& "') + (number_height + (event_height * " & iLevel & "));"
					'custom object name
					sObject = "document.all.item(""Event" & iEventID & "-" & iW & """).style"

					'quit processing if in to next month
					If (iStartWeek + iW - iBalance) > iWeeksInMonth Then Exit For

					Select Case iW
						Case 0
							'first part of event
							Response.Write "<span style='border-right:none;background-color:" & sColor & "' id=Event" & iEventID & "-" & iW & " class=EVENT " & sJScript & ">" & sEventName & "->>></span>"
							'rewrite for custom width - 8 - daynumber
							iDays2 = 7 - Weekday(sEventStart)
							sWidth = "getWidth('day" & iStartDay & "', " & (iDays2 + 1) & ");"
							sScript = sScript & sObject & ".top=" & ssTop & sObject & ".left=" & sLeft & sObject & ".width=" & sWidth
						Case iWeekSpan
							'last part of event
							Response.Write "<span style='border-left:none;background-color:" & sColor & "' id=Event" & iEventID & "-" & iW & " class=EVENT " & sJScript & "><<<-" & sEventName & "</span>"
							'rewrite for custom width - daynumber
							iDays2 = Weekday(sEventEnd)
							sWidth = "getWidth('day" & iStartDay & "', " & (iDays2) & ");"
							'rewrite for custom left
							iStartDay2 = Day(sEventEnd) - iDays2 + 1
							If iStartDay2 < 0 Then
									'rewrite for custom left
									iStartDay2 = Day(DateAdd("d", (iStartDay2 - 1), sEventEnd))
									If DateDiff("d", datFirstDayOfMonth, sEventStart) < 0 Then
										bLeftDontChange = 1
										sLeft = "getLeft('previousday" & iStartDay2 & "');"
										'rewrite for custom width - different month
										sWidth = "getWidth('previousday" & iStartDay & "', " & (iDays + 1) & ");"
									End If
							End If
							If bLeftDontChange <> 1 Then sLeft = "getLeft('day" & iStartDay2 & "');"
							bLeftDontChange = 0
							sScript = sScript & sObject & ".top=" & ssTop & sObject & ".left=" & sLeft & sObject & ".width=" & sWidth
						Case Else
							'middle part of event
							Response.Write "<span style='border-left:none;border-right:none;background-color:" & sColor & "' id=Event" & iEventID & "-" & iW & " class=EVENT " & sJScript & "><<<-" & sEventName & "->>></span>"
							'rewrite for custom width - always 7 because this is a middle week
							iDays2 = 7
							sWidth = "getWidth('day" & iStartDay & "', " & (iDays2) & ");"
							'rewrite for custom left - any Sunday of month
							iStartDay2 = Day(DateAdd("d",8 - iFirstDayOfMonth , datFirstDayOfMonth))
							'iStartDay2 = Day(sEventEnd) - Weekday(sEventEnd) + 1
							sLeft = "getLeft('day" & iStartDay2 & "');"
							sScript = sScript & sObject & ".top=" & ssTop & sObject & ".left=" & sLeft & sObject & ".width=" & sWidth
					End Select
				End If
			Next
		End If
	End If
	rsEvents.MoveNext
Loop
%>
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

	<% = sScript %>
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
<!-- #include file="footer.asp" -->
<%
Sub RenderFirstWeek()
	For ii = 1 To 7
		If iFirstDayOfMonth > ii Then
			'write previous months days
			pDay = 	Day(DateAdd("d",(ii - iFirstDayOfMonth),datFirstDayOfMonth))
			'Response.Write "<td class=FILLCELLS valign=top>&nbsp;</td>"
			Response.Write "<td class=FILLCELLS valign=top id=previousday" & pDay & "><div class=DAYNUMBER id=previousdaynumber" & pDay & ">" & pDay
			Response.Write "</div></td>"
		Else
			Response.Write "<td class=DAYCELLS valign=top id=day" & iDay & "><div class=DAYNUMBER id=daynumber" & iDay & ">" & iDay
			Response.Write "</div></td>"
			iDay = iDay + 1
		End If
	Next
End Sub
Sub RenderMiddleWeek()
	For ii = 1 To 7
		Response.Write "<td class=DAYCELLS valign=top id=day" & iDay & "><div class=DAYNUMBER>" & iDay
		iDay = iDay + 1
	Next
End Sub
Sub RenderLastWeek()
	For ii = 1 To 7
		If iDaysInMonth => iDay Then
			Response.Write "<td class=DAYCELLS valign=top id=day" & iDay & "><div class=DAYNUMBER>" & iDay
			Response.Write "</div></td>"
		Else
			'write post months days
			pDay = 	iDay - iDaysInMonth
			'Response.Write "<td class=FILLCELLS valign=top>&nbsp;</td>"
			Response.Write "<td class=FILLCELLS valign=top id=previousday" & pDay & "><div class=DAYNUMBER id=previousdaynumber" & pDay & ">" & pDay
			Response.Write "</div></td>"
		End If
		iDay = iDay + 1
	Next
End Sub
Function GetColor(ConfidenceFactor)
	Select Case ConfidenceFactor
		Case "High"
			GetColor = "lightgreen"
		Case "Medium"
			GetColor = "yellow"
		Case "Low"
			GetColor = "red"
		Case "None"
			GetColor = "white"
	End Select
End Function
Function Check(sEventStart, sEventEnd)
	iLevelCheck = 1
	For ii = sEventStart To sEventEnd
		Level = d.Item(CStr(ii))
		If Level >= iLevelCheck Then iLevelCheck = Level + 1
	Next
	Check = iLevelCheck
End Function
Sub AddToList(sEventStart, sEventEnd, Level)
	For i = sEventStart To sEventEnd
		sKey = i
		sKey = CStr(sKey)
		If d.Exists(sKey) Then
			d.Item(sKey) = Level
		Else
			d.Add sKey, Level
		End If
	Next
End Sub
%>