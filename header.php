<LINK rel="stylesheet" type="text/css" href="style.css">
<%
sDSN = Application("DSN")
Set conn = CreateObject("ADODB.Connection")
conn.CursorLocation = 3 'adUseClient
conn.ConnectionString = sDSN
conn.Open

''get user info
sUsername = Request.ServerVariables("LOGON_USER")
iSlash = InStr(1, sUsername, "\")
sUsername = Right(sUsername, Len(sUsername) - iSlash)

Session("ValidUser") = 0

'temporary
tmpValidUser = Request.QueryString("edit")
If tmpValidUser = "true" Then
	Session("ValidUser") = 1
	Response.Cookies("validuser") = "1"
	Response.Cookies("validuser").Expires = DateAdd("d",60,Date) 
Else
	If Request.Cookies("validuser") = "1" Then
			Session("ValidUser") = 1
	End If
End If

Sub commented()

	sqlUser = "SELECT * FROM Person WHERE Username = '" & sUsername & "'"
	Set rsUser = CreateObject("ADODB.Recordset")
	rsUser.Open sqlUser, conn

	If rsUser.RecordCount > 0 Then
		Session("UserID") = rsUser.Fields("PersonID").Value
		Session("FirstName") = rsUser.Fields("FirstName").Value
		Session("LastName") = rsUser.Fields("LastName").Value
		Session("Username") = rsUser.Fields("Username").Value
		Session("Email") = rsUser.Fields("Email").Value
		Session("ValidUser") = 1
		rsUser.Close
	End If

	Set rsUser = Nothing

	'super user
	bSU = 0
	If Session("Username") = "e000242" THen bSU = 1

	'are they an approver?
	Session("Approver") = 0
	sqlApprover = "SELECT * FROM Approver WHERE Username = '" & sUsername & "'"
	Set rsApprover = CreateObject("ADODB.Recordset")
	rsApprover.Open sqlApprover, conn
	If rsApprover.RecordCount > 0 Then
		Session("Approver") = 1
		rsApprover.Close
	End If
	Set rsApprover = Nothing

End Sub

'edit, assign, and approve events
bCanEdit = 0
bCanAssign = 0
bCanApprove = 0

If Session("ValidUser") = 1 Then 
	bCanEdit = 1
	bCanAssign = 1
	bCanApprove = 1
End If

If bSU = 1 OR Session("Approver") = 1 Then
	bCanEdit = 1
	bCanAssign = 1
	bCanApprove = 1
End If

%>

