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
 * Abstraction for file container (bdoc or ddoc file) that can be converted to hashcodes format.
 *
 * Container can be in datafiles format (normal operation) or hashcodes format.
 * You can use {@link Container::isHashcodesFormat()} to determine which format container currently has.
 * To switch between formats use methods provided by subinterfaces {@link FileContainer} and {@link StringContainer}.
 *
 * @author Madis Loitmaa
 *
 */
interface Container {

    /**
     * Return array of datafiles in this container.
     *
     * @return DataFile[] list of datafiles
     */
    public function getDataFiles ();

    /**
     * Checks container format.
     *
     * @return boolean true for hashcodes format, false for datafiles format.
     */
    public function isHashcodesFormat ();

    /**
     * Returns entire container as a string.
     *
     * @return string
     */
    public function toString ();

}