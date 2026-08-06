#NoTrayIcon
#Region ;**** Directives created by AutoIt3Wrapper_GUI ****
#AutoIt3Wrapper_Outfile=sap_security_watcher.exe
#AutoIt3Wrapper_UseUpx=n
#EndRegion ;**** Directives created by AutoIt3Wrapper_GUI ****

Opt("WinTitleMatchMode", 1) ; 1 = Match the title from the start

Global $sTitle = "SAP GUI Security"

While 1
    If WinExists($sTitle) Then
        WinActivate($sTitle)
        WinWaitActive($sTitle, "", 2)
        
        ; Centang "Remember My Decision" jika ada
        If ControlCommand($sTitle, "", "Button3", "Exists", "") Then
            ControlCommand($sTitle, "", "Button3", "Check", "")
        EndIf
        
        Sleep(200)
        
        ; Klik tombol "Allow"
        If ControlCommand($sTitle, "", "Button1", "Exists", "") Then
            ControlClick($sTitle, "", "Button1")
        EndIf
    EndIf
    
    Sleep(300)
WEnd
