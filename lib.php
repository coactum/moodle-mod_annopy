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
 * Library of interface functions and constants.
 *
 * @package     mod_annopy
 * @copyright   2023 coactum GmbH
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Indicates API features that the plugin supports.
 *
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_SHOW_DESCRIPTION
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_RATE
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE__BACKUP_MOODLE2
 * @param string $feature Constant for requested feature.
 * @return mixed True if module supports feature, null if it doesn't.
 */
function annopy_supports($feature) {

    // Adding support for FEATURE_MOD_PURPOSE (MDL-71457) and providing backward compatibility (pre-v4.0).
    if (defined('FEATURE_MOD_PURPOSE') && $feature === FEATURE_MOD_PURPOSE) {
        return MOD_PURPOSE_COLLABORATION;
    }

    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;

        default:
            return null;
    }
}

/**
 * Saves a new instance of the plugin into the database.
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_annopy_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function annopy_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();

    $moduleinstance->id = $DB->insert_record('annopy', $moduleinstance);

    /* // Add calendar dates.
    helper::annopy_update_calendar($moduleinstance, $moduleinstance->coursemodule);

    // Add expected completion date.
    if (! empty($moduleinstance->completionexpected)) {
        \core_completion\api::update_completion_date_event($moduleinstance->coursemodule,
            'annopy', $moduleinstance->id, $moduleinstance->completionexpected);
    }*/

    if (isset($moduleinstance->annotationtypes) && !empty($moduleinstance->annotationtypes)) {
        // Add annotation types for the module instance.
        $priority = 1;
        foreach ($moduleinstance->annotationtypes as $id => $checked) {
            if ($checked) {
                $type = $DB->get_record('annopy_atype_templates', array('id' => $id));
                $type->annopy = $moduleinstance->id;
                $type->priority = $priority;

                $priority += 1;

                $DB->insert_record('annopy_annotationtypes', $type);
            }
        }
    }

    return $moduleinstance->id;
}

/**
 * Updates an instance of the plugin in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_annopy_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function annopy_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    $DB->update_record('annopy', $moduleinstance);

    /* // Update calendar.
    helper::annopy_update_calendar($moduleinstance, $moduleinstance->coursemodule);

    // Update completion date.
    $completionexpected = (! empty($moduleinstance->completionexpected)) ? $moduleinstance->completionexpected : null;
    \core_completion\api::update_completion_date_event($moduleinstance->coursemodule,
        'annopy', $moduleinstance->id, $completionexpected);

    // Update grade.
    annopy_grade_item_update($moduleinstance); */

    return true;
}

/**
 * Removes an instance of the plugin from the database.
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function annopy_delete_instance($id) {
    global $DB;

    if (!$annopy = $DB->get_record('annopy', array('id' => $id))) {
        return false;
    }
    if (!$cm = get_coursemodule_from_instance('annopy', $annopy->id)) {
        return false;
    }
    if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
        return false;
    }

    $context = context_module::instance($cm->id);

    // Delete files.
    $fs = get_file_storage();
    $fs->delete_area_files($context->id);

    /* // Update completion for calendar events.
     \core_completion\api::update_completion_date_event($cm->id, 'annopy', $annopy->id, null); */

    // Delete submission.
    $DB->delete_records("annopy_submissions", array("annopy" => $annopy->id));

    // Delete annotations.
    $DB->delete_records("annopy_annotations", array("annopy" => $annopy->id));

    // Delete annotation types for the module instance.
    $DB->delete_records("annopy_annotationtypes", array("annopy" => $annopy->id));

    // Delete annopy, else return false.
    if (!$DB->delete_records("annopy", array("id" => $annopy->id))) {
        return false;
    }

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @param object $course The course object.
 * @param object $user The user object.
 * @param object $mod The modulename.
 * @param object $annopy The plugin instance.
 * @return object A standard object with 2 variables: info and time (last modified)
 */
function annopy_user_outline($course, $user, $mod, $annopy) {
    $return = new stdClass();
    $return->time = time();
    $return->info = '';
    return $return;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in annopy activities and print it out.
 * Return true if there was output, or false is there was none.
 * @param object $course
 * @param bool $viewfullnames capability
 * @param int $timestart
 * @return boolean
 */
function annopy_print_recent_activity($course, $viewfullnames, $timestart) {
    /* global $CFG, $USER, $DB, $OUTPUT;

    $params = array(
        $timestart,
        $course->id,
        'annopy'
    );

    // Moodle branch check.
    if ($CFG->branch < 311) {
        $namefields = user_picture::fields('u', null, 'userid');
    } else {
        $userfieldsapi = \core_user\fields::for_userpic();
        $namefields = $userfieldsapi->get_sql('u', false, '', 'userid', false)->selects;;
    }

    $sql = "SELECT e.id, e.timecreated, cm.id AS cmid, $namefields
              FROM {annopy_entries} e
              JOIN {annopy} d ON d.id = e.annopy
              JOIN {course_modules} cm ON cm.instance = d.id
              JOIN {modules} md ON md.id = cm.module
              JOIN {user} u ON u.id = e.userid
             WHERE e.timecreated > ? AND d.course = ? AND md.name = ?
          ORDER BY timecreated DESC
    ";

    $newentries = $DB->get_records_sql($sql, $params);

    $modinfo = get_fast_modinfo($course);

    $show = array();

    foreach ($newentries as $entry) {
        if (! array_key_exists($entry->cmid, $modinfo->get_cms())) {
            continue;
        }
        $cm = $modinfo->get_cm($entry->cmid);

        if (! $cm->uservisible) {
            continue;
        }
        if ($entry->userid == $USER->id) {
            $show[] = $entry;
            continue;
        }
        $context = context_module::instance($entry->cmid);

        $teacher = has_capability('mod/annopy:manageentries', $context);

        // Only teachers can see other students entries.
        if (!$teacher) {
            continue;
        }

        $groupmode = groups_get_activity_groupmode($cm, $course);

        if ($groupmode == SEPARATEGROUPS && ! has_capability('moodle/site:accessallgroups', $context)) {
            if (isguestuser()) {
                // Shortcut - guest user does not belong into any group.
                continue;
            }

            // This will be slow - show only users that share group with me in this cm.
            if (! $modinfo->get_groups($cm->groupingid)) {
                continue;
            }
            $usersgroups = groups_get_all_groups($course->id, $entry->userid, $cm->groupingid);
            if (is_array($usersgroups)) {
                $usersgroups = array_keys($usersgroups);
                $intersect = array_intersect($usersgroups, $modinfo->get_groups($cm->groupingid));
                if (empty($intersect)) {
                    continue;
                }
            }
        }
        $show[] = $entry;
    }

    if (empty($show)) {
        return false;
    }

    echo $OUTPUT->heading(get_string('newannopyentries', 'annopy') . ':', 6);

    foreach ($show as $entry) {
        $cm = $modinfo->get_cm($entry->cmid);
        $context = context_module::instance($entry->cmid);
        $link = $CFG->wwwroot . '/mod/annopy/view.php?id=' . $cm->id;
        print_recent_activity_note($entry->timecreated, $entry, $cm->name, $link, false, $viewfullnames);
        echo '<br>';
    }

    return true; */
    return false; // True if anything was printed, otherwise false.
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * annopy_print_recent_mod_activity().
 *
 * @param array $activities
 *            sequentially indexed array of objects with the 'cmid' property
 * @param int $index
 *            the index in the $activities to use for the next record
 * @param int $timestart
 *            append activity since this time
 * @param int $courseid
 *            the id of the course we produce the report for
 * @param int $cmid
 *            course module id
 * @param int $userid
 *            check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid
 *            check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function annopy_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid = 0, $groupid = 0) {

    /* global $CFG, $COURSE, $USER, $DB;

    if ($COURSE->id == $courseid) {
        $course = $COURSE;
    } else {
        $course = $DB->get_record('course', array('id' => $courseid));
    }

    $modinfo = get_fast_modinfo($course);

    $cm = $modinfo->get_cm($cmid);
    $params = array();
    if ($userid) {
        $userselect = 'AND u.id = :userid';
        $params['userid'] = $userid;
    } else {
        $userselect = '';
    }

    if ($groupid) {
        $groupselect = 'AND gm.groupid = :groupid';
        $groupjoin = 'JOIN {groups_members} gm ON  gm.userid=u.id';
        $params['groupid'] = $groupid;
    } else {
        $groupselect = '';
        $groupjoin = '';
    }

    $params['cminstance'] = $cm->instance;
    $params['timestart'] = $timestart;
    $params['submitted'] = 1;

    if ($CFG->branch < 311) {
        $userfields = user_picture::fields('u', null, 'userid');
    } else {
        $userfieldsapi = \core_user\fields::for_userpic();
        $userfields = $userfieldsapi->get_sql('u', false, '', 'userid', false)->selects;
    }

    $entries = $DB->get_records_sql(
        'SELECT e.id, e.timecreated, ' . $userfields .
        '  FROM {annopy_entries} e
        JOIN {annopy} m ON m.id = e.annopy
        JOIN {user} u ON u.id = e.userid ' . $groupjoin .
        '  WHERE e.timecreated > :timestart AND
            m.id = :cminstance
            ' . $userselect . ' ' . $groupselect .
            ' ORDER BY e.timecreated DESC', $params);

    if (!$entries) {
         return;
    }

    $groupmode = groups_get_activity_groupmode($cm, $course);
    $cmcontext = context_module::instance($cm->id);
    $grader = has_capability('moodle/grade:viewall', $cmcontext);
    $accessallgroups = has_capability('moodle/site:accessallgroups', $cmcontext);
    $viewfullnames = has_capability('moodle/site:viewfullnames', $cmcontext);
    $teacher = has_capability('mod/annopy:manageentries', $cmcontext);

    $show = array();
    foreach ($entries as $entry) {
        if ($entry->userid == $USER->id) {
            $show[] = $entry;
            continue;
        }

        // Only teachers can see other students entries.
        if (!$teacher) {
            continue;
        }

        if ($groupmode == SEPARATEGROUPS && !$accessallgroups) {
            if (isguestuser()) {
                // Shortcut - guest user does not belong into any group.
                continue;
            }

            // This will be slow - show only users that share group with me in this cm.
            if (!$modinfo->get_groups($cm->groupingid)) {
                continue;
            }
            $usersgroups = groups_get_all_groups($course->id, $entry->userid, $cm->groupingid);
            if (is_array($usersgroups)) {
                $usersgroups = array_keys($usersgroups);
                $intersect = array_intersect($usersgroups, $modinfo->get_groups($cm->groupingid));
                if (empty($intersect)) {
                    continue;
                }
            }
        }
        $show[] = $entry;
    }

    if (empty($show)) {
        return;
    }

    if ($grader) {
        require_once($CFG->libdir.'/gradelib.php');
        $userids = array();
        foreach ($show as $id => $entry) {
            $userids[] = $entry->userid;
        }
        $grades = grade_get_grades($courseid, 'mod', 'annopy', $cm->instance, $userids);
    }

    $aname = format_string($cm->name, true);
    foreach ($show as $entry) {
        $activity = new stdClass();

        $activity->type = 'annopy';
        $activity->cmid = $cm->id;
        $activity->name = $aname;
        $activity->sectionnum = $cm->sectionnum;
        $activity->timestamp = $entry->timecreated;
        $activity->user = new stdClass();
        if ($grader) {
            $activity->grade = $grades->items[0]->grades[$entry->userid]->str_long_grade;
        }

        if ($CFG->branch < 311) {
            $userfields = explode(',', user_picture::fields());
        } else {
            $userfields = explode(',', implode(',', \core_user\fields::get_picture_fields()));
        }

        foreach ($userfields as $userfield) {
            if ($userfield == 'id') {
                // Aliased in SQL above.
                $activity->user->{$userfield} = $entry->userid;
            } else {
                $activity->user->{$userfield} = $entry->{$userfield};
            }
        }
        $activity->user->fullname = fullname($entry, $viewfullnames);

        $activities[$index++] = $activity;
    }

    return; */
}

/**
 * Prints single activity item prepared by {@see annopy_get_recent_mod_activity()}
 *
 * @param object $activity      the activity object the annopy resides in
 * @param int    $courseid      the id of the course the annopy resides in
 * @param bool   $detail        not used, but required for compatibilty with other modules
 * @param int    $modnames      not used, but required for compatibilty with other modules
 * @param bool   $viewfullnames not used, but required for compatibilty with other modules
 */
function annopy_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
    global $CFG, $OUTPUT;

    echo '<table border="0" cellpadding="3" cellspacing="0" class="annopy-recent">';

    echo '<tr><td class="userpicture" valign="top">';
    echo $OUTPUT->user_picture($activity->user);
    echo '</td><td>';

    if ($detail) {
        $modname = $modnames[$activity->type];
        echo '<div class="title">';
        echo $OUTPUT->image_icon('icon', $modname, 'annopy');
        echo '<a href="' . $CFG->wwwroot . '/mod/annopy/view.php?id=' . $activity->cmid . '">';
        echo $activity->name;
        echo '</a>';
        echo '</div>';
    }

    echo '<div class="grade"><strong>';
    echo '<a href="' . $CFG->wwwroot . '/mod/annopy/view.php?id=' . $activity->cmid . '">'
        . get_string('entryadded', 'mod_annopy') . '</a>';
    echo '</strong></div>';

    echo '<div class="user">';
    echo "<a href=\"$CFG->wwwroot/user/view.php?id={$activity->user->id}&amp;course=$courseid\">";
    echo "{$activity->user->fullname}</a> - " . userdate($activity->timestamp);
    echo '</div>';

    echo '</td></tr></table>';
}

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the module (called by course/reset.php).
 *
 * @param object $mform Form passed by reference.
 */
function annopy_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'annopyheader', get_string('modulenameplural', 'mod_annopy'));

    $mform->addElement('checkbox', 'reset_annopy_all', get_string('deletealluserdata', 'mod_annopy'));
}

/**
 * Course reset form defaults.
 *
 * @param object $course Course object.
 * @return array
 */
function annopy_reset_course_form_defaults($course) {
    return array('reset_annopy_all' => 1);
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * This function will remove all userdata from the specified annopy.
 *
 * @param object $data The data submitted from the reset course.
 * @return array status array
 */
function annopy_reset_userdata($data) {
    global $CFG, $DB;

    require_once($CFG->libdir . '/filelib.php');
    require_once($CFG->dirroot . '/rating/lib.php');

    $componentstr = get_string('modulenameplural', 'annopy');
    $status = array();

    /*
    // Get annopys in course that should be resetted.
    $sql = "SELECT m.id
                FROM {annopy} m
                WHERE m.course = ?";

    $params = array(
        $data->courseid
    );

    $annopys = $DB->get_records_sql($sql, $params);

    // Delete entries and their annotations, files and ratings.
    if (!empty($data->reset_annopy_all)) {

        $fs = get_file_storage();

        // Get ratings manager.
        $rm = new rating_manager();
        $ratingdeloptions = new stdClass;
        $ratingdeloptions->component = 'mod_annopy';
        $ratingdeloptions->ratingarea = 'entry';

        foreach ($annopys as $annopyid => $unused) {
            if (!$cm = get_coursemodule_from_instance('annopy', $annopyid)) {
                continue;
            }

            // Remove files.
            $context = context_module::instance($cm->id);
            $fs->delete_area_files($context->id, 'mod_annopy', 'entry');
            $fs->delete_area_files($context->id, 'mod_annopy', 'feedback');

            // Remove ratings.
            $ratingdeloptions->contextid = $context->id;
            $rm->delete_ratings($ratingdeloptions);
        }

        // Remove all grades from gradebook (if that is not already done by the reset_gradebook_grades).
        if (empty($data->reset_gradebook_grades)) {
            annopy_reset_gradebook($data->courseid);
        }

        // Delete the annotations of all entries.
        $DB->delete_records_select('annopy_annotations', "annopy IN ($sql)", $params);

        // Delete all entries.
        $DB->delete_records_select('annopy_entries', "annopy IN ($sql)", $params);

        $status[] = array(
            'component' => $modulename,
            'item' => get_string('alluserdatadeleted', 'annopy'),
            'error' => false
        );
    } */

    // Updating dates - shift may be negative too.
    if ($data->timeshift) {
        // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
        // See MDL-9367.
        shift_course_mod_dates('annopy', array('assesstimestart', 'assesstimefinish'), $data->timeshift, $data->courseid);
        $status[] = array('component' => $componentstr, 'item' => get_string('datechanged'), 'error' => false);
    }

    return $status;
}


/**
 * Removes all grades in the annopy gradebook
 *
 * @param int $courseid
 */
function annopy_reset_gradebook($courseid) {
    /* global $DB;

    $params = array($courseid);

    $sql = "SELECT ma.*, cm.idnumber as cmidnumber, ma.course as courseid
              FROM {annopy} ma, {course_modules} cm, {modules} m
             WHERE m.name='annopy' AND m.id=cm.module AND cm.instance=ma.id AND ma.course=?";

    if ($annopys = $DB->get_records_sql($sql, $params)) {
        foreach ($annopys as $annopy) {
            annopy_grade_item_update($annopy, 'reset');
        }
    } */
}

/**
 * Get annopy grades for a user (custom function used by annopy_update_grades).
 *
 * @param object $annopy If null, all annopys
 * @param int $userid If false all users
 * @return object $grades
 */
function annopy_get_user_grades($annopy, $userid = 0) {
    global $CFG;

    require_once($CFG->dirroot . '/rating/lib.php');

    $ratingoptions = new stdClass();
    $ratingoptions->component = 'mod_annopy';
    $ratingoptions->ratingarea = 'entry';
    $ratingoptions->modulename = 'annopy';
    $ratingoptions->moduleid = $annopy->id;
    $ratingoptions->userid = $userid;
    $ratingoptions->aggregationmethod = $annopy->assessed;
    $ratingoptions->scaleid = $annopy->scale;
    $ratingoptions->itemtable = 'annopy_entries';
    $ratingoptions->itemtableusercolumn = 'userid';

    $rm = new rating_manager();

    return $rm->get_user_grades($ratingoptions);
}

/**
 * Update grades in the annopy gradebook.
 *
 * Needed by {@see grade_update_mod_grades()}.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param int $userid Update grade of specific user only, 0 means all participants.
 */
function annopy_update_grades($moduleinstance, $userid = 0) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $cm = get_coursemodule_from_instance('annopy', $moduleinstance->id);
    $moduleinstance->cmidnumber = $cm->idnumber;

    if (!$moduleinstance->assessed) {
        annopy_grade_item_update($moduleinstance);
    } else if ($grades = annopy_get_user_grades($moduleinstance, $userid)) {
        annopy_grade_item_update($moduleinstance, $grades);
    } else if ($userid && $nullifnone) {
        $grade = new stdClass();
        $grade->userid = $userid;
        $grade->rawgrade = null;
        annopy_grade_item_update($moduleinstance, $grade);
    } else {
        annopy_grade_item_update($moduleinstance);
    }
}

/**
 * Creates or updates grade item for given annopy.
 *
 * Needed by {@see grade_update_mod_grades()}.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param array $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return void.
 */
function annopy_grade_item_update($moduleinstance, $grades = null) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $params = array(
        'itemname' => $annopy->name,
        'idnumber' => $annopy->cmidnumber
    );

    if (! $annopy->assessed || $annopy->scale == 0) {
        $params['gradetype'] = GRADE_TYPE_NONE;
    } else if ($annopy->scale > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax'] = $annopy->scale;
        $params['grademin'] = 0;
    } else if ($annopy->scale < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid'] = - $annopy->scale;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/annopy', $annopy->course, 'mod', 'annopy', $annopy->id, 0, $grades, $params);
}

/**
 * Delete grade item for given module instance.
 *
 * @param stdClass $moduleinstance Instance object.
 * @return grade_item.
 */
function annopy_grade_item_delete($moduleinstance) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('/mod/annopy', $moduleinstance->course, 'mod', 'annopy',
                        $moduleinstance->id, 0, null, array('deleted' => 1));
}

/**
 * Checks if scale is being used by any instance of mod_annopy.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by any mod_annopy instance.
 */
function annopy_scale_used_anywhere($scaleid) {
    global $DB;

    if (empty($scaleid)) {
        return false;
    }

    return $DB->record_exists_select('annopy', "scale = ? and assessed > 0", [$scaleid * -1]);
}

/**
 * Returns the lists of all browsable file areas within the given module context.
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@see file_browser::get_file_info_context_module()}.
 *
 * @package     mod_annopy
 * @category    files
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return string[]
 */
function annopy_get_file_areas($course, $cm, $context) {
    return array(
        'entry' => get_string('entry', 'mod_annopy'),
    );
}

/**
 * File browsing support for mod_annopy file areas (for attachements?).
 *
 * @package     mod_annopy
 * @category    files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info Instance or null if not found
 */
function annopy_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the plugins file areas.
 *
 * @package     mod_annopy
 * @category    files
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module object.
 * @param stdClass $context The mod_annopy's context.
 * @param string $filearea The name of the file area.
 * @param array $args Extra arguments (itemid, path).
 * @param bool $forcedownload Whether or not force download.
 * @param array $options Additional options affecting the file serving.
 * @return bool false if file not found, does not return if found - just sends the file.
 */
function annopy_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options = array()) {
    global $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);

    if (!$course->visible && !has_capability('moodle/course:viewhiddencourses', $context)) {
        return false;
    }

    // Args[0] should be the submission id.
    $submissionid = intval(array_shift($args));

    if ($filearea !== 'submission') {
        return false;
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_annopy/$filearea/$submissionid/$relativepath";
    $file = $fs->get_file_by_hash(sha1($fullpath));

    // Finally send the file.
    send_stored_file($file, null, 0, $forcedownload, $options);
}

/**
 * Extends the global navigation tree by adding mod_annopy nodes if there is a relevant content.
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $annopynode An object representing the navigation tree node.
 * @param  stdClass $course Course object
 * @param  context_course $coursecontext Course context
 */
function annopy_extend_navigation_course($annopynode, $course, $coursecontext) {
    $modinfo = get_fast_modinfo($course); // Get mod_fast_modinfo from $course.
    $index = 1; // Set index.
    foreach ($modinfo->get_cms() as $cmid => $cm) { // Search existing course modules for this course.
        if ($index == 1 && $cm->modname == "annopy" && $cm->uservisible && $cm->available) {
            $url = new moodle_url("/mod/" . $cm->modname . "/index.php",
                array("id" => $course->id)); // Set url for the link in the navigation node.
            $node = navigation_node::create(get_string('viewallannopys', 'annopy'), $url,
                navigation_node::TYPE_CUSTOM, null , null , null);
            $annopynode->add_node($node);
            $index++;
        }
    }
}

/**
 * Extends the settings navigation with the mod_annopy settings.
 *
 * This function is called when the context for the page is a mod_annopy module.
 * This is not called by AJAX so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav
 * @param navigation_node $annopynode
 */
function annopy_extend_settings_navigation($settingsnav, $annopynode = null) {
}
