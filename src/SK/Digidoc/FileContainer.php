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
 * Container with files as input and output.
 *
 * Converting between container formats is possible through write* methods.
 * Write methods return new FileContainer-s pointing to new files.
 *
 * @author Madis Loitmaa
 *
 */
interface FileContainer extends Container {

    /**
     * Writes container in hashcodes format to new file.
     *
     * @param string $hashcodesFilename file name to write to.
     *
     * @return \SK\Digidoc\FileContainer New container, pointing to new file.
     */
    public function writeAsHashcodes ($hashcodesFilename);

    /**
     * Writes container in datafiles format.
     *
     * @param string     $filename  file name to write to.
     * @param DataFile[] $datafiles array of {@link DataFile}-s, to replace hashcodes with.
     *                              return FileContainer New container new cointainer, pointing to new file.
     */
    public function writeWithDataFiles ($filename, $datafiles);
}
