<?php

$typo3InstallDir = \TYPO3\CMS\Core\Core\Environment::getPublicPath();

return array(
	'QBNK\\Tests\\' => $typo3InstallDir . '/typo3conf/ext/qbank/Contrib/qbnk/guzzle5-oauth2-subscriber/tests',
    'QBNK\\QBank\\API\\' => $typo3InstallDir . '/typo3conf/ext/qbank/Contrib/qbnk/qbank3api-phpwrapper',
    'QBNK\\GuzzleOAuth2\\' => $typo3InstallDir . '/typo3conf/ext/qbank/Contrib/qbnk/guzzle5-oauth2-subscriber/src',
);

?>
