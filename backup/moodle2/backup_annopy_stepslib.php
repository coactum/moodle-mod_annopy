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
 * Backup steps for mod_annopy are defined here.
 *
 * @package     mod_annopy
 * @category    backup
 * @copyright   2023 coactum GmbH
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// For more information about the backup and restore process, please visit:
// More information about the backup process: {@link https://docs.moodle.org/dev/Backup_API}.
// More information about the restore process: {@link https://docs.moodle.org/dev/Restore_API}.

/**
 * Define the complete structure for backup, with file and id annotations.
 */
class backup_annopy_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the structure of the resulting xml file.
     *
     * @return backup_nested_element The structure wrapped by the common 'activity' element.
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        /* // Replace with the attributes and final elements that the element will handle.
        $annopy = new backup_nested_element('annopy', array('id'), array(
            'name', 'intro', 'introformat', 'timecreated', 'timemodified'));

        $entries = new backup_nested_element('entries');
        $entry = new backup_nested_element('entry', array('id'), array(
            'userid', 'timecreated', 'timemodified', 'text', 'format'));

        // Build the tree with these elements with $root as the root of the backup tree.
        $annopy->add_child($entries);
        $entries->add_child($entry);

        // Define the source tables for the elements.

        $annopy->set_source_table('annopy', array('id' => backup::VAR_ACTIVITYID));

        if ($userinfo) {
            // Entries.
            $entry->set_source_table('annopy_entries', array('annopy' => backup::VAR_PARENTID));
        }

        // Define id annotations.
        $rating->annotate_ids('user', 'userid');

        // Define file annotations.
        $annopy->annotate_files('mod_annopy', 'intro', null); // This file area has no itemid.
        $entry->annotate_files('mod_annopy', 'entry', 'id'); */

        return $this->prepare_activity_structure($annopy);
    }
}
