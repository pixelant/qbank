/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

import DocumentService from '@typo3/core/document-service.js';
import $ from 'jquery';
import { MessageUtility } from '@typo3/backend/utility/message-utility.js';
import { AjaxResponse } from '@typo3/core/ajax/ajax-response.js';
import NProgress from 'nprogress';
import AjaxRequest from '@typo3/core/ajax/ajax-request.js';
import Modal, { ModalElement, Types, Sizes } from '@typo3/backend/modal.js';
import Notification from '@typo3/backend/notification.js';
import Severity from '@typo3/backend/severity.js';
import RegularEvent from '@typo3/core/event/regular-event.js';
import { topLevelModuleImport } from '@typo3/backend/utility/top-level-module-import.js';

// qbank-connector.js is the "original" qbank-connector from qbank extension for TYPO3 v11.
// Think MBL had to do some changes in it to get it to work, but not sure.
import '@pixelant/qbank/qbank-connector.js';

// qbank-connector.min.js is a "new" qbank-connector from qbank.
// Can't find any documentation regarding how to use it.... but seems to be better to use if possuible.
// import '@pixelant/qbank/qbank-connector.min.js';

class Response {
  file;
  error;
}

/**
 * Module: @qbank/qbank-media
 * Javascript for show the qbank media dialog
 * Based on online-media TYPO3 core source dialog Build/Sources/TypeScript/backend/online-media.ts
 *
 * Meant to replace vendor/pixelant/qbank/Resources/Public/JavaScript/Qbank.js
 */
class QbankMedia {
  constructor() {
    DocumentService.ready().then(async () => {
      // Since the web component is used in a modal and therefore in outer frames, we have to import the module in the
      // top level scope. Not doing so causes issues in at least Firefox.
      // await topLevelModuleImport('@typo3/backend/form-engine/element/online-media-form-element.js');
      console.log('QbankMedia')
      this.registerEvents();
    });
  }

  registerEvents() {
    new RegularEvent('click', (e, target) => {
      this.triggerModal(target);
    }).delegateTo(document, '.t3js-qbank-media-add-btn');
  }

  addOnlineMedia(trigger, modalElement, url) {
    const target = trigger.dataset.targetFolder;
    const allowed = trigger.dataset.onlineMediaAllowed;
    const irreObjectUid = trigger.dataset.fileIrreObject;

    NProgress.start();
    new AjaxRequest(TYPO3.settings.ajaxUrls.qbank_download_file).post({
      url: url,
      targetFolder: target,
      allowed: allowed,
    }).then(async (response) => {
      const data = await response.resolve();
      if (data.file) {
        const message = {
          actionName: 'typo3:foreignRelation:insert',
          objectGroup: irreObjectUid,
          table: 'sys_file',
          uid: data.file,
        };
        MessageUtility.send(message);
        modalElement.hideModal();
      } else {
        Notification.error(top.TYPO3.lang['online_media.error.new_media.failed'], data.error);
      }
      NProgress.done();
    });
  }

  triggerModal(trigger) {
    const btnSubmit = trigger.dataset.btnSubmit || 'Add';
    const placeholder = trigger.dataset.placeholder || 'Paste media url here...';
    const allowedHelpText = trigger.dataset.onlineMediaAllowedHelpText || 'Allow to embed from sources:';
    const qbankHost = trigger.dataset.qbankHost || '';
    const qbankDeploymentsites = trigger.dataset.qbankDeploymentsites || '';
    const qbankToken = trigger.dataset.qbankToken || '';

    console.log('qbankHost' , qbankHost);
    console.log('qbankDeploymentsites' , qbankDeploymentsites);
    // console.log('qbankToken' , qbankToken);

    const onlineMediaForm = document.createElement('iframe');
    onlineMediaForm.placeholder = placeholder;
    onlineMediaForm.setAttribute('help-text', allowedHelpText);
    onlineMediaForm.setAttribute('extensions', trigger.dataset.onlineMediaAllowed);

    const qbankConnector = new QBankConnector({
      api: {
        host: qbankHost,
        access_token: qbankToken,
        protocol: 'https',
        search: {
          deploymentSiteIds: qbankDeploymentsites.split(',')
        }
      },
      gui: {
        basehref: 'https://' + qbankHost + '/connector/'
      }
    });

    const mediaPicker = new qbankConnector.mediaPicker({
      container: onlineMediaForm,
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
      onReady: function () {
        console.log('qBankIframe onReady');
      },
      onSelect: function (media, crop, previousUsage) {
        console.log('qBankIframe onSelect');
      }
    });

    Modal.advanced({
      type: Types.default,
      title: trigger.title,
      content: onlineMediaForm,
      severity: Severity.notice,
      size: Sizes.full,
      callback: (modalElement) => {
      }/*,
      buttons: [{
        text: btnSubmit,
        btnClass: 'btn btn-primary',
        name: 'ok',
        trigger: () => {
          onlineMediaForm.querySelector('form').requestSubmit();
        },
      }],*/
    });
  }
}

export default new QbankMedia();
