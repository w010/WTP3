# cli wrapper dla typo3 na dockerze
# v0.1
# wolo.pl '.' studio 2017

# w ten sposob przypisujemy parametry. to musi byc na samym poczatku
param(
	[string]$a,
	[string]$b,
	[string]$c,
	[string]$d
)

# test
# Write-Host $a

vagrant ssh --command "docker exec -it berglanddev_php_1 php /var/www/htdocs/typo3/cli_dispatch.phpsh $a $b $c $d"


# example:
# typocli.ps1 extbase cacheapi:clearallcaches