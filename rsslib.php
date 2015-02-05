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
 * This file adds support to rss feeds generation
 *
 * @package mod_forumanonymous
 * @category rss
 * @copyright 2001 Eloy Lafuente (stronk7) http://contiento.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Returns the path to the cached rss feed contents. Creates/updates the cache if necessary.
 * @param stdClass $context the context
 * @param array    $args    the arguments received in the url
 * @return string the full path to the cached RSS feed directory. Null if there is a problem.
 */
function forumanonymous_rss_get_feed($context, $args) {
    global $CFG, $DB, $USER;

    $status = true;

    //are RSS feeds enabled?
    if (empty($CFG->forumanonymous_enablerssfeeds)) {
        debugging('DISABLED (module configuration)');
        return null;
    }

    $forumanonymousid  = clean_param($args[3], PARAM_INT);
    $cm = get_coursemodule_from_instance('forumanonymous', $forumanonymousid, 0, false, MUST_EXIST);
    $modcontext = context_module::instance($cm->id);

    //context id from db should match the submitted one
    if ($context->id != $modcontext->id || !has_capability('mod/forumanonymous:viewdiscussion', $modcontext)) {
        return null;
    }

    $forumanonymous = $DB->get_record('forumanonymous', array('id' => $forumanonymousid), '*', MUST_EXIST);
    if (!rss_enabled_for_mod('forumanonymous', $forumanonymous)) {
        return null;
    }

    //the sql that will retreive the data for the feed and be hashed to get the cache filename
    list($sql, $params) = forumanonymous_rss_get_sql($forumanonymous, $cm);

    // Hash the sql to get the cache file name.
    $filename = rss_get_file_name($forumanonymous, $sql, $params);
    $cachedfilepath = rss_get_file_full_name('mod_forumanonymous', $filename);

    //Is the cache out of date?
    $cachedfilelastmodified = 0;
    if (file_exists($cachedfilepath)) {
        $cachedfilelastmodified = filemtime($cachedfilepath);
    }
    // Used to determine if we need to generate a new RSS feed.
    $dontrecheckcutoff = time()-60;
    // If it hasn't been generated we will need to create it, otherwise only update
    // if there is new stuff to show and it is older than the cut off date set above.
    if (($cachedfilelastmodified == 0) || (($dontrecheckcutoff > $cachedfilelastmodified) &&
        forumanonymous_rss_newstuff($forumanonymous, $cm, $cachedfilelastmodified))) {
        // Need to regenerate the cached version.
        $result = forumanonymous_rss_feed_contents($forumanonymous, $sql, $params, $modcontext);
        $status = rss_save_file('mod_forumanonymous', $filename, $result);
    }

    //return the path to the cached version
    return $cachedfilepath;
}

/**
 * Given a forumanonymous object, deletes all cached RSS files associated with it.
 *
 * @param stdClass $forumanonymous
 */
function forumanonymous_rss_delete_file($forumanonymous) {
    rss_delete_file('mod_forumanonymous', $forumanonymous);
}

///////////////////////////////////////////////////////
//Utility functions

/**
 * If there is new stuff in the forumanonymous since $time this returns true
 * Otherwise it returns false.
 *
 * @param stdClass $forumanonymous the forumanonymous object
 * @param stdClass $cm    Course Module object
 * @param int      $time  check for items since this epoch timestamp
 * @return bool True for new items
 */
function forumanonymous_rss_newstuff($forumanonymous, $cm, $time) {
    global $DB;

    list($sql, $params) = forumanonymous_rss_get_sql($forumanonymous, $cm, $time);

    return $DB->record_exists_sql($sql, $params);
}

/**
 * Determines which type of SQL query is required, one for posts or one for discussions, and returns the appropriate query
 *
 * @param stdClass $forumanonymous the forumanonymous object
 * @param stdClass $cm    Course Module object
 * @param int      $time  check for items since this epoch timestamp
 * @return string the SQL query to be used to get the Discussion/Post details from the forumanonymous table of the database
 */
function forumanonymous_rss_get_sql($forumanonymous, $cm, $time=0) {
    if ($forumanonymous->rsstype == 1) { // Discussion RSS
        return forumanonymous_rss_feed_discussions_sql($forumanonymous, $cm, $time);
    } else { // Post RSS
        return forumanonymous_rss_feed_posts_sql($forumanonymous, $cm, $time);
    }
}

/**
 * Generates the SQL query used to get the Discussion details from the forumanonymous table of the database
 *
 * @param stdClass $forumanonymous     the forumanonymous object
 * @param stdClass $cm        Course Module object
 * @param int      $newsince  check for items since this epoch timestamp
 * @return string the SQL query to be used to get the Discussion details from the forumanonymous table of the database
 */
function forumanonymous_rss_feed_discussions_sql($forumanonymous, $cm, $newsince=0) {
    global $CFG, $DB, $USER;

    $timelimit = '';

    $modcontext = null;

    $now = round(time(), -2);
    $params = array();

    $modcontext = context_module::instance($cm->id);

    if (!empty($CFG->forumanonymous_enabletimedposts)) { /// Users must fulfill timed posts
        if (!has_capability('mod/forumanonymous:viewhiddentimedposts', $modcontext)) {
            $timelimit = " AND ((d.timestart <= :now1 AND (d.timeend = 0 OR d.timeend > :now2))";
            $params['now1'] = $now;
            $params['now2'] = $now;
            if (isloggedin()) {
                $timelimit .= " OR d.userid = :userid";
                $params['userid'] = $USER->id;
            }
            $timelimit .= ")";
        }
    }

    // Do we only want new posts?
    if ($newsince) {
        $params['newsince'] = $newsince;
        $newsince = " AND p.modified > :newsince";
    } else {
        $newsince = '';
    }

    // Get group enforcing SQL.
    $groupmode = groups_get_activity_groupmode($cm);
    $currentgroup = groups_get_activity_group($cm);
    list($groupselect, $groupparams) = forumanonymous_rss_get_group_sql($cm, $groupmode, $currentgroup, $modcontext);

    // Add the groupparams to the params array.
    $params = array_merge($params, $groupparams);

    $forumanonymoussort = "d.timemodified DESC";
    $postdata = "p.id AS postid, p.subject, p.created as postcreated, p.modified, p.discussion, p.userid, p.message as postmessage, p.messageformat AS postformat, p.messagetrust AS posttrust";

    $sql = "SELECT $postdata, d.id as discussionid, d.name as discussionname, d.timemodified, d.usermodified, d.groupid, d.timestart, d.timeend,
                   u.firstname as userfirstname, u.lastname as userlastname, u.email, u.picture, u.imagealt
              FROM {forumanonymous_discussions} d
                   JOIN {forumanonymous_posts} p ON p.discussion = d.id
                   JOIN {user} u ON p.userid = u.id
             WHERE d.forumanonymous = {$forumanonymous->id} AND p.parent = 0
                   $timelimit $groupselect $newsince
          ORDER BY $forumanonymoussort";
    return array($sql, $params);
}

/**
 * Generates the SQL query used to get the Post details from the forumanonymous table of the database
 *
 * @param stdClass $forumanonymous     the forumanonymous object
 * @param stdClass $cm        Course Module object
 * @param int      $newsince  check for items since this epoch timestamp
 * @return string the SQL query to be used to get the Post details from the forumanonymous table of the database
 */
function forumanonymous_rss_feed_posts_sql($forumanonymous, $cm, $newsince=0) {
    $modcontext = context_module::instance($cm->id);

    // Get group enforcement SQL.
    $groupmode = groups_get_activity_groupmode($cm);
    $currentgroup = groups_get_activity_group($cm);
    $params = array();

    list($groupselect, $groupparams) = forumanonymous_rss_get_group_sql($cm, $groupmode, $currentgroup, $modcontext);

    // Add the groupparams to the params array.
    $params = array_merge($params, $groupparams);

    // Do we only want new posts?
    if ($newsince) {
        $params['newsince'] = $newsince;
        $newsince = " AND p.modified > :newsince";
    } else {
        $newsince = '';
    }

    $sql = "SELECT p.id AS postid,
                 d.id AS discussionid,
                 d.name AS discussionname,
                 u.id AS userid,
                 u.firstname AS userfirstname,
                 u.lastname AS userlastname,
                 p.subject AS postsubject,
                 p.message AS postmessage,
                 p.created AS postcreated,
                 p.messageformat AS postformat,
                 p.messagetrust AS posttrust
            FROM {forumanonymous_discussions} d,
               {forumanonymous_posts} p,
               {user} u
            WHERE d.forumanonymous = {$forumanonymous->id} AND
                p.discussion = d.id AND
                u.id = p.userid $newsince
                $groupselect
            ORDER BY p.created desc";

    return array($sql, $params);
}

/**
 * Retrieve the correct SQL snippet for group-only forumanonymouss
 *
 * @param stdClass $cm           Course Module object
 * @param int      $groupmode    the mode in which the forumanonymous's groups are operating
 * @param bool     $currentgroup true if the user is from the a group enabled on the forumanonymous
 * @param stdClass $modcontext   The context instance of the forumanonymous module
 * @return string SQL Query for group details of the forumanonymous
 */
function forumanonymous_rss_get_group_sql($cm, $groupmode, $currentgroup, $modcontext=null) {
    $groupselect = '';
    $params = array();

    if ($groupmode) {
        if ($groupmode == VISIBLEGROUPS or has_capability('moodle/site:accessallgroups', $modcontext)) {
            if ($currentgroup) {
                $groupselect = "AND (d.groupid = :groupid OR d.groupid = -1)";
                $params['groupid'] = $currentgroup;
            }
        } else {
            // Separate groups without access all.
            if ($currentgroup) {
                $groupselect = "AND (d.groupid = :groupid OR d.groupid = -1)";
                $params['groupid'] = $currentgroup;
            } else {
                $groupselect = "AND d.groupid = -1";
            }
        }
    }

    return array($groupselect, $params);
}

/**
 * This function return the XML rss contents about the forumanonymous
 * It returns false if something is wrong
 *
 * @param stdClass $forumanonymous the forumanonymous object
 * @param string $sql the SQL used to retrieve the contents from the database
 * @param array $params the SQL parameters used
 * @param object $context the context this forumanonymous relates to
 * @return bool|string false if the contents is empty, otherwise the contents of the feed is returned
 *
 * @Todo MDL-31129 implement post attachment handling
 */

function forumanonymous_rss_feed_contents($forumanonymous, $sql, $params, $context) {
    global $CFG, $DB, $USER;

    $status = true;

    $recs = $DB->get_recordset_sql($sql, $params, 0, $forumanonymous->rssarticles);

    //set a flag. Are we displaying discussions or posts?
    $isdiscussion = true;
    if (!empty($forumanonymous->rsstype) && $forumanonymous->rsstype!=1) {
        $isdiscussion = false;
    }

    if (!$cm = get_coursemodule_from_instance('forumanonymous', $forumanonymous->id, $forumanonymous->course)) {
        print_error('invalidcoursemodule');
    }

    $formatoptions = new stdClass();
    $items = array();
    foreach ($recs as $rec) {
            $item = new stdClass();
            $user = new stdClass();

            if ($isdiscussion && !forumanonymous_user_can_see_discussion($forumanonymous, $rec->discussionid, $context)) {
                // This is a discussion which the user has no permission to view
                $item->title = get_string('forumanonymoussubjecthidden', 'forumanonymous');
                $message = get_string('forumanonymousbodyhidden', 'forumanonymous');
                $item->author = get_string('forumanonymousauthorhidden', 'forumanonymous');
            } else if (!$isdiscussion && !forumanonymous_user_can_see_post($forumanonymous, $rec->discussionid, $rec->postid, $USER, $cm)) {
                // This is a post which the user has no permission to view
                $item->title = get_string('forumanonymoussubjecthidden', 'forumanonymous');
                $message = get_string('forumanonymousbodyhidden', 'forumanonymous');
                $item->author = get_string('forumanonymousauthorhidden', 'forumanonymous');
            } else {
                // The user must have permission to view
                if ($isdiscussion && !empty($rec->discussionname)) {
                    $item->title = format_string($rec->discussionname);
                } else if (!empty($rec->postsubject)) {
                    $item->title = format_string($rec->postsubject);
                } else {
                    //we should have an item title by now but if we dont somehow then substitute something somewhat meaningful
                    $item->title = format_string($forumanonymous->name.' '.userdate($rec->postcreated,get_string('strftimedatetimeshort', 'langconfig')));
                }
                $user->firstname = $rec->userfirstname;
                $user->lastname = $rec->userlastname;
                $item->author = fullname($user);
                $message = file_rewrite_pluginfile_urls($rec->postmessage, 'pluginfile.php', $context->id,
                        'mod_forumanonymous', 'post', $rec->postid);
                $formatoptions->trusted = $rec->posttrust;
            }

            if ($isdiscussion) {
                $item->link = $CFG->wwwroot."/mod/forumanonymous/discuss.php?d=".$rec->discussionid;
            } else {
                $item->link = $CFG->wwwroot."/mod/forumanonymous/discuss.php?d=".$rec->discussionid."&parent=".$rec->postid;
            }

            $formatoptions->trusted = $rec->posttrust;
            $item->description = format_text($message, $rec->postformat, $formatoptions, $forumanonymous->course);

            //TODO: MDL-31129 implement post attachment handling
            /*if (!$isdiscussion) {
                $post_file_area_name = str_replace('//', '/', "$forumanonymous->course/$CFG->moddata/forumanonymous/$forumanonymous->id/$rec->postid");
                $post_files = get_directory_list("$CFG->dataroot/$post_file_area_name");

                if (!empty($post_files)) {
                    $item->attachments = array();
                }
            }*/
            $item->pubdate = $rec->postcreated;

            $items[] = $item;
        }
    $recs->close();

    // Create the RSS header.
    $header = rss_standard_header(strip_tags(format_string($forumanonymous->name,true)),
                                  $CFG->wwwroot."/mod/forumanonymous/view.php?f=".$forumanonymous->id,
                                  format_string($forumanonymous->intro,true)); // TODO: fix format
    // Now all the RSS items, if there are any.
    $articles = '';
    if (!empty($items)) {
        $articles = rss_add_items($items);
    }
    // Create the RSS footer.
    $footer = rss_standard_footer();

    return $header . $articles . $footer;
}
