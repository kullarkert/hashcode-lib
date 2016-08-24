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
 * Abstraction of a bdoc file.
 * Implementaion of {@link FileContainer} for bdoc files.
 *
 * @author Madis Loitmaa
 *
 */
class BdocContainer implements FileContainer {

    const DEFAULT_HASH_ALGORITHM = 'sha256';

    /**
     * Regular expression for finding hashcodes-*.xml files in bdoc container.
     *
     * @var string
     */
    const HASHCODES_FILES_REGEX = '|^META-INF/hashcodes-\\w+.xml$|';

    private $filename;

    public function __construct ($filename) {
        $this->filename = $filename;
    }

    /**
     * Utility method for calcualting file hash for DDS AddDataFile method.
     *
     * @param string $content file content
     *
     * @return string file hash
     */
    public static function datafileHashcode ($content) {
        return base64_encode(hash('sha256', $content, true));
    }

    public function writeAsHashcodes ($hashcodesFilename) {
        copy($this->filename, $hashcodesFilename);
        $zip = new \ZipArchive();
        $zip->open($hashcodesFilename);
        $this->deleteDataFiles($zip);
        $this->writeHashcodes($zip, $this->getDataFiles());
        $this->writeComment($zip, self::containerComment());
        $zip->close();

        return new BdocContainer($hashcodesFilename);
    }

    private function deleteDataFiles (\ZipArchive $zip) {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if ($this->isDataFile($filename)) {
                $zip->deleteName($filename);
            }
        }
    }

    private function isDataFile ($filename) {
        return $filename !== 'mimetype' && strpos($filename, 'META-INF/') !== 0;
    }

    private function writeHashcodes (\ZipArchive $zip, $datafiles) {
        foreach (array ('sha256', 'sha512') as $algorithm) {
            $zip->addFromString(
                $this->hashcodesFilename($algorithm),
                HashcodesXml::dataFilesToHashcodesXml($datafiles, $algorithm)
            );
        }
    }

    private function hashcodesFilename ($algorithm) {
        return "META-INF/hashcodes-$algorithm.xml";
    }

    public function getDataFiles () {
        $zip = new \ZipArchive();
        $zip->open($this->filename);
        $datafiles = array ();
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if ($this->isDataFile($filename)) {
                $datafiles[] = new BdocDataFile($this->filename, $filename);
            }
        }
        $zip->close();

        return $datafiles;
    }

    private function writeComment (\ZipArchive $zip, $comment) {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $zip->setCommentIndex($i, $comment);
        }
    }

    private static function containerComment () {
        return sprintf(
            'dds-hashcode %s - PHP %s, %s %s %s',
            Digidoc::version(), phpversion(),
            php_uname('s'),
            php_uname('r'),
            php_uname('v')
        );
    }

    public function writeWithDataFiles ($bdocFilename, $datafiles) {
        copy($this->filename, $bdocFilename);
        $zip = new \ZipArchive();
        $zip->open($bdocFilename);
        $this->deleteHashcodeFiles($zip);
        foreach ($datafiles as $datafile) {
            $zip->addFromString($datafile->getName(), $datafile->getContent());
        }
        $this->writeComment($zip, self::containerComment());
        $zip->close();

        return new BdocContainer($bdocFilename);
    }

    private function deleteHashcodeFiles (\ZipArchive $zip) {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if ($this->isHashcodesFile($filename)) {
                $zip->deleteName($filename);
            }
        }
    }

    private function isHashcodesFile ($filename) {
        return preg_match(self::HASHCODES_FILES_REGEX, $filename);
    }

    public function isHashcodesFormat () {
        $zip = new \ZipArchive();
        $zip->open($this->filename);
        $result = $zip->locateName($this->hashcodesFilename('sha256')) !== false;
        $zip->close();

        return $result;
    }

    public function toString () {
        return file_get_contents($this->filename);
    }

    public function getContainerFormat () {
        return 'BDOC 2.1';
    }
}

