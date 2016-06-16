.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _section-domain-record-configuration:

===========================
Domain record configuration
===========================

This configuration is almost the same as in
:ref:`section-extension-manager-configuration`. The described options either
differ from Extension Manager configuration or are only available here.


.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Enable 404 handling configuration for this domain record

   Data type
         boolean

   Description
         It enables the possibility of a per-domain configuration. The default
         setting (0) prevents, that every domain must be configured, when
         per-domain config is enabled in the extension manager

   Default
         0

.. container:: table-row

   Property
         HTTP Forbidden header

   Data type
         options

   Description
         This option differs a little from extension manager config. There is
         one additional option None to prevent sending headers even if a
         special header is configured in extension manager. Default means
         'act as configured in extension manager'.

         **Available options:**

         * None (suppress config from extension manager)
         * Default (as configured in extension manager)
         * HTTP/1.1 400 Bad Request
         * HTTP/1.1 401 Unauthorized
         * HTTP/1.1 402 Payment Required
         * HTTP/1.1 403 Forbidden

   Default
         Default


.. ###### END~OF~TABLE ######
