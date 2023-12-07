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
 * Prints the annotation type form for the module instance.
 *
 * @package     mod_annopy
 * @copyright   2023 coactum GmbH
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;
use mod_annopy\output\annopy_annotations_summary;

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once($CFG->dirroot . '/mod/annopy/annotationtypes_form.php');

// Course_module ID.
$id = required_param('id', PARAM_INT);

// If template (1) or annopy (2) annotation type.
$mode = optional_param('mode', 1, PARAM_INT);

// ID of type that should be edited.
$edit = optional_param('edit', 0, PARAM_INT);

// Set the basic variables $course, $cm and $moduleinstance.
if ($id) {
    [$course, $cm] = get_course_and_cm_from_cmid($id, 'annopy');
    $moduleinstance = $DB->get_record('annopy', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    throw new moodle_exception('missingparameter');
}

if (!$cm) {
    throw new moodle_exception(get_string('incorrectmodule', 'annopy'));
} else if (!$course) {
    throw new moodle_exception(get_string('incorrectcourseid', 'annopy'));
} else if (!$coursesections = $DB->get_record("course_sections", ["id" => $cm->section])) {
    throw new moodle_exception(get_string('incorrectmodule', 'annopy'));
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

$redirecturl = new moodle_url('/mod/annopy/annotations_summary.php', ['id' => $id]);

// Capabilities check.
if (!$edit) { // If type or template should be added.
    if ($mode == 1 && !(has_capability('mod/annopy:addannotationtypetemplate', $context))) { // If no permission to add template.
        redirect($redirecturl, get_string('notallowedtodothis', 'mod_annopy'), null, notification::NOTIFY_ERROR);
    } else if ($mode == 2 && !(has_capability('mod/annopy:addannotationtype', $context))) { // If no permission to add type.
        redirect($redirecturl, get_string('notallowedtodothis', 'mod_annopy'), null, notification::NOTIFY_ERROR);
    }
} else if ($edit !== 0) {
    if ($mode == 1 && !(has_capability('mod/annopy:editannotationtypetemplate', $context))) { // If no permission to edit template.
        redirect($redirecturl, get_string('notallowedtodothis', 'mod_annopy'), null, notification::NOTIFY_ERROR);
    } else if ($mode == 2 && !(has_capability('mod/annopy:editannotationtype', $context))) { // If no permission to edit type.
        redirect($redirecturl, get_string('notallowedtodothis', 'mod_annopy'), null, notification::NOTIFY_ERROR);
    }
}

// Get type or template to be edited.
if ($edit !== 0) {
    if ($mode == 1) { // If type is template type.
        $editedtype = $DB->get_record('annopy_atype_templates', ['id' => $edit]);

        if (isset($editedtype->defaulttype) && $editedtype->defaulttype == 1
            && !has_capability('mod/annopy:managedefaultannotationtypetemplates', $context)) {

                redirect($redirecturl, get_string('notallowedtodothis', 'mod_annopy'), null, notification::NOTIFY_ERROR);
        }
    } else if ($mode == 2) { // If type is annopy type.
        $editedtype = $DB->get_record('annopy_annotationtypes', ['id' => $edit]);

        if ($moduleinstance->id !== $editedtype->annopy) {
            redirect($redirecturl, get_string('annotationtypecantbeedited', 'mod_annopy'), null, notification::NOTIFY_ERROR);
        }
    }

    if ($editedtype && $mode == 2 ||
        ((isset($editedtype->defaulttype) && $editedtype->defaulttype == 1 &&
        has_capability('mod/annopy:managedefaultannotationtypetemplates', $context))
        || (isset($editedtype->defaulttype) && isset($editedtype->userid) &&
        $editedtype->defaulttype == 0 && $editedtype->userid == $USER->id))) {

        $editedtypeid = $edit;
        $editedtypename = $editedtype->name;
        $editedcolor = '#' . $editedtype->color;

        if ($mode == 1) {
            $editeddefaulttype = $editedtype->defaulttype;
        }
    }
}

$select = "annopy = " . $moduleinstance->id;
$annotationtypes = (array) $DB->get_records_select('annopy_annotationtypes', $select, null, 'priority ASC');

// Instantiate form.
$mform = new mod_annopy_annotationtypes_form(null,
    ['editdefaulttype' => has_capability('mod/annopy:managedefaultannotationtypetemplates', $context), 'mode' => $mode]);

if (isset($editedtypeid)) {
    if ($mode == 1) { // If type is template annotation type.
        $mform->set_data(['id' => $id, 'mode' => $mode, 'typeid' => $editedtypeid,
            'typename' => $editedtypename, 'color' => $editedcolor, 'standardtype' => $editeddefaulttype]);
    } else if ($mode == 2) {
        $mform->set_data(['id' => $id, 'mode' => $mode, 'typeid' => $editedtypeid, 'typename' => $editedtypename,
            'color' => $editedcolor]);
    }
} else {
    $mform->set_data(['id' => $id, 'mode' => $mode, 'color' => '#FFFF00']);
}

if ($mform->is_cancelled()) {
    redirect($redirecturl);
} else if ($fromform = $mform->get_data()) {

    // In this case you process validated data. $mform->get_data() returns data posted in form.
    if ($fromform->typeid == 0 && isset($fromform->typename)) { // Create new annotation type.

        $annotationtype = new stdClass();
        $annotationtype->timecreated = time();
        $annotationtype->timemodified = 0;
        $annotationtype->name = format_text($fromform->typename, 1, ['para' => false]);
        $annotationtype->color = $fromform->color;

        if (isset($fromform->standardtype) && $fromform->standardtype === 1 &&
            has_capability('mod/annopy:managedefaultannotationtypetemplates', $context)) {

            $annotationtype->userid = 0;
            $annotationtype->defaulttype = 1;
        } else {
            $annotationtype->userid = $USER->id;
            $annotationtype->defaulttype = 0;
        }

        if ($mode == 2) { // If type is annopy annotation type.

            if ($annotationtypes) {
                $annotationtype->priority = $annotationtypes[array_key_last($annotationtypes)]->priority + 1;
            } else {
                $annotationtype->priority = 1;
            }

            $annotationtype->annopy = $moduleinstance->id;
        }

        if ($mode == 1) { // If type is template annotation type.
            $DB->insert_record('annopy_atype_templates', $annotationtype);

        } else if ($mode == 2) { // If type is annopy annotation type.
            $DB->insert_record('annopy_annotationtypes', $annotationtype);
        }

        redirect($redirecturl, get_string('annotationtypeadded', 'mod_annopy'), null, notification::NOTIFY_SUCCESS);
    } else if ($fromform->typeid !== 0 && isset($fromform->typename)) { // Update existing annotation type.

        if ($mode == 1) { // If type is template annotation type.
            $annotationtype = $DB->get_record('annopy_atype_templates', ['id' => $fromform->typeid]);
        } else if ($mode == 2) { // If type is annopy annotation type.
            $annotationtype = $DB->get_record('annopy_annotationtypes', ['id' => $fromform->typeid]);
        }

        if ($annotationtype &&
            ($mode == 2 ||
            (isset($annotationtype->defaulttype) && $annotationtype->defaulttype == 1 &&
            has_capability('mod/annopy:managedefaultannotationtypetemplates', $context))
            || (isset($annotationtype->defaulttype) && isset($annotationtype->userid) && $annotationtype->defaulttype == 0
            && $annotationtype->userid == $USER->id))) {

            $annotationtype->timemodified = time();
            $annotationtype->name = format_text($fromform->typename, 1, ['para' => false]);
            $annotationtype->color = $fromform->color;

            if ($mode == 1 && has_capability('mod/annopy:managedefaultannotationtypetemplates', $context)) {
                global $USER;
                if ($fromform->standardtype === 1 && $annotationtype->defaulttype !== $fromform->standardtype) {
                    $annotationtype->defaulttype = 1;
                    $annotationtype->userid = 0;
                } else if ($fromform->standardtype === 0 && $annotationtype->defaulttype !== $fromform->standardtype) {
                    $annotationtype->defaulttype = 0;
                    $annotationtype->userid = $USER->id;
                }
            }

            if ($mode == 1) { // If type is template annotation type.
                $DB->update_record('annopy_atype_templates', $annotationtype);

            } else if ($mode == 2) { // If type is annopy annotation type.
                $DB->update_record('annopy_annotationtypes', $annotationtype);
            }

            redirect($redirecturl, get_string('annotationtypeedited', 'mod_annopy'), null, notification::NOTIFY_SUCCESS);
        } else {
            redirect($redirecturl, get_string('annotationtypecantbeedited', 'mod_annopy'), null, notification::NOTIFY_ERROR);
        }

    } else {
        redirect($redirecturl, get_string('annotationtypeinvalid', 'mod_annopy'), null, notification::NOTIFY_ERROR);
    }
}

// Get the name for this module instance.
$modulename = format_string($moduleinstance->name, true, [
    'context' => $context,
]);

$PAGE->set_url('/mod/annopy/annotationtypes.php', ['id' => $cm->id]);

$navtitle = '';

if (isset($editedtypeid)) {
    $navtitle = get_string('editannotationtype', 'mod_annopy');
} else {
    $navtitle = get_string('addannotationtype', 'mod_annopy');
}

if ($mode == 1) { // If type is template annotation type.
    $navtitle .= ' (' . get_string('template', 'mod_annopy') . ')';
} else if ($mode == 2) { // If type is annopy annotation type.
    $navtitle .= ' (' . get_string('modulename', 'mod_annopy') . ')';
}

$PAGE->navbar->add($navtitle);

$PAGE->set_title(get_string('modulename', 'mod_annopy').': ' . $modulename);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

if ($CFG->branch < 400) {
    $PAGE->force_settings_menu();
}

echo $OUTPUT->header();

if ($CFG->branch < 400) {
    echo $OUTPUT->heading($modulename);

    if ($moduleinstance->intro) {
        echo $OUTPUT->box(format_module_intro('annopy', $moduleinstance, $cm->id), 'generalbox', 'intro');
    }
}

if (isset($editedtypeid) && $mode == 1) {
    if ($editeddefaulttype) {
        echo $OUTPUT->notification(
            get_string('warningeditdefaultannotationtypetemplate', 'mod_annopy'), notification::NOTIFY_ERROR);
    }

    echo $OUTPUT->notification(get_string('changetemplate', 'mod_annopy'), notification::NOTIFY_WARNING);
}

echo $OUTPUT->heading($navtitle, 4);

$mform->display();

echo $OUTPUT->footer();
