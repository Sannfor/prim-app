# Penjalan gambar rancangan antarmuka Bab 4 untuk Bab 4 SRS.
#
# Sejak revisi Bab 4, daftar halaman yang ditangkap tinggal satu tempat, yaitu
# skrip pengemudi di akar proyek RPL. Berkas ini hanya meneruskan pemanggilan
# ke sana agar tidak ada dua daftar halaman yang bisa berbeda.
#
# Jalankan dari dalam folder prim:
#     powershell -ExecutionPolicy Bypass -File tools\buat-gambar-bab4.ps1
#
# Seluruh parameter diteruskan apa adanya, misalnya:
#     powershell -ExecutionPolicy Bypass -File tools\buat-gambar-bab4.ps1 -NoSample

[CmdletBinding()]
param(
    [string]$OutDir,
    [switch]$NoSample,
    [switch]$NoSync
)

$ErrorActionPreference = 'Stop'

$appDir    = Split-Path $PSScriptRoot -Parent
$akarProyek = Split-Path $appDir -Parent
$pengemudi  = Join-Path $akarProyek 'tools\buat-gambar-bab4.ps1'

if (-not (Test-Path $pengemudi)) {
    throw @"
Skrip pengemudi tidak ditemukan:
    $pengemudi

Berkas itu berada di luar folder prim, pada akar proyek RPL. Pastikan folder
prim masih berada di dalam proyek RPL, atau jalankan skrip pengemudi itu
langsung dari akar proyek:

    powershell -ExecutionPolicy Bypass -File tools\buat-gambar-bab4.ps1
"@
}

Write-Host "Meneruskan ke $pengemudi" -ForegroundColor Cyan

$argumen = @('-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', $pengemudi)

if ($OutDir)   { $argumen += @('-OutDir', $OutDir) }
if ($NoSample) { $argumen += '-NoSample' }
if ($NoSync)   { $argumen += '-NoSync' }

& powershell.exe @argumen
exit $LASTEXITCODE
