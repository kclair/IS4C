csc /target:library /out:DelegateForm.dll DelegateBrowserForm.cs DelegateForm.cs
csc /target:library /r:DelegateForm.dll /out:UDPMsgBox.dll UDPMsgBox.cs
csc /target:library /r:DelegateForm.dll /out:SPH.dll SerialPortHandler.cs SPH_Magellan_Scale.cs 
csc /target:exe /r:DelegateForm.dll /r:UDPMsgBox.dll /r:SPH.dll /out:pos.exe Magellan.cs
csc /target:library /r:DelegateForm.dll /r:UDPMsgBox.dll /r:SPH.dll /out:Magellan.dll Magellan.cs
csc /target:exe /r:DelegateForm.dll /r:UDPMsgBox.dll /r:SPH.dll /r:Magellan.dll /out:posSVC.exe MagellanWinSVC.cs
