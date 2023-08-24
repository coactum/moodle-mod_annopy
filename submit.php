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
 * The page for submitting in mod_annopy.
 *
 * @package     mod_annopy
 * @copyright   2023 coactum GmbH
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;
use mod_annopy\local\helper;

require(__DIR__.'/../../config.php');
require_once('./submit_form.php');

global $DB;

// Course Module ID.
$id = required_param('id', PARAM_INT);

// Module instance ID as alternative.
$a = optional_param('a', null, PARAM_INT);

// ID of the submission to be edited (if existing).
$submissionid = optional_param('submissionid', '0', PARAM_INT);

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

require_capability('mod/annopy:addsubmission', $context);

$data = new stdClass();
$data->id = $cm->id;

// Get submission that should be edited.
if ($DB->record_exists('annopy_submissions', array('annopy' => $moduleinstance->id))) {
    $submission = $DB->get_record('annopy_submissions', array('annopy' => $moduleinstance->id));

    // Prevent editing of submissions not started by this user.
    if ($submission->author != $USER->id) {
        redirect(new moodle_url('/mod/annopy/view.php?id=' . $cm->id), get_string('editsubmissionnotpossible', 'mod_annopy'),
            null, notification::NOTIFY_ERROR);
    }

    $data->submissionid = $submission->id;
    $data->timecreated = time();
    $data->submission = $submission->content;
    $data->submissionformat = $submission->format;
    $data->title = $submission->title;

    $title = get_string('editsubmission', 'mod_annopy');

} else {
    $submission = false;

    $data->submissionid = null;
    $data->timecreated = time();
    $data->submission = '';
    $data->submissionformat = FORMAT_HTML;
    $data->title = get_string('submission', 'mod_annopy');

    $title = get_string('addsubmission', 'mod_annopy');
}

list ($editoroptions, $attachmentoptions) = helper::annopy_get_editor_and_attachment_options($course, $context, $moduleinstance);

$data = file_prepare_standard_editor($data, 'submission', $editoroptions, $context,
    'mod_annopy', 'submission', $data->submissionid);
$data = file_prepare_standard_filemanager($data, 'attachment', $attachmentoptions, $context,
    'mod_annopy', 'attachment', $data->submissionid);

// Create form.
$form = new mod_annopy_submit_form(null, array('editoroptions' => $editoroptions));

// Set existing data for this submission.
$form->set_data($data);

if ($form->is_cancelled()) {
    redirect($CFG->wwwroot . '/mod/annopy/view.php?id=' . $cm->id);
} else if ($fromform = $form->get_data()) {

    global $DB;

    if (isset($fromform->submissionid)) {

        if ($fromform->submissionid !== 0) { // Update existing submission.
            // Get existing submission.
            $submission = $DB->get_record('annopy_submissions',
                array('annopy' => $moduleinstance->id, 'id' => $fromform->submissionid));

            // Set new version and time modified.
            $submission->currentversion += 1;
            $submission->timemodified = time();

            // Set editor for plugin files.
            $fromform = file_postupdate_standard_editor($fromform, 'submission', $editoroptions, $editoroptions['context'],
            'mod_annopy', 'submission', $submission->id);

            $submissiontext = file_rewrite_pluginfile_urls($fromform->submission, 'pluginfile.php', $context->id,
                'mod_annopy', 'submission', $submission->id);

            // Set submission title, content and format.
            $submission->title = format_text($fromform->title, 1, array('para' => false));

            $submission->content = format_text($submissiontext,
                $fromform->submission_editor['format'], array('para' => false));

            $submission->format = (int) $fromform->submission_editor['format'];

            // Update submission.
            $updated = $DB->update_record('annopy_submissions', $submission);

            if ($updated) {
                // Trigger submission updated event.
                $event = \mod_annopy\event\submission_updated::create(array(
                    'objectid' => $submission->id,
                    'context' => $context
                ));
                $event->trigger();

                redirect(new moodle_url('/mod/annopy/view.php', array('id' => $id)),
                    get_string('submissionmodified', 'mod_annopy'), null, notification::NOTIFY_SUCCESS);

            } else {
                redirect(new moodle_url('/mod/annopy/view.php', array('id' => $id)),
                    get_string('submissionnotmodified', 'mod_annopy'), null, notification::NOTIFY_ERROR);
            }

        } else if ($fromform->submissionid === 0) { // New submission.
            if (!$DB->get_record('annopy_submissions', array('annopy' => $moduleinstance->id))) { // No submission made yet.

                // Create new submission object.
                $submission = new stdClass();
                $submission->annopy = (int) $moduleinstance->id;
                $submission->author = $USER->id;

                // Set new version, time created, and modified.
                $submission->currentversion = 1;
                $submission->timecreated = time();
                $submission->timemodified = null;

                // Set submission title, content and format.
                $submission->title = format_text($fromform->title, 1, array('para' => false));
                $submission->content = '';
                $submission->format = 1;

                // Save submission.
                $submission->id = $DB->insert_record('annopy_submissions', $submission);

                // Set editor for plugin files.
                $fromform = file_postupdate_standard_editor($fromform, 'submission', $editoroptions, $editoroptions['context'],
                'mod_annopy', 'submission', $submission->id);

                $submissiontext = file_rewrite_pluginfile_urls($fromform->submission, 'pluginfile.php',
                    $context->id, 'mod_annopy', 'submission', $submission->id);

                // Set submission text and format.
                $submission->content = format_text($submissiontext,
                    $fromform->submission_editor['format'], array('para' => false));
                $submission->format = (int) $fromform->submission_editor['format'];

                // Update submission with formatted content.
                $updated = $DB->update_record('annopy_submissions', $submission);

                if ($updated) {
                    // Trigger submission created event.
                    $event = \mod_annopy\event\submission_created::create(array(
                        'objectid' => $submission->id,
                        'context' => $context
                    ));
                    $event->trigger();

                    redirect(new moodle_url('/mod/annopy/view.php',
                        array('id' => $id)),
                        get_string('submissioncreated', 'mod_annopy'), null, notification::NOTIFY_SUCCESS);

                } else {
                    redirect(new moodle_url('/mod/annopy/view.php',
                        array('id' => $id)),
                        get_string('submissionnotcreated', 'mod_annopy'), null, notification::NOTIFY_ERROR);
                }

            } else {
                redirect(new moodle_url('/mod/annopy/view.php',
                    array('id' => $id)),
                    get_string('submissionfaileddoubled', 'mod_annopy'), null, notification::NOTIFY_ERROR);
            }
        }
    }

}

// Get the name for this activity.
$modulename = format_string($moduleinstance->name, true, array(
    'context' => $context
));

$PAGE->set_url('/mod/annopy/submit.php', array('id' => $id));
$PAGE->navbar->add($title);
$PAGE->set_title($modulename . ' - ' . $title);
$PAGE->set_heading($course->fullname);
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

echo $OUTPUT->heading($title, 4);

// Display the form for adding or editing the submission.
$form->display();

echo $OUTPUT->footer();
