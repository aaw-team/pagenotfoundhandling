.. include:: ../Includes.txt

.. _section-administration:

==============================
Administration
==============================

.. _section-installation:

Installation
==============================

Install the extension via extension manager.

Once installed the extension is already active. By default the 404 Page is a
simple pure HTML template, that includes a title and a message with almost
no styles.

.. important::
   If your default website language is not english, configure
   your default language code in the extension manager.

Suggestions
^^^^^^^^^^^

* Optional you can make use of the third-party extension
  `static_info_tables <http://typo3.org/extensions/repository/view/static_info_tables/current/>`_.

.. _section-available-markers:

Available markers
==============================

In template files or in fetched pages, several markers will be replaced before
outputting:

.. container:: table-row

   Marker
         ###TITLE###

   Description
         ``page_title`` from locallang_404.xml


.. container:: table-row

   Marker
         ###MESSAGE###

   Description
         ``page_message`` from locallang_404.xml


.. container:: table-row

   Marker
         ###REASON_TITLE###

   Description
         ``reason_title`` from locallang_404.xml


.. container:: table-row

   Marker
         ###REASON###

   Description
         From TYPO3 (autofilled)


.. container:: table-row

   Marker
         ###CURRENT_URL_TITLE###

   Description
         ``current_url_title`` from locallang_404.xml


.. container:: table-row

   Marker
         ###CURRENT_URL###

   Description
         From TYPO3 (autofilled)
