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

$fileProperties = [
    'name' => [
        'typeConverter' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
        'label' => function() {
            return $GLOBALS['TCA']['sys_file']['columns']['name']['label'];
        },
        'disabled' => false,
    ]
];

$mappableMetaDataProperties = [
    'title' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
    'description' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
    'alternative' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
];

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('filemetadata')) {
    $mappableMetaDataProperties = array_merge(
        $mappableMetaDataProperties,
        [
            'keywords' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
            'caption' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
            'creator_tool' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
            'download_name' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
            'creator' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
            'publisher' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
            'source' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
            'copyright' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
            'location_country' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
            'location_region' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
            'location_city' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
            'latitude' => \Pixelant\Qbank\TypeConverter\FloatTypeConverter::class,
            'longitude' => \Pixelant\Qbank\TypeConverter\FloatTypeConverter::class,
            'ranking' => \Pixelant\Qbank\TypeConverter\FloatTypeConverter::class,
            'content_creation_date' => \Pixelant\Qbank\TypeConverter\IntegerTypeConverter::class,
            'content_modification_date' => \Pixelant\Qbank\TypeConverter\IntegerTypeConverter::class,
            'note' => \Pixelant\Qbank\TypeConverter\StringTypeConverter::class,
        ]
    );
}

foreach ($mappableMetaDataProperties as $metaDataProperty => $typeConverter) {
    $fileProperties[$metaDataProperty] = [
        'typeConverter' => $typeConverter,
        'label' => function() use ($metaDataProperty) {
            return $GLOBALS['TCA']['sys_file_metadata']['columns'][$metaDataProperty]['label'];
        },
        'disabled' => false,
    ];
}

$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties'] = array_merge_recursive(
    $fileProperties,
    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['qbank']['fileProperties'] ?? []
);
