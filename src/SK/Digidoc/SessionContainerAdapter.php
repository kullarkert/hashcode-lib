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
 * Adapter for using FileContainers as StringContainers.
 *
 * Provides {@link StringContainer} interface to {@linl FileContainer}
 * using temporary files stored in {@link DigidocSession}
 *
 * @author Madis Loitmaa
 *
 */
class SessionContainerAdapter implements StringContainer {
    private $session;
    private $fileContainer;

    /**
     * Default constructor.
     *
     * Use {@link DigidocSession::containerFromString} to create instance from string.
     *
     * @param DigidocSession $session
     * @param FileContainer  $fileContainer
     */
    public function __construct (DigidocSession $session, FileContainer $fileContainer) {
        $this->session = $session;
        $this->fileContainer = $fileContainer;
    }

    public function getDataFiles () {
        return $this->fileContainer->getDataFiles();
    }

    public function toString () {
        return $this->fileContainer->toString();
    }

    public function isHashcodesFormat () {
        return $this->fileContainer->isHashcodesFormat();
    }

    public function toHashcodeFormat () {
        $newFile = $this->session->createFile();

        return new SessionContainerAdapter(
            $this->session,
            $this->fileContainer->writeAsHashcodes($newFile)
        );
    }

    public function toDatafilesFormat ($datafiles) {
        $newFile = $this->session->createFile();

        return new SessionContainerAdapter(
            $this->session,
            $this->fileContainer->writeWithDataFiles($newFile, $datafiles)
        );
    }

    public function getContainerFormat () {
        return $this->fileContainer->getContainerFormat();
    }
}
