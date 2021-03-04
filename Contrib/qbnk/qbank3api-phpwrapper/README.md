# QBank 3 API PHP Wrapper #

[![Latest Stable Version](https://poser.pugx.org/qbnk/qbank3api-phpwrapper/v/stable.svg)](https://packagist.org/packages/qbnk/qbank3api-phpwrapper)
[![Latest Unstable Version](https://poser.pugx.org/qbnk/qbank3api-phpwrapper/v/unstable.svg)](https://packagist.org/packages/qbnk/qbank3api-phpwrapper)
[![License](https://poser.pugx.org/qbnk/qbank3api-phpwrapper/license.svg)](https://packagist.org/packages/qbnk/qbank3api-phpwrapper)

## Introduction ##

QBank 3 API PHP Wrapper is a library that makes it easy to use the QBank 3 API directly from PHP. No need to handle the
connections or interpreting the results yourself.

## Installation ##

Install via composer.

	{
		"require": {
			"qbnk/qbank3api-phpwrapper": "dev-master"
		}
	}
	
## Usage ##

This documentation presumes that you have a firm grasp of the concepts in QBank. If you do not or perhaps want a 
reference to lean on, please read the articles in the Knowledge Base for QBank. The Knowledge Base is found at: 
[support.qbank.se](http://support.qbank.se)

Instantiate the wrapper by creating a new `QBankApi`. The `QBankApi` class constructor takes three arguments of which 
two are required. The first is the URL to the api of the QBank you want to connect to. If the QBank is hosted by QBNK 
the domain of the QBank is sufficient; otherwise the full URL to the API endpoint is needed.

The second argument is a `Credentials` object which contains all necessary information to authenticate against QBank.
These are as follows:

* Client id - A unique id used to identify the caller. Issued by QBNK and usually per organisation.
* Username - A username of a user in the QBank that you are connecting to. The access to content and actions are determined by this.
* Password - The users password.

To get a client id issued, please contact us at [support@qbank.se](mailto:support@qbank.se?subject=Request%20for%20API%20client%20id)

```php
<?php

use QBNK\QBank\API\QBankApi;
use QBNK\QBank\API\Credentials;

$credentials = new Credentials('CLIENT_ID', 'myUsername', 'myPassword');
$qbankApi = new QBankApi('customer.qbank.se', $credentials);

```

### Searching ###

Searching is essential to find `Media` in QBank. Usually one would have at least some criteria for listing `Media` but 
even when wanting to display _everything_ searching is the way to go. To find every `Media` in QBank you would execute 
a search with no parameters. With no filtering options set, the search would match everything just like the `*` wildcard 
in the terminal or similar.

```php
<?php

use QBNK\QBank\API\QBankApi;
use QBNK\QBank\API\Credentials;
use QBNK\QBank\API\Model\Search;

$credentials = new Credentials('CLIENT_ID', 'myUsername', 'myPassword');
$qbankApi = new QBankApi('customer.qbank.se', $credentials);
$results = $qbankApi->search()->search(new Search());
var_dump(count($results));	// Prints: int(50)
```

But why did we only get 50 results when our QBank contains thousands of `Media`? The simple answer is that searches are 
paginated to not flood you with data and return to you in a reasonable amount of time. The default page size is 50. It 
is pretty simple to get several pages of results with the number of results of your choosing in each.

```php
<?php

use QBNK\QBank\API\QBankApi;
use QBNK\QBank\API\Credentials;
use QBNK\QBank\API\Model\Search;

$credentials = new Credentials('CLIENT_ID', 'myUsername', 'myPassword');
$qbankApi = new QBankApi('customer.qbank.se', $credentials);
$search = new Search();
$search->setLimit(10);
for ($i = 0; $i < 5; $i++) {
	$search->setOffset($i * $search->getLimit());
    $results = $qbankApi->search()->search($search);
    var_dump(count($results));
}

/* 
Prints: 
int(10)
int(10)
int(10)
int(10)
int(10)
*/
```

The results of an executed `Search` is a `SearchResult` object. This is merely a wrapper class around an array of 
`Media` objects, but with some extra information tacked on. One of these are the very useful `getTotalHits()` method.
With it you can search with a reasonable limit and still print out the number of total results. You can then write code 
to fetch more results when needed.
 
`SearchResult` also implements `Iterator` so that you can loop over it just as a regular array. This makes it easy to 
display your results.

```php
<?php

use QBNK\QBank\API\QBankApi;
use QBNK\QBank\API\Credentials;
use QBNK\QBank\API\Model\Search;

$credentials = new Credentials('CLIENT_ID', 'myUsername', 'myPassword');
$qbankApi = new QBankApi('customer.qbank.se', $credentials);
$results = $qbankApi->search()->search(new Search());
var_dump(count($results));	// Prints: int(50)
var_dump($results->getTotalHits()); // Prints: int(34814);
foreach ($results as $media) {	//Prints a list of 50 media id and name combinations 
    echo 'ID: '.$media->getMediaId().' Name: '.$media->getName()."\n";
}
```

#### Searching with criteria

Usually we have some default criteria such as `Media` belonging to specific `Category` or maybe deployed to a specific 
site. This is easily done by adding criteria to the `Search` object.

```php
<?php

use QBNK\QBank\API\QBankApi;
use QBNK\QBank\API\Credentials;
use QBNK\QBank\API\Model\Search;

$credentials = new Credentials('CLIENT_ID', 'myUsername', 'myPassword');
$qbankApi = new QBankApi('customer.qbank.se', $credentials);
$search = new Search()
    ->setCategoryIds([1, 2]);
    ->setDeploymentSiteIds([1])
;
$results = $qbankApi->search()->search($search);
```

You may also want to filter on a `Property`. This is done by adding a `PropertyCriteria` to your search object. This 
will however not get those set in the `SearchResult`. To get `Property` values for `Media` in a `SearchResult` a 
`PropertyRequest` has to be added to the `Search`object.

```php
<?php

use QBNK\QBank\API\QBankApi;
use QBNK\QBank\API\Credentials;
use QBNK\QBank\API\Model\Search;

$credentials = new Credentials('CLIENT_ID', 'myUsername', 'myPassword');
$qbankApi = new QBankApi('customer.qbank.se', $credentials);
$search = new Search()
    ->setProperties([
    	new PropertyRequest()->setSystemName('property1'),
    	new PropertyCriteria()->setSystemName('property2')
    ])
;
$results = $qbankApi->search()->search($search);
```

There are many more possibilities to filter your searching by and this has just been a demonstration of some of the most 
used ones. Browse through the setters in the `Search` class and it should be pretty obvious what is possible.

### Events

Events are calls that are made to report back to QBank that something has happened worthy of statistics collection, an event.
Statistics for events are shown in QBank. 

To be able to report an event a session id must be obtained. The session id identifies the acting user over a period of time. 
To obtain a session id, a session source must be provided. The session source identifies the source of the event 
(eg. frontend, api, app). To get a session source issued, contact [support@qbank.se](mailto:support@qbank.se?subject=Request%20for%20Session%20Source).

```php
<?php

use QBNK\QBank\API\QBankApi;
use QBNK\QBank\API\Credentials;
use QBNK\QBank\API\Model\Search;

$credentials = new Credentials('CLIENT_ID', 'myUsername', 'myPassword');
$qbankApi = new QBankApi('customer.qbank.se', $credentials);
$sessionId = $qbankApi->events()->session(SESSION_SOURCE_ID, 'identifierhash', '127.0.0.1', 'CustomApp/1.0', USER_ID);
$qbankApi->events()->view($sessionId, MEDIA_ID);
```

To report custom events (the ones not covered by the methods available), just use the `custom()` method. These will be 
displayed in the statistics alongside the other.

```php
<?php

use QBNK\QBank\API\QBankApi;
use QBNK\QBank\API\Credentials;
use QBNK\QBank\API\Model\Search;

$credentials = new Credentials('CLIENT_ID', 'myUsername', 'myPassword');
$qbankApi = new QBankApi('customer.qbank.se', $credentials);
$sessionId = $qbankApi->events()->session(SESSION_SOURCE_ID, 'identifierhash', '127.0.0.1', 'CustomApp/1.0', USER_ID);
$qbankApi->events()->custom($sessionId, MEDIA_ID, 'Some custom event');
```

It is also possible to report external usage eg. from a CMS or other. This is not shown in the statistics, but in the 
media detail in QBank. This provides a good overview of where a media is used. Of course you can also remove the usage 
report if it is no longer in use. See the methods `addUsage()` and `removeUsage()` respectively.