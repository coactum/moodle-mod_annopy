<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Helper utilities for the module.
 *
 * @package   mod_annopy
 * @copyright 2023 coactum GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_annopy\local;

/**
 * Utility class for the module.
 *
 * @package   mod_annopy
 * @copyright 2023 coactum GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {
    /**
     * Return the editor and attachment options for a submission.
     * @param stdClass $course The course object.
     * @param stdClass $context The context object.

     * @return array $editoroptions Array containing the editor options.
     * @return array $attachmentoptions Array containing the attachment options.
     */
    public static function annopy_get_editor_and_attachment_options($course, $context) {
        // For the editor.
        $editoroptions = array(
            'trusttext' => true,
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $course->maxbytes,
            'context' => $context,
            'subdirs' => false,
        );

        // If maxfiles would be set to an int and more files are given the editor saves them all but
        // saves the overcouting incorrect so that white box is displayed.

        // For a file attachments field (not really needed here).
        $attachmentoptions = array(
            'subdirs' => false,
            'maxfiles' => 1,
            'maxbytes' => $course->maxbytes
        );

        return array(
            $editoroptions,
            $attachmentoptions
        );
    }
}
