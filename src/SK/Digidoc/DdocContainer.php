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
 * Implementaion of {@link FileContainer} for ddoc files.
 */
class DdocContainer implements FileContainer {
    private $filename;

    private static $dataFileNodeName = 'DataFile';
    private static $contentTypeHashCode = 'HASHCODE';
    private static $containerFormatVersion = 'DIGIDOC-XML 1.3';

    private static $supporteDdocVersions = array ('1.1', '1.2', '1.3');

    /**
     * Constructs ddoc container from filename.
     *
     * @param string $filename container file path
     */
    public function __construct ($filename) {
        $this->filename = $filename;
        $this->validateFormatAndVersion();
    }

    private function validateFormatAndVersion () {
        $xmlreader = new \XMLReader();
        $xmlreader->open($this->filename);
        if (!self::isValidFormatAndVersion($xmlreader)) {
            throw new DigidocException('Invalid container format or version. Only Digidoc versions from 1.1 to 1.3 are supported.');
        }
    }

    private static function isValidFormatAndVersion (\XMLReader $xmlreader) {
        $result = false;
        while ($xmlreader->read()) {
            if ($xmlreader->localName === 'SignedDoc' && $xmlreader->nodeType === \XMLReader::ELEMENT) {
                $ddocVersion = $xmlreader->getAttribute('version');
                $result = $xmlreader->getAttribute('format') === 'DIGIDOC-XML'
                    && in_array($ddocVersion, self::$supporteDdocVersions);
                break;
            }
        }
        $xmlreader->close();

        return $result;
    }

    /**
     * Utility method to calculate file hashcode for using with DDS AddDataFile mehtod.
     *
     * @param string $filename filename
     * @param string $id       file id. D0 for first file D1 for second and so on...
     * @param string $mimetype file mime type
     * @param string $content  file content
     *
     * @return string file base64 encode file hashcode
     */
    public static function datafileHashcode ($filename, $id, $mimetype, $content) {
        $attributes = array (
            'xmlns'       => 'http://www.sk.ee/DigiDoc/v1.3.0#',
            'ContentType' => 'EMBEDDED_BASE64',
            'Filename'    => $filename,
            'Id'          => $id,
            'MimeType'    => $mimetype,
            'Size'        => strlen($content)
        );

        $xml = '<DataFile';
        foreach ($attributes as $key => $val) {
            $xml .= " $key=\"" . htmlspecialchars($val) . "\"";
        }

        $xml .= '>' . self::consistantBase64Encoder($content, true);
        $xml .= '</DataFile>';

        return base64_encode(sha1($xml, true));
    }

    /**
     * Tests if passed in data is supported document format and version.
     * DdocContainer class supports DIGIDOC-XML versions from 1.1 to 1.3.
     *
     * @param string $xmlData ddoc file contents.
     *
     * @return boolean true if format and version are supported.
     */
    public static function isSupportedFormatAndVersion ($xmlData) {
        $xmlreader = new \XMLReader();
        $xmlreader->XML($xmlData);

        return self::isValidFormatAndVersion($xmlreader);

    }

    public function getDataFiles () {
        $xml = new \XMLReader();
        $xml->open($this->filename);
        $datafiles = array ();

        while ($xml->read()) {
            if ($xml->localName === self::$dataFileNodeName && $xml->nodeType === \XMLReader::ELEMENT) {
                $datafiles[] = new DdocDataFile($this->filename, $xml->getAttribute('Filename'));
            }
        }
        $xml->close();

        return $datafiles;
    }

    public function writeAsHashcodes ($hashcodesFilename) {
        $signedDoc = new \SimpleXMLElement(file_get_contents($this->filename));

        /** @var \SimpleXMLElement $child */
        foreach ($signedDoc->children() as $child) {
            if ($child->getName() === self::$dataFileNodeName) {
                $this->convertToHashcode($child);
            }
        }
        $signedDoc->asXML($hashcodesFilename);

        return new DdocContainer($hashcodesFilename);
    }

    private function convertToHashcode (\SimpleXMLElement $datafileXml) {
        $datafile = new DdocDataFile($this->filename, $datafileXml->attributes()->Filename);
        $datafileXml->attributes()->ContentType = self::$contentTypeHashCode;
        $datafileXml->addAttribute('DigestType', 'sha1');
        $datafileXml->addAttribute('DigestValue', $datafile->hashcode());

        $datafileXml[0] = null;
    }

    public function writeWithDataFiles ($ddocFilename, $datafiles) {
        $signedDoc = simplexml_load_file($this->filename);

        $datafilesByName = array_combine(array_map(function (DataFile $datafile) {
            return $datafile->getName();
        }, $datafiles), $datafiles);
        $this->convertHashcodeToFile($signedDoc, $datafilesByName);
        $signedDoc->asXML($ddocFilename);
        return new DdocContainer($ddocFilename);
    }

    private function convertHashcodeToFile (\SimpleXMLElement $datafileXml, array $datafiles) {
        $dataFileToChange = dom_import_simplexml($datafileXml);
        $dataFileTags = $dataFileToChange->parentNode->getElementsByTagName(self::$dataFileNodeName);
        $existingDdoc = false;

        /** @var \DOMElement $dataFileTag */
        foreach ($dataFileTags as $dataFileTag) {
            $fileName = $dataFileTag->getAttribute('Filename');

            /** @var DataFile $file */
            $file = $datafiles[$fileName];
            if ($file->getRawContent() !== null) {
                $existingDdoc = true;
                $this->replaceDataFileTag($file, $dataFileTag, $dataFileToChange);
            } else {
                $this->changeDataFileTag($dataFileTag, $file);
            }
        }

        if ($existingDdoc) {
            $this->fixDataFileNamespace($dataFileToChange);
        }
    }

    private static function consistantBase64Encoder($content, $multiLine) {
        $encodedContent = base64_encode($content);
        $output = $multiLine ? chunk_split($encodedContent, 64, "\n") : $encodedContent."\n";

        return $output;
    }

    public function isHashcodesFormat () {
        $xml = new \XMLReader();
        $xml->open($this->filename);
        $result = false;

        while ($xml->read()) {
            if ($xml->localName === self::$dataFileNodeName && $xml->nodeType === \XMLReader::ELEMENT) {
                $result = $xml->getAttribute('ContentType') === self::$contentTypeHashCode;
                break;
            }
        }
        $xml->close();

        return $result;
    }

    public function toString () {
        return file_get_contents($this->filename);
    }

    public function getContainerFormat () {
        return self::$containerFormatVersion;
    }

    /**
     * @param DataFile    $file
     * @param \DOMElement $dataFileTag
     * @param \DOMElement $dataFileToChange
     */
    private function replaceDataFileTag ($file, $dataFileTag, $dataFileToChange) {
        $fileXml = dom_import_simplexml(simplexml_load_string($file->getRawContent()));
        $replacementImport = $dataFileToChange->ownerDocument->importNode($fileXml, true);
        $dataFileTag->parentNode->replaceChild($replacementImport, $dataFileTag);

        return $dataFileToChange;
    }

    /**
     * @param $dataFileTag
     * @param $file
     */
    private function changeDataFileTag (\DOMElement $dataFileTag, DataFile $file) {
        $dataFileTag->removeAttribute('DigestType');
        $dataFileTag->removeAttribute('DigestValue');
        $dataFileTag->setAttribute('ContentType', 'EMBEDDED_BASE64');

        $dataFileTag->nodeValue = self::consistantBase64Encoder($file->getContent(), $file->isMMultiLine());
    }

    /**
     * @param $dataFileToChange
     */
    private function fixDataFileNamespace (\DOMElement $dataFileToChange) {
        $namespace = $dataFileToChange->lookupNamespaceUri(null);
        $signedDoc = simplexml_import_dom($dataFileToChange);
        /** @var \SimpleXMLElement $child */
        foreach ($signedDoc->children() as $child) {
            $childNamespace = $child->getNamespaces();
            if ($child->getName() === self::$dataFileNodeName && count($childNamespace) > 0) {
                $child->addAttribute('xmlns', $namespace);
            }
        }
    }

}