#!/bin/bash

echo "clear temp and autoload"

rm -R ./typo3conf/autoload/*
rm -R ./typo3temp/autoload/*
rm -R ./typo3temp/var/Cache*
rm -R ./typo3temp/Cache/*
