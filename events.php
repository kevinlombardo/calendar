<LINK rel="stylesheet" type="text/css" href="style.css">
<html>
<head>
<title>Event Info</title>
<script language="Javascript">
function confirmDelete(){
	return false;
}
</script>
</head>
<body>
<div style="text-align:right;font-size:10"><a href=# onclick='self.opener.location.reload();self.close()' style="text-decoration:none">CLOSE</a></div>
<?php

$mode = $_GET['mode'];
$iEventID = $_POST['EventID'];

//--------------------------------------
//insert / update events
//--------------------------------------
if (mode = "update"){
	$iCalendarID = $_POST['cboCalendarID'];
	$sEventName = $_POST['EventName'];
	$sEventDescr = $_POST['EventDescr'];
	$sEventStart = $_POST['EventStart'];
	$sEventEnd = $_POST['EventEnd'];
	$sRequestedBy = $_POST['cboRequestedBy'];
	$sApprovedBy = $_POST['cboApprovedBy'];

	if ($iEventID != ""){
		//run an update
		$sqlModify = "UPDATE Event SET " .
					"CalendarID = '$iCalendarID'," .
					"EventName = '$sEventName'," .
					"EventDescr = '$sEventDescr'," .
					"EventStart = '$sEventStart'," .
					"EventEnd = '$sEventEnd'," .
					"ConfidenceFactor = '$sConfidenceFactor'," .
					"RequestedBy = '$sRequestedBy'," .
					"AssignedTo = '$sAssignedTo'," .
					"ApprovedBy = '$sApprovedBy'," .
					"Status = '$sStatus'" .
					" WHERE EventID = $iEventID";
		//TODO conn.Execute(sqlModify)
	} else {
		//run an insert
/*
        sqlModify = "exec CreateEvent " & _
					"'" & iCalendarID & "'," & _
					"'" & sEventName & "'," & _
					"'" & sEventDescr & "'," & _
					"'" & sEventStart & "'," & _
					"'" & sEventEnd & "'," & _
					"'" & sConfidenceFactor & "'," & _
					"'" & sRequestedBy & "'," & _
					"'" & sAssignedTo & "'," & _
					"'" & sApprovedBy & "'," & _
					"'" & sStatus & "'"
		Set rsE = conn.Execute(sqlModify)
		iEventID = rsE(0)
		Set rsE =Nothing
		
*/
	}
	$mode = "view";
}

if ($iEventID == ""){ $iEventID = $_GET['eventid'];

if ($iEventID == "" && $mode == "view"){
	print "<span class=ERROR>Error: No Event ID specified.</span>";
	print "</body></html>";
	//TODO Response.End
}

/* TODO
If iEventID <> "" Then
	'get current event information
	sqlGetEvent = "SELECT * FROM Calendar c LEFT JOIN Event e ON c.CalendarID = e.CalendarID WHERE e.EventID = " & iEventID
	Set rsGetEvent = CreateObject("ADODB.Recordset")
	rsGetEvent.Open sqlGetEvent, conn

	If rsGetEvent.RecordCount = 0 Then
		Response.Write "<span class=ERROR>Error: Event not found in database.</span>"
		Response.Write "</body></html>"
		Response.End
	End If

	iCalendarID =  rsGetEvent.Fields("CalendarID").Value
	sCalendarName = rsGetEvent.Fields("CalendarName").Value
	sEventName = rsGetEvent.Fields("EventName").Value
	sEventDescr = rsGetEvent.Fields("EventDescr").Value
	sEventStart = rsGetEvent.Fields("EventStart").Value
	sEventEnd = rsGetEvent.Fields("EventEnd").Value
	sConfidenceFactor = rsGetEvent.Fields("ConfidenceFactor").Value
	sRequestedBy = rsGetEvent.Fields("RequestedBy").Value
	sAssignedTo = rsGetEvent.Fields("AssignedTo").Value
	sApprovedBy = rsGetEvent.Fields("ApprovedBy").Value
	sStatus = rsGetEvent.Fields("Status").Value

	If sStatus = "Not Approved" Then
		bCanEdit = 1
	End If
	
	rsGetEvent.Close
	Set rsGetEvent = Nothing
End If

*/
//--------------------------------------
//	delete events
//--------------------------------------
/* TODO
If mode = "delete" AND iEventID <> "" AND bCanEdit = 1 Then
	sqlDelete = "DELETE Event WHERE EventID = " & iEventID
	conn.Execute(sqlDelete)
	Response.Write "<script language=""Javascript"">"
	Response.Write "self.opener.location.reload();"
	Response.Write "self.close();"
	Response.Write "</script>"
End If
*/

If mode="create" OR bCanEdit = 1 Then
	If bCanAssign = 0 Then sAssign = " DISABLED"
	If bCanApprove = 0 Then sApprove = " DISABLED"
	
	'--------------------------------------
	'	get requestors, assignees, and requestors
	'--------------------------------------
	sql = "SELECT LastName, FirstName FROM Person"
	Set rs = CreateObject("ADODB.Recordset")
	rs.Open sql, conn

	If sRequestedBy = "Project Manager" Then sSelected = " SELECTED"
	sRequestors = "<option value=""Project Manager""" & sSelected & ">Project Manager</option>"
	Do Until rs.EOF
		sName = rs.Fields("LastName").Value & ", " & rs.Fields("FirstName").Value
		If sName = sRequestedBy Then 
			sSelected=" SELECTED"
		Else 
			sSelected = ""
		End If
		sRequestors = sRequestors & vbCrLf & "<option value=""" & sName & """" & sSelected & ">" & sName & "</option>"
		rs.MoveNext
	Loop
	
	'get assignees
	If rs.State = 1 Then rs.MoveFirst
	Do Until rs.EOF
		sName = rs.Fields("LastName").Value & ", " & rs.Fields("FirstName").Value
		If sName = sAssignedTo Then 
			sSelected=" SELECTED"
		Else 
			sSelected = ""
		End If
		sAssignees = sAssignees & vbCrLf & "<option value=""" & sName & """" & sSelected & ">" & sName & "</option>"
		rs.MoveNext
	Loop

	If rs.State = 1 Then rs.Close

	'get approvers
	sql = "SELECT LastName, FirstName FROM Approver"
	rs.Open sql, conn

	Do Until rs.EOF
		sName = rs.Fields("LastName").Value & ", " & rs.Fields("FirstName").Value
		If sName = sApprovedBy Then 
			sSelected=" SELECTED"
		Else 
			sSelected = ""
		End If
		sApprovers = sApprovers & vbCrLf & "<option value=""" & sName & """" & sSelected & ">" & sName & "</option>"
		rs.MoveNext
	Loop

	If rs.State = 1 Then rs.Close
	'--------------------------------------
	'	get calendars
	'--------------------------------------
	sql = "SELECT * FROM Calendar"
	rs.Open sql, conn

	If iCalendarID = "" Then iCalendarID = Request.QueryString("calid")

	Do Until rs.EOF
		iID =  rs.Fields("CalendarID").Value
		
		sName = rs.Fields("CalendarName").Value
		If CStr(iID) = CStr(iCalendarID) Then 
			sSelected=" SELECTED"
		Else 
			sSelected = ""
		End If
		sCalendars = sCalendars & vbCrLf & "<option value=""" & iID & """" & sSelected & ">" & sName & "</option>"
		rs.MoveNext
	Loop

	If rs.State = 1 Then rs.Close
	Set rs = Nothing
	
	sCalendarName = "<select name=cboCalendarID style=""width:200"">" & sCalendars & "</select>"
	
	sEventName = "<input type=""text"" name=EventName size=50 maxlength=50 value=""" & sEventName & """>"
	sEventDescr = "<textarea name=EventDescr rows=3 cols=35 maxlength=255>" & sEventDescr & "</textarea>"
	
	sEventStart = "<input type=""text"" name=EventStart size=20 maxlength=20 value=""" & sEventStart & """>"
	sEventEnd = "<input type=""text"" name=EventEnd size=20 maxlength=20 value=""" & sEventEnd & """>"
	
	Select Case sConfidenceFactor
		Case "High"
			sCFHigh = " SELECTED"
		Case "Medium"
			sCFMedium = " SELECTED"
		Case "Low"
			sCFLow = " SELECTED"
		Case "None"
			sCFNone = " SELECTED"
	End Select
	sConfidenceFactor = "<select name=cboConfidenceFactor style=""width:200""" & sApprove & "><option value=""None""" & sCFNone & ">None</option><option value=""High""" & sCFHigh & ">High</option><option value=""Medium""" & sCFMedium & ">Medium</option><option value=""Low""" & sCFLow & ">Low</option></select>"
	
	sRequestedBy = "<select name=cboRequestedBy style=""width:200""><option value=""N/A"">Select a requestor:</option>" & sRequestors & "</select>"
	sAssignedTo = "<select name=cboAssignedTo style=""width:200""" & sAssign & "><option value=""N/A"">Select an assignee:</option>" & sAssignees & "</select>"
	sApprovedBy = "<select name=cboApprovedBy style=""width:200""" & sApprove & "><option value=""N/A"">Select an approver:</option>" & sApprovers & "</select>"
	
	If sStatus = "Approved" Then
		sStatusApproved = " SELECTED"
	Else
		sStatusNotApproved = " SELECTED"
	End IF
	sStatus = "<select name=cboStatus style=""width:200""><option value=""Not Approved""" & sStatusNotApproved & ">Not Approved</option><option value=""Approved""" & sStatusApproved & ">Approved</option></select>"
End If

If bCanEdit = 1 AND iEventID <> "" Then
	Response.Write "<div style=""text-align:left""><a href=""events.asp?mode=delete&eventid=" & iEventID & """ style=""text-align:right;font-size:10;text-decoration:none"" onlick=""return confirmDelete();"">DELETE</a></div>"
End If

Response.Write "<form action=""events.asp"" method=POST>"
Response.Write "<input type=""hidden"" name=EventID value=""" & iEventID & """>"
Response.Write "<input type=""hidden"" name=mode value=""update"">"

Response.Write "<table>"
Response.Write "<tr>"
Response.Write "<td class=TABLEHEADING>Calendar:&nbsp;</td>"
Response.Write "<td>" & sCalendarName & "</td>"
Response.Write "</tr>"

Response.Write "<tr>"
Response.Write "<td class=TABLEHEADING>Event Name:&nbsp;</td>"
Response.Write "<td>" & sEventName & "</td>"
Response.Write "</tr>"

Response.Write "<tr>"
Response.Write "<td class=TABLEHEADING>Description:&nbsp;</td>"
Response.Write "<td>" & sEventDescr & "</td>"
Response.Write "</tr>"

Response.Write "<tr>"
Response.Write "<td class=TABLEHEADING>Event Start:&nbsp;</td>"
Response.Write "<td>" & sEventStart & "</td>"
Response.Write "</tr>"

Response.Write "<tr>"
Response.Write "<td class=TABLEHEADING>Event End:&nbsp;</td>"
Response.Write "<td>" & sEventEnd & "</td>"
Response.Write "</tr>"

Response.Write "<tr>"
Response.Write "<td class=TABLEHEADING>Confidence:&nbsp;</td>"
Response.Write "<td>" & sConfidenceFactor & "</td>"
Response.Write "</tr>"

Response.Write "<tr>"
Response.Write "<td class=TABLEHEADING>Requested By:&nbsp;</td>"
Response.Write "<td>" & sRequestedBy & "</td>"
Response.Write "</tr>"

Response.Write "<tr>"
Response.Write "<td class=TABLEHEADING>Assigned To:&nbsp;</td>"
Response.Write "<td>" & sAssignedTo & "</td>"
Response.Write "</tr>"

Response.Write "<tr>"
Response.Write "<td class=TABLEHEADING>Approved By:&nbsp;</td>"
Response.Write "<td>" & sApprovedBy & "</td>"
Response.Write "</tr>"

Response.Write "<tr>"
Response.Write "<td class=TABLEHEADING>Status:&nbsp;</td>"
Response.Write "<td>" & sStatus & "</td>"
Response.Write "</tr>"

Response.Write "</table>"

If bCanEdit = 1 AND mode="view" Then
	Response.Write "<center><input type=""submit"" value=""Update >"" name=submit1></center>"
End If
If mode = "create" Then
	Response.Write "<center><input type=""submit"" value=""Create >"" name=submit1></center>"
End If

Response.Write "</form>"
Response.Write "<hr>"

'--------------------------------------
'	add servers
'--------------------------------------
sServerToAdd = Request.Form("ServerToAdd")
If sServerToAdd <> "" Then
	On Error Resume Next
	sqlInsertServer = "exec InsertServerInfo '" & sServerToAdd & "', " & iEventID
	conn.Execute(sqlInsertServer)
	If Err Then Response.Write "<div class=ERROR>" & Err.Description & "</div>"
	On Error Goto 0
End If
'--------------------------------------
'	only show the servers if modifying current event
'--------------------------------------
If iEventID <> "" Then
	sqlServers = "SELECT * FROM Server WHERE EventID = " & iEventID
	Set rsServers = CreateObject("ADODB.Recordset")
	rsServers.Open sqlServers, conn

	If rsServers.RecordCount = 0 Then
		Response.Write "<span class=ERROR>No servers affected.</span>"
	
	Else
		Response.Write "Servers affected:<br>"
		Response.Write "<table border=1>"
		Response.Write "<tr>"
		Response.Write "<th>&nbsp;</th>"
		Response.Write "<th>Name</th>"
		Response.Write "<th>Description</th>"
		Response.Write "<th>Environment</th>"
		Response.Write "</tr>"

		Do Until rsServers.EOF
			iServerID = rsServers.Fields("ServerID").Value
			sServerName = rsServers.Fields("ServerName").Value
			sServerDescr = rsServers.Fields("ServerDescr").Value
			sServerEnv = rsServers.Fields("ServerEnv").Value
			
			If bCanEdit = 1 Then
				sDeleteServer = "<a href=""events.asp?deleteserverid=" & iServerID & "&eventid=" & iEventID & """ style=""text-align:right;font-size:10;text-decoration:none"">DELETE</a>"
			Else
				sDeleteServer = "&nbsp;"
			End If
			
			Response.Write "<tr>"
			Response.Write "<td>" & sDeleteServer & "</td>"
			Response.Write "<td>" & sServerName & "</td>"
			Response.Write "<td>" & sServerDescr & "</td>"
			Response.Write "<td>" & sServerEnv & "</td>"
			Response.Write "</tr>"
			rsServers.MoveNext
		Loop

		Response.Write "</table>"

		rsServers.Close
		Set rsServers = Nothing
	End If
	'get list of server to add
	Response.Write "<br>"
	
	If bCanEdit = 1 Then
		sqlServerInfo = "SELECT * FROM ServerInfo"
		Set rsServerInfo = CreateObject("ADODB.Recordset")
		rsServerInfo.Open sqlServerInfo, conn
	
		sServerList = ""
		Do Until rsServerInfo.EOF
			sServerName = rsServerInfo.Fields("ServerName").Value
			sServerEnv = " (" & rsServerInfo.Fields("ServerName").Value & ")"
			sServerList = sServerList & "<option value=""" & sServerName & """>" & sServerName & sServerEnv & "</option>"
			rsServerInfo.MoveNext
		Loop
	
		If rsServerInfo.State = 1 Then rsServerInfo.Close
		Set rsServerInfo = Nothing
				
		sServersCombo = "<select name=ServerToAdd><option value=""!"">Select a server:</option>"
		sServersCombo = sServersCombo & "<option value=""Dev / Test"">All Dev/Test</option><option value=""Pre-Production"">All Pre-Prod</option><option value=""Production"">All Production</option>"
		sServersCombo = sServersCombo & sServerList
		sServersCombo = sServersCombo & "</select>"

		Response.Write "<form action=""events.asp"" name=AddServer method=POST>"
		Response.Write "<input type=""hidden"" name=EventID value=""" & iEventID & """>"
		Response.Write "Servers:&nbsp;" & sServersCombo
		Response.Write "<INPUT type=""submit"" value=""Add >"" name=submit1>"
		Response.Write "</form>"
	End If	
End If

%>



</body>
</html>
<!-- #include file="footer.asp" -->
