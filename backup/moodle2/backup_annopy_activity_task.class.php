<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * The task that provides all the steps to perform a complete backup is defined here.
 *
 * @package     mod_annopy
 * @category    backup
 * @copyright   2023 coactum GmbH
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// More information about the backup process: {@link https://docs.moodle.org/dev/Backup_API}.
// More information about the restore process: {@link https://docs.moodle.org/dev/Restore_API}.

require_once($CFG->dirroot.'/mod/annopy/backup/moodle2/backup_annopy_stepslib.php');
require_once($CFG->dirroot.'/mod/annopy/backup/moodle2/backup_annopy_settingslib.php');

/**
 * The class provides all the settings and steps to perform one complete backup of mod_annopy.
 */
class backup_annopy_activity_task extends backup_activity_task {

    /**
     * Defines particular settings for the plugin.
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Defines particular steps for the backup process.
     */
    protected function define_my_steps() {
        $this->add_step(new backup_annopy_activity_structure_step('annopy_structure', 'annopy.xml'));
    }

    /**
     * Codes the transformations to perform in the activity in order to get transportable (encoded) links.
     *
     * @param string $content content.
     * @return string $content content.
     */
    public static function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        // Link to the list of plugin instances.
        $search = "/(".$base."\//mod\/annopy\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@ANNOPYINDEX*$2@$', $content);

        // Link to view by moduleid with optional userid if only items of one user should be shown.
        $search = "/(".$base."\/mod\/annopy\/view.php\?id\=)([0-9]+)(&|&amp;)userid=([0-9]+)/";
        $content = preg_replace($search, '$@ANNOPYVIEWBYID*$2*$4@$', $content);

        return $content;
    }
}
