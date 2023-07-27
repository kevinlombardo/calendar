<%@ Language=VBScript %>
<HTML>
<HEAD>
<META NAME="GENERATOR" Content="Microsoft Visual Studio 6.0">
</HEAD>
<BODY>
<h3>Server Variables</h1>
<%

For Each a In Request.ServerVariables
	Response.Write a & "=" & Request.ServerVariables(a) & "<br>"
Next

%>

</BODY>
</HTML>
