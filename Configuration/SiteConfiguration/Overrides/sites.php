<?php

$GLOBALS['SiteConfiguration']['site']['columns']['qbank_deploymentSites'] = [
    'label' => 'Deployment sites',
    'description' => 'Comma-separated list of deployment sites. If empty, the value is inherited from the global '
        . 'extension configuration or environment variables.',
    'config' => [
        'type' => 'input',
        'eval' => 'trim',
        'default' => '',
        'size' => 15,
    ],
];

$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] .= ',--div--;QBank,qbank_deploymentSites';

$GLOBALS['SiteConfiguration']['site_language']['columns']['qbank_deploymentSites'] = [
    'label' => 'QBank deployment sites',
    'description' => 'Comma-separated list of deployment sites. If empty, the value is inherited from the site\'s '
        . 'default, global extension configuration, or environment variables.',
    'config' => [
        'type' => 'input',
        'eval' => 'trim',
        'default' => '',
        'size' => 15,
    ],
];

$GLOBALS['SiteConfiguration']['site_language']['types']['1']['showitem'] .= ',qbank_deploymentSites';
