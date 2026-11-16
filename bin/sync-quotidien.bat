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

REM Purge dans tous les cas : un sync qui echoue apres avoir importe des
REM scrutins doit les montrer quand meme (app:sync:quotidien purge deja le
REM cache HTTP des qu'une etape a ecrit ; ceci vide aussi cache.app). Le delta
REM non acquitte sera repris par la prochaine execution.
php "%PROJET%\bin\console" cache:pool:clear cache.app --no-ansi >> "%JOURNAL%" 2>&1
rmdir /s /q "%PROJET%\var\cache\prod\http_cache" 2>nul

if %CODE% NEQ 0 (
  echo ECHEC ^(code %CODE%^) >> "%JOURNAL%"
) else (
  echo Termine >> "%JOURNAL%"
)

endlocal & exit /b %CODE%
