# Skrip pengemudi: hasilkan seluruh gambar rancangan antarmuka untuk Bab 4 SRS.
#
# Menyalakan server pengembangan, masuk sebagai pelanggan dan administrator,
# lalu menangkap layar penuh setiap halaman yang dibutuhkan Bab 4.
#
# Jalankan dari dalam folder prim:
#     powershell -ExecutionPolicy Bypass -File tools\buat-gambar-bab4.ps1

$ErrorActionPreference = 'Stop'

$appDir    = (Get-Location).Path
$outDir    = Join-Path $appDir 'docs\screens'
$token     = -join ((1..32) | ForEach-Object { '{0:x}' -f (Get-Random -Max 16) })
$base      = 'http://127.0.0.1:8000'
$profile   = Join-Path $appDir 'storage\app\chrome-profile'
$envFile   = Join-Path $appDir '.env'
$envBackup = Join-Path $env:TEMP 'prim-env-backup.txt'
$tool      = Join-Path $appDir 'tools\screenshot.mjs'

New-Item -ItemType Directory -Force -Path $outDir | Out-Null

Write-Host '== 1. Menyiapkan token rute bantuan ==' -ForegroundColor Cyan
Copy-Item $envFile $envBackup -Force
Add-Content -Path $envFile -Value ''
Add-Content -Path $envFile -Value 'PRIM_SCREENSHOT_TOKEN=__TOKEN__'.Replace('__TOKEN__', $token)
Write-Host "   token ditulis ke .env"

Write-Host '== 2. Menyalakan server pengembangan ==' -ForegroundColor Cyan
$server = Start-Process -FilePath 'php' -ArgumentList 'artisan', 'serve', '--host=127.0.0.1', '--port=8000' `
    -WorkingDirectory $appDir -PassThru -WindowStyle Hidden

$ready = $false
foreach ($i in 1..30) {
    Start-Sleep -Milliseconds 700
    try {
        $r = Invoke-WebRequest -Uri "$base/login" -UseBasicParsing -TimeoutSec 5
        if ($r.StatusCode -eq 200) { $ready = $true; break }
    } catch { }
}
if (-not $ready) { throw 'server tidak siap' }
Write-Host '   server siap'

Write-Host '== 3. Mengambil id paket untuk halaman konfirmasi pemesanan ==' -ForegroundColor Cyan
$ids = (& php (Join-Path $appDir 'tools\screenshot-ids.php') 2>$null) -join ''
$ids = $ids.Trim()
$parts = $ids -split '\|'
$planId = $parts[0]
$orderCode = if ($parts.Count -gt 1) { $parts[1] } else { '' }
if (-not $planId) { $planId = '1' }
Write-Host "   plan id = $planId"
Write-Host "   kode pesanan = $orderCode"

function Tangkap($label, $as, $pairs) {
    Write-Host ''
    Write-Host "== $label ==" -ForegroundColor Cyan

    $arguments = @($tool, '--width=1440', "--profile=$profile")

    if ($as) { $arguments += "--as=$as"; $arguments += "--token=$token" }

    foreach ($pair in $pairs) {
        $arguments += "--url=$($pair[0])"
        $arguments += "--out=$($pair[1])"
    }

    & node @arguments
}

$publik = @(
    @("$base/",                        (Join-Path $outDir 'gambar-4-1-beranda.png')),
    @("$base/katalog",                 (Join-Path $outDir 'gambar-4-2-katalog.png')),
    @("$base/katalog/netflix",         (Join-Path $outDir 'gambar-4-3-detail-layanan.png')),
    @("$base/cara-berlangganan",       (Join-Path $outDir 'gambar-4-4-cara-berlangganan.png'))
)

$pelanggan = @(
    @("$base/checkout/$planId",                          (Join-Path $outDir 'gambar-4-5-konfirmasi-pemesanan.png')),
    @("$base/transaksi/$orderCode",                      (Join-Path $outDir 'gambar-4-6-detail-pesanan.png')),
    @("$base/langganan",                                 (Join-Path $outDir 'gambar-4-7-langganan-saya.png')),
    @("$base/profil/pesanan",                            (Join-Path $outDir 'tambahan-pusat-pesanan.png'))
)

$admin = @(
    @("$base/admin",                    (Join-Path $outDir 'gambar-4-8-dashboard.png')),
    @("$base/admin/pesanan",            (Join-Path $outDir 'gambar-4-9-manajemen-pesanan.png')),
    @("$base/admin/produk",             (Join-Path $outDir 'gambar-4-10-manajemen-produk.png')),
    @("$base/admin/pengguna",           (Join-Path $outDir 'gambar-4-11-manajemen-pengguna.png'))
)

Tangkap 'Halaman publik' $null $publik
Tangkap 'Halaman pelanggan' 'andi@prim.test' $pelanggan
Tangkap 'Halaman pengelola' 'admin@prim.com' $admin

Write-Host ''
Write-Host '== 4. Membereskan ==' -ForegroundColor Cyan
if ($server -and -not $server.HasExited) { Stop-Process -Id $server.Id -Force -ErrorAction SilentlyContinue }
Copy-Item $envBackup $envFile -Force
Remove-Item $envBackup -Force -ErrorAction SilentlyContinue

# Pastikan token tidak tertinggal, termasuk bila cadangan .env sudah memuatnya.
$bersih = Get-Content $envFile | Where-Object { $_ -notmatch '^PRIM_SCREENSHOT_TOKEN=' }
Set-Content -Path $envFile -Value $bersih
Write-Host '   server dihentikan, token dibersihkan dari .env'

Write-Host ''
Write-Host '== Hasil ==' -ForegroundColor Green
Get-ChildItem $outDir -Filter '*.png' | Sort-Object Name | ForEach-Object {
    '   {0,-42} {1,8:N0} KB' -f $_.Name, ($_.Length / 1KB)
}
