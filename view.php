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

// Set the basic variables $course, $cm and $moduleinstance.
if ($id) {
    [$course, $cm] = get_course_and_cm_from_cmid($id, 'annopy');
    $moduleinstance = $DB->get_record('annopy', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    throw new moodle_exception('missingparameter');
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

$PAGE->requires->js_call_amd('mod_annopy/view', 'init', array('cmid' => $cm->id));

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

// Check if new session should be saved.
require_once($CFG->dirroot . '/mod/annopy/example_form.php');

// Instantiate form.
$mform = new mod_annopy_example_form(null, array('things' => 123));

if ($fromform = $mform->get_data()) {

    // In this case you process validated data. $mform->get_data() returns data posted in form.
    if (isset($fromform->itemid)) { // Create new item.

        $item = new stdClass();
        $session->annopy = (int) $cm->instance;
        $session->userid = (int) $USER->id;
        $session->timecreated = time();
        $session->property1 = $fromform->property1;

        $newitemnid = $DB->insert_record('annopy_items', $item);

        // Trigger annopy session login successfull event.
        $event = \mod_annopy\event\thing_created::create(array(
            'objectid' => $newitemnid,
            'context' => $context
        ));

        $event->trigger();

        $urlparams = array('id' => $id);
        $redirecturl = new moodle_url('/mod/annopy/view.php', $urlparams);

        // redirect($redirecturl, get_string('creationsuccessfull', 'mod_annopy'), null, notification::NOTIFY_SUCCESS);

    } else { // Update item.
        $thing = $DB->get_record('annopy_things', array('thing' => $cm->instance, 'id' => $fromform->itemid));

        $thing->timemodified = time();
        $thing->text = format_text($fromform->text, 2, array('para' => false));
        $thing->type = $fromform->type;

        $DB->update_record('annopy_things', $thing);

        // Trigger annopy session login failed event.
        $event = \mod_annopy\event\thing_updated::create(array(
            'objectid' => (int) $USER->id,
            'context' => $context
        ));

        $event->trigger();

        $urlparams = array('id' => $id);
        $redirecturl = new moodle_url('/mod/annopy/view.php', $urlparams);

        redirect($redirecturl, get_string('thingupdated', 'mod_annopy'), null, notification::NOTIFY_ERROR);

    }
}

if ($CFG->branch < 400) {
    echo $OUTPUT->heading($modulename);

    if ($moduleinstance->intro) {
        echo $OUTPUT->box(format_module_intro('annopy', $moduleinstance, $cm->id), 'generalbox', 'intro');
    }
}

// Get grading of current user when annopy is rated.
/* if ($moduleinstance->assessed != 0) {
    $ratingaggregationmode = helper::get_annopy_aggregation($moduleinstance->assessed) . ' ' .
        get_string('forallmyentries', 'mod_annopy');
    $gradinginfo = grade_get_grades($course->id, 'mod', 'annopy', $moduleinstance->id, $USER->id);
    $userfinalgrade = $gradinginfo->items[0]->grades[$USER->id];
    $currentuserrating = $userfinalgrade->str_long_grade;
} else {
    $ratingaggregationmode = false;
    $currentuserrating = false;
} */

// Handle groups.
echo groups_print_activity_menu($cm, $CFG->wwwroot . "/mod/annopy/view.php?id=$id");

// Render and output page.
$page = new annopy_view($cm);

echo $OUTPUT->render($page);

$mform = new mod_annopy_example_form(new moodle_url('/mod/annopy/view.php', array('id' => $cm->id)));

// Set default data.
$mform->set_data(array('id' => $cm->id, 'username' => $USER->username));

echo $mform->render();

// Output footer.
echo $OUTPUT->footer();
