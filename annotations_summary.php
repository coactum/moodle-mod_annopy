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
 * Prints the annotation summary for the module.
 *
 * @package     mod_annopy
 * @copyright   2023 coactum GmbH
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_annopy\output\annopy_annotations_summary;
use mod_annopy\local\helper;
use core\output\notification;

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course_module ID.
$id = required_param('id', PARAM_INT);

// ID of type that should be deleted.
$delete = optional_param('delete', 0, PARAM_INT);

// ID of type that should be deleted.
$addtoannopy = optional_param('addtoannopy', 0, PARAM_INT);

// ID of type where priority should be changed.
$priority = optional_param('priority', 0, PARAM_INT);
$action = optional_param('action', 0, PARAM_INT);

// If template (1) or annopy (2) annotation type.
$mode = optional_param('mode', null, PARAM_INT);

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
} else if (!$coursesections = $DB->get_record("course_sections", array("id" => $cm->section))) {
    throw new moodle_exception(get_string('incorrectmodule', 'annopy'));
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

require_capability('mod/annopy:viewmyannotationsummary', $context);

$canaddannotationtype = has_capability('mod/annopy:addannotationtype', $context);
$caneditannotationtype = has_capability('mod/annopy:editannotationtype', $context);
$candeleteannotationtype = has_capability('mod/annopy:deleteannotationtype', $context);

$canaddannotationtypetemplate = has_capability('mod/annopy:addannotationtypetemplate', $context);
$caneditannotationtypetemplate = has_capability('mod/annopy:editannotationtypetemplate', $context);
$candeleteannotationtypetemplate = has_capability('mod/annopy:deleteannotationtypetemplate', $context);

$select = "annopy = " . $moduleinstance->id;
$annotationtypes = (array) $DB->get_records_select('annopy_annotationtypes', $select, null, 'priority ASC');

global $USER;

// Add type to annopy.
if ($addtoannopy && $canaddannotationtype) {
    require_sesskey();

    $redirecturl = new moodle_url('/mod/annopy/annotations_summary.php', array('id' => $id));

    if ($DB->record_exists('annopy_atype_templates', array('id' => $addtoannopy))) {

        global $USER;

        $type = $DB->get_record('annopy_atype_templates', array('id' => $addtoannopy));

        if ($type->defaulttype == 1 || ($type->defaulttype == 0 && $type->userid == $USER->id)) {
            $type->priority = $annotationtypes[array_key_last($annotationtypes)]->priority + 1;
            $type->annopy = $moduleinstance->id;

            $DB->insert_record('annopy_annotationtypes', $type);

            redirect($redirecturl, get_string('annotationtypeadded', 'mod_annopy'), null, notification::NOTIFY_SUCCESS);
        } else {
            redirect($redirecturl, get_string('notallowedtodothis', 'mod_annopy'), null, notification::NOTIFY_ERROR);
        }
    } else {
        redirect($redirecturl, get_string('notallowedtodothis', 'mod_annopy'), null, notification::NOTIFY_ERROR);
    }
}

// Change priority.
if ($caneditannotationtype && $mode == 2 && $priority && $action &&
    $DB->record_exists('annopy_annotationtypes', array('id' => $priority))) {

    require_sesskey();

    $redirecturl = new moodle_url('/mod/annopy/annotations_summary.php', array('id' => $id));

    $type = $DB->get_record('annopy_annotationtypes', array('annopy' => $moduleinstance->id, 'id' => $priority));

    $oldpriority = 0;

    if ($type && $action == 1 && $type->priority != 1) { // Increase priority (show more in front).
        $oldpriority = $type->priority;
        $type->priority -= 1;

        $typeswitched = $DB->get_record('annopy_annotationtypes',
            array('annopy' => $moduleinstance->id, 'priority' => $type->priority));

        if (!$typeswitched) { // If no type with priority+1 search for types with hihgher priority values.
            $typeswitched = $DB->get_records_select('annopy_annotationtypes',
                "annopy = $moduleinstance->id AND priority < $type->priority", null, 'priority ASC');

            if ($typeswitched && isset($typeswitched[array_key_first($typeswitched)])) {
                $typeswitched = $typeswitched[array_key_first($typeswitched)];
            }
        }

    } else if ($type && $action == 2 && $type->priority != $DB->count_records('annopy_annotationtypes',
        array('annopy' => $moduleinstance->id)) + 1) { // Decrease priority (move further back).

        $oldpriority = $type->priority;
        $type->priority += 1;

        $typeswitched = $DB->get_record('annopy_annotationtypes',
            array('annopy' => $moduleinstance->id, 'priority' => $type->priority));

        if (!$typeswitched) { // If no type with priority+1 search for types with higher priority values.
            $typeswitched = $DB->get_records_select('annopy_annotationtypes',
                "annopy = $moduleinstance->id AND priority > $type->priority", null, 'priority ASC');

            if ($typeswitched && isset($typeswitched[array_key_first($typeswitched)])) {
                $typeswitched = $typeswitched[array_key_first($typeswitched)];
            }
        }
    } else {
        redirect($redirecturl, get_string('prioritynotchanged', 'mod_annopy'), null, notification::NOTIFY_ERROR);
    }

    if ($typeswitched) {
        // Update priority for type.
        $DB->update_record('annopy_annotationtypes', $type);

        // Update priority for type that type is switched with.
        $typeswitched->priority = $oldpriority;
        $DB->update_record('annopy_annotationtypes', $typeswitched);

        redirect($redirecturl, get_string('prioritychanged', 'mod_annopy'), null, notification::NOTIFY_SUCCESS);
    } else {
        redirect($redirecturl, get_string('prioritynotchanged', 'mod_annopy'), null, notification::NOTIFY_ERROR);
    }
}

// Delete annotation.
if ($delete !== 0 && $mode) {

    require_sesskey();

    $redirecturl = new moodle_url('/mod/annopy/annotations_summary.php', array('id' => $id));

    if ($mode == 1) { // If type is template annotation type.
        $table = 'annopy_atype_templates';
    } else if ($mode == 2) { // If type is annopy annotation type.
        $table = 'annopy_annotationtypes';
    }

    if ($DB->record_exists($table, array('id' => $delete))) {

        $type = $DB->get_record($table, array('id' => $delete));

        if ($mode == 2 && $candeleteannotationtype ||
            ($type->defaulttype == 1 && has_capability('mod/annopy:managedefaultannotationtypetemplates', $context)
            && $candeleteannotationtypetemplate)
            || ($type->defaulttype == 0 && $type->userid == $USER->id && $candeleteannotationtypetemplate)) {

            $DB->delete_records($table, array('id' => $delete));
            redirect($redirecturl, get_string('annotationtypedeleted', 'mod_annopy'), null, notification::NOTIFY_SUCCESS);
        } else {
            redirect($redirecturl, get_string('notallowedtodothis', 'mod_annopy'), null, notification::NOTIFY_ERROR);
        }
    } else {
        redirect($redirecturl, get_string('notallowedtodothis', 'mod_annopy'), null, notification::NOTIFY_ERROR);
    }
}

// Get the name for this module instance.
$modulename = format_string($moduleinstance->name, true, array(
    'context' => $context
));

$PAGE->set_url('/mod/annopy/annotations_summary.php', array('id' => $cm->id));
$PAGE->navbar->add(get_string('annotationssummary', 'mod_annopy'));

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

$annotationtypesforform = helper::get_annotationtypes_for_form($annotationtypes);
$participants = helper::get_annopy_participants($context, $moduleinstance, $annotationtypesforform);

$strmanager = get_string_manager();
$annotationstotalcount = 0;

foreach ($annotationtypes as $i => $type) {
    $annotationtypes[$i]->canbeedited = $caneditannotationtype;
    $annotationtypes[$i]->canbedeleted = $candeleteannotationtype;

    if (has_capability('mod/annopy:viewparticipants', $context)) {
        $annotationtypes[$i]->totalcount = $DB->count_records('annopy_annotations',
            array('annopy' => $moduleinstance->id, 'type' => $type->id));
    } else {
        $annotationtypes[$i]->totalcount = $DB->count_records('annopy_annotations',
            array('annopy' => $moduleinstance->id, 'type' => $type->id, 'userid' => $USER->id));
    }

    $annotationstotalcount += $annotationtypes[$i]->totalcount;

    if ($strmanager->string_exists($type->name, 'mod_annopy')) {
        $annotationtypes[$i]->name = get_string($type->name, 'mod_annopy');
    } else {
        $annotationtypes[$i]->name = $type->name;
    }
}

$annotationtypes = array_values($annotationtypes);

global $USER;

$annotationtypetemplates = helper::get_all_annotationtype_templates();
foreach ($annotationtypetemplates as $id => $templatetype) {
    if ($templatetype->defaulttype == 1) {
        $annotationtypetemplates[$id]->type = get_string('standard', 'mod_annopy');

        if (!has_capability('mod/annopy:managedefaultannotationtypetemplates', $context)) {
            $annotationtypetemplates[$id]->canbeedited = false;
            $annotationtypetemplates[$id]->canbedeleted = false;
        } else {
            $annotationtypetemplates[$id]->canbeedited = $caneditannotationtypetemplate;
            $annotationtypetemplates[$id]->canbedeleted = $candeleteannotationtypetemplate;
        }
    } else {
        $annotationtypetemplates[$id]->type = get_string('custom', 'mod_annopy');

        if ($templatetype->userid === $USER->id) {
            $annotationtypetemplates[$id]->canbeedited = $caneditannotationtypetemplate;
            $annotationtypetemplates[$id]->canbedeleted = $candeleteannotationtypetemplate;
        } else {
            $annotationtypetemplates[$id]->canbeedited = false;
            $annotationtypetemplates[$id]->canbedeleted = false;
        }
    }

    if ($templatetype->defaulttype == 1 && $strmanager->string_exists($templatetype->name, 'mod_annopy')) {
        $annotationtypetemplates[$id]->name = get_string($templatetype->name, 'mod_annopy');
    } else {
        $annotationtypetemplates[$id]->name = $templatetype->name;
    }
}

$annotationtypetemplates = array_values($annotationtypetemplates);

// Output page.
$page = new annopy_annotations_summary($cm->id, $context, $participants, $annotationtypes, $annotationtypetemplates,
    sesskey(), $annotationstotalcount);

echo $OUTPUT->render($page);

echo $OUTPUT->footer();
