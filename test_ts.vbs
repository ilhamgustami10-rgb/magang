Set service = CreateObject("Schedule.Service")
service.Connect()
Set folder = service.GetFolder("\")
Set task = folder.GetTask("Darsana Export")
Set definition = task.Definition
Set triggers = definition.Triggers
For Each trigger In triggers
    If trigger.Type = 2 Then ' Daily trigger
        trigger.StartBoundary = "2026-08-05T15:00:00"
    End If
Next
folder.RegisterTaskDefinition task.Name, definition, 4, , , 3
WScript.Echo "Success"
