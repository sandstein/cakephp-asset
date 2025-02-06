<?php
/**
 * @author        Wilfried Wolf <wilfried.wolf@sandstein.de>
 * @copyright     Copyright © 2025-present Sandstein Neue Medien GmbH (https://www.sandstein.de)
 */

namespace FrankFoerster\Asset\Routing\Middleware;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Utility\Inflector;

/**
 * AssetMiddleware
 */
class AssetMiddleware extends \Cake\Routing\Middleware\AssetMiddleware
{
    /**
     * Builds asset file path based on the provided $url.
     *
     * @param string $url Asset URL
     * @return string|void Absolute path for asset file
     */
    protected function _getAssetFile($url)
    {
        $parts = explode('/', $url);
        $pluginPart = [];
        $plugin = false;
        for ($i = 0; $i < 2; $i++) {
            if (!isset($parts[$i])) {
                break;
            }
            $pluginPart[] = Inflector::camelize($parts[$i]);
            $possiblePlugin = implode('/', $pluginPart);
            if ($possiblePlugin && Plugin::loaded($possiblePlugin)) {
                $plugin = $possiblePlugin;
                $parts = array_slice($parts, $i + 1);
                break;
            }
        }

        $isAssetRequest = (isset($parts[0]) && $parts[0] === 'ASSETS');
        if ($isAssetRequest && Configure::read('debug')) {
            $parts = array_slice($parts, 1);
        } else {
            $isAssetRequest = false;
        }

        if ($plugin && Plugin::loaded($plugin)) {
            return $this->_getPluginAsset($plugin, $parts, $isAssetRequest);
        } else {
            return $this->_getAppAsset($parts, $isAssetRequest);
        }
    }
    /**
     * Get the Assets or webroot path for the provided $plugin
     * depending on whether $isAssetRequest is true or false.
     *
     * @param string $plugin The name of the plugin.
     * @param array $parts The url split by '/'.
     * @param bool $isAssetRequest Whether the request is for an asset in /src/Assets or webroot.
     * @return string
     */
    protected function _getPluginAsset($plugin, $parts, $isAssetRequest)
    {
        $fileFragment = implode(DS, $parts);
        $path = Plugin::path($plugin);
        if ($isAssetRequest) {
            $path .= 'src' . DS . 'Assets' . DS;
        } else {
            $path .= 'webroot' . DS;
        }

        return $path . $fileFragment;
    }

    /**
     * Get the Assets or webroot path for the app
     * depending on whether $isAssetRequest is true or false.
     *
     * @param array $parts The url split by '/'.
     * @param bool $isAssetRequest Whether the request is for an asset in /src/Assets or webroot.
     * @return string
     */
    protected function _getAppAsset($parts, $isAssetRequest)
    {
        $fileFragment = implode(DS, $parts);
        $path = $isAssetRequest ? ROOT . DS . 'src' . DS . 'Assets' . DS : WWW_ROOT;

        return $path . $fileFragment;
    }
}
