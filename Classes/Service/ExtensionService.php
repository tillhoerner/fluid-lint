<?php

namespace Lemming\FluidLint\Service;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Utility\ListUtility;

class ExtensionService implements SingletonInterface
{
    public function gatherInformation(): array
    {
        /** @var ListUtility $list */
        $list = GeneralUtility::makeInstance(ListUtility::class);

        $extensionInformation = $list->getAvailableExtensions();
        foreach ($extensionInformation as $extensionKey => $info) {
            if (ExtensionManagementUtility::isLoaded($extensionKey)) {
                $extensionInformation[$extensionKey]['installed'] = 1;
            } else {
                $extensionInformation[$extensionKey]['installed'] = 0;
            }
        }
        return $extensionInformation;
    }
}
