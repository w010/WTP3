.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

Installation
------------

The installation process of the extension isn't that simple, but don't give up too fast. First you need
to download and install the extension with the extension manager and include the static extension template
into your root page like you possibly did many times before for other extensions. As an additional step it's
required to include the contents of the example .htaccess file in the extension directory in your own site-wide
.htaccess - in most cases directly after the RewriteBase setting. Now you can finally test the output of your page
and tweak the scriptmerger configuration as it's possible that you will experience javascript errors or other
issues, because of a wrong order of the executed files.