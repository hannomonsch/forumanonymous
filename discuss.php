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
 * Displays a post, and all the posts below it.
 * If no post is given, displays all posts in a discussion
 *
 * @package mod-forumanonymous
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    require_once('../../config.php');

    $d      = required_param('d', PARAM_INT);                // Discussion ID
    $parent = optional_param('parent', 0, PARAM_INT);        // If set, then display this post and all children.
    $mode   = optional_param('mode', 0, PARAM_INT);          // If set, changes the layout of the thread
    $move   = optional_param('move', 0, PARAM_INT);          // If set, moves this discussion to another forumanonymous
    $mark   = optional_param('mark', '', PARAM_ALPHA);       // Used for tracking read posts if user initiated.
    $postid = optional_param('postid', 0, PARAM_INT);        // Used for tracking read posts if user initiated.

    $url = new moodle_url('/mod/forumanonymous/discuss.php', array('d'=>$d));
    if ($parent !== 0) {
        $url->param('parent', $parent);
    }
    $PAGE->set_url($url);

    $discussion = $DB->get_record('forumanonymous_discussions', array('id' => $d), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $discussion->course), '*', MUST_EXIST);
    $forumanonymous = $DB->get_record('forumanonymous', array('id' => $discussion->forumanonymous), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('forumanonymous', $forumanonymous->id, $course->id, false, MUST_EXIST);

    require_course_login($course, true, $cm);

    // move this down fix for MDL-6926
    require_once($CFG->dirroot.'/mod/forumanonymous/lib.php');

    $modcontext = get_context_instance(CONTEXT_MODULE, $cm->id);
    require_capability('mod/forumanonymous:viewdiscussion', $modcontext, NULL, true, 'noviewdiscussionspermission', 'forumanonymous');

    if (!empty($CFG->enablerssfeeds) && !empty($CFG->forumanonymous_enablerssfeeds) && $forumanonymous->rsstype && $forumanonymous->rssarticles) {
        require_once("$CFG->libdir/rsslib.php");

        $rsstitle = format_string($course->shortname, true, array('context' => get_context_instance(CONTEXT_COURSE, $course->id))) . ': %fullname%';
        rss_add_http_header($modcontext, 'mod_forumanonymous', $forumanonymous, $rsstitle);
    }


/// move discussion if requested
    if ($move > 0 and confirm_sesskey()) {
        $return = $CFG->wwwroot.'/mod/forumanonymous/discuss.php?d='.$discussion->id;

        require_capability('mod/forumanonymous:movediscussions', $modcontext);

        if ($forumanonymous->type == 'single') {
            print_error('cannotmovefromsingleforumanonymous', 'forumanonymous', $return);
        }

        if (!$forumanonymousto = $DB->get_record('forumanonymous', array('id' => $move))) {
            print_error('cannotmovetonotexist', 'forumanonymous', $return);
        }

        if (!$cmto = get_coursemodule_from_instance('forumanonymous', $forumanonymousto->id, $course->id)) {
            print_error('cannotmovetonotfound', 'forumanonymous', $return);
        }

        if (!coursemodule_visible_for_user($cmto)) {
            print_error('cannotmovenotvisible', 'forumanonymous', $return);
        }
	
	require_capability('mod/forumanonymous:startdiscussion', context_module::instance($cmto->id));

        if (!forumanonymous_move_attachments($discussion, $forumanonymous->id, $forumanonymousto->id)) {
            echo $OUTPUT->notification("Errors occurred while moving attachment directories - check your file permissions");
        }
        $DB->set_field('forumanonymous_discussions', 'forumanonymous', $forumanonymousto->id, array('id' => $discussion->id));
        $DB->set_field('forumanonymous_read', 'forumanonymousid', $forumanonymousto->id, array('discussionid' => $discussion->id));
        //add_to_log($course->id, 'forumanonymous', 'move discussion', "discuss.php?d=$discussion->id", $discussion->id, $cmto->id);

        require_once($CFG->libdir.'/rsslib.php');
        require_once($CFG->dirroot.'/mod/forumanonymous/rsslib.php');

        // Delete the RSS files for the 2 forumanonymouss to force regeneration of the feeds
        forumanonymous_rss_delete_file($forumanonymous);
        forumanonymous_rss_delete_file($forumanonymousto);

        redirect($return.'&moved=-1&sesskey='.sesskey());
    }

    //add_to_log($course->id, 'forumanonymous', 'view discussion', $PAGE->url->out(false), $discussion->id, $cm->id);

    unset($SESSION->fromdiscussion);

    if ($mode) {
        set_user_preference('forumanonymous_displaymode', $mode);
    }

    $displaymode = get_user_preferences('forumanonymous_displaymode', $CFG->forumanonymous_displaymode);

    if ($parent) {
        // If flat AND parent, then force nested display this time
        if ($displaymode == FORUMANONYMOUS_MODE_FLATOLDEST or $displaymode == FORUMANONYMOUS_MODE_FLATNEWEST) {
            $displaymode = FORUMANONYMOUS_MODE_NESTED;
        }
    } else {
        $parent = $discussion->firstpost;
    }

    if (! $post = forumanonymous_get_post_full($parent)) {
        print_error("notexists", 'forumanonymous', "$CFG->wwwroot/mod/forumanonymous/view.php?f=$forumanonymous->id");
    }

    if (!forumanonymous_user_can_see_post($forumanonymous, $discussion, $post, null, $cm)) {
        print_error('noviewdiscussionspermission', 'forum', "$CFG->wwwroot/mod/forum/view.php?id=$forum->id");
    }

    if ($mark == 'read' or $mark == 'unread') {
        if ($CFG->forumanonymous_usermarksread && forumanonymous_tp_can_track_forumanonymouss($forumanonymous) && forumanonymous_tp_is_tracked($forumanonymous)) {
            if ($mark == 'read') {
                forumanonymous_tp_add_read_record($USER->id, $postid);
            } else {
                // unread
                forumanonymous_tp_delete_read_records($USER->id, $postid);
            }
        }
    }

    $searchform = forumanonymous_search_form($course);

    $forumanonymousnode = $PAGE->navigation->find($cm->id, navigation_node::TYPE_ACTIVITY);
    if (empty($forumanonymousnode)) {
        $forumanonymousnode = $PAGE->navbar;
    } else {
        $forumanonymousnode->make_active();
    }
    $node = $forumanonymousnode->add(format_string($discussion->name), new moodle_url('/mod/forumanonymous/discuss.php', array('d'=>$discussion->id)));
    $node->display = false;
    if ($node && $post->id != $discussion->firstpost) {
        $node->add(format_string($post->subject), $PAGE->url);
    }

    $PAGE->set_title("$course->shortname: ".format_string($discussion->name));
    $PAGE->set_heading($course->fullname);
    $PAGE->set_button($searchform);
    echo $OUTPUT->header();

/// Check to see if groups are being used in this forumanonymous
/// If so, make sure the current person is allowed to see this discussion
/// Also, if we know they should be able to reply, then explicitly set $canreply for performance reasons

    $canreply = forumanonymous_user_can_post($forumanonymous, $discussion, $USER, $cm, $course, $modcontext);
    if (!$canreply and $forumanonymous->type !== 'news') {
        if (isguestuser() or !isloggedin()) {
            $canreply = true;
        }
        if (!is_enrolled($modcontext) and !is_viewing($modcontext)) {
            // allow guests and not-logged-in to see the link - they are prompted to log in after clicking the link
            // normal users with temporary guest access see this link too, they are asked to enrol instead
            $canreply = enrol_selfenrol_available($course->id);
        }
    }

/// Print the controls across the top
    echo '<div class="discussioncontrols clearfix">';

    if (!empty($CFG->enableportfolios) && has_capability('mod/forumanonymous:exportdiscussion', $modcontext)) {
        require_once($CFG->libdir.'/portfoliolib.php');
        $button = new portfolio_add_button();
        $button->set_callback_options('forumanonymous_portfolio_caller', array('discussionid' => $discussion->id), 'mod_forumanonymous');
        $button = $button->to_html(PORTFOLIO_ADD_FULL_FORM, get_string('exportdiscussion', 'mod_forumanonymous'));
        $buttonextraclass = '';
        if (empty($button)) {
            // no portfolio plugin available.
            $button = '&nbsp;';
            $buttonextraclass = ' noavailable';
        }
        echo html_writer::tag('div', $button, array('class' => 'discussioncontrol exporttoportfolio'.$buttonextraclass));
    } else {
        echo html_writer::tag('div', '&nbsp;', array('class'=>'discussioncontrol nullcontrol'));
    }

    // groups selector not needed here
    echo '<div class="discussioncontrol displaymode">';
    forumanonymous_print_mode_form($discussion->id, $displaymode);
    echo "</div>";

    if ($forumanonymous->type != 'single'
                && has_capability('mod/forumanonymous:movediscussions', $modcontext)) {

        echo '<div class="discussioncontrol movediscussion">';
        // Popup menu to move discussions to other forumanonymouss. The discussion in a
        // single discussion forumanonymous can't be moved.
        $modinfo = get_fast_modinfo($course);
        if (isset($modinfo->instances['forumanonymous'])) {
            $forumanonymousmenu = array();
            foreach ($modinfo->instances['forumanonymous'] as $forumanonymouscm) {
                if (!$forumanonymouscm->uservisible || !has_capability('mod/forumanonymous:startdiscussion',
			context_module::instance($forumanonymouscm->id))) {
                    continue;
                }

                $section = $forumanonymouscm->sectionnum;
                $sectionname = get_section_name($course, $section);
                if (empty($forumanonymousmenu[$section])) {
                    $forumanonymousmenu[$section] = array($sectionname => array());
                }
                if ($forumanonymouscm->instance != $forumanonymous->id) {
                    $url = "/mod/forumanonymous/discuss.php?d=$discussion->id&move=$forumanonymouscm->instance&sesskey=".sesskey();
                    $forumanonymousmenu[$section][$sectionname][$url] = format_string($forumanonymouscm->name);
                }
            }
            if (!empty($forumanonymousmenu)) {
                echo '<div class="movediscussionoption">';
                $select = new url_select($forumanonymousmenu, '',
                        array(''=>get_string("movethisdiscussionto", "forumanonymous")),
                        'forumanonymousmenu', get_string('move'));
                echo $OUTPUT->render($select);
                echo "</div>";
            }
        }
        echo "</div>";
    }
    echo '<div class="clearfloat">&nbsp;</div>';
    echo "</div>";

    if (!empty($forumanonymous->blockafter) && !empty($forumanonymous->blockperiod)) {
        $a = new stdClass();
        $a->blockafter  = $forumanonymous->blockafter;
        $a->blockperiod = get_string('secondstotime'.$forumanonymous->blockperiod);
        echo $OUTPUT->notification(get_string('thisforumanonymousisthrottled','forumanonymous',$a));
    }

    if ($forumanonymous->type == 'qanda' && !has_capability('mod/forumanonymous:viewqandawithoutposting', $modcontext) &&
                !forumanonymous_user_has_posted($forumanonymous->id,$discussion->id,$USER->id)) {
        echo $OUTPUT->notification(get_string('qandanotify','forumanonymous'));
    }

    if ($move == -1 and confirm_sesskey()) {
        echo $OUTPUT->notification(get_string('discussionmoved', 'forumanonymous', format_string($forumanonymous->name,true)));
    }

    $canrate = has_capability('mod/forumanonymous:rate', $modcontext);
    forumanonymous_print_discussion($course, $cm, $forumanonymous, $discussion, $post, $displaymode, $canreply, $canrate);

    echo $OUTPUT->footer();



