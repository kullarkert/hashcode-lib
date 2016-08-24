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
 * DataFile representing file inside a ddoc file.
 */
class DdocDataFile implements DataFile {

    private $ddocFilename;
    private $filename;

    private $contentRead = false;
    private $content;
    private $dataFileContentOnMultipleLines = Digidoc::DDOC_DATA_FILE_CHUNK_SPLIT;
    private $rawContent;

    /**
     * Constructor using ddoc filename and file name.
     *
     * @param {string} $ddocFilename ddoc contianer file name
     * @param {string} $filename file name inside ddoc container
     */
    public function __construct ($ddocFilename, $filename) {
        $this->ddocFilename = $ddocFilename;
        $this->filename = $filename;
    }

    public function getName () {
        return $this->filename;
    }

    public function getSize () {
        return strlen($this->getContent());
    }

    public function getContent () {
        if (!$this->contentRead) {
            $this->content = $this->readContent();
        }

        return $this->content;
    }

    private function readContent () {
        $content = $this->readRawContent();
        $this->dataFileContentOnMultipleLines = substr_count($content, "\n") > 1;

        return base64_decode($content);
    }

    private function readRawContent() {
        return $this->handleDataFileElement(function (\XMLReader $xmlReader) {
            $content = $xmlReader->readInnerXml();
            return $content;
        });
    }

    private function handleDataFileElement (\Closure $callback) {
        $xmlReader = new \XMLReader();
        $xmlReader->open($this->ddocFilename);
        $result = null;
        while ($xmlReader->read()) {
            if ($this->isDataFileElement($xmlReader) && $this->isRequestedDataFileFilename($xmlReader)) {
                $this->rawContent = $xmlReader->readOuterXml();
                $result = $callback($xmlReader);
                break;
            }
        }
        $xmlReader->close();

        return $result;
    }

    /**
     * @internal public for testing
     * @return string
     */
    public function hashcode () {
        return base64_encode(sha1($this->readXmlElementCanonized(), true));
    }

    /**
     * @internal public for testing
     * @return string
     */
    public function readXmlElementCanonized () {
        return $this->handleDataFileElement(function (\XMLReader $xmlreader) {
            $dom = new \DOMDocument();
            $node = $xmlreader->expand();
            $dom->appendChild($node);

            return $node->C14N();
        });
    }

    /**
     * Is data file encoded as multi line Base64 string or one line
     *
     * @return bool
     */
    public function isMMultiLine () {
        return $this->dataFileContentOnMultipleLines;
    }

    public function getRawContent () {
        $this->readRawContent();
        return $this->rawContent;
    }

    /**
     * @param $xmlReader
     *
     * @return bool
     */
    private function isDataFileElement ($xmlReader) {
        return $xmlReader->localName === 'DataFile' && $xmlReader->nodeType === \XMLReader::ELEMENT;
    }

    /**
     * @param $xmlReader
     *
     * @return bool
     */
    private function isRequestedDataFileFilename (\XMLReader $xmlReader) {
        return $xmlReader->getAttribute('Filename') == $this->filename;
    }
}
