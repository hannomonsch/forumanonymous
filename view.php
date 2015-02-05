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
 * @package mod-forumanonymous
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    require_once('../../config.php');
    require_once('lib.php');
    require_once($CFG->libdir.'/completionlib.php');

    $id          = optional_param('id', 0, PARAM_INT);       // Course Module ID
    $f           = optional_param('f', 0, PARAM_INT);        // Forum ID
    $mode        = optional_param('mode', 0, PARAM_INT);     // Display mode (for single forumanonymous)
    $showall     = optional_param('showall', '', PARAM_INT); // show all discussions on one page
    $changegroup = optional_param('group', -1, PARAM_INT);   // choose the current group
    $page        = optional_param('page', 0, PARAM_INT);     // which page to show
    $search      = optional_param('search', '', PARAM_CLEAN);// search string

    $params = array();
    if ($id) {
        $params['id'] = $id;
    } else {
        $params['f'] = $f;
    }
    if ($page) {
        $params['page'] = $page;
    }
    if ($search) {
        $params['search'] = $search;
    }
    $PAGE->set_url('/mod/forumanonymous/view.php', $params);

    if ($id) {
        if (! $cm = get_coursemodule_from_id('forumanonymous', $id)) {
            print_error('invalidcoursemodule');
        }
        if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
            print_error('coursemisconf');
        }
        if (! $forumanonymous = $DB->get_record("forumanonymous", array("id" => $cm->instance))) {
            print_error('invalidforumanonymousid', 'forumanonymous');
        }
        if ($forumanonymous->type == 'single') {
            $PAGE->set_pagetype('mod-forumanonymous-discuss');
        }
        // move require_course_login here to use forced language for course
        // fix for MDL-6926
        require_course_login($course, true, $cm);
        $strforumanonymouss = get_string("modulenameplural", "forumanonymous");
        $strforumanonymous = get_string("modulename", "forumanonymous");
    } else if ($f) {

        if (! $forumanonymous = $DB->get_record("forumanonymous", array("id" => $f))) {
            print_error('invalidforumanonymousid', 'forumanonymous');
        }
        if (! $course = $DB->get_record("course", array("id" => $forumanonymous->course))) {
            print_error('coursemisconf');
        }

        if (!$cm = get_coursemodule_from_instance("forumanonymous", $forumanonymous->id, $course->id)) {
            print_error('missingparameter');
        }
        // move require_course_login here to use forced language for course
        // fix for MDL-6926
        require_course_login($course, true, $cm);
        $strforumanonymouss = get_string("modulenameplural", "forumanonymous");
        $strforumanonymous = get_string("modulename", "forumanonymous");
    } else {
        print_error('missingparameter');
    }

    if (!$PAGE->button) {
        $PAGE->set_button(forumanonymous_search_form($course, $search));
    }

    $context = context_module::instance($cm->id);
    $PAGE->set_context($context);

    if (!empty($CFG->enablerssfeeds) && !empty($CFG->forumanonymous_enablerssfeeds) && $forumanonymous->rsstype && $forumanonymous->rssarticles) {
        require_once("$CFG->libdir/rsslib.php");

	$rsstitle = format_string($course->shortname, true, array('context' => context_course::instance($course->id))) . ': %fullname%';
        rss_add_http_header($context, 'mod_forumanonymous', $forumanonymous, $rsstitle);
    }

    // Mark viewed if required
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);

/// Print header.

    $PAGE->set_title(format_string($forumanonymous->name));
    $PAGE->add_body_class('forumanonymoustype-'.$forumanonymous->type);
    $PAGE->set_heading(format_string($course->fullname));

    echo $OUTPUT->header();

/// Some capability checks.
    if (empty($cm->visible) and !has_capability('moodle/course:viewhiddenactivities', $context)) {
        notice(get_string("activityiscurrentlyhidden"));
    }

    if (!has_capability('mod/forumanonymous:viewdiscussion', $context)) {
        notice(get_string('noviewdiscussionspermission', 'forumanonymous'));
    }

/// find out current groups mode
    groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/forumanonymous/view.php?id=' . $cm->id);
    $currentgroup = groups_get_activity_group($cm);
    $groupmode = groups_get_activity_groupmode($cm);

/// Okay, we can show the discussions. Log the forumanonymous view.
//     if ($cm->id) {
//         add_to_log($course->id, "forumanonymous", "view forumanonymous", "view.php?id=$cm->id", "$forumanonymous->id", $cm->id);
//     } else {
//         add_to_log($course->id, "forumanonymous", "view forumanonymous", "view.php?f=$forumanonymous->id", "$forumanonymous->id");
//     }

    $SESSION->fromdiscussion = qualified_me();   // Return here if we post or set subscription etc


/// Print settings and things across the top

    // If it's a simple single discussion forumanonymous, we need to print the display
    // mode control.
    if ($forumanonymous->type == 'single') {
        $discussion = NULL;
        $discussions = $DB->get_records('forumanonymous_discussions', array('forumanonymous'=>$forumanonymous->id), 'timemodified ASC');
        if (!empty($discussions)) {
            $discussion = array_pop($discussions);
        }
        if ($discussion) {
            if ($mode) {
                set_user_preference("forumanonymous_displaymode", $mode);
            }
            $displaymode = get_user_preferences("forumanonymous_displaymode", $CFG->forumanonymous_displaymode);
            forumanonymous_print_mode_form($forumanonymous->id, $displaymode, $forumanonymous->type);
        }
    }

    if (!empty($forumanonymous->blockafter) && !empty($forumanonymous->blockperiod)) {
        $a->blockafter = $forumanonymous->blockafter;
        $a->blockperiod = get_string('secondstotime'.$forumanonymous->blockperiod);
        echo $OUTPUT->notification(get_string('thisforumanonymousisthrottled','forumanonymous',$a));
    }

    if ($forumanonymous->type == 'qanda' && !has_capability('moodle/course:manageactivities', $context)) {
        echo $OUTPUT->notification(get_string('qandanotify','forumanonymous'));
    }

    switch ($forumanonymous->type) {
        case 'single':
            if (!empty($discussions) && count($discussions) > 1) {
                echo $OUTPUT->notification(get_string('warnformorepost', 'forumanonymous'));
            }
            if (! $post = forumanonymous_get_post_full($discussion->firstpost)) {
                print_error('cannotfindfirstpost', 'forumanonymous');
            }
            if ($mode) {
                set_user_preference("forumanonymous_displaymode", $mode);
            }

            $canreply    = forumanonymous_user_can_post($forumanonymous, $discussion, $USER, $cm, $course, $context);
            $canrate     = has_capability('mod/forumanonymous:rate', $context);
            $displaymode = get_user_preferences("forumanonymous_displaymode", $CFG->forumanonymous_displaymode);

            echo '&nbsp;'; // this should fix the floating in FF
            forumanonymous_print_discussion($course, $cm, $forumanonymous, $discussion, $post, $displaymode, $canreply, $canrate);
            break;

        case 'eachuser':
            if (!empty($forumanonymous->intro)) {
                echo $OUTPUT->box(format_module_intro('forumanonymous', $forumanonymous, $cm->id), 'generalbox', 'intro');
            }
            echo '<p class="mdl-align">';
            if (forumanonymous_user_can_post_discussion($forumanonymous, null, -1, $cm)) {
                print_string("allowsdiscussions", "forumanonymous");
            } else {
                echo '&nbsp;';
            }
            echo '</p>';
            if (!empty($showall)) {
                forumanonymous_print_latest_discussions($course, $forumanonymous, 0, 'header', '', -1, -1, -1, 0, $cm);
            } else {
                forumanonymous_print_latest_discussions($course, $forumanonymous, -1, 'header', '', -1, -1, $page, $CFG->forumanonymous_manydiscussions, $cm);
            }
            break;

        case 'teacher':
            if (!empty($showall)) {
                forumanonymous_print_latest_discussions($course, $forumanonymous, 0, 'header', '', -1, -1, -1, 0, $cm);
            } else {
                forumanonymous_print_latest_discussions($course, $forumanonymous, -1, 'header', '', -1, -1, $page, $CFG->forumanonymous_manydiscussions, $cm);
            }
            break;

        case 'blog':
            if (!empty($forumanonymous->intro)) {
                echo $OUTPUT->box(format_module_intro('forumanonymous', $forumanonymous, $cm->id), 'generalbox', 'intro');
            }
            echo '<br />';
            if (!empty($showall)) {
                forumanonymous_print_latest_discussions($course, $forumanonymous, 0, 'plain', '', -1, -1, -1, 0, $cm);
            } else {
                forumanonymous_print_latest_discussions($course, $forumanonymous, -1, 'plain', '', -1, -1, $page, $CFG->forumanonymous_manydiscussions, $cm);
            }
            break;

        default:
            if (!empty($forumanonymous->intro)) {
                echo $OUTPUT->box(format_module_intro('forumanonymous', $forumanonymous, $cm->id), 'generalbox', 'intro');
            }
            echo '<br />';
            if (!empty($showall)) {
                forumanonymous_print_latest_discussions($course, $forumanonymous, 0, 'header', '', -1, -1, -1, 0, $cm);
            } else {
                forumanonymous_print_latest_discussions($course, $forumanonymous, -1, 'header', '', -1, -1, $page, $CFG->forumanonymous_manydiscussions, $cm);
            }


            break;
    }

    echo $OUTPUT->footer($course);


