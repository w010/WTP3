.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

Caching
-------

The extension adds a folder inside the typo3temp directory that contains several subfolders for the different
processes. If you are interested in the naming conventions of the files, you are encouraged to read the
source code. Each file inside this folder contains a hash which is created with the md5 algorithm
with the original file contents as a source. This is extremely useful, because we can detect changes to script
files or stylesheets by a simple comparison and automatically include the current version in the next rendering process.

The scriptmerger registers itself to a hook which is called after clearing of the caches. The registered class
simply removes any files inside the mentioned directories above which have an access date that is older than
two weeks.