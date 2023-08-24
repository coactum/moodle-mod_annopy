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
 * File containing the class definition for the submit form for the module.
 *
 * @package     mod_annopy
 * @copyright   2023 coactum GmbH
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once("$CFG->libdir/formslib.php");

/**
 * Form for the submissions.
 *
 * @package   mod_annopy
 * @copyright 2023 coactum GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL Juv3 or later
 */
class mod_annopy_submit_form extends moodleform {

    /**
     * Define the form - called by parent constructor
     */
    public function definition() {

        global $OUTPUT;

        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'group', null);
        $mform->setType('group', PARAM_INT);

        $mform->addElement('hidden', 'userid', null);
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('hidden', 'submissionid', null);
        $mform->setType('submissionid', PARAM_INT);

        $mform->addElement('text', 'title', get_string('title', 'mod_annopy'), array('size' => '64'));

        $mform->setType('title', PARAM_TEXT);

        $mform->addRule('title', null, 'required', null, 'client');
        $mform->addRule('title', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $mform->addElement('editor', 'submission_editor', get_string('submissioncontent', 'mod_annopy'),
            null, $this->_customdata['editoroptions']);

        $mform->setType('submission_editor', PARAM_RAW);
        $mform->addRule('submission_editor', get_string('errfilloutfield', 'mod_annopy'), 'required', 'client');

        $this->add_action_buttons();
    }

    /**
     * Custom validation should be added here
     * @param array $data Array with all the form data
     * @param array $files Array with files submitted with form
     * @return array Array with errors
     */
    public function validation($data, $files) {
        return array();
    }
}
