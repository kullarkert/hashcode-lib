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
 * @author     Madis Loitmaa
 * @copyright  2014 AS Sertifitseerimiskeskus
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link       http://www.sk.ee
 *
 */
namespace SK\Digidoc;

/**
 * Helper for storing local temporary files.
 *
 * Converting bdoc/ddoc files to and from hascodes format requires creating temporary files.
 * The purpouse of `DigidocSession` is abstract away the file management part when
 * doing the conversion.
 *
 * In case you don't need such abstaction, you can use {@link BdocContainer} or {@link DdocContainer}
 * directly.
 *
 * To create DigidocSession, please use {@link Digidoc::createSession()}.
 *
 * ``` php
 * // Example 1. Signing container in hashcodes format.
 * // $_SESSION is used to store data between requests
 * use SK\Digidoc\Digidoc;
 *
 * $digidoc = new Digidoc();
 * // Returnd DigidocSession
 * $session = $digidoc->createSession();
 * // you can store DigidocSession inside HTTP $_SESSION
 * $_SESSION['hashcodeSession'] = $session;
 * $container = $session->containerFromString($containerDataAsString);
 * // containers can also be stored in $_SESSION
 * $_SESSION['container'] = $container;
 * $containerHashcodes = $container->toHashcodeFormat();
 * // send $containerHashcodes to DDS for signing...
 *
 * // In another request, when signing is completed:
 * // assign signed hashcodes format container to $containerDataFromDds variable
 * // and restore $session and $container from $_SESSION
 * $session = $_SESSION['hashcodeSession'];
 * $container = $_SESSION['container'];
 * $signedHashcodesContainer = $session->containerFromString($containerDataFromDds);
 *
 * // convert signed container in hashcodes format to datafiles format using original $container.
 * $signedContainer = $signedHashcodesContainer->toDatafilesFormat($container->getDataFiles());
 *
 * $containerData = $signedContainer->toString();
 *
 * // clean up local temporary files
 * $session->end();
 *
 * // send $containerData to user
 *
 * ```
 *
 * ``` php
 * // Example 2. Adding datafile to container.
 *
 * use SK\Digidoc\BdocContainer;
 * use SK\Digidoc\Digidoc;
 * use SK\Digidoc\FileSystemDataFile;
 *
 * // Start DDS session with bHoldSession flag set to true.
 * // call CreateSignedDoc to create empty bdoc container or pass
 * // existing container to DDS StartSession call.
 *
 * // add datafile from local filesystem
 * $filename = 'sample.file';
 * // calculate file hash using helper method in BdocContainer
 * // if DDOC container is used, use DdocContainer::datafileHashcode() method instead.
 * $hash = BdocContainer::datafileHashcode(file_get_contents($filename));
 *
 * // call AddDatafile with $hash value
 *
 * // assign contaier data from DDS to variable $ddsContainerData
 * $digidoc = new Digidoc();
 * $session = $digidoc->createSession();
 * $containerHashcodes = $session->containerFromString($ddsContainerData);
 * $container = $containerHashcodes->toDatafilesFormat(array(new FileSystemDataFile($filename)));
 *
 * // You can send container contents to user using $container->toString(),
 * // but it should be called before $session->end();
 *
 * // clean up temporary files
 * $session->end();
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
class DigidocSession {

    private $sessionId;

    private $configuration;

    /**
     * DigidocSession instances should be created by Digidoc::createSession();
     *
     * @param array $configuration
     */
    public function __construct (array $configuration) {
        $this->configuration = $configuration;
    }

    /**
     * Creates container from string.
     *
     * Internally stores container data in a file and returns {@link SessionContainerAdapter}
     * pointing to {@link FileContainer} with created file.
     *
     * Created files will be delete when {@link DigidocSession::end()} is called.
     *
     * @param string $data
     *
     * @throws DigidocException in case of invalid input.
     * @return StringContainer
     */
    public function containerFromString ($data) {
        if (strpos($data, 'PK') === 0) {
            $file = $this->createFile();
            file_put_contents($file, $data);

            return new SessionContainerAdapter($this, new BdocContainer($file));
        } elseif (strpos(trim($data), '<') === 0) {
            $file = $this->createFile();
            file_put_contents($file, $data);

            return new SessionContainerAdapter($this, new DdocContainer($file));
        }
        throw new DigidocException('Invalid container format');
    }

    /**
     * Creates file in session directory.
     *
     * @return string file full path.
     */
    public function createFile () {
        return tempnam($this->requireSessionPath(), 'digidoc');
    }

    private function requireSessionPath () {
        $path = $this->getSessionPath();
        if (!is_dir($path)) {
            mkdir($path, 0700, true);
        }

        return $path;
    }

    private function getSessionPath () {
        return Digidoc::temporaryDirectory($this->configuration) . DIRECTORY_SEPARATOR . $this->getSessionId();
    }

    /**
     * Returns session id.
     *
     * @return string
     */
    public function getSessionId () {
        if ($this->sessionId === null) {
            $this->initSession();
        }

        return $this->sessionId;
    }

    private function initSession () {
        $this->sessionId = uniqid(null, true);
    }

    /**
     * Ends session.
     *
     * Ends session and deletes all local temporary files related to this session.
     */
    public function end () {
        $this->deleteSessionFiles();
    }

    private function deleteSessionFiles () {
        if (file_exists($this->getSessionPath())) {
            Digidoc::deleteAllFilesInDirectory($this->getSessionPath());
            rmdir($this->getSessionPath());
        }
    }
}