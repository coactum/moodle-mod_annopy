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
 * This file contains the forms to create and edit an instance of the module.
 *
 * @package     mod_annopy
 * @copyright   2023 coactum GmbH
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package    mod_annopy
 * @copyright  2023 coactum GmbH
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_annopy_mod_form extends moodleform_mod {

    /**
     * Define the form elements.
     */
    public function definition() {
        global $CFG, $DB, $USER;

        $mform = &$this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('modulename', 'mod_annopy'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'modulename', 'mod_annopy');

        $this->standard_intro_elements();

        $update = optional_param('update', null, PARAM_INT);

        if (!isset($update) || $update == 0) {
            // Add the header for the error types.
            $mform->addElement('header', 'annotationtypeshdr', get_string('annotationtypes', 'annopy'));
            $mform->setExpanded('annotationtypeshdr');

            $select = "defaulttype = 1";
            $select .= " OR userid = " . $USER->id;
            $annotationtypetemplates = (array) $DB->get_records_select('annopy_atype_templates', $select);

            $strmanager = get_string_manager();

            $this->add_checkbox_controller(1);

            foreach ($annotationtypetemplates as $id => $type) {
                if ($type->defaulttype == 1) {
                    $name = '<span style="margin-right: 10px; background-color: #' . $type->color . '" title="' .
                        get_string('standardtype', 'mod_annopy') .'">(S)</span>';
                } else {
                    $name = '<span style="margin-right: 10px; background-color: #' . $type->color . '" title="' .
                        get_string('manualtype', 'mod_annopy') .'">(M)</span>';
                }

                if ($type->defaulttype == 1 && $strmanager->string_exists($type->name, 'mod_annopy')) {
                    $name .= '<span>' . get_string($type->name, 'mod_annopy') . '</span>';
                } else {
                    $name .= '<span>' . $type->name . '</span>';
                }

                $mform->addElement('advcheckbox', 'annotationtypes[' . $id . ']', $name, ' ', array('group' => 1), array(0, 1));
            }

        }

        // Add standard grading elements.
        $this->standard_grading_coursemodule_elements();

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }

    /**
     * Form for custom validation.
     *
     * @param object $data The data from the form.
     * @param object $files The files from the form.
     * @return object $errors The errors.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        /* $minwidth = 20;
        $maxwidth = 80;

        if (!$data['annotationareawidth'] || $data['annotationareawidth'] < $minwidth || $data['annotationareawidth'] > $maxwidth) {
            $errors['annotationareawidth'] = get_string('errannotationareawidthinvalid', 'annopy', array('minwidth' => $minwidth,
            'maxwidth' => $maxwidth));
        } */

        return $errors;
    }
}
