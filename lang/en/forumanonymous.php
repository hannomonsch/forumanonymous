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
 * Strings for component 'forumanonymous', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   forumanonymous
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['anonuser'] = 'Name';
$string['configanonuser'] = 'Display name of the anonymous user.';

$string['activityoverview'] = 'There are new forum posts';
$string['addanewdiscussion'] = 'Add a new discussion topic';
$string['addanewquestion'] = 'Add a new question';
$string['addanewtopic'] = 'Add a new topic';
$string['advancedsearch'] = 'Advanced search';
$string['allforumanonymouss'] = 'All anonymous forums';
$string['allowdiscussions'] = 'Can a {$a} post to this forumanonymous?';
$string['allowsallsubscribe'] = 'This forumanonymous allows everyone to choose whether to subscribe or not';
$string['allowsdiscussions'] = 'This forumanonymous allows each person to start one discussion topic.';
$string['allsubscribe'] = 'Subscribe to all anonymous forums';
$string['allunsubscribe'] = 'Unsubscribe from all anonymous forums';
$string['alreadyfirstpost'] = 'This is already the first post in the discussion';
$string['anyfile'] = 'Any file';
$string['areaattachment'] = 'Attachments';
$string['areapost'] = 'Messages';
$string['attachment'] = 'Attachment';
$string['attachment_help'] = 'You can optionally attach one or more files to a forumanonymous post. If you attach an image, it will be displayed after the message.';
$string['attachmentnopost'] = 'You cannot export attachments without a post id';
$string['attachments'] = 'Attachments';
$string['blockafter'] = 'Post threshold for blocking';
$string['blockafter_help'] = 'This setting specifies the maximum number of posts which a user can post in the given time period. Users with the capability mod/forumanonymous:postwithoutthrottling are exempt from post limits.';
$string['blockperiod'] = 'Time period for blocking';
$string['blockperiod_help'] = 'Students can be blocked from posting more than a given number of posts in a given time period. Users with the capability mod/forumanonymous:postwithoutthrottling are exempt from post limits.';
$string['blockperioddisabled'] = 'Don\'t block';
$string['blogforumanonymous'] = 'Standard forumanonymous displayed in a blog-like format';
$string['bynameondate'] = 'by {$a->name} - {$a->date}';
$string['cannotadd'] = 'Could not add the discussion for this forumanonymous';
$string['cannotadddiscussion'] = 'Adding discussions to this forumanonymous requires group membership.';
$string['cannotadddiscussionall'] = 'You do not have permission to add a new discussion topic for all participants.';
$string['cannotaddsubscriber'] = 'Could not add subscriber with id {$a} to this forumanonymous!';
$string['cannotaddteacherforumanonymousto'] = 'Could not add converted teacher forumanonymous instance to section 0 in the course';
$string['cannotcreatediscussion'] = 'Could not create new discussion';
$string['cannotcreateinstanceforteacher'] = 'Could not create new course module instance for the teacher forumanonymous';
$string['cannotdeleteforumanonymousmodule'] = 'You can not delete the forumanonymous module.';
$string['cannotdeletepost'] = 'You can\'t delete this post!';
$string['cannoteditposts'] = 'You can\'t edit other people\'s posts!';
$string['cannotfinddiscussion'] = 'Could not find the discussion in this forumanonymous';
$string['cannotfindfirstpost'] = 'Could not find the first post in this forumanonymous';
$string['cannotfindorcreateforumanonymous'] = 'Could not find or create a main news forumanonymous for the site';
$string['cannotfindparentpost'] = 'Could not find top parent of post {$a}';
$string['cannotmovefromsingleforumanonymous'] = 'Cannot move discussion from a simple single discussion forumanonymous';
$string['cannotmovenotvisible'] = 'Forum not visible';
$string['cannotmovetonotexist'] = 'You can\'t move to that forumanonymous - it doesn\'t exist!';
$string['cannotmovetonotfound'] = 'Target forumanonymous not found in this course.';
$string['cannotpurgecachedrss'] = 'Could not purge the cached RSS feeds for the source and/or destination forumanonymous(s) - check your file permissions anonymous forums';
$string['cannotremovesubscriber'] = 'Could not remove subscriber with id {$a} from this forumanonymous!';
$string['cannotreply'] = 'You cannot reply to this post';
$string['cannotsplit'] = 'Discussions from this forumanonymous cannot be split';
$string['cannotsubscribe'] = 'Sorry, but you must be a group member to subscribe.';
$string['cannottrack'] = 'Could not stop tracking that forumanonymous';
$string['cannotunsubscribe'] = 'Could not unsubscribe you from that forumanonymous';
$string['cannotupdatepost'] = 'You can not update this post';
$string['cannotviewpostyet'] = 'You cannot read other students questions in this discussion yet because you haven\'t posted';
$string['cannotviewusersposts'] = 'There are no posts made by this user that you are able to view.';
$string['cleanreadtime'] = 'Mark old posts as read hour';
$string['completiondiscussions'] = 'Student must create discussions:';
$string['completiondiscussionsgroup'] = 'Require discussions';
$string['completiondiscussionshelp'] = 'requiring discussions to complete';
$string['completionposts'] = 'Student must post discussions or replies:';
$string['completionpostsgroup'] = 'Require posts';
$string['completionpostshelp'] = 'requiring discussions or replies to complete';
$string['completionreplies'] = 'Student must post replies:';
$string['completionrepliesgroup'] = 'Require replies';
$string['completionreplieshelp'] = 'requiring replies to complete';
$string['configcleanreadtime'] = 'The hour of the day to clean old posts from the \'read\' table.';
$string['configdigestmailtime'] = 'People who choose to have emails sent to them in digest form will be emailed the digest daily. This setting controls which time of day the daily mail will be sent (the next cron that runs after this hour will send it).';
$string['configdisplaymode'] = 'The default display mode for discussions if one isn\'t set.';
$string['configenablerssfeeds'] = 'This switch will enable the possibility of RSS feeds for all anonymous forums.  You will still need to turn feeds on manually in the settings for each forumanonymous.';
$string['configenabletimedposts'] = 'Set to \'yes\' if you want to allow setting of display periods when posting a new forumanonymous discussion (Experimental as not yet fully tested)';
$string['configlongpost'] = 'Any post over this length (in characters not including HTML) is considered long. Posts displayed on the site front page, social format course pages, or user profiles are shortened to a natural break somewhere between the forumanonymous_shortpost and forumanonymous_longpost values.';
$string['configmanydiscussions'] = 'Maximum number of discussions shown in a forumanonymous per page';
$string['configmaxattachments'] = 'Default maximum number of attachments allowed per post.';
$string['configmaxbytes'] = 'Default maximum size for all forumanonymous attachments on the site (subject to course limits and other local settings)';
$string['configoldpostdays'] = 'Number of days old any post is considered read.';
$string['configreplytouser'] = 'When a forumanonymous post is mailed out, should it contain the user\'s email address so that recipients can reply personally rather than via the forumanonymous? Even if set to \'Yes\' users can choose in their profile to keep their email address secret.';
$string['configshortpost'] = 'Any post under this length (in characters not including HTML) is considered short (see below).';
$string['configtrackreadposts'] = 'Set to \'yes\' if you want to track read/unread for each user.';
$string['configusermarksread'] = 'If \'yes\', the user must manually mark a post as read. If \'no\', when the post is viewed it is marked as read.';
$string['confirmsubscribe'] = 'Do you really want to subscribe to forumanonymous \'{$a}\'?';
$string['confirmunsubscribe'] = 'Do you really want to unsubscribe from forumanonymous \'{$a}\'?';
$string['couldnotadd'] = 'Could not add your post due to an unknown error';
$string['couldnotdeletereplies'] = 'Sorry, that cannot be deleted as people have already responded to it';
$string['couldnotupdate'] = 'Could not update your post due to an unknown error';
$string['delete'] = 'Delete';
$string['deleteddiscussion'] = 'The discussion topic has been deleted';
$string['deletedpost'] = 'The post has been deleted';
$string['deletedposts'] = 'Those posts have been deleted';
$string['deletesure'] = 'Are you sure you want to delete this post?';
$string['deletesureplural'] = 'Are you sure you want to delete this post and all replies? ({$a} posts)';
$string['digestmailheader'] = 'This is your daily digest of new posts from the {$a->sitename} anonymous forum. To change your forumanonymous email preferences, go to {$a->userprefs}.';
$string['digestmailprefs'] = 'your user profile';
$string['digestmailsubject'] = '{$a}: forumanonymous digest';
$string['digestmailtime'] = 'Hour to send digest emails';
$string['digestsentusers'] = 'Email digests successfully sent to {$a} users.';
$string['disallowsubscribe'] = 'Subscriptions not allowed';
$string['disallowsubscribeteacher'] = 'Subscriptions not allowed (except for teachers)';
$string['discussion'] = 'Discussion';
$string['discussionmoved'] = 'This discussion has been moved to \'{$a}\'.';
$string['discussionmovedpost'] = 'This discussion has been moved to <a href="{$a->discusshref}">here</a> in the forumanonymous <a href="{$a->forumanonymoushref}">{$a->forumanonymousname}</a>';
$string['discussionname'] = 'Discussion name';
$string['discussions'] = 'Discussions';
$string['discussionsstartedby'] = 'Discussions started by {$a}';
$string['discussionsstartedbyrecent'] = 'Discussions recently started by {$a}';
$string['discussionsstartedbyuserincourse'] = 'Discussions started by {$a->fullname} in {$a->coursename}';
$string['discussthistopic'] = 'Discuss this topic';
$string['displayend'] = 'Display end';
$string['displayend_help'] = 'This setting specifies whether a forumanonymous post should be hidden after a certain date. Note that administrators can always view forumanonymous posts.';
$string['displaymode'] = 'Display mode';
$string['displayperiod'] = 'Display period';
$string['displaystart'] = 'Display start';
$string['displaystart_help'] = 'This setting specifies whether a forumanonymous post should be displayed from a certain date. Note that administrators can always view forumanonymous posts.';
$string['eachuserforumanonymous'] = 'Each person posts one discussion';
$string['edit'] = 'Edit';
$string['editedby'] = 'Edited by {$a->name} - original submission {$a->date}';
$string['editedpostupdated'] = '{$a}\'s post was updated';
$string['editing'] = 'Editing';
$string['emptymessage'] = 'Something was wrong with your post. Perhaps you left it blank, or the attachment was too big. Your changes have NOT been saved.';
$string['erroremptymessage'] = 'Post message cannot be empty';
$string['erroremptysubject'] = 'Post subject cannot be empty.';
$string['errorenrolmentrequired'] = 'You must be enrolled in this course to access this content';
$string['errorwhiledelete'] = 'An error occurred while deleting record.';
$string['everyonecanchoose'] = 'Everyone can choose to be subscribed';
$string['everyonecannowchoose'] = 'Everyone can now choose to be subscribed';
$string['everyoneisnowsubscribed'] = 'Everyone is now subscribed to this forumanonymous';
$string['everyoneissubscribed'] = 'Everyone is subscribed to this forumanonymous';
$string['existingsubscribers'] = 'Existing subscribers';
$string['exportdiscussion'] = 'Export whole discussion';
$string['forcessubscribe'] = 'This forumanonymous forces everyone to be subscribed';
$string['forumanonymous'] = 'Forum anonymous';
$string['forumanonymous:addnews'] = 'Add news';
$string['forumanonymous:addinstance'] = 'Add a new forum';
$string['forumanonymous:addquestion'] = 'Add question';
$string['forumanonymous:allowforcesubscribe'] = 'Allow force subscribe';
$string['forumanonymousauthorhidden'] = 'Author (hidden)';
$string['forumanonymousblockingalmosttoomanyposts'] = 'You are approaching the posting threshold. You have posted {$a->numposts} times in the last {$a->blockperiod} and the limit is {$a->blockafter} posts.';
$string['forumbodyhidden'] = 'This post cannot be viewed by you, probably because you have not posted in the discussion, the maximum editing time hasn\'t passed yet, the discussion has not started or the discussion has expired.';
$string['forumanonymousbodyhidden'] = 'This post cannot be viewed by you, probably because you have not posted in the discussion or the maximum editing time hasn\'t passed yet.';
$string['forumanonymous:createattachment'] = 'Create attachments';
$string['forumanonymous:deleteanypost'] = 'Delete any posts (anytime)';
$string['forumanonymous:deleteownpost'] = 'Delete own posts (within deadline)';
$string['forumanonymous:editanypost'] = 'Edit any post';
$string['forumanonymous:exportdiscussion'] = 'Export whole discussion';
$string['forumanonymous:exportownpost'] = 'Export own post';
$string['forumanonymous:exportpost'] = 'Export post';
$string['forumanonymousintro'] = 'Forum introduction';
$string['forumanonymous:managesubscriptions'] = 'Manage subscriptions';
$string['forumanonymous:movediscussions'] = 'Move discussions';
$string['forumanonymous:postwithoutthrottling'] = 'Exempt from post threshold';
$string['forumanonymousname'] = 'Forum name';
$string['forumanonymousposts'] = 'Forum posts';
$string['forumanonymous:rate'] = 'Rate posts';
$string['forumanonymous:replynews'] = 'Reply to news';
$string['forumanonymous:replypost'] = 'Reply to posts';
$string['forumanonymouss'] = 'Anonymous Forums';
$string['forumanonymous:splitdiscussions'] = 'Split discussions';
$string['forumanonymous:startdiscussion'] = 'Start new discussions';
$string['forumanonymoussubjecthidden'] = 'Subject (hidden)';
$string['forumanonymoustracked'] = 'Unread posts are being tracked';
$string['forumanonymoustrackednot'] = 'Unread posts are not being tracked';
$string['forumanonymoustype'] = 'Forum type';
$string['forumanonymoustype_help'] = 'There are 5 forumanonymous types:

* A single simple discussion - A single discussion topic which everyone can reply to
* Each person posts one discussion - Each student can post exactly one new discussion topic, which everyone can then reply to
* Q and A forumanonymous - Students must first post their perspectives before viewing other students\' posts
* Standard forumanonymous displayed in a blog-like format - An open forumanonymous where anyone can start a new discussion at any time, and in which discussion topics are displayed on one page with "Discuss this topic" links
* Standard forumanonymous for general use - An open forumanonymous where anyone can start a new discussion at any time';
$string['forumanonymous:viewallratings'] = 'View all raw ratings given by individuals';
$string['forumanonymous:viewanyrating'] = 'View total ratings that anyone received';
$string['forumanonymous:viewdiscussion'] = 'View discussions';
$string['forumanonymous:viewhiddentimedposts'] = 'View hidden timed posts';
$string['forumanonymous:viewqandawithoutposting'] = 'Always see Q and A posts';
$string['forumanonymous:viewrating'] = 'View the total rating you received';
$string['forumanonymous:viewsubscribers'] = 'View subscribers';
$string['forumanonymous:addinstance'] = 'Add a new anonymous forum';
$string['forumanonymous:myaddinstance'] = 'Add a new anonymous forum to the My Moodle page';
$string['generalforumanonymous'] = 'Standard forumanonymous for general use';
$string['generalforumanonymouss'] = 'General anonymous forums';
$string['inforumanonymous'] = 'in {$a}';
$string['introblog'] = 'The posts in this forumanonymous were copied here automatically from blogs of users in this course because those blog entries are no longer available';
$string['intronews'] = 'General news and announcements';
$string['introsocial'] = 'An open forumanonymous for chatting about anything you want to';
$string['introteacher'] = 'A forumanonymous for teacher-only notes and discussion';
$string['invalidaccess'] = 'This page was not accessed correctly';
$string['invaliddiscussionid'] = 'Discussion ID was incorrect or no longer exists';
$string['invalidforcesubscribe'] = 'Invalid force subscription mode';
$string['invalidforumanonymousid'] = 'Forum ID was incorrect';
$string['invalidparentpostid'] = 'Parent post ID was incorrect';
$string['invalidpostid'] = 'Invalid post ID - {$a}';
$string['lastpost'] = 'Last post';
$string['learningforumanonymouss'] = 'Learning anonymous forums';
$string['longpost'] = 'Long post';
$string['mailnow'] = 'Mail now';
$string['manydiscussions'] = 'Discussions per page';
$string['markalldread'] = 'Mark all posts in this discussion read.';
$string['markallread'] = 'Mark all posts in this forumanonymous read.';
$string['markread'] = 'Mark read';
$string['markreadbutton'] = 'Mark<br />read';
$string['markunread'] = 'Mark unread';
$string['markunreadbutton'] = 'Mark<br />unread';
$string['maxattachments'] = 'Maximum number of attachments';
$string['maxattachments_help'] = 'This setting specifies the maximum number of files that can be attached to a forumanonymous post.';
$string['maxattachmentsize'] = 'Maximum attachment size';
$string['maxattachmentsize_help'] = 'This setting specifies the largest size of file that can be attached to a forumanonymous post.';
$string['maxtimehaspassed'] = 'Sorry, but the maximum time for editing this post ({$a}) has passed!';
$string['message'] = 'Message';
$string['messageprovider:digests'] = 'Subscribed forumanonymous digests';
$string['messageprovider:posts'] = 'Subscribed forumanonymous posts';
$string['missingsearchterms'] = 'The following search terms occur only in the HTML markup of this message:';
$string['modeflatnewestfirst'] = 'Display replies flat, with newest first';
$string['modeflatoldestfirst'] = 'Display replies flat, with oldest first';
$string['modenested'] = 'Display replies in nested form';
$string['modethreaded'] = 'Display replies in threaded form';
$string['modulename'] = 'Forum anonymous';
$string['modulename_help'] = 'The forum activity module enables participants to have asynchronous discussions i.e. discussions that take place over an extended period of time.

There are several forum types to choose from, such as a standard forum where anyone can start a new discussion at any time; a forum where each student can post exactly one discussion; or a question and answer forum where students must first post before being able to view other students\' posts. A teacher can allow files to be attached to forum posts. Attached images are displayed in the forum post.

Participants can subscribe to a forum to receive notifications of new forum posts. A teacher can set the subscription mode to optional, forced or auto, or prevent subscription completely. If required, students can be blocked from posting more than a given number of posts in a given time period; this can prevent individuals from dominating discussions.

Forum posts can be rated by teachers or students (peer evaluation). Ratings can be aggregated to form a final grade which is recorded in the gradebook.

Forums have many uses, such as

* A social space for students to get to know each other
* For course announcements (using a news forum with forced subscription)
* For discussing course content or reading materials
* For continuing online an issue raised previously in a face-to-face session
* For teacher-only discussions (using a hidden forum)
* A help centre where tutors and students can give advice
* A one-on-one support area for private student-teacher communications (using a forum with separate groups and with one student per group)
* For extension activities, for example ‘brain teasers’ for students to ponder and suggest solutions to';
$string['modulename_link'] = 'mod/forum/view';
$string['modulenameplural'] = 'Anonymous Forums';
$string['more'] = 'more';
$string['movedmarker'] = '(Moved)';
$string['movethisdiscussionto'] = 'Move this discussion to ...';
$string['mustprovidediscussionorpost'] = 'You must provide either a discussion id or post id to export';
$string['namenews'] = 'News forumanonymous';
$string['namenews_help'] = 'The news forumanonymous is a special forumanonymous for announcements that is automatically created when a course is created. A course can have only one news forumanonymous. Only teachers and administrators can post in the news forumanonymous. The "Latest news" block will display recent discussions from the news forumanonymous.';
$string['namesocial'] = 'Social forumanonymous';
$string['nameteacher'] = 'Teacher forumanonymous';
$string['newforumanonymousposts'] = 'New forumanonymous posts';
$string['noattachments'] = 'There are no attachments to this post';
$string['nodiscussions'] = 'There are no discussion topics yet in this forumanonymous';
$string['nodiscussionsstartedby'] = '{$a} has not started any discussions';
$string['nodiscussionsstartedbyyou'] = 'You haven\'t started any discussions yet';
$string['noguestpost'] = 'Sorry, guests are not allowed to post.';
$string['noguesttracking'] = 'Sorry, guests are not allowed to set tracking options.';
$string['nomorepostscontaining'] = 'No more posts containing \'{$a}\' were found';
$string['nonews'] = 'No news has been posted yet';
$string['noonecansubscribenow'] = 'Subscriptions are now disallowed';
$string['nopermissiontosubscribe'] = 'You do not have the permission to view forumanonymous subscribers';
$string['nopermissiontoview'] = 'You do not have permissions to view this post';
$string['nopostforumanonymous'] = 'Sorry, you are not allowed to post to this forumanonymous';
$string['noposts'] = 'No posts';
$string['nopostsmadebyuser'] = '{$a} has made no posts';
$string['nopostsmadebyyou'] = 'You haven\'t made any posts';
$string['nopostscontaining'] = 'No posts containing \'{$a}\' were found';
$string['noquestions'] = 'There are no questions yet in this forumanonymous';
$string['nosubscribers'] = 'There are no subscribers yet for this forumanonymous';
$string['notexists'] = 'Discussion no longer exists';
$string['nothingnew'] = 'Nothing new for {$a}';
$string['notingroup'] = 'Sorry, but you need to be part of a group to see this forumanonymous.';
$string['notinstalled'] = 'The forumanonymous module is not installed';
$string['notpartofdiscussion'] = 'This post is not part of a discussion!';
$string['notrackforumanonymous'] = 'Don\'t track unread posts';
$string['noviewdiscussionspermission'] = 'You do not have the permission to view discussions in this forumanonymous';
$string['nowallsubscribed'] = 'All anonymous forums in {$a} are subscribed.';
$string['nowallunsubscribed'] = 'All anonymous forums in {$a} are not subscribed.';
$string['nownotsubscribed'] = '{$a->name} will NOT be notified of new posts in \'{$a->forum}\'';
$string['nownottracking'] = '{$a->name} is no longer tracking \'{$a->forum}\'.';
$string['nowsubscribed'] = '{$a->name} will be notified of new posts in \'{$a->forum}\'';
$string['nowtracking'] = '{$a->name} is now tracking \'{$a->forumanonymous}\'.';
$string['numposts'] = '{$a} posts';
$string['olderdiscussions'] = 'Older discussions';
$string['oldertopics'] = 'Older topics';
$string['oldpostdays'] = 'Read after days';
$string['openmode0'] = 'No discussions, no replies';
$string['openmode1'] = 'No discussions, but replies are allowed';
$string['openmode2'] = 'Discussions and replies are allowed';
$string['overviewnumpostssince'] = '{$a} posts since last login';
$string['overviewnumunread'] = '{$a} total unread';
$string['page-mod-forumanonymous-x'] = 'Any forumanonymous module page';
$string['page-mod-forumanonymous-view'] = 'Forum module main page';
$string['page-mod-forumanonymous-discuss'] = 'Forum module discussion thread page';
$string['parent'] = 'Show parent';
$string['parentofthispost'] = 'Parent of this post';
$string['pluginadministration'] = 'Forum administration';
$string['pluginname'] = 'Forum anonymous';
$string['postadded'] = '<p>Your post was successfully added.</p> <p>You have {$a} to edit it if you want to make any changes.</p>';
$string['postaddedsuccess'] = 'Your post was successfully added.';
$string['postaddedtimeleft'] = 'You have {$a} to edit it if you want to make any changes.';
$string['postincontext'] = 'See this post in context';
$string['postmailinfo'] = 'This is a copy of a message posted on the {$a} website.

To reply click on this link:';
$string['postmailnow'] = '<p>This post will be mailed out immediately to all forumanonymous subscribers.</p>';
$string['postrating1'] = 'Mostly separate knowing';
$string['postrating2'] = 'Separate and connected';
$string['postrating3'] = 'Mostly connected knowing';
$string['posts'] = 'Posts';
$string['postsmadebyuser'] = 'Posts made by {$a}';
$string['postsmadebyuserincourse'] = 'Posts made by {$a->fullname} in {$a->coursename}';
$string['posttoforumanonymous'] = 'Post to forumanonymous';
$string['postupdated'] = 'Your post was updated';
$string['potentialsubscribers'] = 'Potential subscribers';
$string['processingdigest'] = 'Processing email digest for user {$a}';
$string['processingpost'] = 'Processing post {$a}';
$string['prune'] = 'Split';
$string['prunedpost'] = 'A new discussion has been created from that post';
$string['pruneheading'] = 'Split the discussion and move this post to a new discussion';
$string['qandaforumanonymous'] = 'Q and A forumanonymous';
$string['qandanotify'] = 'This is a question and answer forumanonymous. In order to see other responses to these questions, you must first post your answer';
$string['re'] = 'Re:';
$string['readtherest'] = 'Read the rest of this topic';
$string['replies'] = 'Replies';
$string['repliesmany'] = '{$a} replies so far';
$string['repliesone'] = '{$a} reply so far';
$string['reply'] = 'Reply';
$string['replyforumanonymous'] = 'Reply to forumanonymous';
$string['replytouser'] = 'Use email address in reply';
$string['resetforumanonymouss'] = 'Delete posts from';
$string['resetforumanonymoussall'] = 'Delete all posts';
$string['resetsubscriptions'] = 'Delete all forumanonymous subscriptions';
$string['resettrackprefs'] = 'Delete all forumanonymous tracking preferences';
$string['rsssubscriberssdiscussions'] = 'RSS feed of discussions';
$string['rsssubscriberssposts'] = 'RSS feed of posts';
$string['rssarticles'] = 'Number of RSS recent articles';
$string['rssarticles_help'] = 'This setting specifies the number of articles (either discussions or posts) to include in the RSS feed. Between 5 and 20 generally acceptable.';
$string['rsstype'] = 'RSS feed for this activity';
$string['rsstype_help'] = 'To enable the RSS feed for this activity, select either discussions or posts to be included in the feed.';
$string['search'] = 'Search';
$string['searchdatefrom'] = 'Posts must be newer than this';
$string['searchdateto'] = 'Posts must be older than this';
$string['searchforumanonymousintro'] = 'Please enter search terms into one or more of the following fields:';
$string['searchforumanonymouss'] = 'Search anonymous forums';
$string['searchfullwords'] = 'These words should appear as whole words';
$string['searchnotwords'] = 'These words should NOT be included';
$string['searcholderposts'] = 'Search older posts...';
$string['searchphrase'] = 'This exact phrase must appear in the post';
$string['searchresults'] = 'Search results';
$string['searchsubject'] = 'These words should be in the subject';
$string['searchuser'] = 'This name should match the author';
$string['searchuserid'] = 'The Moodle ID of the author';
$string['searchwhichforumanonymouss'] = 'Choose which anonymous forums to search';
$string['searchwords'] = 'These words can appear anywhere in the post';
$string['seeallposts'] = 'See all posts made by this user';
$string['shortpost'] = 'Short post';
$string['showsubscribers'] = 'Show/edit current subscribers';
$string['singleforumanonymous'] = 'A single simple discussion';
$string['smallmessage'] = '{$a->user} posted in {$a->forumanonymousname}';
$string['startedby'] = 'Started by';
$string['subject'] = 'Subject';
$string['subscribe'] = 'Subscribe to this forumanonymous';
$string['subscribeall'] = 'Subscribe everyone to this forumanonymous';
$string['subscribeenrolledonly'] = 'Sorry, only enrolled users are allowed to subscribe to forum post notifications.';
$string['subscribed'] = 'Subscribed';
$string['subscribenone'] = 'Unsubscribe everyone from this forumanonymous';
$string['subscribers'] = 'Subscribers';
$string['subscribersto'] = 'Subscribers to \'{$a}\'';
$string['subscribestart'] = 'Send me email copies of posts to this forumanonymous';
$string['subscribestop'] = 'I don\'t want email copies of posts to this forumanonymous';
$string['subscription'] = 'Subscription';
$string['subscription_help'] = 'If you are subscribed to a forumanonymous it means you will receive email copies of forumanonymous posts. Usually you can choose whether you wish to be subscribed, though sometimes subscription is forced so that everyone receives email copies of forumanonymous posts.';
$string['subscriptionmode'] = 'Subscription mode';
$string['subscriptionmode_help'] = 'When a participant is subscribed to a forumanonymous it means they will receive email copies of forumanonymous posts.

There are 4 subscription mode options:

* Optional subscription - Participants can choose whether to be subscribed
* Forced subscription - Everyone is subscribed and cannot unsubscribe
* Auto subscription - Everyone is subscribed initially but can choose to unsubscribe at any time
* Subscription disabled - Subscriptions are not allowed';
$string['subscriptionoptional'] = 'Optional subscription';
$string['subscriptionforced'] = 'Forced subscription';
$string['subscriptionauto'] = 'Auto subscription';
$string['subscriptiondisabled'] = 'Subscription disabled';
$string['subscriptions'] = 'Subscriptions';
$string['thisforumanonymousisthrottled'] = 'This forumanonymous has a limit to the number of forumanonymous postings you can make in a given time period - this is currently set at {$a->blockafter} posting(s) in {$a->blockperiod}';
$string['timedposts'] = 'Timed posts';
$string['timestartenderror'] = 'Display end date cannot be earlier than the start date';
$string['trackforumanonymous'] = 'Track unread posts';
$string['tracking'] = 'Track';
$string['trackingoff'] = 'Off';
$string['trackingon'] = 'On';
$string['trackingoptional'] = 'Optional';
$string['trackingtype'] = 'Read tracking for this forumanonymous?';
$string['trackingtype_help'] = 'If enabled, participants can track read and unread messages in the forumanonymous and in discussions.

There are three options:

* Optional - Participants can choose whether to turn tracking on or off
* On - Tracking is always on
* Off - Tracking is always off';
$string['unread'] = 'Unread';
$string['unreadposts'] = 'Unread posts';
$string['unreadpostsnumber'] = '{$a} unread posts';
$string['unreadpostsone'] = '1 unread post';
$string['unsubscribe'] = 'Unsubscribe from this forumanonymous';
$string['unsubscribeall'] = 'Unsubscribe from all anonymous forums';
$string['unsubscribeallconfirm'] = 'You are subscribed to {$a} anonymous forums now. Do you really want to unsubscribe from all anonymous forums and disable forumanonymous auto-subscribe?';
$string['unsubscribealldone'] = 'All optional forum subscriptions were removed. You will still receive notifications from forums with forced subscription. To manage forum notifications go to Messaging in My Profile Settings.';
$string['unsubscribeallempty'] = 'You are not subscribed to any forums. To disable all notifications from this server go to Messaging in My Profile Settings.';
$string['unsubscribed'] = 'Unsubscribed';
$string['unsubscribeshort'] = 'Unsubscribe';
$string['usermarksread'] = 'Manual message read marking';
$string['viewalldiscussions'] = 'View all discussions';
$string['warnafter'] = 'Post threshold for warning';
$string['warnafter_help'] = 'Students can be warned as they approach the maximum number of posts allowed in a given period. This setting specifies after how many posts they are warned. Users with the capability mod/forumanonymous:postwithoutthrottling are exempt from post limits.';
$string['warnformorepost'] = 'Warning! There is more than one discussion in this forumanonymous - using the most recent';
$string['yournewquestion'] = 'Your new question';
$string['yournewtopic'] = 'Your new discussion topic';
$string['yourreply'] = 'Your reply';


