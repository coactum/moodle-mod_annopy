# License #

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.

@copyright 2023 coactum GmbH

# AnnoPy #

## Description ##

In the AnnoPy activity, teachers can upload a wide variety of multimedia texts which participants can then annotate.

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

Further information on the concept behind AnnoPy and its possible use in teaching and learning can be found in German on the current project website (https://annopy.de/).

## Quick installation instructions ##

### Install from git ###
- Navigate to Moodle root folder.
- **git clone https://github.com/coactum/moodle-mod_annopy.git mod/annopy**

### Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/mod/annopy

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

### Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Dependencies ##
No dependencies.