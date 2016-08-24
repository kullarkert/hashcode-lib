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
 *
 */
namespace SK\Digidoc;

/**
 * DataFile representing a regular file in file system.
 *
 * @author Madis Loitmaa
 *
 */
class FileSystemDataFile implements DataFile {

    private $path;

    /**
     * Construct {@link FileSystemDataFile} using file path.
     *
     * @param string $path file path
     */
    public function __construct ($path) {
        $this->path = $path;
    }

    public function getName () {
        return basename($this->path);
    }

    public function getSize () {
        return filesize($this->path);
    }

    public function getContent () {
        return file_get_contents($this->path);
    }

    /**
     * Is data file encoded as multi line Base64 string or one line
     *
     * @return bool
     */
    public function isMMultiLine () {
        return Digidoc::DDOC_DATA_FILE_CHUNK_SPLIT;
    }

    public function getRawContent () {
        return null;
    }
}