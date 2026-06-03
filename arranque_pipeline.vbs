Set WshShell = CreateObject("WScript.Shell")
WshShell.Run "powershell.exe -ExecutionPolicy Bypass -File ""C:\xampp\htdocs\soft-dinner\pipeline.ps1""", 0, False