.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

Constants
=========

Properties
^^^^^^^^^^

===================================================== ========================================== ======================= =============================================
Property                                              Data type                                  :ref:`t3tsref:stdwrap`  Default
===================================================== ========================================== ======================= =============================================
externalFileCacheLifetime_                            :ref:`t3tsref:data-type-integer`           no                      3600
css.enable_                                           :ref:`t3tsref:data-type-boolean`           no                      1
css.addContentInDocument_                             :ref:`t3tsref:data-type-boolean`           no                      0
css.doNotRemoveInDoc_                                 :ref:`t3tsref:data-type-boolean`           no                      0
css.mergedFilePosition_                               :ref:`t3tsref:data-type-string`            no                      *empty*
css.minify.enable_                                    :ref:`t3tsref:data-type-boolean`           no                      1
css.minify.ignore_                                    :ref:`t3tsref:data-type-string`            no                      \.min\.
css.compress.enable_                                  :ref:`t3tsref:data-type-boolean`           no                      1
css.compress.ignore_                                  :ref:`t3tsref:data-type-string`            no                      \.gz\.
css.merge.enable_                                     :ref:`t3tsref:data-type-boolean`           no                      1
css.merge.ignore_                                     :ref:`t3tsref:data-type-string`            no                      *empty*
css.uniqueCharset.enable_                             :ref:`t3tsref:data-type-boolean`           no                      1
css.uniqueCharset.value_                              :ref:`t3tsref:data-type-string`            no                      @charset "UTF-8";
css.postUrlProcessing.pattern_                        :ref:`t3tsref:data-type-string`            no                      *empty*
css.postUrlProcessing.replacement_                    :ref:`t3tsref:data-type-string`            no                      *empty*
javascript.enable_                                    :ref:`t3tsref:data-type-boolean`           no                      1
javascript.addContentInDocument_                      :ref:`t3tsref:data-type-boolean`           no                      0
javascript.parseBody_                                 :ref:`t3tsref:data-type-boolean`           no                      0
javascript.doNotRemoveInDocInBody_                    :ref:`t3tsref:data-type-boolean`           no                      1
javascript.doNotRemoveInDocInHead_                    :ref:`t3tsref:data-type-boolean`           no                      0
javascript.mergedHeadFilePosition_                    :ref:`t3tsref:data-type-string`            no                      *empty*
javascript.mergedBodyFilePosition_                    :ref:`t3tsref:data-type-string`            no                      </body>
javascript.deferLoading_                              :ref:`t3tsref:data-type-boolean`           no                      0
javascript.minify.enable_                             :ref:`t3tsref:data-type-boolean`           no                      1
javascript.minify.ignore_                             :ref:`t3tsref:data-type-string`            no                      \?,\.min\.
javascript.minify.useJSMinPlus_                       :ref:`t3tsref:data-type-boolean`           no                      1
javascript.minify.useJShrink_                         :ref:`t3tsref:data-type-boolean`           no                      0
javascript.compress.enable_                           :ref:`t3tsref:data-type-boolean`           no                      1
javascript.compress.ignore_                           :ref:`t3tsref:data-type-string`            no                      \?,\.gz\.
javascript.merge.enable_                              :ref:`t3tsref:data-type-boolean`           no                      1
javascript.merge.ignore_                              :ref:`t3tsref:data-type-string`            no                      \?
===================================================== ========================================== ======================= =============================================

Property Details
^^^^^^^^^^^^^^^^

externalFileCacheLifetime
"""""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.externalFileCacheLifetime =` :ref:`t3tsref:data-type-integer`

“Time to live” in seconds for the cache files of external CSS and JS

css.enable
""""""""""

:typoscript:`plugin.tx_scriptmerger.css.enable =` :ref:`t3tsref:data-type-boolean`

Enable all css processes

css.addContentInDocument
""""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.css.addContentInDocument =` :ref:`t3tsref:data-type-boolean`

Embed the resulting css directly into the document in favor of a
linked resource (this automatically disables the compression step).

css.doNotRemoveInDoc
""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.css.doNotRemoveInDoc=` :ref:`t3tsref:data-type-boolean`

This option can be used to prevent embedded scripts to be merged, minified or compressed.

css.mergedFilePosition
""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.css.mergedFilePosition=` :ref:`t3tsref:data-type-string`

Use this option to define the final position of the merged files and any other ones that were processed by
the scriptmerger. The value is used inside a regular expression, but you cannot use any wildcards or such stuff.
A possible value could be "</head>". If empty, the position of the first merged file is reused.

css.minify.enable
"""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.css.minify.enable =` :ref:`t3tsref:data-type-boolean`

Enable the minification process

css.minify.ignore
"""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.css.minify.ignore =` :ref:`t3tsref:data-type-string`

A comma-separated list of files which should be ignored from the minification process.
Be careful, because you need to quote the characters yourself as the entries are considered as regular expressions.
If a file is added to all three ignore options, it's not touched at all.

css.compress.enable
"""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.css.compress.enable =` :ref:`t3tsref:data-type-boolean`

Enable the compression process

css.compress.ignore
"""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.css.compress.ignore =` :ref:`t3tsref:data-type-string`

A comma-separated list of files which should be ignored from the compression process.
Be careful, because you need to quote the characters yourself as the entries are considered as regular expressions.
If a file is added to all three ignore options, it's not touched at all.

css.merge.enable
""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.css.merge.enable =` :ref:`t3tsref:data-type-boolean`

Enable the merging process

css.merge.ignore
""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.css.merge.ignore =` :ref:`t3tsref:data-type-string`

A comma-separated list of files which should be ignored from the merging process.
Be careful, because you need to quote the characters yourself as the entries are considered as regular expressions.
If a file is added to all three ignore options, it's not touched at all. Also this setting will trigger the
process to readd the file at the same position it was taken from.

css.uniqueCharset.enable
""""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.css.uniqueCharset.enable =` :ref:`t3tsref:data-type-boolean`

Enables the replacement of multiple @charset definitions by the given value option

css.uniqueCharset.value
"""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.css.uniqueCharset.value =` :ref:`t3tsref:data-type-string`

@charset definition that is added on the top of the merged css files

css.postUrlProcessing.pattern
"""""""""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.css.postUrlProcessing.pattern =` :ref:`t3tsref:data-type-string`

Regular expression pattern (e.g. /(fileadmin)/i)

The pattern and replacement values can be used to fix broken urls inside the combined css file.

css.postUrlProcessing.replacement
"""""""""""""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.css.postUrlProcessing.replacement =` :ref:`t3tsref:data-type-string`

Regular expression replacement (e.g. prefix/$i)

javascript.minify.enable
""""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.javascript.minify.enable =` :ref:`t3tsref:data-type-boolean`

Enable the minification process

javascript.minify.ignore
""""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.javascript.minify.ignore =` :ref:`t3tsref:data-type-string`

A comma-separated list of files which should be ignored from the minification process.
Be careful, because you need to quote the characters yourself as the entries are considered as regular expressions.
If a file is added to all three ignore options, it's not touched at all.

javascript.minify.useJSMinPlus
""""""""""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.javascript.minify.useJSMinPlus =` :ref:`t3tsref:data-type-boolean`

Use JSMin+ instead of JSMin or JShrink.

javascript.minify.useJShrink
""""""""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.javascript.minify.useJShrink =` :ref:`t3tsref:data-type-boolean`

Use JShrink instead of JSMin or JSMin+.

javascript.compress.enable
""""""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.javascript.compress.enable =` :ref:`t3tsref:data-type-boolean`

Enable the compression process.

javascript.compress.ignore
""""""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.javascript.compress.ignore =` :ref:`t3tsref:data-type-string`

A comma-separated list of files which should be ignored from the compression process.
Be careful, because you need to quote the characters yourself as the entries are considered as regular expressions.
If a file is added to all three ignore options, it's not touched at all.

javascript.merge.enable
"""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.javascript.merge.enable =` :ref:`t3tsref:data-type-boolean`

Enable the merging process

javascript.merge.ignore
"""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.javascript.merge.ignore =` :ref:`t3tsref:data-type-string`

A comma-separated list of files which should be ignored from the merging process.
Be careful, because you need to quote the characters yourself as the entries are considered as regular expressions.
If a file is added to all three ignore options, it's not touched at all. Also this setting will trigger the
process to readd the file at the same position it was taken from.

javascript.enable
"""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.javascript.enable=` :ref:`t3tsref:data-type-boolean`

Enable all javascript processes (by default only for the head section)

javascript.addContentInDocument
"""""""""""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.javascript.addContentInDocument=` :ref:`t3tsref:data-type-boolean`

Embed the resulting javascript code directly into the document in favor of a
linked resource (this automatically disables the compression step).

javascript.parseBody
""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.javascript.parseBody=` :ref:`t3tsref:data-type-boolean`

Enable this option to enable the minification, processing and merging processes for the body section.
The resulting files are always included directly before the closing body tag.

javascript.doNotRemoveInDocInBody
"""""""""""""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.javascript.doNotRemoveInDocInBody=` :ref:`t3tsref:data-type-boolean`

This option can be used to prevent embedded scripts inside the document of the body section to be merged, minified
or compressed as this is in many cases a possible error source in the final result. Therefore the option is
enabled by default.

javascript.doNotRemoveInDocInHead
"""""""""""""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.javascript.doNotRemoveInDocInHead=` :ref:`t3tsref:data-type-boolean`

This option can be used to prevent embedded scripts inside the document of the head section to be merged,
minified or compressed.

javascript.mergedHeadFilePosition
"""""""""""""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.javascript.mergedHeadFilePosition=` :ref:`t3tsref:data-type-string`

Use this option to define the final position of the merged files and any other ones in the head section that
were processed by the scriptmerger. The value is used inside a regular expression, but you cannot use any
wildcards or such stuff. A possible value could be "</head>".  If empty, the position of the first merged file
is reused.

javascript.mergedBodyFilePosition
"""""""""""""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.javascript.mergedBodyFilePosition=` :ref:`t3tsref:data-type-string`

Use this option to define the final position of the merged files and any other ones in the body section that
were processed by the scriptmerger. The value is used inside a regular expression, but you cannot use any
wildcards or such stuff.

javascript.deferLoading
"""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.javascript.deferLoading=` :ref:`t3tsref:data-type-boolean`

If you want to load your javascript always after the page onload event, then you are encouraged to activate this option.

Additional Information
^^^^^^^^^^^^^^^^^^^^^^

You can ignore any script or stylesheet by adding the data-ignore attribute with the value 1 to their tag.