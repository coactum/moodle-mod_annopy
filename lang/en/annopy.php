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
$string['modulename_help'] = 'In the AnnoPy activity, teachers can upload a wide variety of multimedia texts which participants can then annotate.

As a collaborative tool, AnnoPy can be used in many different ways in school or university learning contexts, for example to improve participants literary skills or to evaluate their understanding of a text.

For example, AnnoPy can be used in language didactics to promote comparative reading, to practise identifying linguistic patterns in texts or to open up a new perspective on explanatory texts. AnnoPy can also be used to analyze texts on a content level, for example with regard to semantic, grammatical, lexical or text-literary issues. In subjects such as mathematics or computer science, on the other hand, teachers can use AnnoPy to have their own lecture notes worked through and then see at a glance where there are still difficulties in understanding.

Teachers can first upload any multimedia text for annotation; depending on the didactic context, this can also contain images, formulas or programming code, for example.
All participants can then annotate this text by marking the desired text passages and then selecting a type for each annotation and leaving a short comment if necessary.
Just like reusable templates, the available annotation types can be flexibly adapted by the teacher depending on the context.
Finally, teachers can view and analyze all of the participants annotations in detail in a clearly visualized evaluation.

Core features of the plugin:

* Upload of various types of multimedia texts by teachers
* Separate annotation of these texts including comments by each individual participant
* Cumulative display of all annotations on the overview page, sorted by participant
* Annotation types and templates can be individually customized by teachers
* A clear and detailed evaluation of all annotations

Further information on the concept behind AnnoPy and its possible use in teaching and learning can be found in German on the current project website (https://annopy.de/).';
$string['modulename_link'] = 'mod/annopy/view';
$string['pluginadministration'] = 'Administration of AnnoPy';
$string['noannotationtypetemplates'] = 'No annotation type templates are available yet. After creating the AnnoPy, annotation types must therefore be created manually.';

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
$string['nosubmission'] = 'No submission';
$string['allannotations'] = 'All annotations';

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
$string['annotationtypedeleted'] = 'Annotation type does not exists.';

// Strings for annotations_summary and annotationtypes_form.
$string['annotationssummary'] = 'Annotations summary';
$string['participant'] = 'Participant';
$string['backtooverview'] = 'Back to overview';
$string['addannotationtype'] = 'Add annotation type';
$string['annotationtypeadded'] = 'Annotation type added';
$string['editannotationtype'] = 'Edit annotation type';
$string['annotationtypeedited'] = 'Annotation type edited';
$string['editannotationtypetemplate'] = 'Edit template';
$string['annotationtypecantbeedited'] = 'Annotation type could not be changed';
$string['deleteannotationtype'] = 'Delete annotation type';
$string['annotationtypedeleted'] = 'Annotation type deleted';
$string['deleteannotationtypetemplate'] = 'Delete template';
$string['deleteannotationtypetemplateconfirm'] = 'Should this annotation type template really be deleted? This deletes the template for the entire system so that it can no longer be used as a concrete annotation type in new AnnoPys. This action cannot be undone!';
$string['annotationtypeinvalid'] = 'Annotation type invalid';
$string['annopyannotationtypes'] = 'AnnoPy annotation types';
$string['annotationtypetemplates'] = 'Annotation type templates';
$string['annotationtypes'] = 'Annotation types';
$string['template'] = 'Template';
$string['addtoannopy'] = 'Add to AnnoPy';
$string['switchtotemplatetypes'] = 'Switch to the annotation type templates';
$string['switchtoannopytypes'] = 'Switch to the annotation types for the AnnoPy';
$string['notemplatetypes'] = 'No annotation type templates available';
$string['movefor'] = 'Display more in front';
$string['moveback'] = 'Display further back';
$string['prioritychanged'] = 'Order changed';
$string['prioritynotchanged'] = 'Order could not be changed';
$string['annotationcolor'] = 'Color of the annotation type';
$string['standardtype'] = 'Standard annotation type';
$string['manualtype'] = 'Manual annotation type';
$string['standard'] = 'Standard';
$string['custom'] = 'Custom';
$string['type'] = 'Type';
$string['color'] = 'Color';
$string['errnohexcolor'] = 'No hexadecimal value for color.';
$string['warningeditdefaultannotationtypetemplate'] = 'WARNING: This will change the annotation type template system-wide. When creating new AnnoPys, the changed template will then be available for selecting the concrete AnnoPy annotation types.';
$string['changetemplate'] = 'Changing the name or color of the annotation type only affects the template and therefore only takes effect when new AnnoPys are created. The annotation types in existing AnnoPys are not affected by these changes.';
$string['explanationtypename'] = 'Name';
$string['explanationtypename_help'] = 'The name of the annotation type. Will not be translated.';
$string['explanationhexcolor'] = 'Color';
$string['explanationhexcolor_help'] = 'The color of the annotation type as hexadecimal value. This consists of exactly 6 characters (A-F as well as 0-9) and represents a color. If the color is selected here the value is entered automatically, alternatively the hex value can also be entered manually. You can find out the hexadecimal value of any color, for example, at <a href="https://www.w3schools.com/colors/colors_picker.asp" target="_blank">https://www.w3schools.com/colors/colors_picker.asp</a>.';
$string['explanationstandardtype'] = 'Here you can select whether the annotation type should be a default type. In this case teachers can select it as annotation type that can be used in their AnnoPys. Otherwise, only you can add this annotation type to your AnnoPys.';
$string['viewannotationsofuser'] = 'View annotations of the user';

// Strings for course reset.
$string['deletealluserdata'] = 'Delete the submission with associated files, all annotations and all annotation types.';
$string['deleteannotations'] = 'Delete all annotations';
$string['annotationsdeleted'] = 'Annotations were deleted';
$string['deletesubmissionandfiles'] = 'Delete the submission, all associated files and all associated annotations';
$string['submissionandfilesdeleted'] = 'The submission, all associated files and all associated annotations been deleted';
$string['deleteannotationtypes'] = 'Delete all annotation types';
$string['annotationtypesdeleted'] = 'All annotation types were deleted';

// Strings for the recent activity.
$string['newannopyannotations'] = 'New AnnoPy annotations';

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

// Strings for the messages.

// Strings for the calendar.

// Strings for the admin settings.

// Strings for events.
$string['eventsubmissioncreated'] = 'Submission created';
$string['eventsubmissionupdated'] = 'Submission updated';
$string['eventsubmissiondeleted'] = 'Submission deleted';
$string['eventannotationcreated'] = 'Annotation created';
$string['eventannotationupdated'] = 'Annotation updated';
$string['eventannotationdeleted'] = 'Annotation deleted';

// Strings for error messages.
$string['errfilloutfield'] = 'Please fill out this field';
$string['incorrectcourseid'] = 'Course ID is incorrect';
$string['incorrectmodule'] = 'Course Module ID is incorrect';
$string['notallowedtodothis'] = 'No permissions to do this.';
$string['alreadyannotated'] = 'The text can no longer be edited because participants have already annotated it.';

// Strings for the privacy api.
$string['privacy:metadata:annopy_submissions'] = 'Contains the submissions of all AnnoPys.';
$string['privacy:metadata:annopy_annotations'] = 'Contains the annotations made in all AnnoPys.';
$string['privacy:metadata:annopy_atype_templates'] = 'Contains the annotation type templates created by teachers.';
$string['privacy:metadata:annopy_submissions:annopy'] = 'ID of the AnnoPy the submission belongs to.';
$string['privacy:metadata:annopy_submissions:author'] = 'ID of the author of the submission.';
$string['privacy:metadata:annopy_submissions:title'] = 'Title of the submission.';
$string['privacy:metadata:annopy_submissions:content'] = 'Content of the submission.';
$string['privacy:metadata:annopy_submissions:currentversion'] = 'Current version of the submission.';
$string['privacy:metadata:annopy_submissions:format'] = 'Submission format.';
$string['privacy:metadata:annopy_submissions:timecreated'] = 'Date on which the submission was made.';
$string['privacy:metadata:annopy_submissions:timemodified'] = 'Time the submission was last modified.';
$string['privacy:metadata:annopy_annotations:annopy'] = 'ID of the AnnoPy the annotated submission belongs to.';
$string['privacy:metadata:annopy_annotations:submission'] = 'ID of the submission the annotation belongs to.';
$string['privacy:metadata:annopy_annotations:userid'] = 'ID of the user that made the annotation.';
$string['privacy:metadata:annopy_annotations:timecreated'] = 'Date on which the annotation was created.';
$string['privacy:metadata:annopy_annotations:timemodified'] = 'Time the annotation was last modified.';
$string['privacy:metadata:annopy_annotations:type'] = 'ID of the type of the annotation.';
$string['privacy:metadata:annopy_annotations:text'] = 'Content of the annotation.';
$string['privacy:metadata:annopy_atype_templates:timecreated'] = 'Date on which the annotation type template was created.';
$string['privacy:metadata:annopy_atype_templates:timemodified'] = 'Time the annotation type template was last modified.';
$string['privacy:metadata:annopy_atype_templates:name'] = 'Name of the annotation type template.';
$string['privacy:metadata:annopy_atype_templates:color'] = 'Color of the annotation type template as hexadecimal value.';
$string['privacy:metadata:annopy_atype_templates:userid'] = 'ID of the user that made the annotation type template.';
$string['privacy:metadata:core_files'] = 'Files associated with AnnoPy submissions are saved.';
