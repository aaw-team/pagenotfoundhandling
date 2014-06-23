.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

.. highlight:: typoscript


==============================
Administration
==============================

Installation
==============================
* Import and install the extension via extension manager

Once installed the extension is already active. By default the 404 Page is a
simple pure HTML template, that includes a title and a message with almost
no styles.

**Important:** If your default website language is not english, configure
your default language code in the extension manager.

Suggestions
^^^^^^^^^^^

* Optional you can make use of the third-party extension
  `static_info_tables <http://typo3.org/extensions/repository/view/static_info_tables/current/>`_.
* If you have a multilingual environment and
  `realurl <http://typo3.org/extensions/repository/view/realurl/current/>`_
  installed, also install extension
  `realurl_force404lang <http://typo3.org/extensions/repository/view/realurl_force404lang/current/>`_
  to make automatic language guessing work.
  
Available markers
==============================

In template files or in fetched pages, several markers will be replaced before
outputting:



.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Marker
         ###TITLE###
   
   Data type
         -
   
   Description
         'page_title' from locallang_404.xml
         

.. container:: table-row

   Marker
         ###MESSAGE###
   
   Data type
         -
   
   Description
         'page_message' from locallang_404.xml
         

.. container:: table-row

   Marker
         ###REASON_TITLE###
   
   Data type
         -
   
   Description
         'reason_title' from locallang_404.xml
         

.. container:: table-row

   Marker
         ###REASON###
   
   Data type
         -
   
   Description
         From TYPO3 (autofilled)
         

.. container:: table-row

   Marker
         ###CURRENT_URL_TITLE###
   
   Data type
         -
   
   Description
         'current_url_title' from locallang_404.xml
         

.. container:: table-row

   Marker
         ###CURRENT_URL###
   
   Data type
         -
   
   Description
         From TYPO3 (autofilled)



.. ###### END~OF~TABLE ######

