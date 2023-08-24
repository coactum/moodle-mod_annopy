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
 * English strings for the plugin are defined here.
 *
 * @package     mod_annopy
 * @category    string
 * @copyright   2023 coactum GmbH
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Common strings.
$string['pluginname'] = 'AnnoPy';

// Strings for mod_form.php.
$string['modulename'] = 'AnnoPy';
$string['modulename_help'] = 'The AnnoPy activity allows ... ';
$string['modulename_link'] = 'mod/annopy/view';
$string['pluginadministration'] = 'Administration of AnnoPy';

// Strings for index.php.
$string['modulenameplural'] = 'AnnoPys';
$string['nonewmodules'] = 'No new modules';

// Strings for submit_form.php and submit.php.
$string['editsubmissionnotpossible'] = 'Editing submission failed';
$string['addsubmission'] = 'Add submission';
$string['editsubmission'] = 'Edit submission';
$string['title'] = 'Title';
$string['submissioncontent'] = 'Content of the submission';
$string['submissioncreated'] = 'Submission created';
$string['submissionnotcreated'] = 'Submission could not be created';
$string['submissionmodified'] = 'Submission updated';
$string['submissionnotmodified'] = 'Submission could not be updated';
$string['submissionfaileddoubled'] = 'Submission could not be created because it already exists';

// Strings for the view page.
$string['viewallannopys'] = 'View all AnnoPy instances in the course';
$string['overview'] = 'Overview';
$string['submission'] = 'Submission';
$string['author'] = 'Author';
$string['timecreated'] = 'Time created';
$string['lastedited'] = 'Last edited';
$string['currentversion'] = 'Current version';
$string['details'] = 'Details';
$string['numwordsraw'] = '{$a->wordscount} text words using {$a->charscount} characters, including {$a->spacescount} spaces.';
$string['created'] = '{$a->years} years, {$a->month} months, {$a->days} days and {$a->hours} hours ago';
$string['annotations'] = 'Annotations';
$string['toggleallannotations'] = 'Toggle all annotations';
$string['annotationsarefetched'] = 'Annotations being loaded';
$string['reloadannotations'] = 'Reload annotations';
$string['nosubmission'] = 'No submission';

// Strings for annotations.
$string['annotations'] = 'Annotations';
$string['toggleallannotations'] = 'Toggle all annotations';
$string['toggleannotation'] = 'Toggle annotation';
$string['hoverannotation'] = 'Hover annotation';
$string['annotationcreated'] = 'Created at {$a}';
$string['annotationmodified'] = 'Modified at {$a}';
$string['editannotation'] = 'Edit';
$string['deleteannotation'] = 'Delete';
$string['annotationsarefetched'] = 'Annotations being loaded';
$string['reloadannotations'] = 'Reload annotations';
$string['annotationadded'] = 'Annotation added';
$string['annotationedited'] = 'Annotation edited';
$string['annotationdeleted'] = 'Annotation deleted';
$string['annotationinvalid'] = 'Annotation invalid';
$string['annotatedtextnotfound'] = 'Annotated text not found';
$string['annotatedtextinvalid'] = 'The originally annotated text has become invalid. The marking for this annotation must therefore be redone.';
$string['deletedannotationtype'] = 'Deleted type';
$string['annotationtypedeleted'] = 'annotation type does not exists.';

// Strings for lib.php.
$string['deletealluserdata'] = 'Delete all user data';

// Strings for the recent activity.

// Strings for the capabilities.
$string['annopy:addinstance'] = 'Add new AnnoPy';
$string['annopy:potentialparticipant'] = 'Join AnnoPy as a participant';
$string['annopy:viewparticipants'] = 'View participants';
$string['annopy:manageparticipants'] = 'Manage participants';
$string['annopy:addsubmission'] = 'Add submission';
$string['annopy:editsubmission'] = 'Edit submission';
$string['annopy:deletesubmission'] = 'Delete submission';
$string['annopy:addannotation'] = 'Add annotation';
$string['annopy:editannotation'] = 'Edit annotation';
$string['annopy:deleteannotation'] = 'Delete annotation';
$string['annopy:viewannotations'] = 'View annotations';
$string['annopy:viewannotationsevaluation'] = 'View annotations evaluation';
$string['annopy:viewmyannotationsummary'] = 'View summary of my annotations';
$string['annopy:addannotationtype'] = 'Add annotation type';
$string['annopy:editannotationtype'] = 'Edit annotation type';
$string['annopy:deleteannotationtype'] = 'Delete annotation type';
$string['annopy:addannotationtypetemplate'] = 'Add annotation type template';
$string['annopy:editannotationtypetemplate'] = 'Edit annotation type template';
$string['annopy:deleteannotationtypetemplate'] = 'Delete annotation type template';
$string['annopy:managedefaultannotationtypetemplates'] = 'Manage default annotation type templates';

// Strings for the tasks.
$string['task'] = 'Task';

// Strings for the messages.

// Strings for the calendar.

// Strings for the admin settings.

// Strings for events.
$string['eventsubmissioncreated'] = 'Submission created';
$string['eventsubmissionupdated'] = 'Submission updated';

// Strings for error messages.
$string['errfilloutfield'] = 'Please fill out this field';
$string['incorrectcourseid'] = 'Course ID is incorrect';
$string['incorrectmodule'] = 'Course Module ID is incorrect';
$string['notallowedtodothis'] = 'No permissions to do this.';

// Strings for the privacy api.
/*
$string['privacy:metadata:annopy_participants'] = 'Contains the personal data of all AnnoPy participants.';
$string['privacy:metadata:annopy_submissions'] = 'Contains all data related to AnnoPy submissions.';
$string['privacy:metadata:annopy_participants:annopy'] = 'Id of the AnnoPy activity the participant belongs to';
$string['privacy:metadata:annopy_submissions:annopy'] = 'Id of the AnnoPy activity the submission belongs to';
$string['privacy:metadata:core_message'] = 'The AnnoPy plugin sends messages to users and saves their content in the database.';
*/
