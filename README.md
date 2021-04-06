# QBank DAM for TYPO3

## Installation

1. Install the extension using Composer: `composer req pixelant/qbank`
2. Activate the extension in TYPO3 by using the _Admin Tools > Extensions_ module or by running `vendor/bin/typo3 extension:activate qbank; vendor/bin/typo3cms database:updateschema` in the command line.

## Configuration

In order for the extension to work, it must be configured. In the TYPO3 Backend, navigate to _Admin Tools > Settings > Extension Configuration > qbank_ and set the required global configuration options in the "Basic" tab. Configuration options in the "Optional" tab are not required, but enable additional functionality, such as usage reporting.

Configuration options can also be set using environment variables. These will override any setting made in the Extension Configuration module.

### Available global configuration options

####Basic

* **Qbank Client ID** (40 characters)<br>Environment variable: APP_QBANK_CLIENTID
* **Qbank Username**<br>Environment variable: APP_QBANK_USERNAME
* **Qbank User Password**<br>Environment variable: APP_QBANK_PASSWORD
* **Qbank host domain** e.g. "mycompany.qbank.se"<br>Environment variable: APP_QBANK_HOST
* **Target folder for downloaded files** Default: "1:user_upload/qbank"<br>Environment variable: APP_QBANK_DOWNLOADFOLDER

![Basic configuration options.](https://github.com/pixelant/qbank/raw/master/Documentation/Configuration/Images/configuration-basic.png)

#### Optional

* **Event session source ID** Setting this will enable usage reporting. To get a session source issued, contact [support@qbank.se](mailto:support@qbank.se).<br>Environment variable: APP_QBANK_SESSIONSOURCE
* **Deployment sites** Comma-separated list of deployment sites. Empty means show all.<br>Environment variable: APP_QBANK_DEPLOYMENTSITES

![Optional configuration options.](https://github.com/pixelant/qbank/raw/master/Documentation/Configuration/Images/configuration-optional.png)

### Site and language configuration

Deployment sites can also be configured on a per-site and per-site-language basis.

#### Site configuration

In the TYPO3 Backend, navigate to _Site Management > Sites >_ {Your site} _> Qbank_ tab. You can set the field "Deployment sites" to a comma-separated list of deployment sites. If empty, the value is inherited from the global extension configuration or environment variables.

![Per-site configuration of deployment site.](https://github.com/pixelant/qbank/raw/master/Documentation/Configuration/Images/site-configuration.png)

#### Site language configuration

In the TYPO3 Backend, navigate to _Site Management > Sites >_ {Your site} _> Languages_ tab. Select the language you would like to edit and find the "QBank deployment sites" option. You can set the field to a comma-separated list of deployment sites. If empty, the value is inherited from the site's "Deployment sites" field, global extension configuration, or environment variables.

![Per-site-language configuration of deployment site.](https://github.com/pixelant/qbank/raw/master/Documentation/Configuration/Images/language-configuration.png)

## Support

**For help with QBank DAM**, please contact [support@qbank.se](mailto:support@qbank.se).

**For help with installing and configuring** this TYPO3 extension, please [submit an issue](https://github.com/pixelant/qbank/issues/new?template=question.md).

## Bugs and feature requests

Bug reports and feature requests are very welcome. [Create a bug report or feature request](https://github.com/pixelant/qbank/issues/new)
