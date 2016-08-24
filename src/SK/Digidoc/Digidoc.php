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
 * Central hub for configuration and session management.
 *
 * Directory where temporary files are stored can be configured by passing configuration array to constructor. Default
 * directory for temporary files is `sys_get_temp_dir() . DIRECTORY_SEPARATOR . "php-dds-hashcode"`
 *
 * ```php
 * // Example 1. Overriding default configuration.
 * use SK\Digidoc\Digidoc;
 * // You can override default configuration parameters by
 * // by passing array of configuration variables to Digidoc-s constructor.
 * // currently only setting temporary dir is supported like so:
 * $digidoc = new Digidoc(
 *     array(
 *         Digidoc::TEMPORARY_DIR => '/path/to/dir'
 *     ));
 * $digidoc->createSession(); // and so on...
 *
 * ```
 *
 * Every {@link DigidocSession} gets its own private directory for temporary files
 * which will be deleted by calling {@link DigidocSession::end()} on {@link DigidocSession}
 * instance. To delete all temporary files in temporary directory you can call
 * {@link Digidoc::deleteLocalTempFiles()}
 *
 *
 *
 * @author Madis Loitmaa
 *
 */
class Digidoc {
    /**
     * Configuratrion key for temporary dir.
     *
     * @var string
     */
    const TEMPORARY_DIR = 'temporary_dir';
    const DIGIDOC_VERSION = '1.1.3';
    const HASHCODE_DEFAULT_TEMP_HASHCODE_DIRECTORY = 'php-dds-hashcode';
    const DDOC_DATA_FILE_CHUNK_SPLIT = true;

    private $configuration;

    public function __construct ($configuration = array()) {
        $this->configuration = array_merge($this->configurationDefaults(), $configuration);
    }

    private function configurationDefaults () {
        return array (
            self::TEMPORARY_DIR => self::defaultTemporaryDirectory()
        );
    }

    private static function defaultTemporaryDirectory () {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::HASHCODE_DEFAULT_TEMP_HASHCODE_DIRECTORY;
    }

    public static function version () {
        return self::DIGIDOC_VERSION;
    }

    /**
     * Factory method to create hashcode session.
     *
     * @return DigidocSession
     */
    public function createSession () {
        $session = new DigidocSession($this->configuration);

        return $session;
    }

    public function deleteLocalTempFiles () {
        $dir = self::temporaryDirectory($this->configuration);
        if (file_exists($dir)) {
            self::deleteAllFilesInDirectory($dir);
        }
    }

    public static function temporaryDirectory ($configuration) {
        return empty($configuration[self::TEMPORARY_DIR]) ?
            Digidoc::defaultTemporaryDirectory() :
            $configuration[self::TEMPORARY_DIR];
    }

    /**
     * Deletes all files in directory.
     *
     * @internal for internal use only
     *
     * @param String $dir
     */
    public static function deleteAllFilesInDirectory ($dir) {
        $dirIterator = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
        foreach (new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            $file->isDir() ? rmdir($file) : unlink($file);
        }
    }

}
