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
 * Privacy subsystem implementation for AnnoPy.
 *
 * @package    mod_annopy
 * @copyright  2023 coactum GmbH
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_annopy\privacy;

use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\writer;
use core_privacy\local\request\helper;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\transform;
use core_privacy\local\request\contextlist;

/**
 * Implementation of the privacy subsystem plugin provider for the activity module.
 *
 * @copyright  2023 coactum GmbH
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // This plugin has data.
    \core_privacy\local\metadata\provider,

    // This plugin currently implements the original plugin\provider interface.
    \core_privacy\local\request\plugin\provider,

    // This plugin is capable of determining which users have data within it.
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Provides the meta data stored for a user stored by the plugin.
     *
     * @param   collection     $items The initialised collection to add items to.
     * @return  collection     A listing of user data stored through this system.
     */
    public static function get_metadata(collection $items) : collection {

        // The table 'annopy_submissions' stores all submissions.
        $items->add_database_table('annopy_submissions', [
            'annopy' => 'privacy:metadata:annopy_submissions:annopy',
            'author' => 'privacy:metadata:annopy_submissions:author',
            'title' => 'privacy:metadata:annopy_submissions:title',
            'content' => 'privacy:metadata:annopy_submissions:content',
            'currentversion' => 'privacy:metadata:annopy_submissions:currentversion',
            'format' => 'privacy:metadata:annopy_submissions:format',
            'timecreated' => 'privacy:metadata:annopy_submissions:timecreated',
            'timemodified' => 'privacy:metadata:annopy_submissions:timemodified',
        ], 'privacy:metadata:annopy_submissions');

        // The table 'annopy_annotations' stores all annotations.
        $items->add_database_table('annopy_annotations', [
            'annopy' => 'privacy:metadata:annopy_annotations:annopy',
            'submission' => 'privacy:metadata:annopy_annotations:submission',
            'userid' => 'privacy:metadata:annopy_annotations:userid',
            'timecreated' => 'privacy:metadata:annopy_annotations:timecreated',
            'timemodified' => 'privacy:metadata:annopy_annotations:timemodified',
            'type' => 'privacy:metadata:annopy_annotations:type',
            'text' => 'privacy:metadata:annopy_annotations:text',
        ], 'privacy:metadata:annopy_annotations');

        // The table 'annopy_atype_templates' stores all annotation type templates.
        $items->add_database_table('annopy_atype_templates', [
            'timecreated' => 'privacy:metadata:annopy_atype_templates:timecreated',
            'timemodified' => 'privacy:metadata:annopy_atype_templates:timemodified',
            'name' => 'privacy:metadata:annopy_atype_templates:name',
            'color' => 'privacy:metadata:annopy_atype_templates:color',
            'userid' => 'privacy:metadata:annopy_atype_templates:userid',
        ], 'privacy:metadata:annopy_atype_templates');

        // The plugin uses multiple subsystems that save personal data.
        $items->add_subsystem_link('core_files', [], 'privacy:metadata:core_files');

        // No user preferences in the plugin.

        return $items;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * In this case of all annopys where a user is exam participant.
     *
     * @param   int         $userid     The user to search.
     * @return  contextlist $contextlist  The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new contextlist();

        $params = [
            'modulename' => 'annopy',
            'contextlevel' => CONTEXT_MODULE,
            'userid' => $userid,
        ];

        // Get contexts of the submissions.
        $sql = "SELECT c.id
                    FROM {context} c
                    JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                    JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                    JOIN {annopy} a ON a.id = cm.instance
                    JOIN {annopy_submissions} s ON s.annopy = a.id
                    WHERE s.author = :userid
        ";

        $contextlist->add_from_sql($sql, $params);

         // Get contexts for annotations.
         $sql = "SELECT c.id
                    FROM {context} c
                    JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                    JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                    JOIN {annopy} a ON a.id = cm.instance
                    JOIN {annopy_annotations} aa ON aa.annopy = a.id
                    WHERE aa.userid = :userid
        ";

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users within a specific context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!is_a($context, \context_module::class)) {
            return;
        }

        $params = [
            'instanceid' => $context->id,
            'modulename' => 'annopy',
        ];

        // Find users with submissions.
        $sql = "SELECT s.author
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                  JOIN {annopy} a ON a.id = cm.instance
                  JOIN {annopy_submissions} s ON s.annopy = a.id
                 WHERE cm.id = :instanceid
        ";

        $userlist->add_from_sql('author', $sql, $params);

        // Find users with annotations.
        $sql = "SELECT aa.userid
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                  JOIN {annopy} a ON a.id = cm.instance
                  JOIN {annopy_annotations} aa ON aa.annopy = a.id
                 WHERE cm.id = :instanceid
        ";

        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist)) {
            return;
        }

        $user = $contextlist->get_user();
        $userid = $user->id;

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);
        $params = $contextparams;

        // Get all instances.
        $sql = "SELECT
                c.id AS contextid,
                a.*,
                cm.id AS cmid
            FROM {context} c
            JOIN {course_modules} cm ON cm.id = c.instanceid
            JOIN {annopy} a ON a.id = cm.instance
            WHERE (
                c.id {$contextsql}
            )
        ";

        $annopys = $DB->get_recordset_sql($sql, $params);

        if ($annopys->valid()) {
            foreach ($annopys as $annopy) {

                if ($annopy) {

                    $context = \context::instance_by_id($annopy->contextid);

                    // Store the main data.
                    $contextdata = helper::get_context_data($context, $user);

                    // Write it.
                    writer::with_context($context)->export_data([], $contextdata);

                    // Write generic module intro files.
                    helper::export_context_files($context, $user);

                    self::export_submissions_data($userid, $annopy->id, $annopy->contextid);

                    self::export_annotations_data($userid, $annopy->id, $annopy->contextid);

                }

            }
        }

        $annopys->close();
    }

    /**
     * Store all information about all submissions made by this user.
     *
     * @param   int         $userid The userid of the user whose data is to be exported.
     * @param   int         $annopyid The id of the module.
     * @param   int         $annopycontextid The context id of the module.
     */
    protected static function export_submissions_data(int $userid, $annopyid, $annopycontextid) {
        global $DB;

        // Find all submissions for this module written by the user.
        $sql = "SELECT
                    s.id,
                    s.annopy,
                    s.author,
                    s.title,
                    s.content,
                    s.currentversion,
                    s.format,
                    s.timecreated,
                    s.timemodified
                   FROM {annopy_submissions} s
                   WHERE (
                    s.annopy = :annopyid AND
                    s.author = :userid
                    )
        ";

        $params['userid'] = $userid;
        $params['annopyid'] = $annopyid;

        // Get the submissions.
        $submissions = $DB->get_recordset_sql($sql, $params);

        if ($submissions->valid()) {
            foreach ($submissions as $submission) {
                if ($submission) {
                    $context = \context::instance_by_id($annopycontextid);

                    self::export_submission_data($userid, $context, ['annopy-submission-' . $submission->id], $submission);
                }
            }
        }

        $submissions->close();
    }

    /**
     * Export all data in the submission.
     *
     * @param   int         $userid The userid of the user whose data is to be exported.
     * @param   \context    $context The instance of the annopy context.
     * @param   array       $subcontext The location within the current context that this data belongs.
     * @param   \stdClass   $submission The submission.
     */
    protected static function export_submission_data(int $userid, \context $context, $subcontext, $submission) {

        if ($submission->timecreated != 0) {
            $timecreated = transform::datetime($submission->timecreated);
        } else {
            $timecreated = null;
        }

        if ($submission->timemodified != 0) {
            $timemodified = transform::datetime($submission->timemodified);
        } else {
            $timemodified = null;
        }

        // Store related metadata.
        $submissiondata = (object) [
            'annopy' => $submission->annopy,
            'author' => $submission->author,
            'title' => $submission->title,
            'currentversion' => $submission->currentversion,
            'timecreated' => $timecreated,
            'timemodified' => $timemodified,
        ];

        $submissiondata->content = writer::with_context($context)->rewrite_pluginfile_urls($subcontext, 'mod_annopy',
            'submission', $submission->id, $submission->content);

        $submissiondata->content = format_text($submissiondata->content, $submission->format, (object) [
            'para' => false,
            'context' => $context,
        ]);

        // Store the submission data.
        writer::with_context($context)
            ->export_data($subcontext, $submissiondata)
            ->export_area_files($subcontext, 'mod_annopy', 'submission', $submission->id);
    }

    /**
     * Store all information about all annotations made by this user.
     *
     * @param   int         $userid The userid of the user whose data is to be exported.
     * @param   int         $instanceid The id of the module instance.
     * @param   int         $contextid The context id of the module instance.
     */
    protected static function export_annotations_data(int $userid, $instanceid, $contextid) {
        global $DB;

        // Find all annotations for this module instance made by the user.
        $sql = "SELECT
                    a.id,
                    a.annopy,
                    a.submission,
                    a.userid,
                    a.timecreated,
                    a.timemodified,
                    a.type,
                    a.text
                   FROM {annopy_annotations} a
                   WHERE (
                    a.annopy = :instanceid AND
                    a.userid = :userid
                    )
        ";

        $params['userid'] = $userid;
        $params['instanceid'] = $instanceid;

        // Get the annotations.
        $annotations = $DB->get_recordset_sql($sql, $params);

        if ($annotations->valid()) {
            foreach ($annotations as $annotation) {
                if ($annotation) {
                    $context = \context::instance_by_id($contextid);

                    self::export_annotation_data($userid, $context, ['annopy-annotation-' . $annotation->id], $annotation);
                }
            }
        }

        $annotations->close();
    }

    /**
     * Export all data of the annotation.
     *
     * @param   int         $userid The userid of the user whose data is to be exported.
     * @param   \context    $context The instance of the context.
     * @param   array       $subcontext The location within the current context that this data belongs.
     * @param   \stdClass   $annotation The annotation.
     */
    protected static function export_annotation_data(int $userid, \context $context, $subcontext, $annotation) {

        if ($annotation->timemodified != 0) {
            $timemodified = transform::datetime($annotation->timemodified);
        } else {
            $timemodified = null;
        }

        // Store related metadata.
        $annotationdata = (object) [
            'annopy' => $annotation->annopy,
            'submission' => $annotation->submission,
            'userid' => $annotation->userid,
            'timecreated' => transform::datetime($annotation->timecreated),
            'timemodified' => $timemodified,
            'type' => $annotation->type,
            'text' => format_text($annotation->text, 2, ['para' => false]),
        ];

        // Store the annotation data.
        writer::with_context($context)->export_data($subcontext, $annotationdata);
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        // Check that this is a context_module.
        if (!$context instanceof \context_module) {
            return;
        }

        // Get the course module.
        if (!$cm = get_coursemodule_from_id('annopy', $context->instanceid)) {
            return;
        }

        // Delete all files from the submission.
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'mod_annopy', 'submission');

        // Delete all submissions.
        if ($DB->record_exists('annopy_submissions', ['annopy' => $cm->instance])) {
            $DB->delete_records('annopy_submissions', ['annopy' => $cm->instance]);
        }

        // Delete all annotations.
        if ($DB->record_exists('annopy_annotations', ['annopy' => $cm->instance])) {
            $DB->delete_records('annopy_annotations', ['annopy' => $cm->instance]);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {

        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            // Get the course module.
            $cm = $DB->get_record('course_modules', ['id' => $context->instanceid]);

            // Delete ratings.
            $submissionssql = "SELECT
                s.id
                FROM {annopy_submissions} s
                WHERE (
                    s.annopy = :instanceid AND
                    s.author = :author
                )
            ";

            $submissionsparams = [
                'instanceid' => $cm->instance,
                'author' => $userid,
            ];

            // Delete all files from the submissions.
            $fs = get_file_storage();
            $fs->delete_area_files_select($context->id, 'mod_annopy', 'submission', "IN ($submissionssql)", $submissionsparams);

            // Delete submissions for user.
            if ($DB->record_exists('annopy_submissions', ['annopy' => $cm->instance, 'author' => $userid])) {

                $DB->delete_records('annopy_submissions', [
                    'annopy' => $cm->instance,
                    'author' => $userid,
                ]);

            }

            // Delete annotations for user.
            if ($DB->record_exists('annopy_annotations', ['annopy' => $cm->instance, 'userid' => $userid])) {

                $DB->delete_records('annopy_annotations', [
                    'annopy' => $cm->instance,
                    'userid' => $userid,
                ]);

            }
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        $cm = $DB->get_record('course_modules', ['id' => $context->instanceid]);

        list($userinsql, $userinparams) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        $params = array_merge(['instanceid' => $cm->instance], $userinparams);

        $submissionselect = "SELECT
                s.id
                FROM {annopy_submissions} s
                WHERE (
                    s.annopy = :instanceid AND
                    author {$userinsql}
                )
        ";

        // Delete all files from the submissions.
        $fs = get_file_storage();
        $fs->delete_area_files_select($context->id, 'mod_annopy', 'submission', "IN ($submissionselect)", $params);

        // Delete annotations for users submissions that should be deleted.
        if ($DB->record_exists_select('annopy_annotations', "submission IN ({$submissionsselect})", $params)) {
            $DB->delete_records_select('annopy_annotations', "submission IN ({$submissionsselect})", $params);
        }

        // Delete submissions for users.
        if ($DB->record_exists_select('annopy_submissions', "annopy = :instanceid AND author {$userinsql}", $params)) {
            $DB->delete_records_select('annopy_submissions', "annopy = :instanceid AND author {$userinsql}", $params);
        }

        // Delete annotations for users.
        if ($DB->record_exists_select('annopy_annotations', "annopy = :instanceid AND userid {$userinsql}", $params)) {
            $DB->delete_records_select('annopy_annotations', "annopy = :instanceid AND userid {$userinsql}", $params);
        }

    }
}
