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
 * Abstraction for data file in Container.
 *
 * You can provide your own implementation for example for reading files from database blob.
 *
 * @author Madis Loitmaa
 *
 */
interface DataFile {

    /**
     * Get file name (local name without folder).
     *
     * @return string file name
     */
    public function getName ();

    /**
     * Get file size in bytes.
     *
     * @return int file size in bytes
     */
    public function getSize ();

    /**
     * Get file contents.
     *
     * @return string file contents
     */
    public function getContent ();

    /**
     * Is data file encoded as multi line Base64 string or one line
     *
     * @return bool
     */
    public function isMMultiLine();

    /**
     * Get DataFile element unmodified or NULL if there is no
     * DataFile tag present in document container
     *
     * @return {string|null}
     */
    public function getRawContent();
}
