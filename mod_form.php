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
 * @copyright Jamie Pratt <me@jamiep.org>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_forumanonymous_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $COURSE, $DB;

        $mform    =& $this->_form;

	$config = get_config('forumanonymous');
//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('forumanonymousname', 'forumanonymous'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
        $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
       

        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $forumanonymous_types = forumanonymous_get_forumanonymous_types();

        asort($forumanonymous_types);
        $mform->addElement('select', 'type', get_string('forumanonymoustype', 'forumanonymous'), $forumanonymous_types);
        $mform->addHelpButton('type', 'forumanonymoustype', 'forumanonymous');
        $mform->setDefault('type', 'general');

        //$this->add_intro_editor(true, get_string('forumanonymousintro', 'forumanonymous'));
	$this->add_intro_editor($config->requiremodintro, get_string('forumanonymousintro', 'forumanonymous'),0);


        $options = array();
        $options[FORUMANONYMOUS_CHOOSESUBSCRIBE] = get_string('subscriptionoptional', 'forumanonymous');
        $options[FORUMANONYMOUS_FORCESUBSCRIBE] = get_string('subscriptionforced', 'forumanonymous');
        $options[FORUMANONYMOUS_INITIALSUBSCRIBE] = get_string('subscriptionauto', 'forumanonymous');
        $options[FORUMANONYMOUS_DISALLOWSUBSCRIBE] = get_string('subscriptiondisabled','forumanonymous');
        $mform->addElement('select', 'forcesubscribe', get_string('subscriptionmode', 'forumanonymous'), $options);
        $mform->addHelpButton('forcesubscribe', 'subscriptionmode', 'forumanonymous');

        $options = array();
        $options[FORUMANONYMOUS_TRACKING_OPTIONAL] = get_string('trackingoptional', 'forumanonymous');
        $options[FORUMANONYMOUS_TRACKING_OFF] = get_string('trackingoff', 'forumanonymous');
        $options[FORUMANONYMOUS_TRACKING_ON] = get_string('trackingon', 'forumanonymous');
        $mform->addElement('select', 'trackingtype', get_string('trackingtype', 'forumanonymous'), $options);
        $mform->addHelpButton('trackingtype', 'trackingtype', 'forumanonymous');

        $choices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes);
        $choices[1] = get_string('uploadnotallowed');
        $choices[0] = get_string('courseuploadlimit') . ' ('.display_size($COURSE->maxbytes).')';
        $mform->addElement('select', 'maxbytes', get_string('maxattachmentsize', 'forumanonymous'), $choices);
        $mform->addHelpButton('maxbytes', 'maxattachmentsize', 'forumanonymous');
        $mform->setDefault('maxbytes', $CFG->forumanonymous_maxbytes);

        $choices = array(0,1,2,3,4,5,6,7,8,9,10,20,50,100);
        $mform->addElement('select', 'maxattachments', get_string('maxattachments', 'forumanonymous'), $choices);
        $mform->addHelpButton('maxattachments', 'maxattachments', 'forumanonymous');
        $mform->setDefault('maxattachments', $CFG->forumanonymous_maxattachments);

        if ($CFG->enablerssfeeds && isset($CFG->forumanonymous_enablerssfeeds) && $CFG->forumanonymous_enablerssfeeds) {
//-------------------------------------------------------------------------------
            $mform->addElement('header', '', get_string('rss'));
            $choices = array();
            $choices[0] = get_string('none');
            $choices[1] = get_string('discussions', 'forumanonymous');
            $choices[2] = get_string('posts', 'forumanonymous');
            $mform->addElement('select', 'rsstype', get_string('rsstype'), $choices);
            $mform->addHelpButton('rsstype', 'rsstype', 'forumanonymous');

            $choices = array();
            $choices[0] = '0';
            $choices[1] = '1';
            $choices[2] = '2';
            $choices[3] = '3';
            $choices[4] = '4';
            $choices[5] = '5';
            $choices[10] = '10';
            $choices[15] = '15';
            $choices[20] = '20';
            $choices[25] = '25';
            $choices[30] = '30';
            $choices[40] = '40';
            $choices[50] = '50';
            $mform->addElement('select', 'rssarticles', get_string('rssarticles'), $choices);
            $mform->addHelpButton('rssarticles', 'rssarticles', 'forumanonymous');
        }

//-------------------------------------------------------------------------------
        $mform->addElement('header', '', get_string('blockafter', 'forumanonymous'));
        $options = array();
        $options[0] = get_string('blockperioddisabled','forumanonymous');
        $options[60*60*24]   = '1 '.get_string('day');
        $options[60*60*24*2] = '2 '.get_string('days');
        $options[60*60*24*3] = '3 '.get_string('days');
        $options[60*60*24*4] = '4 '.get_string('days');
        $options[60*60*24*5] = '5 '.get_string('days');
        $options[60*60*24*6] = '6 '.get_string('days');
        $options[60*60*24*7] = '1 '.get_string('week');
        $mform->addElement('select', 'blockperiod', get_string('blockperiod', 'forumanonymous'), $options);
        $mform->addHelpButton('blockperiod', 'blockperiod', 'forumanonymous');

        $mform->addElement('text', 'blockafter', get_string('blockafter', 'forumanonymous'));
        $mform->setType('blockafter', PARAM_INT);
        $mform->setDefault('blockafter', '0');
        $mform->addRule('blockafter', null, 'numeric', null, 'client');
        $mform->addHelpButton('blockafter', 'blockafter', 'forumanonymous');
        $mform->disabledIf('blockafter', 'blockperiod', 'eq', 0);


        $mform->addElement('text', 'warnafter', get_string('warnafter', 'forumanonymous'));
        $mform->setType('warnafter', PARAM_INT);
        $mform->setDefault('warnafter', '0');
        $mform->addRule('warnafter', null, 'numeric', null, 'client');
        $mform->addHelpButton('warnafter', 'warnafter', 'forumanonymous');
        $mform->disabledIf('warnafter', 'blockperiod', 'eq', 0);

	$coursecontext = context_course::instance($COURSE->id);
        plagiarism_get_form_elements_module($mform, $coursecontext, 'mod_forumanonymous');
//-------------------------------------------------------------------------------

        $this->standard_grading_coursemodule_elements();

        $this->standard_coursemodule_elements();
//-------------------------------------------------------------------------------
// buttons
        $this->add_action_buttons();

    }

    function definition_after_data() {
        parent::definition_after_data();
        $mform     =& $this->_form;
        $type      =& $mform->getElement('type');
        $typevalue = $mform->getElementValue('type');

        //we don't want to have these appear as possible selections in the form but
        //we want the form to display them if they are set.
        if ($typevalue[0]=='news') {
            $type->addOption(get_string('namenews', 'forumanonymous'), 'news');
            $mform->addHelpButton('type', 'namenews', 'forumanonymous');
            $type->freeze();
            $type->setPersistantFreeze(true);
        }
        if ($typevalue[0]=='social') {
            $type->addOption(get_string('namesocial', 'forumanonymous'), 'social');
            $type->freeze();
            $type->setPersistantFreeze(true);
        }

    }

    function data_preprocessing(&$default_values) {
        parent::data_preprocessing($default_values);

        // Set up the completion checkboxes which aren't part of standard data.
        // We also make the default value (if you turn on the checkbox) for those
        // numbers to be 1, this will not apply unless checkbox is ticked.
        $default_values['completiondiscussionsenabled']=
            !empty($default_values['completiondiscussions']) ? 1 : 0;
        if (empty($default_values['completiondiscussions'])) {
            $default_values['completiondiscussions']=1;
        }
        $default_values['completionrepliesenabled']=
            !empty($default_values['completionreplies']) ? 1 : 0;
        if (empty($default_values['completionreplies'])) {
            $default_values['completionreplies']=1;
        }
        $default_values['completionpostsenabled']=
            !empty($default_values['completionposts']) ? 1 : 0;
        if (empty($default_values['completionposts'])) {
            $default_values['completionposts']=1;
        }
    }

      function add_completion_rules() {
        $mform =& $this->_form;

        $group=array();
        $group[] =& $mform->createElement('checkbox', 'completionpostsenabled', '', get_string('completionposts','forumanonymous'));
        $group[] =& $mform->createElement('text', 'completionposts', '', array('size'=>3));
        $mform->setType('completionposts',PARAM_INT);
        $mform->addGroup($group, 'completionpostsgroup', get_string('completionpostsgroup','forumanonymous'), array(' '), false);
        $mform->disabledIf('completionposts','completionpostsenabled','notchecked');

        $group=array();
        $group[] =& $mform->createElement('checkbox', 'completiondiscussionsenabled', '', get_string('completiondiscussions','forumanonymous'));
        $group[] =& $mform->createElement('text', 'completiondiscussions', '', array('size'=>3));
        $mform->setType('completiondiscussions',PARAM_INT);
        $mform->addGroup($group, 'completiondiscussionsgroup', get_string('completiondiscussionsgroup','forumanonymous'), array(' '), false);
        $mform->disabledIf('completiondiscussions','completiondiscussionsenabled','notchecked');

        $group=array();
        $group[] =& $mform->createElement('checkbox', 'completionrepliesenabled', '', get_string('completionreplies','forumanonymous'));
        $group[] =& $mform->createElement('text', 'completionreplies', '', array('size'=>3));
        $mform->setType('completionreplies',PARAM_INT);
        $mform->addGroup($group, 'completionrepliesgroup', get_string('completionrepliesgroup','forumanonymous'), array(' '), false);
        $mform->disabledIf('completionreplies','completionrepliesenabled','notchecked');

        return array('completiondiscussionsgroup','completionrepliesgroup','completionpostsgroup');
    }

    function completion_rule_enabled($data) {
        return (!empty($data['completiondiscussionsenabled']) && $data['completiondiscussions']!=0) ||
            (!empty($data['completionrepliesenabled']) && $data['completionreplies']!=0) ||
            (!empty($data['completionpostsenabled']) && $data['completionposts']!=0);
    }

    function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }
        // Turn off completion settings if the checkboxes aren't ticked
        $autocompletion = !empty($data->completion) && $data->completion==COMPLETION_TRACKING_AUTOMATIC;
        if (empty($data->completiondiscussionsenabled) || !$autocompletion) {
            $data->completiondiscussions = 0;
        }
        if (empty($data->completionrepliesenabled) || !$autocompletion) {
            $data->completionreplies = 0;
        }
        if (empty($data->completionpostsenabled) || !$autocompletion) {
            $data->completionposts = 0;
        }
        return $data;
    }
}

