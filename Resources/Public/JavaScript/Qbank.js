/**
 * Module: Pixelant/QbankConnector/Qbank
 * Qbank API communication
 */
// define(['jquery', 'http://connector.qbank.se/qbank-connector.min.js'], function($, QBankConnector) {
define([
  'jquery',
  'nprogress',
  'TYPO3/CMS/Backend/Modal',
  'TYPO3/CMS/Backend/Severity',
  'TYPO3/CMS/Qbank/QbankSource'
], function($, NProgress, Modal, Severity) {
  'use strict';

  var QbankSelectorPlugin = function(element) {
    var self = this;

    self.addMedia = function (media) {
      console.log('add media', media);
    }

    self.openModal = function () {
      self.$modal = Modal.advanced({
        type: Modal.types.default,
        title: 'QBank',
        content: 'content',
        severity: Severity.default,
        size: Modal.sizes.full,
        additionalCssClasses: [
          'qbankConnectorWrapper'
        ],
        callback: function(modal) {
          var qbankConnector = new QBankConnector({
            api: {
              host: '',
              access_token: ''
            },
            gui: {
              basehref: ''
            }
          });

          self.mediaPicker = new qbankConnector.mediaPicker({
            container: '#qbankConnectorWrapper .modal-body'
          });
        }
      });
    };
  }

  $(document).on('click', '.t3js-qbank-selector-btn', function (event) {
    event.preventDefault();

    var selectorPlugin = $(this).data('qbankSelectorPlugin');
    if (!selectorPlugin) {
      selectorPlugin = new QbankSelectorPlugin(this);
      $(this).data('qbankSelectorPlugin', selectorPlugin);
    }

    selectorPlugin.openModal();
  });

  document.addEventListener('QbankAddMedia', function (event) {
    selectorPlugin.addMedia();
  });
});
