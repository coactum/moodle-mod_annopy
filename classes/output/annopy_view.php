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
    protected $submission;

    /**
     * Construct this renderable.
     * @param int $cm The course module
     * @param object $course The course
     * @param object $context The context
     * @param object $submission The submission
     */
    public function __construct($cm, $course, $context, $submission) {
        $this->cm = $cm;
        $this->course = $course;
        $this->context = $context;
        $this->submission = $submission;
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

        if ($data->submission) {
            // If submission can be edited.
            $data->submission->canbeedited = has_capability('mod/annopy:editsubmission', $this->context);

            // Set submission user.
            $data->submission->user = $DB->get_record('user', array('id' => $data->submission->author));
            $data->submission->user->userpicture = $OUTPUT->user_picture($data->submission->user,
                array('courseid' => $this->course->id, 'link' => true, 'includefullname' => true, 'size' => 25));

            // Submission stats.
            $data->submission->stats = submissionstats::get_submission_stats($data->submission->content,
                $data->submission->timecreated);
            $data->submission->canviewdetails = has_capability('mod/annopy:addsubmission', $this->context);

            // Prepare annotations.
            $select = "annopy = " . $this->cm->instance;
            $annotationtypes = (array) $DB->get_records_select('annopy_annotationtypes', $select, null, 'priority ASC');
            $data->submission = helper::prepare_annotations($this->cm, $this->course, $this->context, $data->submission,
                get_string_manager(), $annotationtypes, true);
        }

        $data->canaddsubmission = has_capability('mod/annopy:addsubmission', $this->context);

        $data->sesskey = sesskey();
        return $data;
    }
}
