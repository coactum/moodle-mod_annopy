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
 * All the steps to restore mod_annopy are defined here.
 *
 * @package     mod_annopy
 * @category    backup
 * @copyright   2023 coactum GmbH
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// More information about the backup process: {@link https://docs.moodle.org/dev/Backup_API}.
// More information about the restore process: {@link https://docs.moodle.org/dev/Restore_API}.

/**
 * Defines the structure step to restore one mod_annopy activity.
 */
class restore_annopy_activity_structure_step extends restore_activity_structure_step {

    /** @var newinstanceid Store id of new instance if needed to store id of parent instance in subpath. */
    protected $newinstanceid = false;


    /**
     * Defines the structure to be restored.
     *
     * @return restore_path_element[].
     */
    protected function define_structure() {
        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('annopy', '/activity/annopy');

        if ($userinfo) {
            $paths[] = new restore_path_element('annopy_entry', '/activity/annopy/entries/entry');
        }

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Processes the annopy restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_annopy($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
        // See MDL-9367.
        if (!isset($data->assesstimestart)) {
            $data->assesstimestart = 0;
        }
        $data->assesstimestart = $this->apply_date_offset($data->assesstimestart);

        if (!isset($data->assesstimefinish)) {
            $data->assesstimefinish = 0;
        }
        $data->assesstimefinish = $this->apply_date_offset($data->assesstimefinish);

        if (!isset($data->timeopen)) {
            $data->timeopen = 0;
        }
        $data->timeopen = $this->apply_date_offset($data->timeopen);

        if (!isset($data->timeclose)) {
            $data->timeclose = 0;
        }
        $data->timeclose = $this->apply_date_offset($data->timeclose);

        if ($data->scale < 0) { // Scale found, get mapping.
            $data->scale = - ($this->get_mappingid('scale', abs($data->scale)));
        }

        $newitemid = $DB->insert_record('annopy', $data);
        $this->apply_activity_instance($newitemid);
        $this->newinstanceid = $newitemid;

        return;
    }

    /**
     * Defines post-execution actions like restoring files.
     */
    protected function after_execute() {
        // Add related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_annopy', 'intro', null);

        // Component, filearea, mapping.
        $this->add_related_files('mod_annopy', 'entry', 'annopy_entry');
    }
}
