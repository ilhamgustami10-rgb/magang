'================================================================
' DARSANA - Export Realisasi Anggaran dari SAP ke folder
' Syarat : SAP GUI SUDAH LOGIN & layar tidak terkunci
' Jalankan: cscript //nologo export_sap.vbs
'================================================================

'---------- PENGATURAN (boleh diubah) ----------
Dim exportFolder, filePrefix, reportTx, fmArea
exportFolder = "D:\Sap_export"      ' folder tujuan file
filePrefix   = "realisasi_"          ' awalan nama file
reportTx     = "ZFM001"              ' kode transaksi laporan (Budget Usage)
fmArea       = "1000"                ' Financial Management Area (ctxt$4FFIKRS)

'---------- Buat nama file otomatis bertanggal ----------
' Contoh hasil: D:\Sap_export\realisasi_20260731_1003.csv
Dim d, stamp, fullPath
d = Now
stamp = Year(d) & Right("0" & Month(d),2) & Right("0" & Day(d),2) _
      & "_" & Right("0" & Hour(d),2) & Right("0" & Minute(d),2)
fullPath = exportFolder & "\" & filePrefix & stamp & ".csv"

'---------- Hubungkan ke SAP GUI yang sedang login ----------
On Error Resume Next
Dim SapGuiAuto, application, connection, session
Set SapGuiAuto = GetObject("SAPGUI")
If Err.Number <> 0 Or Not IsObject(SapGuiAuto) Then
    WScript.Echo "GAGAL: SAP GUI tidak ditemukan. Pastikan SAP sudah dibuka & login."
    WScript.Quit 1
End If
Set application = SapGuiAuto.GetScriptingEngine
Set connection  = application.Children(0)
Set session     = connection.Children(0)
If Err.Number <> 0 Or Not IsObject(session) Then
    WScript.Echo "GAGAL: Tidak ada sesi SAP aktif. Pastikan sudah login."
    WScript.Quit 1
End If
Err.Clear

'---------- Mulai otomasi ----------
session.findById("wnd[0]").maximize

' 1) Buka laporan lewat KODE TRANSAKSI (lebih andal daripada double-click Favorites)
session.findById("wnd[0]/tbar[0]/okcd").text = "/n" & reportTx
session.findById("wnd[0]").sendVKey 0
If Err.Number <> 0 Then
    WScript.Echo "GAGAL: tidak bisa membuka transaksi " & reportTx & ". Cek kode transaksinya."
    WScript.Quit 2
End If
Err.Clear

' 2) Layar seleksi (pass 1): isi FM Area + Fund Center, lalu Execute (F8)
session.findById("wnd[0]/usr/ctxt$4FFIKRS").text = fmArea
session.findById("wnd[0]/usr/ctxt_4FFICTR-LOW").text  = "A022020000"
session.findById("wnd[0]/usr/ctxt_4FFICTR-HIGH").text = "A022020005"
session.findById("wnd[0]/usr/ctxt_4FFICTR-HIGH").setFocus
session.findById("wnd[0]/usr/ctxt_4FFICTR-HIGH").caretPosition = 10
session.findById("wnd[0]").sendVKey 8

' 3) Layar seleksi (pass 2): isi ulang FM Area + Fund Center
session.findById("wnd[0]/usr/ctxt$4FFIKRS").text = fmArea
session.findById("wnd[0]/usr/ctxt_4FFICTR-LOW").text  = "A022020000"
session.findById("wnd[0]/usr/ctxt_4FFICTR-HIGH").text = "A022020005"
session.findById("wnd[0]/usr/ctxt_4FFICTR-HIGH").setFocus
session.findById("wnd[0]/usr/ctxt_4FFICTR-HIGH").caretPosition = 10

' 4) Atur tipe file (Spreadsheet) -> OK -> Execute (F8)
session.findById("wnd[0]/tbar[1]/btn[7]").press
session.findById("wnd[1]/usr/subOI_DOC_TYPE:SAPLGRWOS:0210/cmbGRWOS_S_SCREEN_FIELDS-FILE_TYPE").setFocus
session.findById("wnd[1]/tbar[0]/btn[0]").press
session.findById("wnd[0]").sendVKey 8

' 5) Export ke Local File
session.findById("wnd[0]/tbar[1]/btn[14]").press

' 6) >>> Folder + nama file diatur OTOMATIS di sini <<<
session.findById("wnd[1]/usr/ctxtLGRWO-OUT_FILE").text = fullPath
session.findById("wnd[1]/usr/ctxtLGRWO-OUT_FILE").setFocus
session.findById("wnd[1]/usr/ctxtLGRWO-OUT_FILE").caretPosition = Len(fullPath)
session.findById("wnd[1]/tbar[0]/btn[0]").press

' 7) Konfirmasi popup bila muncul (aman walau popup tidak ada)
session.findById("wnd[2]/usr/btnSPOP-VAROPTION2").press
session.findById("wnd[1]/usr/btnSPOP-VAROPTION1").press

'---------- Selesai ----------
WScript.Echo "SUKSES: file tersimpan di " & fullPath
WScript.Quit 0
