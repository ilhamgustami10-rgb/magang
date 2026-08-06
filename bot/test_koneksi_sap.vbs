'============================================================
' Diagnosa koneksi ke SAP GUI Scripting
' Jalankan: cscript //nologo test_koneksi_sap.vbs
'============================================================
On Error Resume Next

Dim SapGuiAuto, app, conn, sess
Set SapGuiAuto = GetObject("SAPGUI")
If Err.Number <> 0 Or Not IsObject(SapGuiAuto) Then
    WScript.Echo "GAGAL 1: GetObject(SAPGUI) tidak menemukan SAP."
    WScript.Echo "        -> Penyebab umum: cmd dijalankan 'as Administrator' padahal SAP normal (atau sebaliknya),"
    WScript.Echo "           user Windows beda, atau SAP Logon tidak sedang berjalan."
    WScript.Quit 1
End If

Set app = SapGuiAuto.GetScriptingEngine
If Err.Number <> 0 Or Not IsObject(app) Then
    WScript.Echo "GAGAL 2: GetScriptingEngine gagal (attach ditolak / scripting engine tidak aktif)."
    WScript.Echo "        -> Cek popup 'A script is trying to attach' di SAP, atau matikan opsi Notify."
    WScript.Quit 2
End If

WScript.Echo "Jumlah koneksi terbuka: " & app.Children.Count
If app.Children.Count = 0 Then
    WScript.Echo "GAGAL 3: Tidak ada koneksi SAP terbuka."
    WScript.Quit 3
End If

Set conn = app.Children(0)
WScript.Echo "Jumlah sesi di koneksi 0: " & conn.Children.Count
If conn.Children.Count = 0 Then
    WScript.Echo "GAGAL 4: Tidak ada sesi aktif di koneksi 0."
    WScript.Quit 4
End If

Set sess = conn.Children(0)
WScript.Echo "SUKSES: terhubung ke SAP. Transaksi aktif: " & sess.Info.Transaction
WScript.Quit 0
