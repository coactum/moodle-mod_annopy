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
 * JavaScript for the main page of the plugin.
 *
 * @module   mod_annopy/view
 * @copyright  2023 coactum GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import {get_string as getString} from 'core/str';

export const init = (cmid) => {
    // getString('logintoannopy', 'mod_annopy')
    //     .then(buttonString => {
    //         $('.path-mod-annopy #id_submitbutton').attr('value', buttonString);
    //     })
    //     .catch();

    return cmid;
};