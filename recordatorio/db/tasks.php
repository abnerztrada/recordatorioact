<?php
defined('MOODLE_INTERNAL') || die();

$tasks = array(
    // enroll new users
    array(
        'classname' => '\tool_recordatorio\task\recordatorio',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ),
);
