<?php

namespace Lemming\FluidLint\Utility;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GlobUtility
{
    /**
     * @param string $path
     * @param string $extensions
     * @return array
     */
    public static function getFilesRecursive($path, $extensions)
    {
        if (substr($path, -1) !== '/') {
            $path .= '/';
        }
        $files = GeneralUtility::getAllFilesAndFoldersInPath([], $path, $extensions);
        $files = array_map('realpath', $files);
        return array_values($files);
    }

    /**
     * @param string $extension
     * @param string $path
     * @return string
     */
    public static function getRealPathFromExtensionKeyAndPath($extension, $path = '')
    {
        $path = ExtensionManagementUtility::extPath($extension, $path ?? '');
        return realpath($path);
    }

    public static function getExtensionKeysFromPath($path)
    {
        $extensionDirectories = GeneralUtility::get_dirs($path);
        return array_map(function($dir) use ($path) {
            $composerJson = json_decode(file_get_contents($path . '/' . $dir . '/composer.json'), true);
            return $composerJson['extra']['typo3/cms']['extension-key'];
        }, $extensionDirectories);
    }
}
