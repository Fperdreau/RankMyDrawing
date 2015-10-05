<?php
/**
 * File for class FullBackup
 *
 * PHP version 5
 *
 * @author Florian Perdreau (fp@florianperdreau.fr)
 * @copyright Copyright (C) 2014 Florian Perdreau
 * @license <http://www.gnu.org/licenses/agpl-3.0.txt> GNU Affero General Public License v3
 *
 * This file is part of RankMyDrawings.
 *
 * RankMyDrawings is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * RankMyDrawings is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with RankMyDrawings.  If not, see <http://www.gnu.org/licenses/>.
 */


/**
 * Class FullBackup
 *
 * Scheduled task that creates full backups of the web-site (files & database) and store the corresponding archives
 * in backup/complete.
 */
class FullBackup extends AppCron {
    /**
     * Assign chairmen for the next n sessions
     * @return bool
     */

    public $name = 'FullBackup';
    public $path;
    public $status = 'Off';
    public $installed = False;
    public $time;
    public $dayName;
    public $dayNb;
    public $hour;
    public $options=array("nb_version"=>10);

    public function __construct(AppDb $db) {
        parent::__construct($db);
        $this->path = basename(__FILE__);
        $this->time = AppCron::parseTime($this->dayNb, $this->dayName, $this->hour);
    }

    public function install() {
        // Register the plugin in the db
        $class_vars = get_class_vars($this->name);
        return $this->make($class_vars);
    }

    public function run() {
        // db backup
        $backupFile = backupDb($this->options['nb_version']); // backup database
        mail_backup($backupFile); // Send backup file to admins

        // file backup
        $fileLink = backupFiles(); // Backup site files (archive)

        // Write log only if server request
        $result['status'] = true;
        $result['msg'] = "Full Backup successfully done";
        $result['content'] = "<a href='$fileLink' target='_blank'>Download backup</a>";

        return $result;
    }
}