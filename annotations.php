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
 * File for handling the annotation form.
 *
 * @package     mod_annopy
 * @copyright   2023 coactum GmbH
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;
use mod_annopy\local\helper;

require(__DIR__.'/../../config.php');

global $DB, $CFG;

// Course Module ID.
$id = required_param('id', PARAM_INT);

// Param if annotations should be returned via ajax.
$getannotations = optional_param('getannotations',  0, PARAM_INT);

// Param if annotation should be deleted.
$deleteannotation = optional_param('deleteannotation',  0, PARAM_INT); // Annotation to be deleted.

// The ID of the user whose annotations should be shown.
$userid = optional_param('userid', 0, PARAM_INT);

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

$select = "annopy = " . $moduleinstance->id;
$annotationtypes = (array) $DB->get_records_select('annopy_annotationtypes', $select, null, 'priority ASC');

// Get annotation (ajax).
if ($getannotations) {

    if ($userid) {
        $annotations = $DB->get_records('annopy_annotations', array('annopy' => $moduleinstance->id, 'userid' => $userid));
    } else {
        $annotations = $DB->get_records('annopy_annotations', array('annopy' => $moduleinstance->id));
    }

    $select = "annopy = " . $moduleinstance->id;

    foreach ($annotations as $key => $annotation) {

        if (!array_key_exists($annotation->type, $annotationtypes) &&
            $DB->record_exists('annopy_annotationtypes', array('id' => $annotation->type))) {

            $annotationtypes[$annotation->type] = $DB->get_record('annopy_annotationtypes', array('id' => $annotation->type));
        }

        if (isset($annotationtypes[$annotation->type])) {
            $annotations[$key]->color = $annotationtypes[$annotation->type]->color;
        }

    }

    if ($annotations) {
        echo json_encode($annotations);
    } else {
        echo json_encode(array());
    }

    die;
}

require_capability('mod/annopy:viewannotations', $context);

// Header.
$PAGE->set_url('/mod/annopy/annotations.php', array('id' => $id));
$PAGE->set_title(format_string($moduleinstance->name));

$urlparams = array('id' => $id);

$redirecturl = new moodle_url('/mod/annopy/view.php', $urlparams);

// Delete annotation.
if (has_capability('mod/annopy:deleteannotation', $context) && $deleteannotation !== 0) {
    require_sesskey();

    global $USER;

    if ($DB->record_exists('annopy_annotations', array('id' => $deleteannotation, 'annopy' => $moduleinstance->id,
        'userid' => $USER->id))) {

        $DB->delete_records('annopy_annotations', array('id' => $deleteannotation, 'annopy' => $moduleinstance->id,
            'userid' => $USER->id));

        // Trigger module annotation deleted event.
        $event = \mod_annopy\event\annotation_deleted::create(array(
            'objectid' => $deleteannotation,
            'context' => $context
        ));

        $event->trigger();

        redirect($redirecturl, get_string('annotationdeleted', 'mod_annopy'), null, notification::NOTIFY_SUCCESS);
    } else {
        redirect($redirecturl, get_string('notallowedtodothis', 'mod_annopy'), null, notification::NOTIFY_ERROR);
    }
}

// Save annotation.
require_once($CFG->dirroot . '/mod/annopy/annotation_form.php');

// Instantiate form.
$mform = new mod_annopy_annotation_form(null, array('types' => helper::get_annotationtypes_for_form($annotationtypes)));

if ($fromform = $mform->get_data()) {

    // In this case you process validated data. $mform->get_data() returns data posted in form.
    if ((isset($fromform->annotationid) && $fromform->annotationid !== 0) && isset($fromform->text)) { // Update annotation.
        $annotation = $DB->get_record('annopy_annotations',
            array('annopy' => $moduleinstance->id, 'submission' => $fromform->submission, 'id' => $fromform->annotationid));

        // Prevent changes by user in hidden form fields.
        if (!$annotation) {
            redirect($redirecturl, get_string('annotationinvalid', 'mod_annopy'), null, notification::NOTIFY_ERROR);
        } else if ($annotation->userid != $USER->id) {
            redirect($redirecturl, get_string('notallowedtodothis', 'mod_annopy'), null, notification::NOTIFY_ERROR);
        }

        if (!isset($fromform->type)) {
            redirect($redirecturl, get_string('annotationtypedeleted', 'mod_annopy'), null, notification::NOTIFY_ERROR);
        }

        $annotation->timemodified = time();
        $annotation->text = format_text($fromform->text, 2, array('para' => false));
        $annotation->type = $fromform->type;

        $DB->update_record('annopy_annotations', $annotation);

        // Trigger module annotation updated event.
        $event = \mod_annopy\event\annotation_updated::create(array(
            'objectid' => $fromform->annotationid,
            'context' => $context
        ));

        $event->trigger();

        $urlparams = array('id' => $id, 'annotationmode' => 1, 'focusannotation' => $fromform->annotationid);
        $redirecturl = new moodle_url('/mod/annopy/view.php', $urlparams);

        redirect($redirecturl, get_string('annotationedited', 'mod_annopy'), null, notification::NOTIFY_SUCCESS);
    } else if ((!isset($fromform->annotationid) || $fromform->annotationid === 0) && isset($fromform->text)) { // New annotation.

        if ($fromform->startcontainer != -1 && $fromform->endcontainer != -1 &&
            $fromform->startoffset != -1 && $fromform->endoffset != -1) {

            if (!isset($fromform->type)) {
                redirect($redirecturl, get_string('annotationtypedeleted', 'mod_annopy'), null, notification::NOTIFY_ERROR);
            }

            if (preg_match("/[^a-zA-Z0-9()-\/[\]]/", $fromform->startcontainer)
                || preg_match("/[^a-zA-Z0-9()-\/[\]]/", $fromform->endcontainer)) {

                redirect($redirecturl, get_string('annotationinvalid', 'mod_annopy'), null, notification::NOTIFY_ERROR);
            }

            if (!$DB->record_exists('annopy_submissions', array('annopy' => $moduleinstance->id, 'id' => $fromform->submission))) {
                redirect($redirecturl, get_string('annotationinvalid', 'mod_annopy'), null, notification::NOTIFY_ERROR);
            }

            $annotation = new stdClass();
            $annotation->annopy = (int) $moduleinstance->id;
            $annotation->submission = (int) $fromform->submission;
            $annotation->userid = $USER->id;
            $annotation->timecreated = time();
            $annotation->timemodified = 0;
            $annotation->type = $fromform->type;
            $annotation->startcontainer = $fromform->startcontainer;
            $annotation->endcontainer = $fromform->endcontainer;
            $annotation->startoffset = $fromform->startoffset;
            $annotation->endoffset = $fromform->endoffset;
            $annotation->annotationstart = $fromform->annotationstart;
            $annotation->annotationend = $fromform->annotationend;
            $annotation->exact = $fromform->exact;
            $annotation->prefix = $fromform->prefix;
            $annotation->suffix = $fromform->suffix;
            $annotation->text = $fromform->text;

            $newid = $DB->insert_record('annopy_annotations', $annotation);
            // Trigger module annotation created event.
            $event = \mod_annopy\event\annotation_created::create(array(
                'objectid' => $newid,
                'context' => $context
            ));
            $event->trigger();

            $urlparams = array('id' => $id, 'annotationmode' => 1, 'focusannotation' => $newid);
            $redirecturl = new moodle_url('/mod/annopy/view.php', $urlparams);

            redirect($redirecturl, get_string('annotationadded', 'mod_annopy'), null, notification::NOTIFY_SUCCESS);
        } else {
            redirect($redirecturl, get_string('annotationinvalid', 'mod_annopy'), null, notification::NOTIFY_ERROR);
        }
    }
} else {
    redirect($redirecturl, get_string('annotationinvalid', 'mod_annopy'), null, notification::NOTIFY_ERROR);
}
