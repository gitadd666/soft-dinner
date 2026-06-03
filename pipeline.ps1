# =======================================================================
# CI/CD Pipeline - Versión Final con Captura de Inactividad de 10s
# =======================================================================

$proyectoRuta = "C:\xampp\htdocs\soft-dinner"
$rama = "pruebas"

# --- CONFIGURACION DE TIEMPOS ---
$tiempoRevisionNormal = 60    # 1 minuto
$tiempoSilencioNo = 600       # 10 minutos (Se activa con No, X, o Tiempo Agotado)

$intervaloActual = $tiempoRevisionNormal

if (-not (Test-Path $proyectoRuta)) { Exit }
Set-Location $proyectoRuta

$apiTeclado = Add-Type -MemberDefinition @"
    [DllImport("user32.dll")]
    public static extern short GetAsyncKeyState(int vKey);
"@ -Name "TecladoEstable" -Namespace "Win32" -PassThru

Write-Host "====================================================" -ForegroundColor Cyan
Write-Host " CI/CD Pipeline Activo para Soft Dinner ($rama)" -ForegroundColor Cyan
Write-Host " Atajo de teclado activo: CTRL + ALT + R para forzar revision" -ForegroundColor Yellow
Write-Host "====================================================" -ForegroundColor Cyan

while ($true) {
    Write-Host "[$(Get-Date -Format 'HH:mm:ss')] Buscando cambios en origin/$rama..." -ForegroundColor Gray
    
    & git fetch origin $rama *>$null

    $localCommit = (& git rev-parse HEAD).Trim()
    $remoteCommit = (& git rev-parse origin/$rama).Trim()

    if ($localCommit -ne $remoteCommit) {
        
        # Ejecuta el aviso y espera el código de salida
        $proceso = Start-Process -FilePath "$proyectoRuta\AlertaCICD.exe" -Wait -PassThru -NoNewWindow
        
        # CASO 1: El usuario presionó SÍ (ExitCode = 1)
        if ($proceso.ExitCode -eq 1) {
            Set-Location $proyectoRuta
            & git pull origin $rama *>$null
            
            try {
                Add-Type -AssemblyName System.Windows.Forms
                $exitoIcono = New-Object System.Windows.Forms.NotifyIcon
                $exitoIcono.Icon = [System.Drawing.SystemIcons]::Information
                $exitoIcono.BalloonTipTitle = "Despliegue Exitoso"
                $exitoIcono.BalloonTipText = "Los cambios de origin/$rama ya estan disponibles en localhost."
                $exitoIcono.Visible = $true
                $exitoIcono.ShowBalloonTip(5000)
                Start-Sleep -Seconds 6
                $exitoIcono.Visible = $false
                $exitoIcono.Dispose()
            } catch {}

            $intervaloActual = $tiempoRevisionNormal
        } 
        # CASO 2: Se presionó NO, se cerró con la X, o la ventana se desvaneció a los 10 segundos
        else {
            Write-Host "[!] Modo silencio activado por 10 minutos. Presiona CTRL+ALT+R para despertar." -ForegroundColor Yellow
            $intervaloActual = $tiempoSilencioNo
        }
        
        Start-Sleep -Seconds 2
    } else {
        $intervaloActual = $tiempoRevisionNormal
    }

    # --- SUEÑO INTELIGENTE ---
    $segundosTranscurridos = 0
    $forzarDespertar = $false

    while ($segundosTranscurridos -lt $intervaloActual) {
        $ctrlPresionado = ($apiTeclado::GetAsyncKeyState(0x11) -band 0x8000)
        $altPresionado  = ($apiTeclado::GetAsyncKeyState(0x12) -band 0x8000)
        $rPresionada    = ($apiTeclado::GetAsyncKeyState(0x52) -band 0x8000)

        if ($ctrlPresionado -and $altPresionado -and $rPresionada) {
            Write-Host "[!] Atajo CTRL + ALT + R detectado. Despertando script..." -ForegroundColor Green
            $forzarDespertar = $true
            break
        }

        Start-Sleep -Seconds 1
        $segundosTranscurridos++
    }

    if ($forzarDespertar) {
        Start-Sleep -Seconds 2 
    }
}