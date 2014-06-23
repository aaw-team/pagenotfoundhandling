.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

==============================
Known problems
==============================


With solution(s)
==============================

Extension realurl
^^^^^^^^^^^^^^^^^

At the moment the automatic language guessing does not work when the language
parameter is translated with realurl.

**Solution:**
Install extension `realurl_force404lang <http://typo3.org/extensions/repository/view/realurl_force404lang/current/>`_
if you have a multilingual environment with realurl.
Make sure, you install realurl_force404lang **after** pagenotfoundhandling.
Then - in extension manager configuration of realurl_force404lang - set the
value of basic.pageNotFound_handling empty.


Extension aeurltool
^^^^^^^^^^^^^^^^^^^

The extension aeurltool has a page not found handling too.

**Solution:**
To avoid conflicting 404 configurations, disable 'Use 404-Errorpage' in the
backend module of aeurltool.

Without solution(s)
==============================

Extension cooluri
^^^^^^^^^^^^^^^^^

With cooluri enabled, the extension does not work at all. Cooluri has its own
404 handling and won't report back to TypoScriptFrontendController