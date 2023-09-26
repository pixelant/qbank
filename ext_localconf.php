<?php
defined('TYPO3') or die();

use Pixelant\Qbank\FormEngine\Container\QbankSelectorButtonContainer;

call_user_func(static function () {
    // Add the QBank selector
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ElementBrowsers']['qbank']
        = \Pixelant\Qbank\FormEngine\FieldControl\QbankElementBrowser::class;

    // Add the QBank button
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1615293157] = [
        'nodeName' => 'inline',
        'priority' => 50,
        'class' => QbankSelectorButtonContainer::class,
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
});
