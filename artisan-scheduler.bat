@echo off
REM Ganti versi PHP sesuai di Laragon kamu
set PHP=C:\laragon\bin\php\php-8.2.29\php.exe

REM Pindah ke folder project ini
cd /d C:\laragon\www\Absensismpn1sedati

REM Pastikan environment & cache bersih saat start
%PHP% artisan optimize:clear

REM Jalankan scheduler worker
%PHP% artisan schedule:work
