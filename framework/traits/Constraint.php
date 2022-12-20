<?php

/*
 * Copyright (C) 2022 Manuela v.d. Decken <manuela@isteam.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Constraint
 *
 * @category     name
 * @package      Core package
 * @copyright    Manuela v.d. Decken <manuela@isteam.de>
 * @author       Manuela v.d. Decken <manuela@isteam.de>
 * @license      GNU General Public License 3
 * @version      0.0.1 $Rev: $
 * @revision     $Id: $
 * @since        File available since 05.03.2022
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */
declare(strict_types=1);
// declare(encoding = 'UTF-8');

namespace App\traits;

// use source;

trait Constraint {
    /* @var reference to singleton instance */
    private static $oInstance;

    /**
     * Creates a new instance
     * @return self
     */
    final public static function getInstance(array $aConstraints = null):? self
    {
        $aExtensions = \get_loaded_extensions();
        $aClasses = \get_declared_classes();
        if ($aConstraints['extensions']) {
            $aCommonExt = \array_intersect($aExtensions, $aConstraints['extensions']);
            $aMissingExt = \array_diff($aConstraints['extensions'], $aCommonExt);
            if (!empty($aMissingExt)) {
                throw new \Exception('Missing extensions: ' . \implode(', ', $aMissingExt));
            }
        }
        if ($aConstraints['classes']) {
            $aLoadedClasses = \array_intersect($aClasses, $aConstraints['classes']);
            $aMissingClasses = \array_diff($aConstraints['classes'], $aLoadedClasses);
            if (!empty($aMissingClasses)) {
                throw new \Exception('Missing classes: ' . implode(', ', $aMissingClasses));
            }
        }
        if (!self::$oInstance) {
            self::$oInstance = new self;
        }
        return self::$oInstance;
    }

    /**
     * Prevents cloning the singleton instance.
     *
     * @return void
     */
    private function __clone(): void {}

    /**
     * Prevents unserializing the singleton instance.
     *
     * @return void
     */
    public function __wakeup(): void {}

}
