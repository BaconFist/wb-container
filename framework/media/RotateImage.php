<?php
/**
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS HEADER.
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
 *
 * Description of Lingual
 *
 * @package      Addon package
 * @copyright    Dietmar Wöllbrink <dietmar.woellbrink@websitebaker.org>
 * @author       Dietmar Wöllbrink <dietmar.woellbrink@websitebaker.org>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      1.0.0-dev.0
 * @revision     $Id:  $
 * @since        File available since 02.12.2017
 * @deprecated   no
 * @description  xxx
 *
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

namespace bin\media;

/**
 *
 * Document   : EXIF Image Rotate Class
 * OG Author  : josephtinsley
 * Edited by  : Sir Charles (Added for each loop to edit more than one file <3)
 * Description: PHP class that detects a JPEG image current orientation and rotates a image using the images EXIF data.
 * http://twitter.com/josephtinsley
 *
*/
class RotateImage {
    /*
     * @param string $setFilename - Set the original image filename
     * @param array $exifData - Set the original image filename
     * @param string $savedFilename - Set the rotated image filename
     */

    private $setFilename    = "";
    private $exifData       = "";
    private $degrees        = "";

    public function __construct($setFilename)
    {
        try{
            if(!is_readable($setFilename))
            {
                throw new \Exception('File not found.');
            }
            $this->setFilename = $setFilename;
        } catch (Exception $e ) {
            die($e->getMessage());
        }
    }
    /*
     * EXTRACTS EXIF DATA FROM THE JPEG IMAGE
     */
    public function processExifData()
    {
        $orientation = 0;
        $this->exifData = exif_read_data($this->setFilename);

        foreach($this->exifData as $key => $val)
        {
            if(strtolower($key) == "orientation" )
            {
                $orientation = $val;
                break;
            }
        }
        if( $orientation == 0 )
        {
            $this->_setOrientationDegree(1);
        }
        $this->_setOrientationDegree($orientation);
    }
    /*
     * DETECTS AND SETS THE IMAGE ORIENTATION
     * Orientation flag info  http://www.impulseadventure.com/photo/exif-orientation.html
     */
    private function _setOrientationDegree($orientation)
    {
       switch($orientation):
           case 1:
               $this->degrees = 0;
               break;
           case 3:
               $this->degrees = 180;
               break;
           case 6:
               $this->degrees = 360;
               break;
           case 8:
               $this->degrees = 90;
               break;
       endswitch;

       $this->_rotateImage();
    }
    /*
     * ROTATE THE IMAGE BASED ON THE IMAGE ORIENTATION
     */
    private function _rotateImage()
    {
        if ($this->degrees < 1 )
        {
            return FALSE;
        }
        $image_data = imagecreatefromjpeg($this->setFilename);
        return imagerotate($image_data, $this->degrees, 0);
    }
    /*
     * SAVE THE IMAGE WITH THE SAME FILENAME
     */
    public function savedFileName($savedFilename)
    {
        if($this->degrees < 1 )
        {
            return false;
        }
        $imageResource = $this->_rotateImage();
        if($imageResource == FALSE)
        {
            return false;
        }
        imagejpeg($imageResource, $savedFilename);
        // Free up memory
        imagedestroy($imageResource);
    }
} //END CLASS

