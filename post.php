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
 * Edit and save a new post to a discussion
 *
 * @package mod-forumanonymous
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');
require_once($CFG->libdir.'/completionlib.php');

$reply   = optional_param('reply', 0, PARAM_INT);
$forumanonymous   = optional_param('forumanonymous', 0, PARAM_INT);
$edit    = optional_param('edit', 0, PARAM_INT);
$delete  = optional_param('delete', 0, PARAM_INT);
$prune   = optional_param('prune', 0, PARAM_INT);
$name    = optional_param('name', '', PARAM_CLEAN);
$confirm = optional_param('confirm', 0, PARAM_INT);
$groupid = optional_param('groupid', null, PARAM_INT);

$PAGE->set_url('/mod/forumanonymous/post.php', array(
        'reply' => $reply,
        'forumanonymous' => $forumanonymous,
        'edit'  => $edit,
        'delete'=> $delete,
        'prune' => $prune,
        'name'  => $name,
        'confirm'=>$confirm,
        'groupid'=>$groupid,
        ));
//these page_params will be passed as hidden variables later in the form.
$page_params = array('reply'=>$reply, 'forumanonymous'=>$forumanonymous, 'edit'=>$edit);

$sitecontext = context_system::instance();

if (!isloggedin() or isguestuser()) {

    if (!isloggedin() and !get_referer()) {
        // No referer+not logged in - probably coming in via email  See MDL-9052
        require_login();
    }

    if (!empty($forumanonymous)) {      // User is starting a new discussion in a forumanonymous
        if (! $forumanonymous = $DB->get_record('forumanonymous', array('id' => $forumanonymous))) {
            print_error('invalidforumanonymousid', 'forumanonymous');
        }
    } else if (!empty($reply)) {      // User is writing a new reply
        if (! $parent = forumanonymous_get_post_full($reply)) {
            print_error('invalidparentpostid', 'forumanonymous');
        }
        if (! $discussion = $DB->get_record('forumanonymous_discussions', array('id' => $parent->discussion))) {
            print_error('notpartofdiscussion', 'forumanonymous');
        }
        if (! $forumanonymous = $DB->get_record('forumanonymous', array('id' => $discussion->forumanonymous))) {
            print_error('invalidforumanonymousid');
        }
    }
    if (! $course = $DB->get_record('course', array('id' => $forumanonymous->course))) {
        print_error('invalidcourseid');
    }

    if (!$cm = get_coursemodule_from_instance('forumanonymous', $forumanonymous->id, $course->id)) { // For the logs
        print_error('invalidcoursemodule');
    } else {
        $modcontext = context_module::instance($cm->id);
    }

    $PAGE->set_cm($cm, $course, $forumanonymous);
    $PAGE->set_context($modcontext);
    $PAGE->set_title($course->shortname);
    $PAGE->set_heading($course->fullname);

    echo $OUTPUT->header();
    echo $OUTPUT->confirm(get_string('noguestpost', 'forumanonymous').'<br /><br />'.get_string('liketologin'), get_login_url(), get_referer(false));
    echo $OUTPUT->footer();
    exit;
}

require_login(0, false);   // Script is useless unless they're logged in

if (!empty($forumanonymous)) {      // User is starting a new discussion in a forumanonymous
    if (! $forumanonymous = $DB->get_record("forumanonymous", array("id" => $forumanonymous))) {
        print_error('invalidforumanonymousid', 'forumanonymous');
    }
    if (! $course = $DB->get_record("course", array("id" => $forumanonymous->course))) {
        print_error('invalidcourseid');
    }
    if (! $cm = get_coursemodule_from_instance("forumanonymous", $forumanonymous->id, $course->id)) {
        print_error("invalidcoursemodule");
    }

    $coursecontext = context_course::instance($course->id);

    if (! forumanonymous_user_can_post_discussion($forumanonymous, $groupid, -1, $cm)) {
        if (!isguestuser()) {
            if (!is_enrolled($coursecontext)) {
                if (enrol_selfenrol_available($course->id)) {
                    $SESSION->wantsurl = qualified_me();
                    $SESSION->enrolcancel = $_SERVER['HTTP_REFERER'];
                    redirect($CFG->wwwroot.'/enrol/index.php?id='.$course->id, get_string('youneedtoenrol'));
                }
            }
        }
        print_error('nopostforumanonymous', 'forumanonymous');
    }

    if (!$cm->visible and !has_capability('moodle/course:viewhiddenactivities', $coursecontext)) {
        print_error("activityiscurrentlyhidden");
    }

    if (isset($_SERVER["HTTP_REFERER"])) {
        $SESSION->fromurl = $_SERVER["HTTP_REFERER"];
    } else {
        $SESSION->fromurl = '';
    }


    // Load up the $post variable.

    $post = new stdClass();
    $post->course        = $course->id;
    $post->forumanonymous         = $forumanonymous->id;
    $post->discussion    = 0;           // ie discussion # not defined yet
    $post->parent        = 0;
    $post->subject       = '';
    //UDE-HACK
    if (has_capability('moodle/course:manageactivities', context_course::instance($forumanonymous->course) , $USER->id, false)){
	$post->userid  	= $USER->id;// übernehmen
    }else{
    /* get user-ID of Anonymous user*/
	$post->userid = $DB->get_record('config', array('name'=>'forumanonymous_anonid'), 'value')->value;
    }	
    //UDE-HACK Ende
    $post->message       = '';
    $post->messageformat = editors_get_preferred_format();
    $post->messagetrust  = 0;

    if (isset($groupid)) {
        $post->groupid = $groupid;
    } else {
        $post->groupid = groups_get_activity_group($cm);
    }

    forumanonymous_set_return();

} else if (!empty($reply)) {      // User is writing a new reply

    if (! $parent = forumanonymous_get_post_full($reply)) {
        print_error('invalidparentpostid', 'forumanonymous');
    }
    if (! $discussion = $DB->get_record("forumanonymous_discussions", array("id" => $parent->discussion))) {
        print_error('notpartofdiscussion', 'forumanonymous');
    }
    if (! $forumanonymous = $DB->get_record("forumanonymous", array("id" => $discussion->forumanonymous))) {
        print_error('invalidforumanonymousid', 'forumanonymous');
    }
    if (! $course = $DB->get_record("course", array("id" => $discussion->course))) {
        print_error('invalidcourseid');
    }
    if (! $cm = get_coursemodule_from_instance("forumanonymous", $forumanonymous->id, $course->id)) {
        print_error('invalidcoursemodule');
    }

    // Ensure lang, theme, etc. is set up properly. MDL-6926
    $PAGE->set_cm($cm, $course, $forumanonymous);

    $coursecontext = context_course::instance($course->id);
    $modcontext    = context_module::instance($cm->id);

    if (! forumanonymous_user_can_post($forumanonymous, $discussion, $USER, $cm, $course, $modcontext)) {
        if (!isguestuser()) {
            if (!is_enrolled($coursecontext)) {  // User is a guest here!
                $SESSION->wantsurl = qualified_me();
                $SESSION->enrolcancel = $_SERVER['HTTP_REFERER'];
                redirect($CFG->wwwroot.'/enrol/index.php?id='.$course->id, get_string('youneedtoenrol'));
            }
        }
        print_error('nopostforumanonymous', 'forumanonymous');
    }

    // Make sure user can post here
    if (isset($cm->groupmode) && empty($course->groupmodeforce)) {
        $groupmode =  $cm->groupmode;
    } else {
        $groupmode = $course->groupmode;
    }
    if ($groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $modcontext)) {
        if ($discussion->groupid == -1) {
            print_error('nopostforumanonymous', 'forumanonymous');
        } else {
            if (!groups_is_member($discussion->groupid)) {
                print_error('nopostforumanonymous', 'forumanonymous');
            }
        }
    }

    if (!$cm->visible and !has_capability('moodle/course:viewhiddenactivities', $coursecontext)) {
        print_error("activityiscurrentlyhidden");
    }

    // Load up the $post variable.

    $post = new stdClass();
    $post->course      = $course->id;
    $post->forumanonymous       = $forumanonymous->id;
    $post->discussion  = $parent->discussion;
    $post->parent      = $parent->id;
    $post->subject     = $parent->subject;
    //UDE-HACK
    if (has_capability('moodle/course:manageactivities', context_course::instance($forumanonymous->course) , $USER->id, false)){
	$post->userid  	= $USER->id;// übernehmen
    }else{
    /* get user-ID of Anonymous user*/
	$post->userid = $DB->get_record('config', array('name'=>'forumanonymous_anonid'), 'value')->value;
    }	
    //UDE-HACK Ende
    $post->message     = '';

    $post->groupid = ($discussion->groupid == -1) ? 0 : $discussion->groupid;

    $strre = get_string('re', 'forumanonymous');
    if (!(substr($post->subject, 0, strlen($strre)) == $strre)) {
        $post->subject = $strre.' '.$post->subject;
    }

    unset($SESSION->fromdiscussion);

} else if (!empty($edit)) {  // User is editing their own post

    if (! $post = forumanonymous_get_post_full($edit)) {
        print_error('invalidpostid', 'forumanonymous');
    }
    if ($post->parent) {
        if (! $parent = forumanonymous_get_post_full($post->parent)) {
            print_error('invalidparentpostid', 'forumanonymous');
        }
    }

    if (! $discussion = $DB->get_record("forumanonymous_discussions", array("id" => $post->discussion))) {
        print_error('notpartofdiscussion', 'forumanonymous');
    }
    if (! $forumanonymous = $DB->get_record("forumanonymous", array("id" => $discussion->forumanonymous))) {
        print_error('invalidforumanonymousid', 'forumanonymous');
    }
    if (! $course = $DB->get_record("course", array("id" => $discussion->course))) {
        print_error('invalidcourseid');
    }
    if (!$cm = get_coursemodule_from_instance("forumanonymous", $forumanonymous->id, $course->id)) {
        print_error('invalidcoursemodule');
    } else {
        $modcontext = context_module::instance($cm->id);
    }

    $PAGE->set_cm($cm, $course, $forumanonymous);

    if (!($forumanonymous->type == 'news' && !$post->parent && $discussion->timestart > time())) {
        if (((time() - $post->created) > $CFG->maxeditingtime) and
                    !has_capability('mod/forumanonymous:editanypost', $modcontext)) {
            print_error('maxtimehaspassed', 'forumanonymous', '', format_time($CFG->maxeditingtime));
        }
    }
    if (($post->userid <> $USER->id) and
                !has_capability('mod/forumanonymous:editanypost', $modcontext)) {
        print_error('cannoteditposts', 'forumanonymous');
    }


    // Load up the $post variable.
    $post->edit   = $edit;
    $post->course = $course->id;
    $post->forumanonymous  = $forumanonymous->id;
    $post->groupid = ($discussion->groupid == -1) ? 0 : $discussion->groupid;

    $post = trusttext_pre_edit($post, 'message', $modcontext);

    unset($SESSION->fromdiscussion);


}else if (!empty($delete)) {  // User is deleting a post

    if (! $post = forumanonymous_get_post_full($delete)) {
        print_error('invalidpostid', 'forumanonymous');
    }
    if (! $discussion = $DB->get_record("forumanonymous_discussions", array("id" => $post->discussion))) {
        print_error('notpartofdiscussion', 'forumanonymous');
    }
    if (! $forumanonymous = $DB->get_record("forumanonymous", array("id" => $discussion->forumanonymous))) {
        print_error('invalidforumanonymousid', 'forumanonymous');
    }
    if (!$cm = get_coursemodule_from_instance("forumanonymous", $forumanonymous->id, $forumanonymous->course)) {
        print_error('invalidcoursemodule');
    }
    if (!$course = $DB->get_record('course', array('id' => $forumanonymous->course))) {
        print_error('invalidcourseid');
    }

    require_login($course, false, $cm);
    $modcontext = context_module::instance($cm->id);

    if ( !(($post->userid == $USER->id && has_capability('mod/forumanonymous:deleteownpost', $modcontext))
                || has_capability('mod/forumanonymous:deleteanypost', $modcontext)) ) {
        print_error('cannotdeletepost', 'forumanonymous');
    }


    $replycount = forumanonymous_count_replies($post);

    if (!empty($confirm) && confirm_sesskey()) {    // User has confirmed the delete
        //check user capability to delete post.
        $timepassed = time() - $post->created;
        if (($timepassed > $CFG->maxeditingtime) && !has_capability('mod/forumanonymous:deleteanypost', $modcontext)) {
            print_error("cannotdeletepost", "forumanonymous",
                      forumanonymous_go_back_to("discuss.php?d=$post->discussion"));
        }

        if ($post->totalscore) {
            notice(get_string('couldnotdeleteratings', 'rating'),
                    forumanonymous_go_back_to("discuss.php?d=$post->discussion"));

        } else if ($replycount && !has_capability('mod/forumanonymous:deleteanypost', $modcontext)) {
            print_error("couldnotdeletereplies", "forumanonymous",
                    forumanonymous_go_back_to("discuss.php?d=$post->discussion"));

        } else {
            if (! $post->parent) {  // post is a discussion topic as well, so delete discussion
                if ($forumanonymous->type == 'single') {
                    notice("Sorry, but you are not allowed to delete that discussion!",
                            forumanonymous_go_back_to("discuss.php?d=$post->discussion"));
                }
                forumanonymous_delete_discussion($discussion, false, $course, $cm, $forumanonymous);

                //UDE-HACK add_to_log($discussion->course, "forumanonymous", "delete discussion",
                //           "view.php?id=$cm->id", "$forumanonymous->id", $cm->id);

                redirect("view.php?f=$discussion->forumanonymous");

            } else if (forumanonymous_delete_post($post, has_capability('mod/forumanonymous:deleteanypost', $modcontext),
                $course, $cm, $forumanonymous)) {

                if ($forumanonymous->type == 'single') {
                    // Single discussion forumanonymouss are an exception. We show
                    // the forumanonymous itself since it only has one discussion
                    // thread.
                    $discussionurl = "view.php?f=$forumanonymous->id";
                } else {
                    $discussionurl = "discuss.php?d=$post->discussion";
                }

                //UDE-HACK add_to_log($discussion->course, "forumanonymous", "delete post", $discussionurl, "$post->id", $cm->id);

                redirect(forumanonymous_go_back_to($discussionurl));
            } else {
                print_error('errorwhiledelete', 'forumanonymous');
            }
        }


    } else { // User just asked to delete something

        forumanonymous_set_return();
        $PAGE->navbar->add(get_string('delete', 'forumanonymous'));
        $PAGE->set_title($course->shortname);
        $PAGE->set_heading($course->fullname);

        if ($replycount) {
            if (!has_capability('mod/forumanonymous:deleteanypost', $modcontext)) {
                print_error("couldnotdeletereplies", "forumanonymous",
                      forumanonymous_go_back_to("discuss.php?d=$post->discussion"));
            }
            echo $OUTPUT->header();
            echo $OUTPUT->confirm(get_string("deletesureplural", "forumanonymous", $replycount+1),
                         "post.php?delete=$delete&confirm=$delete",
                         $CFG->wwwroot.'/mod/forumanonymous/discuss.php?d='.$post->discussion.'#p'.$post->id);

            forumanonymous_print_post($post, $discussion, $forumanonymous, $cm, $course, false, false, false);

            if (empty($post->edit)) {
                $forumanonymoustracked = forumanonymous_tp_is_tracked($forumanonymous);
                $posts = forumanonymous_get_all_discussion_posts($discussion->id, "created ASC", $forumanonymoustracked);
                forumanonymous_print_posts_nested($course, $cm, $forumanonymous, $discussion, $post, false, false, $forumanonymoustracked, $posts);
            }
        } else {
            echo $OUTPUT->header();
            echo $OUTPUT->confirm(get_string("deletesure", "forumanonymous", $replycount),
                         "post.php?delete=$delete&confirm=$delete",
                         $CFG->wwwroot.'/mod/forumanonymous/discuss.php?d='.$post->discussion.'#p'.$post->id);
            forumanonymous_print_post($post, $discussion, $forumanonymous, $cm, $course, false, false, false);
        }

    }
    echo $OUTPUT->footer();
    die;


} else if (!empty($prune)) {  // Pruning

    if (!$post = forumanonymous_get_post_full($prune)) {
        print_error('invalidpostid', 'forumanonymous');
    }
    if (!$discussion = $DB->get_record("forumanonymous_discussions", array("id" => $post->discussion))) {
        print_error('notpartofdiscussion', 'forumanonymous');
    }
    if (!$forumanonymous = $DB->get_record("forumanonymous", array("id" => $discussion->forumanonymous))) {
        print_error('invalidforumanonymousid', 'forumanonymous');
    }
    if ($forumanonymous->type == 'single') {
        print_error('cannotsplit', 'forumanonymous');
    }
    if (!$post->parent) {
        print_error('alreadyfirstpost', 'forumanonymous');
    }
    if (!$cm = get_coursemodule_from_instance("forumanonymous", $forumanonymous->id, $forumanonymous->course)) { // For the logs
        print_error('invalidcoursemodule');
    } else {
        $modcontext = context_module::instance($cm->id);
    }
    if (!has_capability('mod/forumanonymous:splitdiscussions', $modcontext)) {
        print_error('cannotsplit', 'forumanonymous');
    }

    if (!empty($name) && confirm_sesskey()) {    // User has confirmed the prune

	//UDE-HACK
	if (has_capability('moodle/course:manageactivities', context_course::instance($forumanonymous->course) , $USER->id, false)){
	    $discussion->userid  	= $USER->id;// übernehmen
	}else{
	/* get user-ID of Anonymous user*/
	    $discussion->userid = $DB->get_record('config', array('name'=>'forumanonymous_anonid'), 'value')->value;
	}	
	//UDE-HACK Ende
	
        $newdiscussion = new stdClass();
        $newdiscussion->course       = $discussion->course;
        $newdiscussion->forumanonymous        = $discussion->forumanonymous;
        $newdiscussion->name         = $name;
        $newdiscussion->firstpost    = $post->id;
        $newdiscussion->userid       = $discussion->userid;
        $newdiscussion->groupid      = $discussion->groupid;
        $newdiscussion->assessed     = $discussion->assessed;
        $newdiscussion->usermodified = $discussion->userid;
        $newdiscussion->timestart    = $discussion->timestart;
        $newdiscussion->timeend      = $discussion->timeend;

        $newid = $DB->insert_record('forumanonymous_discussions', $newdiscussion);

        $newpost = new stdClass();
        $newpost->id      = $post->id;
        $newpost->parent  = 0;
        $newpost->subject = $name;

        $DB->update_record("forumanonymous_posts", $newpost);

        forumanonymous_change_discussionid($post->id, $newid);

        // update last post in each discussion
        forumanonymous_discussion_update_last_post($discussion->id);
        forumanonymous_discussion_update_last_post($newid);

        //UDE-HACK add_to_log($discussion->course, "forumanonymous", "prune post",
        //               "discuss.php?d=$newid", "$post->id", $cm->id);

        redirect(forumanonymous_go_back_to("discuss.php?d=$newid"));

    } else { // User just asked to prune something

        $course = $DB->get_record('course', array('id' => $forumanonymous->course));

        $PAGE->set_cm($cm);
        $PAGE->set_context($modcontext);
        $PAGE->navbar->add(format_string($post->subject, true), new moodle_url('/mod/forumanonymous/discuss.php', array('d'=>$discussion->id)));
        $PAGE->navbar->add(get_string("prune", "forumanonymous"));
        $PAGE->set_title(format_string($discussion->name).": ".format_string($post->subject));
        $PAGE->set_heading($course->fullname);
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('pruneheading', 'forumanonymous'));
        echo '<center>';

        include('prune.html');

        forumanonymous_print_post($post, $discussion, $forumanonymous, $cm, $course, false, false, false);
        echo '</center>';
    }
    echo $OUTPUT->footer();
    die;
} else {
    print_error('unknowaction');

}

if (!isset($coursecontext)) {
    // Has not yet been set by post.php.
    $coursecontext = context_course::instance($forumanonymous->course);
}


// from now on user must be logged on properly

if (!$cm = get_coursemodule_from_instance('forumanonymous', $forumanonymous->id, $course->id)) { // For the logs
    print_error('invalidcoursemodule');
}
$modcontext = context_module::instance($cm->id);
require_login($course, false, $cm);

if (isguestuser()) {
    // just in case
    print_error('noguest');
}

if (!isset($forumanonymous->maxattachments)) {  // TODO - delete this once we add a field to the forumanonymous table
    $forumanonymous->maxattachments = 3;
}

require_once('post_form.php');

$mform_post = new mod_forumanonymous_post_form('post.php', array('course'=>$course, 'cm'=>$cm, 'coursecontext'=>$coursecontext, 'modcontext'=>$modcontext, 'forumanonymous'=>$forumanonymous, 'post'=>$post), 'post', '', array('id' => 'mformforumanonymous'));

$draftitemid = file_get_submitted_draft_itemid('attachments');
file_prepare_draft_area($draftitemid, $modcontext->id, 'mod_forumanonymous', 'attachment', empty($post->id)?null:$post->id, mod_forumanonymous_post_form::attachment_options($forumanonymous));

//load data into form NOW!

//UDE-HACK
// if ($USER->id != $post->userid) {   // Not the original author, so add a message to the end
//     $data = new stdClass();
//     $data->date = userdate($post->modified);
//     if ($post->messageformat == FORMAT_HTML) {
//         $data->name = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$USER->id.'&course='.$post->course.'">'.
//                        fullname($USER).'</a>';
//         $post->message .= '<p><span class="edited">('.get_string('editedby', 'forumanonymous', $data).')</span></p>';
//     } else {
//         $data->name = fullname($USER);
//         $post->message .= "\n\n(".get_string('editedby', 'forumanonymous', $data).')';
//     }
//     unset($data);
// }
//UDE-HACK Ende

$formheading = '';
if (!empty($parent)) {
    $heading = get_string("yourreply", "forumanonymous");
    $formheading = get_string('reply', 'forumanonymous');
} else {
    if ($forumanonymous->type == 'qanda') {
        $heading = get_string('yournewquestion', 'forumanonymous');
    } else {
        $heading = get_string('yournewtopic', 'forumanonymous');
    }
}

//UDE-HACK
// if (forumanonymous_is_subscribed($USER->id, $forumanonymous->id)) {
//     $subscribe = true;
// 
// } else if (forumanonymous_user_has_posted($forumanonymous->id, 0, $USER->id)) {
//     $subscribe = false;
// 
// } else {
//     // user not posted yet - use subscription default specified in profile
//     $subscribe = !empty($USER->autosubscribe);
// }
$subscribe = true;

if (has_capability('moodle/course:manageactivities', context_course::instance($forumanonymous->course), $USER->id, false)){
    $post->userid  	= $USER->id;// übernehmen
}else{
/* get user-ID of Anonymous user*/
    $post->userid = $DB->get_record('config', array('name'=>'forumanonymous_anonid'), 'value')->value;
}	
//UDE-HACK Ende

$draftid_editor = file_get_submitted_draft_itemid('message');
$currenttext = file_prepare_draft_area($draftid_editor, $modcontext->id, 'mod_forumanonymous', 'post', empty($post->id) ? null : $post->id, mod_forumanonymous_post_form::editor_options(), $post->message);
$mform_post->set_data(array(        'attachments'=>$draftitemid,
                                    'general'=>$heading,
                                    'subject'=>$post->subject,
                                    'message'=>array(
                                        'text'=>$currenttext,
                                        'format'=>empty($post->messageformat) ? editors_get_preferred_format() : $post->messageformat,
                                        'itemid'=>$draftid_editor
                                    ),
                                    'subscribe'=>$subscribe?1:0,
                                    'mailnow'=>!empty($post->mailnow),
                                    'userid'=>$post->userid,
                                    'parent'=>$post->parent,
                                    'discussion'=>$post->discussion,
                                    'course'=>$course->id) +
                                    $page_params +

                            (isset($post->format)?array(
                                    'format'=>$post->format):
                                array())+

                            (isset($discussion->timestart)?array(
                                    'timestart'=>$discussion->timestart):
                                array())+

                            (isset($discussion->timeend)?array(
                                    'timeend'=>$discussion->timeend):
                                array())+

                            (isset($post->groupid)?array(
                                    'groupid'=>$post->groupid):
                                array())+

                            (isset($discussion->id)?
                                    array('discussion'=>$discussion->id):
                                    array()));

if ($fromform = $mform_post->get_data()) {

    if (empty($SESSION->fromurl)) {
        $errordestination = "$CFG->wwwroot/mod/forumanonymous/view.php?f=$forumanonymous->id";
    } else {
        $errordestination = $SESSION->fromurl;
    }

    $fromform->itemid        = $fromform->message['itemid'];
    $fromform->messageformat = $fromform->message['format'];
    $fromform->message       = $fromform->message['text'];
    // WARNING: the $fromform->message array has been overwritten, do not use it anymore!
    $fromform->messagetrust  = trusttext_trusted($modcontext);

    $contextcheck = isset($fromform->groupinfo) && has_capability('mod/forumanonymous:movediscussions', $modcontext);

    if ($fromform->edit) {           // Updating a post
        unset($fromform->groupid);
        $fromform->id = $fromform->edit;
        $message = '';

        //fix for bug #4314
        if (!$realpost = $DB->get_record('forumanonymous_posts', array('id' => $fromform->id))) {
            $realpost = new stdClass();
            $realpost->userid = -1;
        }


        // if user has edit any post capability
        // or has either startnewdiscussion or reply capability and is editting own post
        // then he can proceed
        // MDL-7066
        if ( !(($realpost->userid == $USER->id && (has_capability('mod/forumanonymous:replypost', $modcontext)
                            || has_capability('mod/forumanonymous:startdiscussion', $modcontext))) ||
                            has_capability('mod/forumanonymous:editanypost', $modcontext)) ) {
            print_error('cannotupdatepost', 'forumanonymous');
        }

        // If the user has access to all groups and they are changing the group, then update the post.
        if ($contextcheck) {
            if (empty($fromform->groupinfo)) {
                $fromform->groupinfo = -1;
            }
            $DB->set_field('forumanonymous_discussions' ,'groupid' , $fromform->groupinfo, array('firstpost' => $fromform->id));
        }

        $updatepost = $fromform; //realpost
        $updatepost->forumanonymous = $forumanonymous->id;
        if (!forumanonymous_update_post($updatepost, $mform_post, $message)) {
            print_error("couldnotupdate", "forumanonymous", $errordestination);
        }

        // MDL-11818
        if (($forumanonymous->type == 'single') && ($updatepost->parent == '0')){ // updating first post of single discussion type -> updating forumanonymous intro
            $forumanonymous->intro = $updatepost->message;
            $forumanonymous->timemodified = time();
            $DB->update_record("forumanonymous", $forumanonymous);
        }

        $timemessage = 2;
        if (!empty($message)) { // if we're printing stuff about the file upload
            $timemessage = 4;
        }

        if ($realpost->userid == $USER->id) {
            $message .= '<br />'.get_string("postupdated", "forumanonymous");
        } else {
            $realuser = $DB->get_record('user', array('id' => $realpost->userid));
            $message .= '<br />'.get_string("editedpostupdated", "forumanonymous", fullname($realuser));
        }

        if ($subscribemessage = forumanonymous_post_subscription($fromform, $forumanonymous)) {
            $timemessage = 4;
        }
        if ($forumanonymous->type == 'single') {
            // Single discussion forumanonymouss are an exception. We show
            // the forumanonymous itself since it only has one discussion
            // thread.
            $discussionurl = "view.php?f=$forumanonymous->id";
        } else {
            $discussionurl = "discuss.php?d=$discussion->id#p$fromform->id";
        }
        //UDE-HACK add_to_log($course->id, "forumanonymous", "update post",
        //        "$discussionurl&amp;parent=$fromform->id", "$fromform->id", $cm->id);

        redirect(forumanonymous_go_back_to("$discussionurl"), $message.$subscribemessage, $timemessage);

        exit;


    } else if ($fromform->discussion) { // Adding a new post to an existing discussion
        unset($fromform->groupid);
        $message = '';
        $addpost = $fromform;
        $addpost->forumanonymous=$forumanonymous->id;
        
        //UDE-HACK
        if (has_capability('moodle/course:manageactivities', context_course::instance($forumanonymous->course), $USER->id, false)){
	    $addpost->userid        = $USER->id;// übernehmen
	    $noDisplay = false;
	}else{
	/* get user-ID of Anonymous user*/
	    $addpost->userid = $DB->get_record('config', array('name'=>'forumanonymous_anonid'), 'value')->value;
	    $noDisplay = true;
	}	
	//UDE-HACK Ende
        if ($fromform->id = forumanonymous_add_new_post($addpost, $mform_post, $message)) {

            $timemessage = 2;
            if (!empty($message)) { // if we're printing stuff about the file upload
                $timemessage = 4;
            }

            if ($subscribemessage = forumanonymous_post_subscription($fromform, $forumanonymous)) {
                $timemessage = 4;
            }

            if (!empty($fromform->mailnow)) {
                $message .= get_string("postmailnow", "forumanonymous");
                $timemessage = 4;
            } else {
                $message .= '<p>'.get_string("postaddedsuccess", "forumanonymous") . '</p>';
                $message .= '<p>'.get_string("postaddedtimeleft", "forumanonymous", format_time($CFG->maxeditingtime)) . '</p>';
            }

            if ($forumanonymous->type == 'single') {
                // Single discussion forumanonymouss are an exception. We show
                // the forumanonymous itself since it only has one discussion
                // thread.
                $discussionurl = "view.php?f=$forumanonymous->id";
            } else {
                $discussionurl = "discuss.php?d=$discussion->id";
            }
            //UDE-HACK add_to_log($course->id, "forumanonymous", "add post",
            //          "$discussionurl&amp;parent=$fromform->id", "$fromform->id", $cm->id);

            // Update completion state
            $completion=new completion_info($course);
            if($completion->is_enabled($cm) &&
                ($forumanonymous->completionreplies || $forumanonymous->completionposts)) {
                $completion->update_state($cm,COMPLETION_COMPLETE);
            }

            //UDE-HACK
            if( $noDisplay)
	      $timemessage = 0; //no email-messageformat
	    //UDE-HACK Ende
            redirect(forumanonymous_go_back_to("$discussionurl#p$fromform->id"), $message.$subscribemessage, $timemessage);

        } else {
            print_error("couldnotadd", "forumanonymous", $errordestination);
        }
        exit;

    } else {                     // Adding a new discussion
        if (!forumanonymous_user_can_post_discussion($forumanonymous, $fromform->groupid, -1, $cm, $modcontext)) {
            print_error('cannotcreatediscussion', 'forumanonymous');
        }
        // If the user has access all groups capability let them choose the group.
        if ($contextcheck) {
            $fromform->groupid = $fromform->groupinfo;
        }
        if (empty($fromform->groupid)) {
            $fromform->groupid = -1;
        }

        $fromform->mailnow = empty($fromform->mailnow) ? 0 : 1;

        $discussion = $fromform;
        $discussion->name    = $fromform->subject;

        $newstopic = false;
        if ($forumanonymous->type == 'news' && !$fromform->parent) {
            $newstopic = true;
        }
        $discussion->timestart = $fromform->timestart;
        $discussion->timeend = $fromform->timeend;
 
        $message = '';
        //UDE-HACK
        if (has_capability('moodle/course:manageactivities', context_course::instance($forumanonymous->course), $USER->id, false)){
	    $discussion->userid        = $USER->id;// übernehmen
	    $noDisplay = false;
	}else{
	/* get user-ID of Anonymous user*/
	    $discussion->userid = $DB->get_record('config', array('name'=>'forumanonymous_anonid'), 'value')->value;
	    $noDisplay = true;
	}	
	//UDE-HACK Ende
        if ($discussion->id = forumanonymous_add_discussion($discussion, $mform_post, $message)) {

            //UDE-HACK add_to_log($course->id, "forumanonymous", "add discussion",
            //        "discuss.php?d=$discussion->id", "$discussion->id", $cm->id);

            $timemessage = 2;
            if (!empty($message)) { // if we're printing stuff about the file upload
                $timemessage = 4;
            }

            if ($fromform->mailnow) {
                $message .= get_string("postmailnow", "forumanonymous");
                $timemessage = 4;
            } else {
                $message .= '<p>'.get_string("postaddedsuccess", "forumanonymous") . '</p>';
                $message .= '<p>'.get_string("postaddedtimeleft", "forumanonymous", format_time($CFG->maxeditingtime)) . '</p>';
            }

            if ($subscribemessage = forumanonymous_post_subscription($discussion, $forumanonymous)) {
                $timemessage = 4;
            }

            // Update completion status
            $completion=new completion_info($course);
            if($completion->is_enabled($cm) &&
                ($forumanonymous->completiondiscussions || $forumanonymous->completionposts)) {
                $completion->update_state($cm,COMPLETION_COMPLETE);
            }

            //UDE-HACK
            if( $noDisplay)
	      $timemessage = 0; //no email-messageformat
	    //UDE-HACK Ende
            redirect(forumanonymous_go_back_to("view.php?f=$fromform->forumanonymous"), $message.$subscribemessage, $timemessage);

        } else {
            print_error("couldnotadd", "forumanonymous", $errordestination);
        }

        exit;
    }
}



// To get here they need to edit a post, and the $post
// variable will be loaded with all the particulars,
// so bring up the form.

// $course, $forumanonymous are defined.  $discussion is for edit and reply only.

if ($post->discussion) {
    if (! $toppost = $DB->get_record("forumanonymous_posts", array("discussion" => $post->discussion, "parent" => 0))) {
        print_error('cannotfindparentpost', 'forumanonymous', '', $post->id);
    }
} else {
    $toppost = new stdClass();
    $toppost->subject = ($forumanonymous->type == "news") ? get_string("addanewtopic", "forumanonymous") :
                                                   get_string("addanewdiscussion", "forumanonymous");
}

if (empty($post->edit)) {
    $post->edit = '';
}

if (empty($discussion->name)) {
    if (empty($discussion)) {
        $discussion = new stdClass();
    }
    $discussion->name = $forumanonymous->name;
}
if ($forumanonymous->type == 'single') {
    // There is only one discussion thread for this forumanonymous type. We should
    // not show the discussion name (same as forumanonymous name in this case) in
    // the breadcrumbs.
    $strdiscussionname = '';
} else {
    // Show the discussion name in the breadcrumbs.
    $strdiscussionname = format_string($discussion->name).':';
}

$forcefocus = empty($reply) ? NULL : 'message';

if (!empty($discussion->id)) {
    $PAGE->navbar->add(format_string($toppost->subject, true), "discuss.php?d=$discussion->id");
}

if ($post->parent) {
    $PAGE->navbar->add(get_string('reply', 'forumanonymous'));
}

if ($edit) {
    $PAGE->navbar->add(get_string('edit', 'forumanonymous'));
}

$PAGE->set_title("$course->shortname: $strdiscussionname ".format_string($toppost->subject));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

// checkup
if (!empty($parent) && !forumanonymous_user_can_see_post($forumanonymous, $discussion, $post, null, $cm)) {
    print_error('cannotreply', 'forumanonymous');
}
if (empty($parent) && empty($edit) && !forumanonymous_user_can_post_discussion($forumanonymous, $groupid, -1, $cm, $modcontext)) {
    print_error('cannotcreatediscussion', 'forumanonymous');
}

if ($forumanonymous->type == 'qanda'
            && !has_capability('mod/forumanonymous:viewqandawithoutposting', $modcontext)
            && !empty($discussion->id)
            && !forumanonymous_user_has_posted($forumanonymous->id, $discussion->id, $USER->id)) {
    echo $OUTPUT->notification(get_string('qandanotify','forumanonymous'));
}

forumanonymous_check_throttling($forumanonymous, $cm);

if (!empty($parent)) {
    if (! $discussion = $DB->get_record('forumanonymous_discussions', array('id' => $parent->discussion))) {
        print_error('notpartofdiscussion', 'forumanonymous');
    }

    forumanonymous_print_post($parent, $discussion, $forumanonymous, $cm, $course, false, false, false);
    if (empty($post->edit)) {
        if ($forumanonymous->type != 'qanda' || forumanonymous_user_can_see_discussion($forumanonymous, $discussion, $modcontext)) {
            $forumanonymoustracked = forumanonymous_tp_is_tracked($forumanonymous);
            $posts = forumanonymous_get_all_discussion_posts($discussion->id, "created ASC", $forumanonymoustracked);
            forumanonymous_print_posts_threaded($course, $cm, $forumanonymous, $discussion, $parent, 0, false, $forumanonymoustracked, $posts);
        }
    }
} else {
    if (!empty($forumanonymous->intro)) {
        echo $OUTPUT->box(format_module_intro('forumanonymous', $forumanonymous, $cm->id), 'generalbox', 'intro');

        if (!empty($CFG->enableplagiarism)) {
            require_once($CFG->libdir.'/plagiarismlib.php');
            echo plagiarism_print_disclosure($cm->id);
        }
    }
}

if (!empty($formheading)) {
    echo $OUTPUT->heading($formheading, 2, array('class' => 'accesshide'));
}
$mform_post->display();

echo $OUTPUT->footer();

