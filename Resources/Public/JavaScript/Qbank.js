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
  'TYPO3/CMS/Backend/Utility/MessageUtility',
  'TYPO3/CMS/Qbank/QbankSource'
], function($, NProgress, Modal, Severity, MessageUtility) {
  'use strict';

  var QbankSelectorPlugin = function(element) {
    var self = this;

    self.$button = $(element);
    self.irreObjectId = self.$button.data('fileIrreObject');

    self.addMedia = function (media) {
      NProgress.start();

      $.ajax(
        TYPO3.settings.ajaxUrls['qbank_download_file'],
        {
          data: {
            mediaId: media.mediaId,
          },
          method: 'POST'
        }
      )
      .done(function (data) {
        MessageUtility.MessageUtility.send({
          actionName: 'typo3:foreignRelation:insert',
          objectGroup: self.irreObjectId,
          table: 'sys_file',
          uid: data.fileUid,
        });
      })
      .fail(function (response, message) {
        var errorMessage = 'The request could not be completed due to an error. (' + message + ')';

        if (message === 'error') {
          errorMessage = response.responseJSON.message;
        }

        var errorModal = Modal.confirm(
          'ERROR',
          errorMessage,
          Severity.error,
          [{
            text: TYPO3.lang['button.ok'] || 'OK',
            btnClass: 'btn-' + Severity.getCssClass(Severity.error),
            name: 'ok',
            active: true,
          }]
        ).on('confirm.button.ok', function() {
          errorModal.modal('hide');
        });
      })
      .always(function () {
        NProgress.done();
      });
    }

    self.openModal = function () {
      self.$modal = Modal.advanced({
        type: Modal.types.default,
        title: 'QBank',
        content: '',
        severity: Severity.default,
        size: Modal.sizes.full,
        callback: function(modal) {
          $('.modal-body', modal).empty();

          var qbankConnector = new QBankConnector({
            api: {
              host: TYPO3.settings.FormEngineInline.qbank.host,
              access_token: TYPO3.settings.FormEngineInline.qbank.token,
              protocol: 'https'
            },
            gui: {
              basehref: 'https://' + TYPO3.settings.FormEngineInline.qbank.host + '/connector/'
            }
          });

          var mediaPicker = new qbankConnector.mediaPicker({
            container: $('.modal-body', modal),
            modules: {
              content: {
                header: false,
                details: false
              },
              imageTool: false,
              upload: false,
              download: false,
              detail: {
                showUseOriginalButton: false
              }
            },
            onReady: function() {
              $('iframe', modal).height($('.modal-body', modal).innerHeight());
            },
            onSelect: function(media, crop, previousUsage) {
              self.$modal.modal('hide');
              self.addMedia(media);
            }
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
      //$(this).data('qbankSelectorPlugin', selectorPlugin);
    }

    selectorPlugin.openModal();
  });

  document.addEventListener('QbankAddMedia', function (event) {
    selectorPlugin.addMedia();
  });
});
