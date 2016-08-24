<?php
/**
 * Dds-Hashcode Library
 * Copyright (C) 2014 AS Sertifitseerimiskeskus www.sk.ee.
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
 * @package    DDS-Hashcode
 * @copyright  2014 AS Sertifitseerimiskeskus
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link       http://www.sk.ee
 */

namespace SK\Digidoc;

/**
 * DataFile representing file inside a bdoc file.
 */
class BdocDataFile implements DataFile {
    private $bdocFilename;
    private $filename;

    /**
     * Constructor with bdoc filepath and datafile local name.
     *
     * @param {string} $bdocFilename bdoc file path
     * @param {string} $filename datafile local name
     */
    public function __construct ($bdocFilename, $filename) {
        $this->bdocFilename = $bdocFilename;
        $this->filename = $filename;
    }

    public function getName () {
        return $this->filename;
    }

    public function getSize () {
        $zip = new \ZipArchive();

        $zip->open($this->bdocFilename);
        $stat = $zip->statName($this->filename);
        $result = $stat['size'];
        $zip->close();

        return $result;
    }

    public function getContent () {
        $zip = new \ZipArchive();

        $zip->open($this->bdocFilename);
        $result = $zip->getFromName($this->filename);
        $zip->close();

        return $result;
    }

    /**
     * Is data file encoded as multi line Base64 string or one line
     *
     * @return bool
     */
    public function isMMultiLine () {
        return false;
    }

    public function getRawContent () {
        return null;
    }
}