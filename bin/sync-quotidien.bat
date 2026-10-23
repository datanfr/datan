@echo off
REM ---------------------------------------------------------------------------
REM Mise a jour quotidienne de la base datan depuis les donnees des Tricoteuses.
REM Lance par le Planificateur de taches Windows (voir bin\planifier-sync.ps1).
REM
REM APP_ENV=prod est imperatif : en dev, le journal Doctrine conserve chaque
REM requete en memoire et fait echouer l'import des votes nominatifs.
REM ---------------------------------------------------------------------------

setlocal

set "PROJET=%~dp0.."
set "APP_ENV=prod"
set "APP_DEBUG=0"

if not exist "%PROJET%\var\log" mkdir "%PROJET%\var\log"
set "JOURNAL=%PROJET%\var\log\sync-quotidien.log"

echo. >> "%JOURNAL%"
echo ===== %DATE% %TIME% ===== >> "%JOURNAL%"

php "%PROJET%\bin\console" app:sync:quotidien --no-ansi >> "%JOURNAL%" 2>&1
set CODE=%ERRORLEVEL%

if %CODE% NEQ 0 (
  echo ECHEC ^(code %CODE%^) >> "%JOURNAL%"
) else (
  REM Les pages sont mises en cache une heure ; on repart d'un cache propre
  REM pour que les nouveaux scrutins soient visibles immediatement.
  php "%PROJET%\bin\console" cache:pool:clear cache.app --no-ansi >> "%JOURNAL%" 2>&1
  rmdir /s /q "%PROJET%\var\cache\prod\http_cache" 2>nul
  echo Termine >> "%JOURNAL%"
)

endlocal & exit /b %CODE%
