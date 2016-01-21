.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.


.. include:: ../../Includes.txt

.. highlight:: typoscript

================================
Extension Manager configuration
================================

Go to the Extension Manager, find the extension pagenotfoundhandling and click
on the extension name to open the configuration interface.

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         default404Page
   
   Data type
         int
   
   Description
         An uid of a page out of the page tree that will be displayed as
         content of the 404 page. '0' disables this feature. See
         3.1.3 Available Markers for details on marker substitution
   
   Default
         0


.. container:: table-row

   Property
         defaultTemplateFile
   
   Data type
         string
   
   Description
         This template will be used if no default404Page is configured.
         See 3.1.3 Available Markers for details on marker substitution
   
   Default
         EXT:pagenotfoundhandling/Resources/Private/Templates/default.html


.. container:: table-row

   Property
         additional404GetParams
   
   Data type
         string
   
   Description
         These will be appended to the URL when fetching default404Page.
         The marker::
         
           ###CURRENT_URL###
         
         is replaced within this string.
   
   Default
         -

.. container:: table-row

   Property
         default403Page
   
   Data type
         int
   
   Description
         Behaves like default404Page, but activates a separate handling for
         requests on protected pages. Leave empty to disable this feature.
   
   Default
         -

.. container:: table-row

   Property
         default403TemplateFile
   
   Data type
         string
   
   Description
         Behaves like defaultTemplateFile, but activates a separate handling
         for requests on protected pages. Leave empty to disable this feature.
   
   Default
         -

.. container:: table-row

   Property
         additional403GetParams
   
   Data type
         string
   
   Description
         These will be appended to the URL when fetching
         default404Page/default403Page in case of access restriction error.
         The marker::
         
           ###CURRENT_URL###
         
         is replaced within this string.
   
   Default
         -

.. container:: table-row

   Property
         default403Header
   
   Data type
         options
   
   Description
         Sent when a pages is not found because of access restrictions. Set to Default to prevent sending special headers.
         
         **Available options:**

         Default (Do not send special headers)
         
         HTTP/1.1 400 Bad Request
         
         HTTP/1.1 401 Unauthorized
         
         HTTP/1.1 402 Payment Required
         
         HTTP/1.1 403 Forbidden
   
   Default
           HTTP/1.1 403 Forbidden

.. container:: table-row

   Property
         disableDomainConfig
   
   Data type
         boolean
   
   Description
         Domain dependent configurations will be ignored. The TCA of
         sys_domains will not be extended.
   
   Default
         0

.. container:: table-row

   Property
         languageParam
   
   Data type
         string
   
   Description
         The _GET variable that holds the language uid. In most cases this will
         be "L", which is the TYPO3 default.
   
   Default
         L

.. container:: table-row

   Property
         ignoreLanguage
   
   Data type
         boolean
   
   Description
         The language parameter in the request URL ($_GET['L']) will be
         ignored, default language will be used. See option 'defaultLanguageKey'
         also.
   
   Default
         0

.. container:: table-row

   Property
         defaultLanguageKey
   
   Data type
         boolean
   
   Description
         This tells the extension which language is your default language.
         You can use values like 'de' for german, 'dk' for danish, etc..
         Use this only if your default language is not english (TYPO3 default).
         At the moment the extension only supports german and english, to use
         your own language, see the option 'locallangFile' blow.
   
   Default
         default

.. container:: table-row

   Property
         forceLanguage
   
   Data type
         int
   
   Description
         This language is one from sys_language (the pid is shown in the
         selector box). So, if you have no language records in your setup,
         the selector box will be empty. If this option is used $_GET['L']
         will be ignored.
   
   Default
         -

.. container:: table-row

   Property
         passthroughContentTypeHeader
   
   Data type
         boolean
   
   Description
         When a 404 page is defined, the HTTP header 'Content-Type' will be
         captured and sent when delivering the 404 page
   
   Default
         0

.. container:: table-row

   Property
         sendXForwardedForHeader
   
   Data type
         boolean
   
   Description
         Send the REMOTE_ADDR in the 'X-Forwarded-For' HTTP header when 
         fetching the 404 page. Be sure to correctly configure
         [TYPO3_CONF_VARS][SYS][reverseProxyIP] when using this feature.
   
   Default
         0

.. container:: table-row

   Property
         additionalHeaders
   
   Data type
         string
   
   Description
         Send additional HTTP headers with the 404/403 page response.
         Multiple headers are separated with '|'
   
   Default
         -

.. container:: table-row

   Property
         absoluteReferencePrefix
   
   Data type
         string
   
   Description
         If your TYPO3 installation runs in a subdir of the DOCUMENT_ROOT,
         add the relative path from DOCUMENT_ROOT to the installation here
   
   Default
         -

.. container:: table-row

   Property
         digestAuthentication
   
   Data type
         string
   
   Description
         If the 404/403 page is access restricted with HTTP digest
         authentication, you can configure the username and password here.
         
         Format: "username:password"
         
         HTTP digest authentication requires cURL to be installed and enabled in
         $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlUse']
         
         Note: this is NOT required for HTTP basic authentication (this type of
         authentication works out-of-the box)!
   
   Default
         -


.. ###### END~OF~TABLE ######


