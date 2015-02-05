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
 * Subscribe to or unsubscribe from a forumanonymous or manage forumanonymous subscription mode
 *
 * This script can be used by either individual users to subscribe to or
 * unsubscribe from a forumanonymous (no 'mode' param provided), or by forumanonymous managers
 * to control the subscription mode (by 'mode' param).
 * This script can be called from a link in email so the sesskey is not
 * required parameter. However, if sesskey is missing, the user has to go
 * through a confirmation page that redirects the user back with the
 * sesskey.
 *
 * @package    mod
 * @subpackage forumanonymous
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/mod/forumanonymous/lib.php');

$id      = required_param('id', PARAM_INT);             // the forumanonymous to subscribe or unsubscribe to
$mode    = optional_param('mode', null, PARAM_INT);     // the forumanonymous's subscription mode
$user    = optional_param('user', 0, PARAM_INT);        // userid of the user to subscribe, defaults to $USER
$sesskey = optional_param('sesskey', null, PARAM_RAW);  // sesskey

$url = new moodle_url('/mod/forumanonymous/subscribe.php', array('id'=>$id));
if (!is_null($mode)) {
    $url->param('mode', $mode);
}
if ($user !== 0) {
    $url->param('user', $user);
}
if (!is_null($sesskey)) {
    $url->param('sesskey', $sesskey);
}
$PAGE->set_url($url);

$forumanonymous   = $DB->get_record('forumanonymous', array('id' => $id), '*', MUST_EXIST);
$course  = $DB->get_record('course', array('id' => $forumanonymous->course), '*', MUST_EXIST);
$cm      = get_coursemodule_from_instance('forumanonymous', $forumanonymous->id, $course->id, false, MUST_EXIST);
$context = context_module::instance($cm->id);

if ($user) {
    require_sesskey();
    if (!has_capability('mod/forumanonymous:managesubscriptions', $context)) {
        print_error('nopermissiontosubscribe', 'forumanonymous');
    }
    $user = $DB->get_record('user', array('id' => $user),'*', MUST_EXIST);
} else {
    $user = $USER;
}

if (isset($cm->groupmode) && empty($course->groupmodeforce)) {
    $groupmode = $cm->groupmode;
} else {
    $groupmode = $course->groupmode;
}
if ($groupmode && !forumanonymous_is_subscribed($user->id, $forumanonymous) && !has_capability('moodle/site:accessallgroups', $context)) {
    if (!groups_get_all_groups($course->id, $USER->id)) {
        print_error('cannotsubscribe', 'forumanonymous');
    }
}

require_login($course, false, $cm);

if (is_null($mode) and !is_enrolled($context, $USER, '', true)) {   // Guests and visitors can't subscribe - only enrolled
    $PAGE->set_title($course->shortname);
    $PAGE->set_heading($course->fullname);
    if (isguestuser()) {
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(get_string('subscribeenrolledonly', 'forumanonymous').'<br /><br />'.get_string('liketologin'),
                     get_login_url(), new moodle_url('/mod/forumanonymous/view.php', array('f'=>$id)));
        echo $OUTPUT->footer();
        exit;
    } else {
        // there should not be any links leading to this place, just redirect
        redirect(new moodle_url('/mod/forumanonymous/view.php', array('f'=>$id)), get_string('subscribeenrolledonly', 'forumanonymous'));
    }
}

$returnto = optional_param('backtoindex',0,PARAM_INT)
    ? "index.php?id=".$course->id
    : "view.php?f=$id";

if (!is_null($mode) and has_capability('mod/forumanonymous:managesubscriptions', $context)) {
    require_sesskey();
    switch ($mode) {
        case FORUM_CHOOSESUBSCRIBE : // 0
            forumanonymous_forcesubscribe($forumanonymous->id, FORUM_CHOOSESUBSCRIBE);
            redirect($returnto, get_string("everyonecannowchoose", "forumanonymous"), 1);
            break;
        case FORUM_FORCESUBSCRIBE : // 1
            forumanonymous_forcesubscribe($forumanonymous->id, FORUM_FORCESUBSCRIBE);
            redirect($returnto, get_string("everyoneisnowsubscribed", "forumanonymous"), 1);
            break;
        case FORUM_INITIALSUBSCRIBE : // 2
            forumanonymous_forcesubscribe($forumanonymous->id, FORUM_INITIALSUBSCRIBE);
            redirect($returnto, get_string("everyoneisnowsubscribed", "forumanonymous"), 1);
            break;
        case FORUM_DISALLOWSUBSCRIBE : // 3
            forumanonymous_forcesubscribe($forumanonymous->id, FORUM_DISALLOWSUBSCRIBE);
            redirect($returnto, get_string("noonecansubscribenow", "forumanonymous"), 1);
            break;
        default:
            print_error(get_string('invalidforcesubscribe', 'forumanonymous'));
    }
}

if (forumanonymous_is_forcesubscribed($forumanonymous)) {
    redirect($returnto, get_string("everyoneisnowsubscribed", "forumanonymous"), 1);
}

$info = new stdClass();
$info->name  = fullname($user);
$info->forumanonymous = format_string($forumanonymous->name);

if (forumanonymous_is_subscribed($user->id, $forumanonymous->id)) {
    if (is_null($sesskey)) {    // we came here via link in email
        $PAGE->set_title($course->shortname);
        $PAGE->set_heading($course->fullname);
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(get_string('confirmunsubscribe', 'forumanonymous', format_string($forumanonymous->name)),
                new moodle_url($PAGE->url, array('sesskey' => sesskey())), new moodle_url('/mod/forumanonymous/view.php', array('f' => $id)));
        echo $OUTPUT->footer();
        exit;
    }
    require_sesskey();
    if (forumanonymous_unsubscribe($user->id, $forumanonymous->id)) {
        //add_to_log($course->id, "forumanonymous", "unsubscribe", "view.php?f=$forumanonymous->id", $forumanonymous->id, $cm->id);
        redirect($returnto, get_string("nownotsubscribed", "forumanonymous", $info), 1);
    } else {
        print_error('cannotunsubscribe', 'forumanonymous', $_SERVER["HTTP_REFERER"]);
    }

} else {  // subscribe
    if ($forumanonymous->forcesubscribe == FORUM_DISALLOWSUBSCRIBE &&
                !has_capability('mod/forumanonymous:managesubscriptions', $context)) {
        print_error('disallowsubscribe', 'forumanonymous', $_SERVER["HTTP_REFERER"]);
    }
    if (!has_capability('mod/forumanonymous:viewdiscussion', $context)) {
        print_error('noviewdiscussionspermission', 'forumanonymous', $_SERVER["HTTP_REFERER"]);
    }
    if (is_null($sesskey)) {    // we came here via link in email
        $PAGE->set_title($course->shortname);
        $PAGE->set_heading($course->fullname);
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(get_string('confirmsubscribe', 'forumanonymous', format_string($forumanonymous->name)),
                new moodle_url($PAGE->url, array('sesskey' => sesskey())), new moodle_url('/mod/forumanonymous/view.php', array('f' => $id)));
        echo $OUTPUT->footer();
        exit;
    }
    require_sesskey();
    forumanonymous_subscribe($user->id, $forumanonymous->id);
    //add_to_log($course->id, "forumanonymous", "subscribe", "view.php?f=$forumanonymous->id", $forumanonymous->id, $cm->id);
    redirect($returnto, get_string("nowsubscribed", "forumanonymous", $info), 1);
}
