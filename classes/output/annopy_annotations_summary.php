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
 * Class containing data for the annotations summary.
 *
 * @package     mod_annopy
 * @copyright   2023 coactum GmbH
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_annopy\output;

use renderable;
use renderer_base;
use templatable;
use stdClass;

/**
 * Class containing data for the annotations summary.
 *
 * @package     mod_annopy
 * @copyright   2023 coactum GmbH
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class annopy_annotations_summary implements renderable, templatable {

    /** @var int */
    protected $cmid;
    /** @var object */
    protected $context;
    /** @var object */
    protected $participants;
    /** @var object */
    protected $annopyannotationtypes;
    /** @var object */
    protected $annotationtypetemplates;
    /** @var string */
    protected $sesskey;
    /** @var int */
    protected $annotationstotalcount;

    /**
     * Construct this renderable.
     * @param int $cmid The course module id
     * @param array $context The context
     * @param array $participants The participants of the annopy instance
     * @param array $annopyannotationtypes The annotationtypes used in the annopy instance
     * @param array $annotationtypetemplates The annotationtype templates available for the current user
     * @param string $sesskey The session key
     * @param int $annotationstotalcount The total count of annotations
     */
    public function __construct($cmid, $context, $participants, $annopyannotationtypes, $annotationtypetemplates,
        $sesskey, $annotationstotalcount) {

        $this->cmid = $cmid;
        $this->context = $context;
        $this->participants = $participants;
        $this->annopyannotationtypes = $annopyannotationtypes;
        $this->annotationtypetemplates = $annotationtypetemplates;
        $this->sesskey = $sesskey;
        $this->annotationstotalcount = $annotationstotalcount;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output Renderer base.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $USER;

        $data = new stdClass();
        $data->cmid = $this->cmid;
        $data->myuserid = $USER->id;
        $data->participants = $this->participants;
        $data->annopyannotationtypes = $this->annopyannotationtypes;
        $data->annotationtypetemplates = $this->annotationtypetemplates;
        $data->sesskey = $this->sesskey;
        $data->annotationstotalcount = $this->annotationstotalcount;

        if (has_capability('mod/annopy:addannotationtypetemplate', $this->context) ||
            has_capability('mod/annopy:editannotationtypetemplate', $this->context)) {

            $data->canmanageannotationtypetemplates = true;
        } else {
            $data->canmanageannotationtypetemplates = false;
        }

        $data->canaddannotationtype = has_capability('mod/annopy:addannotationtype', $this->context);
        $data->canaddannotationtypetemplate = has_capability('mod/annopy:addannotationtypetemplate', $this->context);
        $data->canviewparticipants = has_capability('mod/annopy:viewparticipants', $this->context);

        return $data;
    }
}
