<?php
/**
 * PhpThumb Library Definition File
 *
 * This file contains the definitions for the PhpThumbFactory class.
 * It also includes the other required base class files.
 *
 * If you've got some auto-loading magic going on elsewhere in your code, feel free to
 * remove the include_once statements at the beginning of this file... just make sure that
 * these files get included one way or another in your code.
 *
 * PHP Version 5 with GD 2.0+
 * PhpThumb : PHP Thumb Library <http://phpthumb.gxdlabs.com>
 * Copyright (c) 2009, Ian Selby/Gen X Design
 *
 * Author(s): Ian Selby <ian@gen-x-design.com>
 *
 * Licensed under the MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author Ian Selby <ian@gen-x-design.com>
 * @copyright Copyright (c) 2009 Gen X Design
 * @link http://phpthumb.gxdlabs.com
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version 3.0
 * @package PhpThumb
 * @filesource
 */

namespace bin\media\inc;



// define some useful constants
define('THUMBLIB_BASE_PATH', str_replace('\\','/',__DIR__) );
define('THUMBLIB_PLUGIN_PATH', THUMBLIB_BASE_PATH . '/Plugins/');
define('DEFAULT_THUMBLIB_IMPLEMENTATION', 'gd');
//print '<pre>';\print_r(sprintf('[%d] include FileName %s',__LINE__, THUMBLIB_BASE_PATH));print '</pre>';
//\trigger_error(sprintf('[%d] is_readable FileName %s',__LINE__, str_replace(str_replace('\\','/',WB_PATH),'',$sFilePath)), E_USER_NOTICE);
//\trigger_error(sprintf('[%d] is_readable THUMBLIB_BASE_PATH %s',__LINE__, str_replace(str_replace('\\','/',WB_PATH),'',THUMBLIB_BASE_PATH)), E_USER_NOTICE);
//\trigger_error(sprintf('[%d] is_readable Image %s',__LINE__, $implementationMap['gd']), E_USER_NOTICE);

/**
 * Include the PhpThumb Class
require_once THUMBLIB_BASE_PATH . '/PhpThumb.inc.php';
if (!class_exists('PhpThumb', false)) {include(WB_PATH.'/framework/media/inc/PhpThumb.php');}
 */
/**
 * Include the ThumbBase Class
require_once THUMBLIB_BASE_PATH . '/ThumbBase.inc.php';
if (!class_exists('ThumbBase', false)) {include(WB_PATH.'/framework/media/inc//ThumbBase.php');}
 */
/**
 * Include the GdThumb Class
require_once THUMBLIB_BASE_PATH . '/GdThumb.inc.php';
if (!class_exists('GdThumb', false)) {include(WB_PATH.'/framework/media/inc//GdThumb.php');}
 */

/**
 * PhpThumbFactory Object
 *
 * This class is responsible for making sure everything is set up and initialized properly,
 * and returning the appropriate thumbnail class instance.  It is the only recommended way
 * of using this library, and if you try and circumvent it, the sky will fall on your head :)
 *
 * Basic use is easy enough.  First, make sure all the settings meet your needs and environment...
 * these are the static variables defined at the beginning of the class.
 *
 * Once that's all set, usage is pretty easy.  You can simply do something like:
 * <code>$thumb = PhpThumbFactory::create('/path/to/file.png');</code>
 *
 * Refer to the documentation for the create function for more information
 *
 * @package PhpThumb
 * @subpackage Core
 */
class PhpThumbFactory
{
    /**
     * Which implemenation of the class should be used by default
     *
     * Currently, valid options are:
     *  - imagick
     *  - gd
     *
     * These are defined in the implementation map variable, inside the create function
     *
     * @var string
     */
    public static $defaultImplemenation = DEFAULT_THUMBLIB_IMPLEMENTATION;
    /**
     * Where the plugins can be loaded from
     *
     * Note, it's important that this path is properly defined.  It is very likely that you'll
     * have to change this, as the assumption here is based on a relative path.
     *
     * @var string
     */
    public static $pluginPath = THUMBLIB_PLUGIN_PATH;

    /**
     * Factory Function
     *
     * This function returns the correct thumbnail object, augmented with any appropriate plugins.
     * It does so by doing the following:
     *  - Getting an instance of PhpThumb
     *  - Loading plugins
     *  - Validating the default implemenation
     *  - Returning the desired default implementation if possible
     *  - Returning the GD implemenation if the default isn't available
     *  - Throwing an exception if no required libraries are present
     *
     * @return GdThumb
     * @uses PhpThumb
     * @param string $filename The path and file to load [optional]
     */
    public static function create ($filename = null, array $options = [], $isDataStream = false)
    {
        // map our implementation to their class names
        $implementationMap = [
            'imagick'    => '\bin\media\inc\ImagickThumb',
            'gd'         => '\bin\media\inc\GdThumb'
        ];

        // grab an instance of PhpThumb
        $pt = \bin\media\inc\PhpThumb::getInstance();
        // load the plugins
        $pt->loadPlugins(self::$pluginPath);

        $toReturn = null;
        $implementation = self::$defaultImplemenation;
        // attempt to load the default implementation
        if ($pt->isValidImplementation(self::$defaultImplemenation))
        {
            $imp = $implementationMap[self::$defaultImplemenation];
            $toReturn = new $imp($filename, $options, $isDataStream);
        }
        // load the gd implementation if default failed
        else if ($pt->isValidImplementation('gd'))
        {
            $imp = $implementationMap['gd'];
            $implementation = 'gd';
            $toReturn = new $imp($filename, $options, $isDataStream);
        }
        // throw an exception if we can't load
        else
        {
            throw new \Exception('You must have either the GD or iMagick extension loaded to use this library');
        }

        $registry = $pt->getPluginRegistry($implementation);
        $toReturn->importPlugins($registry);
        return $toReturn;
    }
}