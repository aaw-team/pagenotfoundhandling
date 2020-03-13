.. include:: ../../Includes.txt


.. _section-configuration-site:

===================
Site Error Handling
===================

The Site Error Handling options explained in depth.

.. figure:: ../../Images/SiteConfiguration.png
   :alt: Error handling in Site Configuration

   Error handling in Site Configuration


errorPhpClassFQCN
-----------------

:aspect:`Datatype`
    string

:aspect:`Description`
    This MUST be set to the class name of the PageErrorHandler of this
    extension:
    `AawTeam\Pagenotfoundhandling\ErrorHandler\PageErrorHandler` (or
    :php:`\AawTeam\Pagenotfoundhandling\ErrorHandler\PageErrorHandler::class` in
    PHP code).


errorPage
---------

:aspect:`Datatype`
    string

:aspect:`Description`
    The URI to the page, that sould be fetched and displayed as error page.

    .. important::

       Note that the resulting URI must be accessible to the webserver, which
       is hosting the TYPO3 website. Make sure that the webserver internally
       knows about the DNS name and the `errorPage` is accessible.

       In case of a problem you might want to use
       `debugErrorPageRequestException`.

:aspect:`Example`
    `t3://page?uid=123`


additionalGetParams
-------------------

:aspect:`Datatype`
    string [optional]

:aspect:`Description`
    Additional query parameters for the `GET` request, that fetches the error
    page (defined in `errorPage`).

    The current URL (that lead to the error handler being invoked) is available,
    through `###CURRENT_URL###`.

    Forbidden parameter names are (case insensitive): `id`, `chash`, `l`, `mp`.
    They will be quietly ignored.

:aspect:`Example`
    `&tx_myext[key]=value&errorUrl=###CURRENT_URL###`
