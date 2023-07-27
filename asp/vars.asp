<%@ Language=VBScript %>
<HTML>
<HEAD>
<META NAME="GENERATOR" Content="Microsoft Visual Studio 6.0">
</HEAD>
<BODY>
<%
Response.Write "<h3>Querystring</h3>"
For Each var In Request.QueryString
	Response.Write var & "=" & Request.QueryString(var) & "<br>"
Next
Response.Write "<h3>Form</h3>"
For Each var In Request.Form
	Response.Write var & "=" & Request.Form(var) & "<br>"
Next

Response.Write "<h3>Server Variables</h3>"
For Each var In Request.ServerVariables
	Response.Write var & "=" & Request.ServerVariables(var) & "<br>"
Next

%>

</BODY>
</HTML>
