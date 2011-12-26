<?php

// require
require_once 'config.php';
require_once '../active_collab.php';

// create instance
$ac = new ActiveCollab(KEY, URL);

// $response = $ac->info();
// $response = $ac->rolesSystem();
// $response = $ac->rolesProject();
// $response = $ac->rolesGet(8);
// $response = $ac->people();
// $response = $ac->peopleAddCompany('name ' . time());
// $response = $ac->peopleCompanyGet(5);
// $response = $ac->projects();
// $response = $ac->projectsAdd('name', 15);
// $response = $ac->projectsGet(45);
//$response = $ac->projectsPages(45);
//$response = $ac->projectsPagesAdd(45, time(), 'page created on ' . date('Y-m-d H:i:s'));
//$response = $ac->projectsPagesArchive(45, 5155);
//$response = $ac->projectsPagesEdit(45, 5155, '[edited]');
//$response = $ac->projectsPagesGet(45, 5155);
//$response = $ac->projectsPagesUnarchive(45, 5155);


// $response = $ac->projectsUserTasksGet(45);
// $response = $ac->projectsTickets(45);
// $response = $ac->projectsTasksAdd(45, 4786, 'just a body ' . time());
// $response = $ac->projectsTasksAdd(45, 4786, 'with priority' . time(), 1);
// $response = $ac->projectsTasksAdd(45, 4786, 'with due date' . time(), null, mktime(00, 00, 00, 12, 20, 2011));
// $response = $ac->projectsTasksAdd(45, 4786, 'with assignees' . time(), null, null, array(15), 15);

Spoon::dump($response);
?>