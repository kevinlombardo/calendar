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
</head>
<body onload="moveEvents();">
<div>
<span><b><% = MonthName(iMonth) %></b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
<span style="background-color:blue">&nbsp;&nbsp;&nbsp;</span>
<span>=Not Approved&nbsp;&nbsp;&nbsp;</span>
<span style="background-color:lightgreen">&nbsp;&nbsp;&nbsp;</span>
<span>=<b>High</b> confidence factor&nbsp;&nbsp;&nbsp;</span>
<span style="background-color:yellow">&nbsp;&nbsp;&nbsp;</span>
<span>=<b>Medium</b> confidence factor&nbsp;&nbsp;&nbsp;</span>
<span style="background-color:red">&nbsp;&nbsp;&nbsp;</span>
<span>=<b>Low</b> confidence factor&nbsp;&nbsp;&nbsp;</span>
</div>


<%
'Response.Write "<div class=WATERMARK id=MonthWatermark>" & MonthName(iMonth) & "</div>"
'Response.Write "<div id=MonthWatermark>" & MonthName(iMonth) & "</div>"
'------------------------------------------------------------
'	Write day names
'------------------------------------------------------------
Response.Write "<table class=BASETABLE style=""width:910 px;height:650 px"" id=calendartable onresize=""moveEvents();"">"
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

'catch december events
If iMonth = 12 Then
	iMonth2 = 1 
	iYear2 = iYear + 1
Else
	iMonth2 = iMonth + 1
	iYear2 = iYear
End If

'sBetween = "'" & iMonth & "/1/" & iYear & "' AND '" & iMonth2 & "/1/" & iYear2 & "'"
sBetween = "'" & datFirstDayOfMonth & "' AND '" & datLastDayOfMonth & "'"
'sqlEvents = "SELECT DISTINCT e.EventID, e.EventName, e.EventDescr, e.EventStart, e.EventEnd, e.ConfidenceFactor, s.ServerEnv FROM Calendar c LEFT JOIN Event e ON c.CalendarID = e.CalendarID LEFT JOIN Server s ON e.EventID = s.EventID WHERE c.CalendarID = " & iCalendarID & " AND ((e.EventStart BETWEEN " & sBetween & ") OR (e.EventEnd BETWEEN " & sBetween & ")) OR (e.EventStart < '" & datFirstDayOfMonth & "' AND e.EventEnd > '" & datLastDayOfMonth & "')"

iCalendarID = Request("calid")
If iCalendarID = "" Then
	sqlEvents = "SELECT DISTINCT e.EventID, e.EventName, e.EventDescr, e.EventStart, e.EventEnd, e.ConfidenceFactor, e.Status, s.ServerEnv, c.CalendarName FROM Calendar c LEFT JOIN Event e ON c.CalendarID = e.CalendarID LEFT JOIN Server s ON e.EventID = s.EventID WHERE ((e.EventStart BETWEEN " & sBetween & ") OR (e.EventEnd BETWEEN " & sBetween & ")) OR (e.EventStart < '" & datFirstDayOfMonth & "' AND e.EventEnd > '" & datLastDayOfMonth & "')"
Else
	sqlEvents = "SELECT DISTINCT e.EventID, e.EventName, e.EventDescr, e.EventStart, e.EventEnd, e.ConfidenceFactor, e.Status, s.ServerEnv, c.CalendarName FROM Calendar c LEFT JOIN Event e ON c.CalendarID = e.CalendarID LEFT JOIN Server s ON e.EventID = s.EventID WHERE c.CalendarID = " & iCalendarID & " AND ((e.EventStart BETWEEN " & sBetween & ") OR (e.EventEnd BETWEEN " & sBetween & ")) OR (e.EventStart < '" & datFirstDayOfMonth & "' AND e.EventEnd > '" & datLastDayOfMonth & "')"
End If


Set rsEvents = CreateObject("ADODB.Recordset")
rsEvents.Open sqlEvents, conn

sScript = ""

'write out events
Do Until rsEvents.EOF
	iEventID = rsEvents.Fields("EventID").Value
	sEventName = rsEvents.Fields("EventName").Value
	sEventStart = rsEvents.Fields("EventStart").Value
	sEventEnd = rsEvents.Fields("EventEnd").Value
	If iCalendarID = "" Then
		sEventName = sEventName & " (" & rsEvents.Fields("CalendarName").Value & ")"
	End If

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

	If DateDiff("d", datFirstDayOfMonth, sEventStart) < 0 Then
		'rewrite for custom left - different month
		sLeft = "getLeft('previousday" & iStartDay & "');"
	End If

	'this returns the top of the row
	'ssTop = "getTop('week" & iStartWeek & "');"
iLevel = Check(sEventStart, sEventEnd)
ssTop = "top_of_table + getTopOfRow('week" & iStartWeek & "') + (number_height + (event_height * " & iLevel & "));"
AddToList sEventStart, sEventEnd, iLevel

	'this returns the width of one cell * number of days of event
	sWidth = "getWidth('day" & iStartDay & "', " & (iDays + 1) & ");"		

'javascript for hyperlink
'sJScript = "onmouseover=""this.style.cursor='hand';this.style.color='blue'"" onmouseout=""this.style.cursor='default';;this.style.color='black'"" onclick=""return launchDetails('" & iEventID & "')"""

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
	//var t_height = document.all.item("calendartable").style.pixelHeight;
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