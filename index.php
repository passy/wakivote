<?php
require_once('../../config.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT);   // course
$PAGE->set_url('/mod/wakivote/index.php', array('id'=>$id));

if (! $course = $DB->get_record('course', array('id'=>$id))) {
    print_error('invalidcourseid');
}

require_course_login($course);
$PAGE->set_pagelayout('incourse');

add_to_log($course->id, 'wakivote', 'view all', "index.php?id=$course->id", '');


/// Get all required strings

$strsectionname = get_string('sectionname', 'format_'.$course->format);
$strwakivotes = get_string('modulenameplural', 'chat');
$strwakivote  = get_string('modulename', 'chat');


/// Print the header
$PAGE->navbar->add($strwakivotes);
$PAGE->set_title($strwakivotes);
echo $OUTPUT->header();

/// Get all the appropriate data

if (! $wakivotes = get_all_instances_in_course('wakivote', $course)) {
    notice(get_string('thereareno', 'moodle', $strwakivotes), "../../course/view.php?id=$course->id");
    die();
}

$usesections = course_format_uses_sections($course->format);
if ($usesections) {
    $sections = get_all_sections($course->id);
}

/// Print the list of instances (your module will probably extend this)

$timenow  = time();
$strname  = get_string('name');

$table = new html_table();

if ($usesections) {
    $table->head  = array ($strsectionname, $strname);
    $table->align = array ('center', 'left');
} else {
    $table->head  = array ($strname);
    $table->align = array ('left');
}

$currentsection = '';
foreach ($wakivotes as $wakivote) {
    if (!$chat->visible) {
        //Show dimmed if the mod is hidden
        $link = "<a class=\"dimmed\" href=\"view.php?id=$wakivote->coursemodule\">".format_string($wakivote->name,true)."</a>";
    } else {
        //Show normal if the mod is visible
        $link = "<a href=\"view.php?id=$wakivote->coursemodule\">".format_string($wakivote->name,true)."</a>";
    }
    $printsection = '';
    if ($wakivote->section !== $currentsection) {
        if ($wakivote->section) {
            $printsection = get_section_name($course, $sections[$wakivote->section]);
        }
        if ($currentsection !== '') {
            $table->data[] = 'hr';
        }
        $currentsection = $wakivote->section;
    }
    if ($usesections) {
        $table->data[] = array ($printsection, $link);
    } else {
        $table->data[] = array ($link);
    }
}

echo '<br />';

echo html_writer::table($table);

/// Finish the page

echo $OUTPUT->footer();


