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

use SimpleXMLElement;

/**
 * Utility class for manipulating hashcodes-*.xml files inside {@link BdocContainer}s.
 *
 * @author Madis Loitmaa
 * @internal
 */
class HashcodesXml {
    const HASH_CODES_ELEMENT_NAME = 'hashcodes';
    const FILE_ENTRY_ELEMENT_NAME = 'file-entry';
    const FILE_ENTRY_ELEMENT_ATTRIBUTE_FULL_PATH = 'full-path';
    const FILE_ENTRY_ELEMENT_ATTRIBUTE_HASH = 'hash';
    const FILE_ENTRY_ELEMENT_ATTRIBUTE_SIZE = 'size';

    public static function parse ($xml) {
        $hashcodes = new SimpleXMLElement($xml);

        $fileEntries = array ();

        foreach ($hashcodes->children() as $child) {
            $fileEntries[] = self::xmlElementToFileEntry($child);
        }

        return $fileEntries;
    }

    private static function xmlElementToFileEntry (SimpleXMLElement $fileEntry) {
        $fullPathAttributeName = self::FILE_ENTRY_ELEMENT_ATTRIBUTE_FULL_PATH;

        return new HashcodesFileEntry(
            (string)$fileEntry->attributes()->$fullPathAttributeName,
            (string)$fileEntry->attributes()->hash,
            (int)$fileEntry->attributes()->size);
    }

    public static function dataFilesToHashcodesXml ($datafiles, $hashAlgorithm) {
        $fileEntries = array ();
        foreach ($datafiles as $datafile) {
            $fileEntries[] = self::convertDataFileToFileEntry($datafile, $hashAlgorithm);
        }

        return self::write($fileEntries);

    }

    public static function convertDataFileToFileEntry (DataFile $datafile, $hashAlgorithm) {
        return new HashcodesFileEntry(
            $datafile->getName(),
            base64_encode(hash($hashAlgorithm, $datafile->getContent(), true)),
            $datafile->getSize());
    }

    /**
     * @param $fileEntries
     *
     * @return string
     */
    public static function write ($fileEntries) {
        $rootElement = new SimpleXMLElement('<'.self::HASH_CODES_ELEMENT_NAME.'/>');
        foreach ($fileEntries as $fe) {
            self::fileEntryToXmlElem($fe, $rootElement->addChild(self::FILE_ENTRY_ELEMENT_NAME));
        }

        return self::getXml($rootElement);
    }

    /**
     * @param \SimpleXMLElement $element
     *
     * @return string Returns full XML on successful conversion or empty string
     */
    private static function getXml(\SimpleXMLElement $element) {
        $getTest = $element->asXML();
        if ($getTest === false) {
            $getTest = '';
        }

        return $getTest;
    }

    private static function fileEntryToXmlElem (HashcodesFileEntry $fe, \SimpleXMLElement $elem) {
        $elem->addAttribute(self::FILE_ENTRY_ELEMENT_ATTRIBUTE_FULL_PATH, $fe->getFullPath());
        $elem->addAttribute(self::FILE_ENTRY_ELEMENT_ATTRIBUTE_HASH, $fe->getHash());
        $elem->addAttribute(self::FILE_ENTRY_ELEMENT_ATTRIBUTE_SIZE, $fe->getSize());
    }
}