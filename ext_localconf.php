<?php
// Add the QBank selector
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ElementBrowsers']['qbank'] = \Pixelant\Qbank\FormEngine\FieldControl\QbankElementBrowser::class;

// Add the QBank button
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1615293157] = [
    'nodeName' => 'inline',
    'priority' => 50,
    'class' => \Pixelant\Qbank\FormEngine\Container\QbankSelectorButtonContainer::class,
];
