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

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/mod/forumanonymous/lib.php');
require_once($CFG->libdir . '/rsslib.php');

$id = optional_param('id', 0, PARAM_INT);                   // Course id
$subscribe = optional_param('subscribe', null, PARAM_INT);  // Subscribe/Unsubscribe all forumanonymouss

$url = new moodle_url('/mod/forumanonymous/index.php', array('id'=>$id));
if ($subscribe !== null) {
    require_sesskey();
    $url->param('subscribe', $subscribe);
}
$PAGE->set_url($url);

if ($id) {
    if (! $course = $DB->get_record('course', array('id' => $id))) {
        print_error('invalidcourseid');
    }
} else {
    $course = get_site();
}

require_course_login($course);
$PAGE->set_pagelayout('incourse');
$coursecontext = context_course::instance($course->id);

unset($SESSION->fromdiscussion);

//add_to_log($course->id, 'forumanonymous', 'view forumanonymouss', "index.php?id=$course->id");

$strforumanonymouss       = get_string('forumanonymouss', 'forumanonymous');
$strforumanonymous        = get_string('forumanonymous', 'forumanonymous');
$strdescription  = get_string('description');
$strdiscussions  = get_string('discussions', 'forumanonymous');
$strsubscribed   = get_string('subscribed', 'forumanonymous');
$strunreadposts  = get_string('unreadposts', 'forumanonymous');
$strtracking     = get_string('tracking', 'forumanonymous');
$strmarkallread  = get_string('markallread', 'forumanonymous');
$strtrackforumanonymous   = get_string('trackforumanonymous', 'forumanonymous');
$strnotrackforumanonymous = get_string('notrackforumanonymous', 'forumanonymous');
$strsubscribe    = get_string('subscribe', 'forumanonymous');
$strunsubscribe  = get_string('unsubscribe', 'forumanonymous');
$stryes          = get_string('yes');
$strno           = get_string('no');
$strrss          = get_string('rss');

$searchform = forumanonymous_search_form($course);


// Start of the table for General forumanonymouss

$generaltable = new html_table();
$generaltable->head  = array ($strforumanonymous, $strdescription, $strdiscussions);
$generaltable->align = array ('left', 'left', 'center');

if ($usetracking = forumanonymous_tp_can_track_forumanonymouss()) {
    $untracked = forumanonymous_tp_get_untracked_forumanonymouss($USER->id, $course->id);

    $generaltable->head[] = $strunreadposts;
    $generaltable->align[] = 'center';

    $generaltable->head[] = $strtracking;
    $generaltable->align[] = 'center';
}

$subscribed_forumanonymouss = forumanonymous_get_subscribed_forumanonymouss($course);

$can_subscribe = is_enrolled($coursecontext);
if ($can_subscribe) {
    $generaltable->head[] = $strsubscribed;
    $generaltable->align[] = 'center';
}

if ($show_rss = (($can_subscribe || $course->id == SITEID) &&
                 isset($CFG->enablerssfeeds) && isset($CFG->forumanonymous_enablerssfeeds) &&
                 $CFG->enablerssfeeds && $CFG->forumanonymous_enablerssfeeds)) {
    $generaltable->head[] = $strrss;
    $generaltable->align[] = 'center';
}

$usesections = course_format_uses_sections($course->format);

$table = new html_table();

// Parse and organise all the forumanonymouss.  Most forumanonymouss are course modules but
// some special ones are not.  These get placed in the general forumanonymouss
// category with the forumanonymouss in section 0.

$forumanonymouss = $DB->get_records('forumanonymous', array('course' => $course->id));

$generalforumanonymouss  = array();
$learningforumanonymouss = array();
$modinfo = get_fast_modinfo($course);

if (!isset($modinfo->instances['forumanonymous'])) {
    $modinfo->instances['forumanonymous'] = array();
}

foreach ($modinfo->instances['forumanonymous'] as $forumanonymousid=>$cm) {
    if (!$cm->uservisible or !isset($forumanonymouss[$forumanonymousid])) {
        continue;
    }

    $forumanonymous = $forumanonymouss[$forumanonymousid];

    if (!$context = get_context_instance(CONTEXT_MODULE, $cm->id)) {
        continue;   // Shouldn't happen
    }

    if (!has_capability('mod/forumanonymous:viewdiscussion', $context)) {
        continue;
    }

    // fill two type array - order in modinfo is the same as in course
    if ($forumanonymous->type == 'news' or $forumanonymous->type == 'social') {
        $generalforumanonymouss[$forumanonymous->id] = $forumanonymous;

    } else if ($course->id == SITEID or empty($cm->sectionnum)) {
        $generalforumanonymouss[$forumanonymous->id] = $forumanonymous;

    } else {
        $learningforumanonymouss[$forumanonymous->id] = $forumanonymous;
    }
}

/// Do course wide subscribe/unsubscribe
if (!is_null($subscribe) and !isguestuser()) {
    foreach ($modinfo->instances['forumanonymous'] as $forumanonymousid=>$cm) {
        $forumanonymous = $forumanonymouss[$forumanonymousid];
	$modcontext = context_module::instance($cm->id);
        $cansub = false;

        if (has_capability('mod/forumanonymous:viewdiscussion', $modcontext)) {
            $cansub = true;
        }
        if ($cansub && $cm->visible == 0 &&
            !has_capability('mod/forumanonymous:managesubscriptions', $modcontext))
        {
            $cansub = false;
        }
        if (!forumanonymous_is_forcesubscribed($forumanonymous)) {
            $subscribed = forumanonymous_is_subscribed($USER->id, $forumanonymous);
            if ((has_capability('moodle/course:manageactivities', $coursecontext, $USER->id) || $forumanonymous->forcesubscribe != FORUMANONYMOUS_DISALLOWSUBSCRIBE) && $subscribe && !$subscribed && $cansub) {
                forumanonymous_subscribe($USER->id, $forumanonymousid);
            } else if (!$subscribe && $subscribed) {
                forumanonymous_unsubscribe($USER->id, $forumanonymousid);
            }
        }
    }
    $returnto = forumanonymous_go_back_to("index.php?id=$course->id");
    $shortname = format_string($course->shortname, true, array('context' => context_course::instance($course->id)));
    if ($subscribe) {
        //add_to_log($course->id, 'forumanonymous', 'subscribeall', "index.php?id=$course->id", $course->id);
        redirect($returnto, get_string('nowallsubscribed', 'forumanonymous', $shortname), 1);
    } else {
        //add_to_log($course->id, 'forumanonymous', 'unsubscribeall', "index.php?id=$course->id", $course->id);
        redirect($returnto, get_string('nowallunsubscribed', 'forumanonymous', $shortname), 1);
    }
}

/// First, let's process the general forumanonymouss and build up a display

if ($generalforumanonymouss) {
    foreach ($generalforumanonymouss as $forumanonymous) {
        $cm      = $modinfo->instances['forumanonymous'][$forumanonymous->id];
	$context = context_module::instance($cm->id);

        $count = forumanonymous_count_discussions($forumanonymous, $cm, $course);

        if ($usetracking) {
            if ($forumanonymous->trackingtype == FORUMANONYMOUS_TRACKING_OFF) {
                $unreadlink  = '-';
                $trackedlink = '-';

            } else {
                if (isset($untracked[$forumanonymous->id])) {
                        $unreadlink  = '-';
                } else if ($unread = forumanonymous_tp_count_forumanonymous_unread_posts($cm, $course)) {
                        $unreadlink = '<span class="unread"><a href="view.php?f='.$forumanonymous->id.'">'.$unread.'</a>';
                    $unreadlink .= '<a title="'.$strmarkallread.'" href="markposts.php?f='.
                                   $forumanonymous->id.'&amp;mark=read"><img src="'.$OUTPUT->pix_url('t/markasread') . '" alt="'.$strmarkallread.'" class="iconsmall" /></a></span>';
                } else {
                    $unreadlink = '<span class="read">0</span>';
                }

                if ($forumanonymous->trackingtype == FORUMANONYMOUS_TRACKING_ON) {
                    $trackedlink = $stryes;

                } else {
                    $aurl = new moodle_url('/mod/forumanonymous/settracking.php', array('id'=>$forumanonymous->id));
                    if (!isset($untracked[$forumanonymous->id])) {
                        $trackedlink = $OUTPUT->single_button($aurl, $stryes, 'post', array('title'=>$strnotrackforumanonymous));
                    } else {
                        $trackedlink = $OUTPUT->single_button($aurl, $strno, 'post', array('title'=>$strtrackforumanonymous));
                    }
                }
            }
        }

        $forumanonymous->intro = shorten_text(format_module_intro('forumanonymous', $forumanonymous, $cm->id), $CFG->forumanonymous_shortpost);
        $forumanonymousname = format_string($forumanonymous->name, true);

        if ($cm->visible) {
            $style = '';
        } else {
            $style = 'class="dimmed"';
        }
        $forumanonymouslink = "<a href=\"view.php?f=$forumanonymous->id\" $style>".format_string($forumanonymous->name,true)."</a>";
        $discussionlink = "<a href=\"view.php?f=$forumanonymous->id\" $style>".$count."</a>";

        $row = array ($forumanonymouslink, $forumanonymous->intro, $discussionlink);
        if ($usetracking) {
            $row[] = $unreadlink;
            $row[] = $trackedlink;    // Tracking.
        }

        if ($can_subscribe) {
            if ($forumanonymous->forcesubscribe != FORUMANONYMOUS_DISALLOWSUBSCRIBE) {
                $row[] = forumanonymous_get_subscribe_link($forumanonymous, $context, array('subscribed' => $stryes,
                        'unsubscribed' => $strno, 'forcesubscribed' => $stryes,
                        'cantsubscribe' => '-'), false, false, true, $subscribed_forumanonymouss);
            } else {
                $row[] = '-';
            }
        }

        //If this forumanonymous has RSS activated, calculate it
        if ($show_rss) {
            if ($forumanonymous->rsstype and $forumanonymous->rssarticles) {
                //Calculate the tooltip text
                if ($forumanonymous->rsstype == 1) {
                    $tooltiptext = get_string('rsssubscriberssdiscussions', 'forumanonymous');
                } else {
                    $tooltiptext = get_string('rsssubscriberssposts', 'forumanonymous');
                }

                if (!isloggedin() && $course->id == SITEID) {
                    $userid = guest_user()->id;
                } else {
                    $userid = $USER->id;
                }
                //Get html code for RSS link
                $row[] = rss_get_link($context->id, $userid, 'mod_forumanonymous', $forumanonymous->id, $tooltiptext);
            } else {
                $row[] = '&nbsp;';
            }
        }

        $generaltable->data[] = $row;
    }
}


// Start of the table for Learning forumanonymouss
$learningtable = new html_table();
$learningtable->head  = array ($strforumanonymous, $strdescription, $strdiscussions);
$learningtable->align = array ('left', 'left', 'center');

if ($usetracking) {
    $learningtable->head[] = $strunreadposts;
    $learningtable->align[] = 'center';

    $learningtable->head[] = $strtracking;
    $learningtable->align[] = 'center';
}

if ($can_subscribe) {
    $learningtable->head[] = $strsubscribed;
    $learningtable->align[] = 'center';
}

if ($show_rss = (($can_subscribe || $course->id == SITEID) &&
                 isset($CFG->enablerssfeeds) && isset($CFG->forumanonymous_enablerssfeeds) &&
                 $CFG->enablerssfeeds && $CFG->forumanonymous_enablerssfeeds)) {
    $learningtable->head[] = $strrss;
    $learningtable->align[] = 'center';
}

/// Now let's process the learning forumanonymouss

if ($course->id != SITEID) {    // Only real courses have learning forumanonymouss
    // 'format_.'$course->format only applicable when not SITEID (format_site is not a format)
    $strsectionname  = get_string('sectionname', 'format_'.$course->format);
    // Add extra field for section number, at the front
    array_unshift($learningtable->head, $strsectionname);
    array_unshift($learningtable->align, 'center');


    if ($learningforumanonymouss) {
        $currentsection = '';
            foreach ($learningforumanonymouss as $forumanonymous) {
            $cm      = $modinfo->instances['forumanonymous'][$forumanonymous->id];
	    $context = context_module::instance($cm->id);

            $count = forumanonymous_count_discussions($forumanonymous, $cm, $course);

            if ($usetracking) {
                if ($forumanonymous->trackingtype == FORUMANONYMOUS_TRACKING_OFF) {
                    $unreadlink  = '-';
                    $trackedlink = '-';

                } else {
                    if (isset($untracked[$forumanonymous->id])) {
                        $unreadlink  = '-';
                    } else if ($unread = forumanonymous_tp_count_forumanonymous_unread_posts($cm, $course)) {
                        $unreadlink = '<span class="unread"><a href="view.php?f='.$forumanonymous->id.'">'.$unread.'</a>';
                        $unreadlink .= '<a title="'.$strmarkallread.'" href="markposts.php?f='.
					$forumanonymous->id.'&amp;mark=read"><img src="'.$OUTPUT->pix_url('t/markasread') . '" alt="'.$strmarkallread.'" class="iconsmall" /></a></span>';
                    } else {
                        $unreadlink = '<span class="read">0</span>';
                    }

                    if ($forumanonymous->trackingtype == FORUMANONYMOUS_TRACKING_ON) {
                        $trackedlink = $stryes;

                    } else {
                        $aurl = new moodle_url('/mod/forumanonymous/settracking.php', array('id'=>$forumanonymous->id));
                        if (!isset($untracked[$forumanonymous->id])) {
                            $trackedlink = $OUTPUT->single_button($aurl, $stryes, 'post', array('title'=>$strnotrackforumanonymous));
                        } else {
                            $trackedlink = $OUTPUT->single_button($aurl, $strno, 'post', array('title'=>$strtrackforumanonymous));
                        }
                    }
                }
            }

            $forumanonymous->intro = shorten_text(format_module_intro('forumanonymous', $forumanonymous, $cm->id), $CFG->forumanonymous_shortpost);

            if ($cm->sectionnum != $currentsection) {
                $printsection = get_section_name($course, $cm->sectionnum);
                if ($currentsection) {
                    $learningtable->data[] = 'hr';
                }
                $currentsection = $cm->sectionnum;
            } else {
                $printsection = '';
            }

            $forumanonymousname = format_string($forumanonymous->name,true);

            if ($cm->visible) {
                $style = '';
            } else {
                $style = 'class="dimmed"';
            }
            $forumanonymouslink = "<a href=\"view.php?f=$forumanonymous->id\" $style>".format_string($forumanonymous->name,true)."</a>";
            $discussionlink = "<a href=\"view.php?f=$forumanonymous->id\" $style>".$count."</a>";

            $row = array ($printsection, $forumanonymouslink, $forumanonymous->intro, $discussionlink);
            if ($usetracking) {
                $row[] = $unreadlink;
                $row[] = $trackedlink;    // Tracking.
            }

            if ($can_subscribe) {
                if ($forumanonymous->forcesubscribe != FORUMANONYMOUS_DISALLOWSUBSCRIBE) {
                    $row[] = forumanonymous_get_subscribe_link($forumanonymous, $context, array('subscribed' => $stryes,
                        'unsubscribed' => $strno, 'forcesubscribed' => $stryes,
                        'cantsubscribe' => '-'), false, false, true, $subscribed_forumanonymouss);
                } else {
                    $row[] = '-';
                }
            }

            //If this forumanonymous has RSS activated, calculate it
            if ($show_rss) {
                if ($forumanonymous->rsstype and $forumanonymous->rssarticles) {
                    //Calculate the tolltip text
                    if ($forumanonymous->rsstype == 1) {
                        $tooltiptext = get_string('rsssubscriberssdiscussions', 'forumanonymous');
                    } else {
                        $tooltiptext = get_string('rsssubscriberssposts', 'forumanonymous');
                    }
                    //Get html code for RSS link
                    $row[] = rss_get_link($context->id, $USER->id, 'mod_forumanonymous', $forumanonymous->id, $tooltiptext);
                } else {
                    $row[] = '&nbsp;';
                }
            }

            $learningtable->data[] = $row;
        }
    }
}


/// Output the page
$PAGE->navbar->add($strforumanonymouss);
$PAGE->set_title("$course->shortname: $strforumanonymouss");
$PAGE->set_heading($course->fullname);
$PAGE->set_button($searchform);
echo $OUTPUT->header();

if (!isguestuser() && isloggedin()) {
    echo $OUTPUT->box_start('subscription');
    echo html_writer::tag('div',
        html_writer::link(new moodle_url('/mod/forumanonymous/index.php', array('id'=>$course->id, 'subscribe'=>1, 'sesskey'=>sesskey())),
            get_string('allsubscribe', 'forumanonymous')),
        array('class'=>'helplink'));
    echo html_writer::tag('div',
        html_writer::link(new moodle_url('/mod/forumanonymous/index.php', array('id'=>$course->id, 'subscribe'=>0, 'sesskey'=>sesskey())),
            get_string('allunsubscribe', 'forumanonymous')),
        array('class'=>'helplink'));
    echo $OUTPUT->box_end();
    echo $OUTPUT->box('&nbsp;', 'clearer');
}

if ($generalforumanonymouss) {
    echo $OUTPUT->heading(get_string('generalforumanonymouss', 'forumanonymous'));
    echo html_writer::table($generaltable);
}

if ($learningforumanonymouss) {
    echo $OUTPUT->heading(get_string('learningforumanonymouss', 'forumanonymous'));
    echo html_writer::table($learningtable);
}

echo $OUTPUT->footer();

