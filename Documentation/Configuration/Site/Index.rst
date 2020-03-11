.. include:: ../../Includes.txt

.. _section-site-configuration:

========================
TYPO3 Site Configuration
========================

As of TYPO3 v9, the Site Configuration became available. This extension extends
the errorHandling options of the Site Configuration.

To configure a error page, add a new `errorHandling` section, set the
"HTTP Error Status Code", choose "PHP Class" in "How to handle Errors" and then
add this extension's error handler in "ErrorHandler Class Target (FQCN)" (you
can easily select it from the succestion selector). You might be forced to
reload the screen multiple times during that process.

Finally, you can select the desired page in "Error Page". This will then be the
page, which gets displayed in case of an error (well, not "an" error, but the
error you chose in "HTTP Error Status Code").

.. figure:: ../../Images/Introduction/SiteConfiguration.png
   :alt: Error handling in Site Configuration

Error handling in Site Configuration

.. _section-site-configuration-advanced:

Advanced error handling
=======================

In the Tab "Error Handling (advanced)" of the Site Configuration, you'll find
all the nice options you might want to use. At the moment, the descriptions
right beneath the option names (and the option names themselves) must hold as
"documentation".

.. figure:: ../../Images/Introduction/SiteConfigurationAdvanced.png
   :alt: Advanced error handling in Site Configuration

Advanced error handling in Site Configuration
