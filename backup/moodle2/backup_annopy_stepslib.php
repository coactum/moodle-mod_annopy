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

        // Replace with the attributes and final elements that the element will handle.
        $annopy = new backup_nested_element('annopy', ['id'], [
            'name', 'intro', 'introformat', 'timecreated', 'timemodified']);

        $annotationtypes = new backup_nested_element('annotationtypes');
        $annotationtype = new backup_nested_element('annotationtype', ['id'], [
            'timecreated', 'timemodified', 'name', 'color', 'priority']);

        $submissions = new backup_nested_element('submissions');
        $submission = new backup_nested_element('submission', ['id'], [
            'author', 'title', 'content', 'currentversion', 'format', 'timecreated', 'timemodified']);

        $annotations = new backup_nested_element('annotations');
        $annotation = new backup_nested_element('annotation', ['id'], [
            'userid', 'timecreated', 'timemodified', 'type', 'startcontainer', 'endcontainer',
            'startoffset', 'endoffset', 'annotationstart', 'annotationend', 'exact', 'prefix', 'suffix', 'text']);

        // Build the tree with these elements with $root as the root of the backup tree.
        $annopy->add_child($annotationtypes);
        $annotationtypes->add_child($annotationtype);

        $annopy->add_child($submissions);
        $submissions->add_child($submission);

        $submission->add_child($annotations);
        $annotations->add_child($annotation);

        // Define the source tables for the elements.
        $annopy->set_source_table('annopy', ['id' => backup::VAR_ACTIVITYID]);

        // Annotation types.
        $annotationtype->set_source_table('annopy_annotationtypes', ['annopy' => backup::VAR_PARENTID]);

        if ($userinfo) {
            // Submissions.
            $submission->set_source_table('annopy_submissions', ['annopy' => backup::VAR_PARENTID]);

            // Annotations.
            $annotation->set_source_table('annopy_annotations', ['submission' => backup::VAR_PARENTID]);
        }

        // Define id annotations.
        if ($userinfo) {
            $submission->annotate_ids('user', 'author');
            $annotation->annotate_ids('user', 'userid');
        }

        // Define file annotations.
        $annopy->annotate_files('mod_annopy', 'intro', null); // This file area has no itemid.
        $submission->annotate_files('mod_annopy', 'submission', 'id');

        return $this->prepare_activity_structure($annopy);
    }
}
