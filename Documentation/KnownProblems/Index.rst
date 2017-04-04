.. include:: ../Includes.txt

.. _section-known-problems:

==============
Known problems
==============


With solution
=============

Language guessing and realurl
-----------------------------

Language guessing does not always work when the language parameter is translated
with old versions of realurl.

**Solution:**
Update realurl to at least version 1.12.8


Extension cooluri
-----------------

By default, cooluri handles 404 errors itself.

**Solution:**

Configure cooluri to use the default TYPO3 404 handling. Luckily it provides
this option itself, you just have to uncomment it in CoolUriConf.xml:

.. code-block:: xml

    <cooluri>
        <cache>
            <pagenotfound>
                <behavior type="userfunc">Bednarik\Cooluri\Integration\Cooluri->pageNotFound</behavior>
            </pagenotfound>
        </cache>
    </cooluri>


Without solution
================

Language guessing and cooluri
-----------------------------

With cooluri, the language guessing does not work at all.
