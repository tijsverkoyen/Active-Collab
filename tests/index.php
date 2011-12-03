<?php

// require
require_once 'config.php';
require_once '../active_collab.php';

// create instance
$ac = new ActiveCollab(KEY, URL);

//$response = $ac->info();
//$response = $ac->rolesSystem();
//$response = $ac->rolesProject();
//$response = $ac->rolesGet(8);
//$response = $ac->people();
$response = $ac->peopleCompanyGet(5);
//$response = $ac->projects();
//$response = $ac->projectsGet(87);
//$response = $ac->projectsUserTasksGet(87);
//$response = $ac->projectsTickets(87);

Spoon::dump($response);
?>