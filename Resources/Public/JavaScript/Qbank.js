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
  'TYPO3/CMS/Core/Ajax/AjaxRequest',
  'TYPO3/CMS/Qbank/QbankSource'
], function($, NProgress, Modal, Severity, MessageUtility, AjaxRequest) {
  'use strict';

  MessageUtility = MessageUtility.MessageUtility;

  var QbankSelectorPlugin = function(element) {
    var self = this;

    self.$button = $(element);
    self.irreObjectId = self.$button.data('fileIrreObject');
    self.allowedExtensions = self.$button.data('fileAllowed').split(',');

    /**
     * Adds QBank media to file IRRE element.
     *
     * @param media
     */
    self.addMedia = function (media) {
      NProgress.start();

      const request = new AjaxRequest(TYPO3.settings.ajaxUrls['qbank_download_file']);

      request.post({
        mediaId: media.mediaId
      }).then(
        async function(response) {
          const data = await response.resolve();

          if (response.response.status !== 200 || !data.success) {
            var errorMessage = 'The request could not be completed due to an error.';

            if (data.message) {
              errorMessage = data.message;
            }

            self.displayError(errorMessage);

            NProgress.done();

            return;
          }

          MessageUtility.send({
            actionName: 'typo3:foreignRelation:insert',
            objectGroup: self.irreObjectId,
            table: 'sys_file',
            uid: data.fileUid,
          });

          NProgress.done();
        },
        function (error) {
          self.displayError('The request failed due to an error: ' + error.status + ' ' + error.statusText);

          NProgress.done();
        }
      );
    }

    /**
     * Displays an error message in a modal.
     *
     * @param message The error message to display
     */
    self.displayError = function (message) {
      const errorModal = Modal.confirm(
        'ERROR',
        message,
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
    }

    /**
     * Returns true if the media can be inserted.
     *
     * @param media result from QBank
     * @returns {boolean}
     */
    self.validateMedia = function (media) {
      if (self.allowedExtensions.indexOf(media.extension) === -1) {
        self.displayError('The file extension "' + media.extension + '" is not allowed here.');
        return false;
      }

      return true;
    }

    /**
     * Opens the QBank selector inside a modal.
     */
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
              if (self.validateMedia(media)) {
                self.addMedia(media);
              }
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
    }

    selectorPlugin.openModal();
  });

  // Enable the button so it can't be clicked too early.
  $('.t3js-qbank-selector-btn').removeAttr('disabled');
});
