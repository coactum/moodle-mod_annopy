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
 * Stats utilities for AnnoPy submissions.
 *
 * @package   mod_annopy
 * @copyright 2023 coactum GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_annopy\local;

use stdClass;
use core_text;

/**
 * Utility class for AnnoPy submission stats.
 *
 * @package   mod_annopy
 * @copyright 2023 coactum GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submissionstats {

    /**
     * Get the statistics for this submission.
     *
     * @param string $submissiontext The text for this submission.
     * @param string $submissiontimecreated The time then the submission was created.
     * @return array submissionstats Array with the statistics of the submission.
     */
    public static function get_submission_stats($submissiontext, $submissiontimecreated) {

        $cleantext = preg_replace('#<[^>]+>#', ' ', $submissiontext, -1, $replacementspacescount);

        $submissionstats = array();
        $submissionstats['words'] = self::get_stats_words($cleantext);
        $submissionstats['chars'] = self::get_stats_chars($cleantext) - $replacementspacescount;
        $submissionstats['sentences'] = self::get_stats_sentences($cleantext);
        $submissionstats['paragraphs'] = self::get_stats_paragraphs($cleantext);
        $submissionstats['uniquewords'] = self::get_stats_uniquewords($cleantext);
        $submissionstats['spaces'] = self::get_stats_spaces($cleantext) - $replacementspacescount;
        $submissionstats['charswithoutspaces'] = $submissionstats['chars'] - $submissionstats['spaces'];

        $timenow = new \DateTime(date('Y-m-d G:i:s', time()));
        $timesubmissioncreated = new \DateTime(date('Y-m-d G:i:s', $submissiontimecreated));

        if ($timenow >= $timesubmissioncreated) {
            $submissionstats['datediff'] = date_diff($timenow, $timesubmissioncreated);
        } else {
            $submissionstats['datediff'] = false;
        }

        return $submissionstats;
    }

    /**
     * Get the character count statistics for this annopy submission.
     *
     * @param string $submissiontext The text for this submission.
     * @ return int The number of characters.
     */
    public static function get_stats_chars($submissiontext) {
        return core_text::strlen($submissiontext);
    }

    /**
     * Get the word count statistics for this annopy submission.
     *
     * @param string $submissiontext The text for this submission.
     * @ return int The number of words.
     */
    public static function get_stats_words($submissiontext) {
        return count_words($submissiontext);
    }

    /**
     * Get the sentence count statistics for this annopy submission.
     *
     * @param string $submissiontext The text for this submission.
     * @ return int The number of sentences.
     */
    public static function get_stats_sentences($submissiontext) {
        $sentences = preg_split('/[!?.]+(?![0-9])/', $submissiontext);
        $sentences = array_filter($sentences);
        return count($sentences);
    }

    /**
     * Get the paragraph count statistics for this annopy submission.
     *
     * @param string $submissiontext The text for this submission.
     * @ return int The number of paragraphs.
     */
    public static function get_stats_paragraphs($submissiontext) {
        $paragraphs = explode("\n", $submissiontext);
        $paragraphs = array_filter($paragraphs);
        return count($paragraphs);
    }

    /**
     * Get the unique word count statistics for this annopy submission.
     *
     * @param string $submissiontext The text for this submission.
     * @return int The number of unique words.
     */
    public static function get_stats_uniquewords($submissiontext) {
        $items = core_text::strtolower($submissiontext);
        $items = str_word_count($items, 1);
        $items = array_unique($items);
        return count($items);
    }

    /**
     * Get the raw spaces count statistics for this annopy submission.
     *
     * @param string $submissiontext The text for this submission.
     * @return int The number of spaces.
     */
    public static function get_stats_spaces($submissiontext) {
        return substr_count($submissiontext, ' ');
    }
}
