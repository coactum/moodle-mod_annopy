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
 * Helper utilities for the module.
 *
 * @package   mod_annopy
 * @copyright 2023 coactum GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_annopy\local;

use mod_annopy_annotation_form;
use moodle_url;

/**
 * Utility class for the module.
 *
 * @package   mod_annopy
 * @copyright 2023 coactum GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {
    /**
     * Return the editor and attachment options for a submission.
     * @param stdClass $course The course object.
     * @param stdClass $context The context object.

     * @return array $editoroptions Array containing the editor options.
     * @return array $attachmentoptions Array containing the attachment options.
     */
    public static function annopy_get_editor_and_attachment_options($course, $context) {
        // For the editor.
        $editoroptions = array(
            'trusttext' => true,
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $course->maxbytes,
            'context' => $context,
            'subdirs' => false,
        );

        // If maxfiles would be set to an int and more files are given the editor saves them all but
        // saves the overcouting incorrect so that white box is displayed.

        // For a file attachments field (not really needed here).
        $attachmentoptions = array(
            'subdirs' => false,
            'maxfiles' => 1,
            'maxbytes' => $course->maxbytes
        );

        return array(
            $editoroptions,
            $attachmentoptions
        );
    }

    /**
     * Returns annotation types array for select form.
     *
     * @param stdClass $annotationtypes The annotation types.
     * @return array action
     */
    public static function get_annotationtypes_for_form($annotationtypes) {
        $types = array();
        $strmanager = get_string_manager();
        foreach ($annotationtypes as $key => $type) {
            if ($strmanager->string_exists($type->name, 'mod_annopy')) {
                $types[$key] = get_string($type->name, 'mod_annopy');
            } else {
                $types[$key] = $type->name;
            }
        }

        return $types;
    }

    /**
     * Returns all annotation type templates.
     *
     * @return array action
     */
    public static function get_all_annotationtype_templates() {
        global $USER, $DB;

        $select = "defaulttype = 1";
        $select .= " OR userid = " . $USER->id;

        $annotationtypetemplates = (array) $DB->get_records_select('annopy_atype_templates', $select);

        return $annotationtypetemplates;
    }

    /**
     * Prepare the annotations for the submission.
     *
     * @param object $cm The course module.
     * @param object $course The course.
     * @param object $context The context.
     * @param object $submission The submission to be processed.
     * @param object $strmanager The moodle strmanager object needed to check annotation types in the annotation form.
     * @param object $annotationtypes The annotation types for the module.
     * @param object $annotationmode If annotationmode is activated.
     * @return object The submission with its annotations.
     */
    public static function prepare_annotations($cm, $course, $context, $submission, $strmanager, $annotationtypes,
        $annotationmode) {

        global $DB, $USER, $CFG, $OUTPUT;

        // Get annotations for submission.
        $submission->annotations = array_values($DB->get_records('annopy_annotations',
            array('annopy' => $cm->instance, 'submission' => $submission->id)));

        foreach ($submission->annotations as $key => $annotation) {

            // If annotation type does not exist.
            if (!$DB->record_exists('annopy_annotationtypes', array('id' => $annotation->type))) {
                $submission->annotations[$key]->color = 'FFFF00';
                $submission->annotations[$key]->type = get_string('deletedannotationtype', 'mod_annopy');
            } else {
                $submission->annotations[$key]->color = $annotationtypes[$annotation->type]->color;

                if ($strmanager->string_exists($annotationtypes[$annotation->type]->name, 'mod_annopy')) {
                    $submission->annotations[$key]->type = get_string($annotationtypes[$annotation->type]->name, 'mod_annopy');
                } else {
                    $submission->annotations[$key]->type = $annotationtypes[$annotation->type]->name;
                }
            }

            if (has_capability('mod/annopy:editannotation', $context) && $annotation->userid == $USER->id) {
                $submission->annotations[$key]->canbeedited = true;
            } else {
                $submission->annotations[$key]->canbeedited = false;
            }

            if ($annotationmode) {
                // Add annotater images to annotations.
                $annotater = $DB->get_record('user', array('id' => $annotation->userid));
                $annotaterimage = $OUTPUT->user_picture($annotater,
                    array('courseid' => $course->id, 'link' => true, 'includefullname' => true, 'size' => 20));
                $submission->annotations[$key]->userpicturestr = $annotaterimage;

            } else {
                $submission->annotationform = false;
            }
        }

        // Sort annotations and find its position.
        usort($submission->annotations, function ($a, $b) {
            if ($a->annotationstart === $b->annotationstart) {
                return $a->annotationend <=> $b->annotationend;
            }

            return $a->annotationstart <=> $b->annotationstart;
        });

        $pos = 1;
        foreach ($submission->annotations as $key => $annotation) {
            $submission->annotations[$key]->position = $pos;
            $pos += 1;
        }

        if ($annotationmode) {
            // Add annotation form.
            require_once($CFG->dirroot . '/mod/annopy/annotation_form.php');
            $mform = new mod_annopy_annotation_form(new moodle_url('/mod/annopy/annotations.php', array('id' => $cm->id)),
                array('types' => self::get_annotationtypes_for_form($annotationtypes)));
            // Set default data.
            $mform->set_data(array('id' => $cm->id, 'submission' => $submission->id));

            $submission->annotationform = $mform->render();
        }

        return $submission;
    }
}
