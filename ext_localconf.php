<?php
declare(strict_types=1);

use Pixelant\Qbank\Xclass\Form\Container\FilesControlContainer as FilesControlContainerXclass;
use TYPO3\CMS\Backend\Form\Container\FilesControlContainer;

defined('TYPO3') or die();

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][FilesControlContainer::class] = [
    'className' => FilesControlContainerXclass::class,
];

$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
    \TYPO3\CMS\Core\Imaging\IconRegistry::class
);

$iconRegistry->registerIcon(
    'tx-qbank-logo',
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ['source' => 'EXT:qbank/Resources/Public/Icons/qbank-logo.svg']
);

$iconRegistry->registerIcon(
    'mimetypes-x-qbank-mapping',
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ['source' => 'EXT:qbank/Resources/Public/Icons/mimetypes-x-qbank-mapping.svg']
);

if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties'])) {
    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties'] = [];
}

// QBank file properties.
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties']['name'] = [
    'typeConverter' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file.name',
    'disabled' => false,
];

$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties']['title'] = [
    'typeConverter' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file.title',
    'disabled' => false,
];

$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties']['description'] = [
    'typeConverter' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file.description',
    'disabled' => false,
];

$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties']['alternative'] = [
    'typeConverter' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file.alternative',
    'disabled' => false,
];

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('filemetadata')) {
    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties']['keywords'] = [
        'typeConverter' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
        'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.keywords',
        'disabled' => false,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties']['caption'] = [
        'typeConverter' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
        'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.caption',
        'disabled' => false,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties']['creator_tool'] = [
        'typeConverter' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
        'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.creator_tool',
        'disabled' => false,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties']['download_name'] = [
        'typeConverter' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
        'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.download_name',
        'disabled' => false,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties']['creator'] = [
        'typeConverter' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
        'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.creator',
        'disabled' => false,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties']['publisher'] = [
        'typeConverter' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
        'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.publisher',
        'disabled' => false,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties']['source'] = [
        'typeConverter' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
        'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.source',
        'disabled' => false,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties']['copyright'] = [
        'typeConverter' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
        'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.copyright',
        'disabled' => false,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties']['location_country'] = [
        'typeConverter' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
        'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.location_country',
        'disabled' => false,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties']['location_region'] = [
        'typeConverter' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
        'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.location_region',
        'disabled' => false,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties']['location_city'] = [
        'typeConverter' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
        'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.location_city',
        'disabled' => false,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties']['latitude'] = [
        'typeConverter' => \Pixelant\Qbank\TypeConverter\FloatTypeConverter::class,
        'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.latitude',
        'disabled' => false,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties']['longitude'] = [
        'typeConverter' => \Pixelant\Qbank\TypeConverter\FloatTypeConverter::class,
        'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.longitude',
        'disabled' => false,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties']['ranking'] = [
        'typeConverter' => \Pixelant\Qbank\TypeConverter\FloatTypeConverter::class,
        'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.ranking',
        'disabled' => false,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties']['content_creation_date'] = [
        'typeConverter' => \Pixelant\Qbank\TypeConverter\IntegerTypeConverter::class,
        'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.content_creation_date',
        'disabled' => false,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties']['content_modification_date'] = [
        'typeConverter' => \Pixelant\Qbank\TypeConverter\IntegerTypeConverter::class,
        'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.content_modification_date',
        'disabled' => false,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties']['note'] = [
        'typeConverter' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
        'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.note',
        'disabled' => false,
    ];
}
