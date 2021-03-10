/**
 * Module: TYPO3/CMS/Qbank
 * Qbank API communication
 */
// https://connector.qbank.se/qbank-connector.min.js
define(['jquery', 'TYPO3/CMS/Qbank/qbank-connector'], function($) {

  /**
   * the main popover object
   *
   * @type {{}}
   * @exports TYPO3/CMS/Qbank/QbankConnectorSource
   */
  var QbankConnectorSource = {
  };

  TYPO3.QbankConnectorSource = QbankConnectorSource;
  return QbankConnectorSource;
});
