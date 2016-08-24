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
 * Class for representing file-entry tag in hashcodes-*.xml file.
 *
 * @author Madis Loitmaa
 * @internal
 *
 */
class HashcodesFileEntry {
    private $fullPath;
    private $hash;
    private $size;

    public function __construct ($fullPath, $hash, $size) {
        $this->fullPath = $fullPath;
        $this->hash = $hash;
        $this->size = $size;
    }

    public function getFullPath () {
        return $this->fullPath;
    }

    public function getHash () {
        return $this->hash;
    }

    public function getSize () {
        return $this->size;
    }
}