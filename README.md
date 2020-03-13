# Error Handler for TYPO3

This extension implements a versatile Error Handler for the
[TYPO3 CMS Site Handling](https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/SiteHandling/ErrorHandling.html).

## Features

* Seamless integration with the [TYPO3 Site Handling](https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/SiteHandling/Index.html)
* Fetch and display any TYPO3 page in case of an error
* Configuration of the request, that fetches the URL
   * Adjust TYPO3-internal language
   * Define additional `GET` query parameters (per Site and per `errorCode`)
   * Automatically manage authentication (HTTP Basic authentication \[[RFC 2617](https://tools.ietf.org/html/rfc2617)\] / [TYPO3 Frontend User Authentication](https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Authentication/Index.html#authentication))
   * And more..
* Statistics
   * Data collection (can be disabled)
   * Analysis backend module (still experimental)

## Installation

Composer package

```
composer require aaw-team/pagenotfoundhandling
```

## Docs

Documentation is available from
https://docs.typo3.org/p/aaw-team/pagenotfoundhandling/master/en-us/

### Troubleshooting

See https://github.com/aaw-team/pagenotfoundhandling/wiki/Troubleshooting

## Changelog

See https://github.com/aaw-team/pagenotfoundhandling/wiki/Changelog

## License

GPLv2.0

## Copyright

2014-2020 by Agentur am Wasser | Maeder & Partner AG (https://www.agenturamwasser.ch)
