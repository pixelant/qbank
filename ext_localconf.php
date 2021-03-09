<?php

// Add the QBank button
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1615293157] = [
    'nodeName' => 'inline',
    'priority' => 50,
    'class' => \Pixelant\Qbank\FormEngine\Container\QbankSelectorButtonContainer::class,
];
