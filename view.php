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
 * Prints an instance of mod_annopy.
 *
 * @package     mod_annopy
 * @copyright   2023 coactum GmbH
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_annopy\output\annopy_view;
use core\output\notification;

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course_module ID.
$id = optional_param('id', 0, PARAM_INT);

// Param with id of annotation that should be focused.
$focusannotation = optional_param('focusannotation',  0, PARAM_INT); // ID of annotation.

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

// Trigger course_module_viewed event.
$event = \mod_annopy\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $context
));

$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('annopy', $moduleinstance);
$event->trigger();

// Get the name for this activity.
$modulename = format_string($moduleinstance->name, true, array(
    'context' => $context
));

// Set $PAGE and completion.
$PAGE->set_url('/mod/annopy/view.php', array('id' => $cm->id));

$PAGE->navbar->add(get_string("overview", "annopy"));
/* $PAGE->navbar->add(get_string("overview", "annopy"), new moodle_url('/mod/annopy/view.php', array('id' => $cm->id)));
if (true) {
    $PAGE->navbar->add(get_string("overview", "annopy"));
    $PAGE->set_url('/mod/annopy/view.php', array('id' => $cm->id));
} */

$PAGE->requires->js_call_amd('mod_annopy/annotations', 'init',
    array( 'cmid' => $cm->id, 'canaddannotation' => has_capability('mod/annopy:addannotation', $context), 'myuserid' => $USER->id,
    'focusannotation' => $focusannotation));

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_title(get_string('modulename', 'mod_annopy').': ' . $modulename);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Add settingsmenu and heading for moodle < 400.
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

// Handle groups.
echo groups_print_activity_menu($cm, $CFG->wwwroot . "/mod/annopy/view.php?id=$id");

// Get submission for the module.
$submission = $DB->get_record('annopy_submissions', array('annopy' => $moduleinstance->id));

// Render and output page.
$page = new annopy_view($cm, $course, $context, $submission);

echo $OUTPUT->render($page);

// Output footer.
echo $OUTPUT->footer();
