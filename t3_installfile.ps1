
$PSScriptRoot = Split-Path -Parent -Path $MyInvocation.MyCommand.Definition
#echo "$PSScriptRoot"
echo > "$PSScriptRoot/typo3conf/ENABLE_INSTALL_TOOL"
pause
