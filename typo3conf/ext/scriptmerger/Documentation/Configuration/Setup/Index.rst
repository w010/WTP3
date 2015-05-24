.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

Setup
=====

Properties
^^^^^^^^^^

===================================================== ========================================== ======================= =============================================
Property                                              Data type                                  :ref:`t3tsref:stdwrap`  Default
===================================================== ========================================== ======================= =============================================
urlRegularExpressions.pattern1_                       :ref:`t3tsref:data-type-string`            no                      *empty*
urlRegularExpressions.pattern1.replacement_           :ref:`t3tsref:data-type-string`            no                      *empty*
urlRegularExpressions.pattern1.useWholeContent_       :ref:`t3tsref:data-type-boolean`           no                      0
===================================================== ========================================== ======================= =============================================

Property Details
^^^^^^^^^^^^^^^^

urlRegularExpressions
"""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.urlRegularExpressions =` :ref:`t3tsref:data-type-array`

Custom regular expressions that process any links on the site. Can be used to change links to use a CDN or an
special cookie-free asset domain. It's possible to define multiple expressions.

urlRegularExpressions.pattern1
""""""""""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.urlRegularExpressions.pattern1 =` :ref:`t3tsref:data-type-string`

Regular expression (e.g. http://domain.tld((filadmin\|typo3temp/).+))

urlRegularExpressions.pattern1.replacement
""""""""""""""""""""""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.urlRegularExpressions.pattern1.replacement =` :ref:`t3tsref:data-type-string`

Replacement expression (e.g. http://assets.domain.tld/$1)

urlRegularExpressions.pattern1.useWholeContent
""""""""""""""""""""""""""""""""""""""""""""""

:typoscript:`plugin.tx_scriptmerger.urlRegularExpressions.pattern1.useWholeContent =` :ref:`t3tsref:data-type-boolean`

Use the whole page content as the source for the regular expression instead of only URLs. In this case you must
handle the quoting and modifier stuff yourself (e.g. /http:\/\/de\./is)