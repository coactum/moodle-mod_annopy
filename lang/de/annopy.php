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
 * German strings for the plugin are defined here.
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
$string['modulename_help'] = 'Die Aktivität AnnoPy erlaubt ... ';
$string['modulename_link'] = 'mod/annopy/view';
$string['pluginadministration'] = 'Administration der AnnoPy-Instanz';

// Strings for index.php.
$string['modulenameplural'] = 'AnnoPys';
$string['nonewmodules'] = 'Keine neuen Instanzen';

// Strings for submit_form.php and submit.php.
$string['editsubmissionnotpossible'] = 'Bearbeiten der Einreichung fehlgeschlagen';
$string['addsubmission'] = 'Einreichung anlegen';
$string['editsubmission'] = 'Einreichung bearbeiten';
$string['title'] = 'Titel';
$string['submissioncontent'] = 'Inhalt der Einreichung';
$string['submissioncreated'] = 'Einreichung angelegt';
$string['submissionnotcreated'] = 'Einreichung konnte nicht angelegt werden';
$string['submissionmodified'] = 'Einreichung aktualisiert';
$string['submissionnotmodified'] = 'Einreichung konnte nicht aktualisiert werden';
$string['submissionfaileddoubled'] = 'Einreichung konnte nicht angelegt werden da sie bereits existiert.';

// Strings for the view page.
$string['viewallannopys'] = 'Alle AnnoPy-Instanzen im Kurs ansehen';
$string['overview'] = 'Übersicht';
$string['submission'] = 'Einreichung';
$string['author'] = 'Autor';
$string['timecreated'] = 'Zeitpunkt der Erstellung';
$string['lastedited'] = 'Zuletzt bearbeitet';
$string['currentversion'] = 'Versionsnummer';
$string['details'] = 'Details';
$string['numwordsraw'] = '{$a->wordscount} Wörter mit {$a->charscount} Zeichen, einschließlich {$a->spacescount} Leerzeichen.';
$string['created'] = 'vor {$a->years} Jahren, {$a->month} Monaten, {$a->days} Tagen und {$a->hours} Stunden';
$string['nosubmission'] = 'Keine Einreichung';

// Strings for annotations.
$string['annotations'] = 'Annotationen';
$string['toggleallannotations'] = 'Alle Annotation aus- / einklappen';
$string['toggleannotation'] = 'Annotation aus- / einklappen';
$string['hoverannotation'] = 'Annotation hervorheben';
$string['annotationcreated'] = 'Erstellt am {$a}';
$string['annotationmodified'] = 'Bearbeitet am {$a}';
$string['editannotation'] = 'Bearbeiten';
$string['deleteannotation'] = 'Löschen';
$string['annotationsarefetched'] = 'Annotationen werden geladen';
$string['reloadannotations'] = 'Annotationen neu laden';
$string['annotationadded'] = 'Annotation hinzugefügt';
$string['annotationedited'] = 'Annotation geändert';
$string['annotationdeleted'] = 'Annotation gelöscht';
$string['annotationinvalid'] = 'Annotation ungültig';
$string['annotatedtextnotfound'] = 'Annotierter Text nicht gefunden';
$string['annotatedtextinvalid'] = 'Der ursprünglich annotierte Text ist ungültig geworden. Die Markierung für diese Annotation muss deshalb neu gesetzt werden.';
$string['deletedannotationtype'] = 'Gelöschter Stil';
$string['annotationtypedeleted'] = 'Annotationsstil nicht vorhanden.';

// Strings for lib.php.
$string['deletealluserdata'] = 'Alle Benutzerdaten löschen';

// Strings for the recent activity.

// Strings for the capabilities.
$string['annopy:addinstance'] = 'Neue AnnoPy-Instanz hinzufügen';
$string['annopy:potentialparticipant'] = 'AnnoPy als Teilnehmer:in beitreten';
$string['annopy:viewparticipants'] = 'Teilnehmer:innen ansehen';
$string['annopy:manageparticipants'] = 'Teilnehmer:innen verwalten';
$string['annopy:addsubmission'] = 'Einreichung hinzufügen';
$string['annopy:editsubmission'] = 'Einreichung bearbeiten';
$string['annopy:deletesubmission'] = 'Einreichung löschen';
$string['annopy:addannotation'] = 'Annotation hinzufügen';
$string['annopy:editannotation'] = 'Annotation bearbeiten';
$string['annopy:deleteannotation'] = 'Annotation löschen';
$string['annopy:viewannotations'] = 'Annotationen ansehen';
$string['annopy:viewannotationsevaluation'] = 'Annotationsauswertung ansehen';
$string['annopy:viewmyannotationsummary'] = 'Zusammenfasung meiner Annotationen ansehen';
$string['annopy:addannotationtype'] = 'Annotationsstil hinzufügen';
$string['annopy:editannotationtype'] = 'Annotationsstil bearbeiten';
$string['annopy:deleteannotationtype'] = 'Annotationsstil löschen';
$string['annopy:addannotationtypetemplate'] = 'Annotationsstilvorlage hinzufügen';
$string['annopy:editannotationtypetemplate'] = 'Annotationsstilvorlage bearbeiten';
$string['annopy:deleteannotationtypetemplate'] = 'Annotationsstilvorlage löschen';
$string['annopy:managedefaultannotationtypetemplates'] = 'Standard Annotationsstil-Vorlagen verwalten';

// Strings for the tasks.
$string['task'] = 'Aufgabe';

// Strings for the messages.

// Strings for the calendar.

// Strings for the admin settings.

// Strings for events.
$string['eventsubmissioncreated'] = 'Einreichung abgegeben';
$string['eventsubmissionupdated'] = 'Einreichung aktualisiert';

// Strings for error messages.
$string['errfilloutfield'] = 'Bitte Feld ausfüllen';
$string['incorrectcourseid'] = 'Inkorrekte Kurs-ID';
$string['incorrectmodule'] = 'Inkorrekte Kurs-Modul-ID';
$string['notallowedtodothis'] = 'Keine Berechtigung dies zu tun.';

// Strings for the privacy api.
/*
$string['privacy:metadata:annopy_participants'] = 'Enthält die persönlichen Daten aller AnnoPys Teilnehmenden.';
$string['privacy:metadata:annopy_submissions'] = 'Enthält alle Daten zu AnnoPy Einreichungen.';
$string['privacy:metadata:annopy_participants:annopy'] = 'ID des AnnoPys des Teilnehmers';
$string['privacy:metadata:annopy_submissions:annopy'] = 'ID des AnnoPys der Einreichung';
$string['privacy:metadata:core_message'] = 'Das AnnoPy Plugin sendet Nachrichten an Benutzer und speichert deren Inhalte in der Datenbank.';
*/
