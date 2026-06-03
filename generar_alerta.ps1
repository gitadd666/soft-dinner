# =======================================================================
# Compilador de Alerta - Modo ToolWindow Invasivo con Tiempo de 15 Segundos
# =======================================================================

$proyectoRuta = "C:\xampp\htdocs\soft-dinner"
$iconoRuta = "$proyectoRuta\logo.ico"

$source = @"
using System;
using System.Windows.Forms;
using System.Drawing;
using System.Drawing.Drawing2D;
using System.Runtime.InteropServices;

public class AlertaProgram {
    [DllImport("user32.dll")]
    private static extern bool SetForegroundWindow(IntPtr hWnd);

    [DllImport("user32.dll")]
    private static extern bool ShowWindow(IntPtr hWnd, int nCmdShow);

    [DllImport("user32.dll")]
    private static extern bool SetWindowPos(IntPtr hWnd, IntPtr hWndInsertAfter, int X, int Y, int cx, int cy, uint uFlags);

    private static readonly IntPtr HWND_TOPMOST = new IntPtr(-1);
    private const uint SWP_NOSIZE = 0x0001;
    private const uint SWP_NOMOVE = 0x0002;
    private const uint SWP_SHOWWINDOW = 0x0040;

    public class AlertaFlotante : Form {
        protected override CreateParams CreateParams {
            get {
                CreateParams cp = base.CreateParams;
                cp.ExStyle |= 0x00000080; 
                cp.ExStyle |= 0x00000008; 
                return cp;
            }
        }
    }

    public static void Main() {
        // --- 1. LA BARRA DISCRETA DE LA ESQUINA ---
        AlertaFlotante form = new AlertaFlotante();
        form.Text = "Soft Dinner - CI/CD";
        form.Size = new Size(360, 95);
        
        form.FormBorderStyle = FormBorderStyle.None;
        form.StartPosition = FormStartPosition.Manual;
        form.BackColor = Color.FromArgb(32, 32, 32); 
        
        form.ShowInTaskbar = false; 
        form.TopMost = true;

        try {
            if (System.IO.File.Exists(@"$iconoRuta")) {
                form.Icon = new Icon(@"$iconoRuta");
            }
        } catch {}

        AplicarEsquinasRedondeadas(form, 8);

        form.Paint += (s, e) => {
            using (Pen pen = new Pen(Color.FromArgb(60, 60, 60), 1)) {
                e.Graphics.DrawRectangle(pen, 0, 0, form.Width - 1, form.Height - 1);
            }
        };

        Rectangle pantalla = Screen.PrimaryScreen.WorkingArea;
        form.Left = pantalla.Width - form.Width - 16;
        form.Top = pantalla.Height - form.Height - 16;

        // --- ICONO VISUAL INTERNO (VENTANA 1) ---
        PictureBox pbIcono1 = new PictureBox();
        pbIcono1.Size = new Size(24, 24);
        pbIcono1.Location = new Point(16, 12);
        pbIcono1.SizeMode = PictureBoxSizeMode.StretchImage;
        try {
            if (System.IO.File.Exists(@"$iconoRuta")) {
                pbIcono1.Image = Image.FromFile(@"$iconoRuta");
            }
        } catch {}
        form.Controls.Add(pbIcono1);

        // --- BOTON X DE CERRAR ---
        Label lblCerrar = new Label();
        lblCerrar.Text = "X"; 
        lblCerrar.Font = new Font("Arial", 10.5f, FontStyle.Regular); 
        lblCerrar.ForeColor = Color.FromArgb(160, 160, 160);
        lblCerrar.BackColor = Color.Transparent;
        lblCerrar.Location = new Point(326, 6);
        lblCerrar.Size = new Size(26, 26);
        lblCerrar.TextAlign = ContentAlignment.MiddleCenter;
        lblCerrar.Cursor = Cursors.Hand;

        lblCerrar.MouseEnter += (s, e) => {
            lblCerrar.BackColor = Color.FromArgb(232, 17, 35); 
            lblCerrar.ForeColor = Color.White;
        };
        lblCerrar.MouseLeave += (s, e) => {
            lblCerrar.BackColor = Color.Transparent;
            lblCerrar.ForeColor = Color.FromArgb(160, 160, 160);
        };
        
        lblCerrar.Click += (s, e) => {
            Environment.Exit(0); 
        };
        form.Controls.Add(lblCerrar);

        // --- TEXTOS (VENTANA 1) ---
        Label lblToastTitulo = new Label();
        lblToastTitulo.Text = "Soft Dinner - CI/CD";
        lblToastTitulo.Font = new Font("Segoe UI Semibold", 9.5f, FontStyle.Bold);
        lblToastTitulo.ForeColor = Color.White;
        lblToastTitulo.Location = new Point(48, 14);
        lblToastTitulo.Size = new Size(260, 20);
        form.Controls.Add(lblToastTitulo);

        Label lblToastCuerpo = new Label();
        lblToastCuerpo.Text = "Nuevos cambios detectados en origin/pruebas.\nClick aqui para revisar las opciones.";
        lblToastCuerpo.Font = new Font("Segoe UI", 9f, FontStyle.Regular);
        lblToastCuerpo.ForeColor = Color.FromArgb(193, 193, 193);
        lblToastCuerpo.Location = new Point(16, 38);
        lblToastCuerpo.Size = new Size(300, 40);
        form.Controls.Add(lblToastCuerpo);

        // --- CAMBIO AQUÍ: Temporizador configurado a 15 segundos (15000 ms) ---
        Timer timer = new Timer();
        timer.Interval = 15000; 
        timer.Tick += (s, e) => { 
            form.Close(); 
            Environment.Exit(0); 
        };

        // --- ACCION AL HACER CLIC EN LA ALERTA ---
        EventHandler accionClic = (s, e) => {
            timer.Stop();
            form.Hide(); 

            // --- 2. LA SEGUNDA VENTANA (MODAL DE DECISION CENTRAL) ---
            AlertaFlotante ventanaModal = new AlertaFlotante();
            ventanaModal.Size = new Size(420, 180);
            ventanaModal.FormBorderStyle = FormBorderStyle.None;
            ventanaModal.StartPosition = FormStartPosition.CenterScreen;
            ventanaModal.BackColor = Color.FromArgb(32, 32, 32);
            ventanaModal.TopMost = true; 
            AplicarEsquinasRedondeadas(ventanaModal, 8);

            try {
                if (System.IO.File.Exists(@"$iconoRuta")) {
                    ventanaModal.Icon = new Icon(@"$iconoRuta");
                }
            } catch {}

            ventanaModal.Paint += (sender, ea) => {
                using (Pen pen = new Pen(Color.FromArgb(60, 60, 60), 1)) {
                    ea.Graphics.DrawRectangle(pen, 0, 0, ventanaModal.Width - 1, ventanaModal.Height - 1);
                }
            };

            // --- ICONO VISUAL INTERNO (VENTANA 2) ---
            PictureBox pbIcono2 = new PictureBox();
            pbIcono2.Size = new Size(24, 24);
            pbIcono2.Location = new Point(24, 22); 
            pbIcono2.SizeMode = PictureBoxSizeMode.StretchImage;
            try {
                if (System.IO.File.Exists(@"$iconoRuta")) {
                    pbIcono2.Image = Image.FromFile(@"$iconoRuta");
                }
            } catch {}
            ventanaModal.Controls.Add(pbIcono2);

            // --- TEXTOS (VENTANA 2) ---
            Label lblTitulo = new Label();
            lblTitulo.Text = "Actualizacion de Soft Dinner";
            lblTitulo.Font = new Font("Segoe UI Semibold", 11f, FontStyle.Bold);
            lblTitulo.ForeColor = Color.White;
            lblTitulo.Location = new Point(56, 22); 
            lblTitulo.Size = new Size(340, 25);
            ventanaModal.Controls.Add(lblTitulo);

            Label lblMensaje = new Label();
            lblMensaje.Text = "Deseas aplicar la actualizacion en localhost ahora mismo?\nSe detectaron nuevos cambios en la rama remota.";
            lblMensaje.Font = new Font("Segoe UI", 9.5f, FontStyle.Regular);
            lblMensaje.ForeColor = Color.FromArgb(193, 193, 193);
            lblMensaje.Location = new Point(24, 55);
            lblMensaje.Size = new Size(370, 45);
            ventanaModal.Controls.Add(lblMensaje);

            Panel panelBotones = new Panel();
            panelBotones.BackColor = Color.FromArgb(28, 28, 28);
            panelBotones.Size = new Size(420, 55);
            panelBotones.Location = new Point(0, 125);
            ventanaModal.Controls.Add(panelBotones);

            Button btnSi = CrearBotonWindows("Si, actualizar", new Point(190, 10), Color.FromArgb(0, 103, 192), Color.White);
            btnSi.Click += (sender, ea) => { 
                ventanaModal.Close();
                form.Close(); 
                Environment.Exit(1); 
            };
            panelBotones.Controls.Add(btnSi);

            Button btnNo = CrearBotonWindows("No, despues", new Point(300, 10), Color.FromArgb(45, 45, 45), Color.White);
            btnNo.Click += (sender, ea) => { 
                ventanaModal.Close();
                form.Close(); 
                Environment.Exit(0); 
            };
            btnNo.MouseEnter += (sender, ea) => { btnNo.BackColor = Color.FromArgb(55, 55, 55); };
            btnNo.MouseLeave += (sender, ea) => { btnNo.BackColor = Color.FromArgb(45, 45, 45); };
            panelBotones.Controls.Add(btnNo);

            ventanaModal.Load += (sender, ea) => {
                ShowWindow(ventanaModal.Handle, 5); 
                SetForegroundWindow(ventanaModal.Handle);
                SetWindowPos(ventanaModal.Handle, HWND_TOPMOST, 0, 0, 0, 0, SWP_NOMOVE | SWP_NOSIZE | SWP_SHOWWINDOW);
            };

            ventanaModal.ShowDialog();
            form.Close(); 
        };

        form.Click += accionClic;
        lblToastTitulo.Click += accionClic;
        lblToastCuerpo.Click += accionClic;
        pbIcono1.Click += accionClic; 

        form.Load += (s, e) => {
            ShowWindow(form.Handle, 5);
            SetForegroundWindow(form.Handle);
            SetWindowPos(form.Handle, HWND_TOPMOST, 0, 0, 0, 0, SWP_NOMOVE | SWP_NOSIZE | SWP_SHOWWINDOW);
        };

        timer.Start();
        Application.Run(form);
    }

    private static Button CrearBotonWindows(string texto, Point ubicacion, Color fondo, Color textoColor) {
        Button btn = new Button();
        btn.Text = texto;
        btn.Location = ubicacion;
        btn.Size = new Size(100, 32);
        btn.FlatStyle = FlatStyle.Flat;
        btn.FlatAppearance.BorderSize = 1;
        btn.FlatAppearance.BorderColor = Color.FromArgb(70, 70, 70);
        btn.BackColor = fondo;
        btn.ForeColor = textoColor;
        btn.Font = new Font("Segoe UI", 9f, FontStyle.Regular);
        btn.Cursor = Cursors.Hand;
        
        if (fondo == Color.FromArgb(0, 103, 192)) {
            btn.FlatAppearance.BorderColor = Color.FromArgb(0, 120, 215);
            btn.MouseEnter += (s, e) => { btn.BackColor = Color.FromArgb(24, 117, 200); };
            btn.MouseLeave += (s, e) => { btn.BackColor = fondo; };
        }
        return btn;
    }

    private static void AplicarEsquinasRedondeadas(Form formulario, int radio) {
        GraphicsPath path = new GraphicsPath();
        path.AddArc(0, 0, radio, radio, 180, 90);
        path.AddArc(formulario.Width - radio, 0, radio, radio, 270, 90);
        path.AddArc(formulario.Width - radio, formulario.Height - radio, radio, radio, 0, 90);
        path.AddArc(0, formulario.Height - radio, radio, radio, 90, 90);
        formulario.Region = new Region(path);
    }
}
"@

# --- COMPILACION ---
$providerOptions = [System.Collections.Generic.Dictionary[string,string]]::new()
$providerOptions.Add("CompilerVersion", "v4.0")
$codeProvider = [Microsoft.CSharp.CSharpCodeProvider]::new($providerOptions)

$compilerParameters = [System.CodeDom.Compiler.CompilerParameters]::new()
$compilerParameters.GenerateExecutable = $true
$compilerParameters.OutputAssembly = "$proyectoRuta\AlertaCICD.exe"
$compilerParameters.CompilerOptions = "/target:winexe"

$compilerParameters.ReferencedAssemblies.Add("System.dll") | Out-Null
$compilerParameters.ReferencedAssemblies.Add("System.Windows.Forms.dll") | Out-Null
$compilerParameters.ReferencedAssemblies.Add("System.Drawing.dll") | Out-Null

if (Test-Path $iconoRuta) {
    $compilerParameters.CompilerOptions += " /win32icon:`"$iconoRuta`""
}

$compilerResults = $codeProvider.CompileAssemblyFromSource($compilerParameters, $source)

if ($compilerResults.Errors.Count -gt 0) {
    foreach ($error in $compilerResults.Errors) { Write-Error $error.ToString() }
} else {
    Write-Host "EXE RECOMPILADO CON EXITO - TIEMPO EXTENDIDO A 15 SEGUNDOS." -ForegroundColor Green
}