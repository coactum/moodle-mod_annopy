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
 * Class containing data for the main page.
 *
 * @package     mod_annopy
 * @copyright   2023 coactum GmbH
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_annopy\output;

use mod_annopy\local\submissionstats;
use mod_annopy\local\helper;
use renderable;
use renderer_base;
use templatable;
use stdClass;

/**
 * Class containing data for the main page.
 *
 * @package     mod_annopy
 * @copyright   2023 coactum GmbH
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class annopy_view implements renderable, templatable {

    /** @var object */
    protected $cm;
    /** @var object */
    protected $course;
    /** @var object */
    protected $context;
    /** @var object */
    protected $moduleinstance;
    /** @var object */
    protected $submission;
    /** @var int */
    protected $userid;

    /**
     * Construct this renderable.
     * @param object $cm The course module
     * @param object $course The course
     * @param object $context The context
     * @param object $moduleinstance The module instance
     * @param object $submission The submission
     * @param int $userid The ID of the user whose annotations should be shown
     */
    public function __construct($cm, $course, $context, $moduleinstance, $submission, $userid) {
        $this->cm = $cm;
        $this->course = $course;
        $this->context = $context;
        $this->moduleinstance = $moduleinstance;
        $this->submission = $submission;
        $this->userid = $userid;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output Renderer base.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $DB, $USER, $OUTPUT;

        $data = new stdClass();
        $data->cmid = $this->cm->id;
        $data->submission = $this->submission;

        $select = "annopy = " . $this->cm->instance;
        $annotationtypes = (array) $DB->get_records_select('annopy_annotationtypes', $select, null, 'priority ASC');

        if ($data->submission) {
            // Set submission author.
            $data->submission->author = $DB->get_record('user', array('id' => $data->submission->author));
            $data->submission->author->userpicture = $OUTPUT->user_picture($data->submission->author,
                array('courseid' => $this->course->id, 'link' => true, 'includefullname' => true, 'size' => 25));

            // Submission stats.
            $data->submission->stats = submissionstats::get_submission_stats($data->submission->content,
                $data->submission->timecreated);
            $data->submission->canviewdetails = has_capability('mod/annopy:addsubmission', $this->context);

            // Prepare annotations.
            $data->submission = helper::prepare_annotations($this->cm, $this->course, $this->context, $data->submission,
                get_string_manager(), $annotationtypes, $this->userid, true);

            // If submission can be edited.
            if (has_capability('mod/annopy:editsubmission', $this->context) && !$data->submission->totalannotationscount) {
                $data->submission->canbeedited = true;
            } else {
                $data->submission->canbeedited = false;
            }
        }

        $data->canaddsubmission = has_capability('mod/annopy:addsubmission', $this->context);
        $data->caneditsubmission = has_capability('mod/annopy:editsubmission', $this->context);
        $data->canviewparticipants = has_capability('mod/annopy:viewparticipants', $this->context);

        $data->sesskey = sesskey();
        $data->pagebar = helper::get_pagebar($this->context, $this->userid, $this->submission, $this->moduleinstance,
            helper::get_annotationtypes_for_form($annotationtypes));

        return $data;
    }
}
