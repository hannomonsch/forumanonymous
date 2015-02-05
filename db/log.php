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
 * Definition of log events
 *
 * @package    mod
 * @subpackage forumanonymous
 * @copyright  2010 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $DB; // TODO: this is a hack, we should really do something with the SQL in SQL tables

$logs = array(
    array('module'=>'forumanonymous', 'action'=>'add', 'mtable'=>'forumanonymous', 'field'=>'name'),
    array('module'=>'forumanonymous', 'action'=>'update', 'mtable'=>'forumanonymous', 'field'=>'name'),
    array('module'=>'forumanonymous', 'action'=>'add discussion', 'mtable'=>'forumanonymous_discussions', 'field'=>'name'),
    array('module'=>'forumanonymous', 'action'=>'add post', 'mtable'=>'forumanonymous_posts', 'field'=>'subject'),
    array('module'=>'forumanonymous', 'action'=>'update post', 'mtable'=>'forumanonymous_posts', 'field'=>'subject'),
    array('module'=>'forumanonymous', 'action'=>'user report', 'mtable'=>'user', 'field'=>$DB->sql_concat('firstname', "' '" , 'lastname')),
    array('module'=>'forumanonymous', 'action'=>'move discussion', 'mtable'=>'forumanonymous_discussions', 'field'=>'name'),
    array('module'=>'forumanonymous', 'action'=>'view subscribers', 'mtable'=>'forumanonymous', 'field'=>'name'),
    array('module'=>'forumanonymous', 'action'=>'view discussion', 'mtable'=>'forumanonymous_discussions', 'field'=>'name'),
    array('module'=>'forumanonymous', 'action'=>'view forumanonymous', 'mtable'=>'forumanonymous', 'field'=>'name'),
    array('module'=>'forumanonymous', 'action'=>'subscribe', 'mtable'=>'forumanonymous', 'field'=>'name'),
    array('module'=>'forumanonymous', 'action'=>'unsubscribe', 'mtable'=>'forumanonymous', 'field'=>'name'),
);