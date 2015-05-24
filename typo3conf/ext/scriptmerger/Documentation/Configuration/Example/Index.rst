.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

Example
=======

The following configuration was used on one a production site. On this site all javascript and css files were
merged, compressed and minified. Furthermore I excluded any mootools scripts from being minified as they are
already provided as minified source. Also the deployJava script was ignored in the merging procecess, because this
script was added inside the body and the merging would cause an invalid loading order afterwards. On page
type 101,the scriptmerger plugin was completely disabled, because the type contains a template which was used by a
pdf generator extension that had several problems with the minified and merged scripts.

You see that it possible to get an advanced configuration with just some small changes to the configuration!

.. code-block:: typoscript

	plugin.tx_scriptmerger {
		javascript {
			parseBody = 1
			minify {
				ignore = \?,\.min\.,mootools-
			}

			merge {
				ignore = \?,deployJava
			}
		}
	}

	[globalVar = GP:type = 101]
	plugin.tx_scriptmerger {
		css.enable = 0
		javascript.enable = 0
	}
	[global]