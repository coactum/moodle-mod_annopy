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
 * AnnoPy submission search.
 *
 * @package   mod_annopy
 * @copyright 2023 coactum GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_annopy\search;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/annopy/lib.php');
require_once($CFG->dirroot . '/lib/grouplib.php');

/**
 * AnnoPy submission search.
 *
 * @package   mod_annopy
 * @copyright 2023 coactum GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submission extends \core_search\base_mod {

    /**
     * Returns recordset containing required data for indexing AnnoPy submissions.
     *
     * @param int $modifiedfrom timestamp
     * @param \context|null $context Optional context to restrict scope of returned results
     * @return moodle_recordset|null Recordset (or null if no results)
     */
    public function get_document_recordset($modifiedfrom = 0, \context $context = null) {
        global $DB;

        list ($contextjoin, $contextparams) = $this->get_context_restriction_sql($context, 'annopy', 'm', SQL_PARAMS_NAMED);
        if ($contextjoin === null) {
            return null;
        }

        $sql = "SELECT s.*, a.course
                  FROM {annopy_submissions} s
                  JOIN {annopy} a ON a.id = s.annopy
          $contextjoin
                 WHERE s.timemodified >= :timemodified
              ORDER BY s.timemodified ASC";
        return $DB->get_recordset_sql($sql, array_merge($contextparams, [
            'timemodified' => $modifiedfrom,
        ]));
    }

    /**
     * Returns the documents associated with this AnnoPy submission id.
     *
     * @param stdClass $submission annopy submission.
     * @param array $options
     * @return \core_search\document
     */
    public function get_document($submission, $options = []) {
        try {
            $cm = $this->get_cm('annopy', $submission->annopy, $submission->course);
            $context = \context_module::instance($cm->id);
        } catch (\dml_missing_record_exception $ex) {
            // Notify it as we run here as admin, we should see everything.
            debugging('Error retrieving mod_annopy '.$submission->id.' document, not all required data is available: '
                .$ex->getMessage(), DEBUG_DEVELOPER);
            return false;
        } catch (\dml_exception $ex) {
            // Notify it as we run here as admin, we should see everything.
            debugging('Error retrieving mod_annopy' . $submission->id . ' document: ' . $ex->getMessage(), DEBUG_DEVELOPER);
            return false;
        }

        // Prepare associative array with data from DB.
        $doc = \core_search\document_factory::instance($submission->id, $this->componentname, $this->areaname);
        // I am using the submission date (timecreated) for the title.
        $doc->set('title', content_to_text((userdate($submission->timecreated)), $submission->format));
        $doc->set('content', content_to_text('Submission: ' . $submission->text, $submission->format));
        $doc->set('contextid', $context->id);
        $doc->set('courseid', $submission->course);
        $doc->set('userid', $submission->author);
        $doc->set('owneruserid', \core_search\manager::NO_OWNER_ID);
        $doc->set('modified', $submission->timemodified);
        $doc->set('description1', '');

        // Check if this document should be considered new.
        if (isset($options['lastindexedtime']) && ($options['lastindexedtime'] < $submission->timemodified)) {
            // If the document was created after the last index time, it must be new.
            $doc->set_is_new(true);
        }
        return $doc;
    }

    /**
     * Whether the user can access the document or not.
     *
     * @throws \dml_missing_record_exception
     * @throws \dml_exception
     * @param int $id annopy submission id
     * @return bool
     */
    public function check_access($id) {
        global $USER;

        try {
            $submission = $this->get_submission($id);
            $cminfo = $this->get_cm('annopy', $submission->annopy, $submission->course);
        } catch (\dml_missing_record_exception $ex) {
            return \core_search\manager::ACCESS_DELETED;
        } catch (\dml_exception $ex) {
            return \core_search\manager::ACCESS_DENIED;
        }

        if (! $cminfo->uservisible) {
            return \core_search\manager::ACCESS_DENIED;
        }

        if ($submission->author != $USER->id && ! has_capability('mod/annopy:viewparticipants', $cminfo->context)) {
            return \core_search\manager::ACCESS_DENIED;
        }

        return \core_search\manager::ACCESS_GRANTED;
    }

    /**
     * Link to AnnoPy submission.
     *
     * @param \core_search\document $doc
     * @return \moodle_url
     */
    public function get_doc_url(\core_search\document $doc) {
        global $USER;

        $contextmodule = \context::instance_by_id($doc->get('contextid'));

        $submissionuserid = $doc->get('userid');
        $url = '/mod/annopy/view.php';

        return new \moodle_url($url, [
            'id' => $contextmodule->instanceid,
        ]);
    }

    /**
     * Link to the annopy.
     *
     * @param \core_search\document $doc
     * @return \moodle_url
     */
    public function get_context_url(\core_search\document $doc) {
        $contextmodule = \context::instance_by_id($doc->get('contextid'));
        return new \moodle_url('/mod/annopy/view.php', [
            'id' => $contextmodule->instanceid,
        ]);
    }

    /**
     * Returns the specified annopy submission checking the internal cache.
     *
     * Store minimal information as this might grow.
     *
     * @throws \dml_exception
     * @param int $submissionid
     * @return stdClass
     */
    protected function get_submission($submissionid) {
        global $DB;
        return $DB->get_record_sql("SELECT s.*, a.course FROM {annopy_submissions} s
                                      JOIN {annopy} a ON a.id = s.annopy
                                     WHERE s.id = ?", ['id' => $submissionid], MUST_EXIST);
    }
}
