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
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/mod/forumanonymous/lib.php');

    $settings->add(new admin_setting_configcheckbox('forumanonymous/requiremodintro',
		       get_string('requiremodintro', 'admin'), get_string('configrequiremodintro', 'admin'), 0));

    $settings->add(new admin_setting_configselect('forumanonymous_displaymode', get_string('displaymode', 'forumanonymous'),
                       get_string('configdisplaymode', 'forumanonymous'), FORUM_MODE_NESTED, forumanonymous_get_layout_modes()));

//     $settings->add(new admin_setting_configcheckbox('forumanonymous_replytouser', get_string('replytouser', 'forumanonymous'),
//                        get_string('configreplytouser', 'forumanonymous'), 0);


/*    $settings->add(new admin_setting_configtext('forumanonymous_anonuser', get_string('anonuser', 'forumanonymous'),
                       get_string('configanonuser', 'forumanonymous'), 'Anonymous'));*/    




    // Less non-HTML characters than this is short
    $settings->add(new admin_setting_configtext('forumanonymous_shortpost', get_string('shortpost', 'forumanonymous'),
                       get_string('configshortpost', 'forumanonymous'), 300, PARAM_INT));

    // More non-HTML characters than this is long
    $settings->add(new admin_setting_configtext('forumanonymous_longpost', get_string('longpost', 'forumanonymous'),
                       get_string('configlongpost', 'forumanonymous'), 600, PARAM_INT));

    // Number of discussions on a page
    $settings->add(new admin_setting_configtext('forumanonymous_manydiscussions', get_string('manydiscussions', 'forumanonymous'),
                       get_string('configmanydiscussions', 'forumanonymous'), 100, PARAM_INT));

    if (isset($CFG->maxbytes)) {
        $settings->add(new admin_setting_configselect('forumanonymous_maxbytes', get_string('maxattachmentsize', 'forumanonymous'),
                           get_string('configmaxbytes', 'forumanonymous'), 512000, get_max_upload_sizes($CFG->maxbytes)));
    }

    // Default number of attachments allowed per post in all forumanonymouss
    $settings->add(new admin_setting_configtext('forumanonymous_maxattachments', get_string('maxattachments', 'forumanonymous'),
                       get_string('configmaxattachments', 'forumanonymous'), 9, PARAM_INT));

    // Default whether user needs to mark a post as read
    $settings->add(new admin_setting_configcheckbox('forumanonymous_trackreadposts', get_string('trackforumanonymous', 'forumanonymous'),
                       get_string('configtrackreadposts', 'forumanonymous'), 1));

    // Default number of days that a post is considered old
    $settings->add(new admin_setting_configtext('forumanonymous_oldpostdays', get_string('oldpostdays', 'forumanonymous'),
                       get_string('configoldpostdays', 'forumanonymous'), 14, PARAM_INT));

    // Default whether user needs to mark a post as read
    $settings->add(new admin_setting_configcheckbox('forumanonymous_usermarksread', get_string('usermarksread', 'forumanonymous'),
                       get_string('configusermarksread', 'forumanonymous'), 0));

    $options = array();
    for ($i = 0; $i < 24; $i++) {
        $options[$i] = sprintf("%02d",$i);
    }
    // Default time (hour) to execute 'clean_read_records' cron
    $settings->add(new admin_setting_configselect('forumanonymous_cleanreadtime', get_string('cleanreadtime', 'forumanonymous'),
                       get_string('configcleanreadtime', 'forumanonymous'), 2, $options));

    // Default time (hour) to send digest email
    $settings->add(new admin_setting_configselect('digestmailtime', get_string('digestmailtime', 'forumanonymous'),
                       get_string('configdigestmailtime', 'forumanonymous'), 17, $options));

    if (empty($CFG->enablerssfeeds)) {
        $options = array(0 => get_string('rssglobaldisabled', 'admin'));
        $str = get_string('configenablerssfeeds', 'forumanonymous').'<br />'.get_string('configenablerssfeedsdisabled2', 'admin');

    } else {
        $options = array(0=>get_string('no'), 1=>get_string('yes'));
        $str = get_string('configenablerssfeeds', 'forumanonymous');
    }
    $settings->add(new admin_setting_configselect('forumanonymous_enablerssfeeds', get_string('enablerssfeeds', 'admin'),
                       $str, 0, $options));

    $settings->add(new admin_setting_configcheckbox('forumanonymous_enabletimedposts', get_string('timedposts', 'forumanonymous'),
                       get_string('configenabletimedposts', 'forumanonymous'), 0));
}

