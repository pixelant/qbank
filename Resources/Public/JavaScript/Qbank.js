/**
 * Module: Pixelant/QbankConnector/Qbank
 * Qbank API communication
 */
// define(['jquery', 'http://connector.qbank.se/qbank-connector.min.js'], function($, QBankConnector) {
define(['jquery','TYPO3/CMS/Core/Ajax/AjaxRequest', 'TYPO3/CMS/Backend/Utility/MessageUtility',  'nprogress', 'TYPO3/CMS/Backend/Modal', 'TYPO3/CMS/Backend/Severity', 'TYPO3/CMS/Qbank/QbankSource'], function($, ajaxRequest, MessageUtility, NProgress, Modal, Severity) {
	'use strict';

    var Qbank = {
        config: {
            api: {
                host: clienturl,
                access_token: token
            },
            gui: {
                basehref: 'http://'+clienturl+'/connector/'
            },
            deploymentSite: deploymentSites
        },
        target: $('body').data('target-folder'),
		irreObjectUid: $('body').data('irre-object-id'),
		allowed: $('body').data('allowed'),
        maxSize: $('body').data('max-size'),
    };
    Qbank.mediaPicker = {};

    Qbank.addOnlineMedia = function(media, image, previousUsage) {

        NProgress.start();
        Qbank.setIsLoading(true);
        $.post(TYPO3.settings.ajaxUrls['qbank_media_create'],
            {
                targetFolder: Qbank.target,
                allowed: Qbank.allowed,
                maxSize: Qbank.maxSize,
                media: media,
                image: image,
                previousUsage: previousUsage
            },
            function(data) {
                if (data.file) {
                    // If file was successfully downloaded and added to the FAL, trigger the creation of the file reference
                    const e = {
                        actionName: 'typo3:foreignRelation:insert',
                        objectGroup: Qbank.irreObjectUid,
                        table: 'sys_file',
                        uid: data.file
                    }
                    MessageUtility.MessageUtility.send(e)
                    Modal.currentModal.modal('hide')
                } else {
                    const confirm = modal.confirm(
                        'ERROR',
                        data.error,
                        severity.error,
                        [
                            {
                                text: TYPO3.lang['button.ok'] || 'OK',
                                btnClass: 'btn-' + severity.getCssClass(severity.error),
                                name: 'ok',
                                active: !0
                            }
                        ])
                        .on('confirm.button.ok', () => {
                            confirm.modal('hide')
                        })
                }
                NProgress.done()
                // if (data.file) {
                //     if (ElementBrowser.getParent()) {
                //         const message = {
                //            objectGroup: Qbank.irreObjectUid,
                //            table: 'sys_file',
                //            uid: data.file,
                //         };
                //        MessageUtility.send(message);
                //         NProgress.done();
                //         Qbank.setIsLoading(false);
                //         Qbank.mediaPicker.close();
                //         window.opener.focus();
                //         close();
                //     }
                // } else {
                //     NProgress.done();
                //     Qbank.setIsLoading(false);
                //     var $confirm = Modal.confirm(
                //         'ERROR',
                //         data.error,
                //         Severity.error,
                //         [{
                //             text: TYPO3.lang['button.ok'] || 'OK',
                //             btnClass: 'btn-' + Modal.getSeverityClass(Severity.error),
                //             name: 'ok',
                //             active: true
                //         }]
                //     ).on('confirm.button.ok', function() {
                //         $confirm.modal('hide');
                //     });
                // }
            }
        );
    };

    /**
	 * Initialize context help trigger
	 */
	Qbank.initialize = function() {
        var QBC = new QBankConnector(Qbank.config);
        Qbank.mediaPicker = new QBC.mediaPicker({
            container: '#wrapper',
            onSelect: Qbank.mediaSelected,
            onReady: Qbank.pickerReady,
            onClose: Qbank.pickerClosed,
            modules: {
                content: {
                    header: false
                }
            }
        });
    }

    Qbank.mediaSelected = function(media, image, previousUsage, templates) {
        Qbank.addOnlineMedia(media, image, previousUsage);
    };

    Qbank.setIsLoading = function($isLoading) {
        if ($isLoading == false) {
            $('#overlay').fadeOut();
            $('#wrapper').show();
        } else {
            $('#overlay').fadeIn();
            $('#wrapper').hide();
        }
    }

    Qbank.over = function() {
        $('#overlay').fadeIn();
        $('#wrapper').hide();
    }

    Qbank.pickerReady = function() {
        Qbank.setIsLoading(false);
    };

    Qbank.pickerClosed = function() {
        Qbank.setIsLoading(true);
        return true;
    };

    Qbank.initialize();
	TYPO3.Qbank = Qbank;
	return Qbank;    
});
