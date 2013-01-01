<?php

//require
require_once '../../../autoload.php';
require_once 'config.php';

use \TijsVerkoyen\ActiveCollab\ActiveCollab;

// create instance
$ac = new ActiveCollab(TOKEN, API_URL);

try {
// @todo  $response = $ac->projectsMilestones();
// @todo  $response = $ac->projectsMilestonesAdd();
// @todo  $response = $ac->projectsMilestonesGet();
// @todo  $response = $ac->projectsMilestonesEdit();
// @todo  $response = $ac->projectsMilestonesReschedule();

// @todo  $response = $ac->apiIsAlive();

//  $response = $ac->info();
// @todo  $response = $ac->infoLabelsProject();
// @todo  $response = $ac->infoLabelsAssignment();
// @todo  $response = $ac->infoRoles();
// @todo  $response = $ac->infoRolesProject();

// @todo  $response = $ac->people();
// @todo  $response = $ac->peopleAddCompany();
// @todo  $response = $ac->peopleCompanyGet();
// @todo  $response = $ac->peopleCompanyEdit();
// @todo  $response = $ac->peopleCompanyUserAdd();
// @todo  $response = $ac->peopleCompanyUserGet();
// @todo  $response = $ac->peopleCompanyUserEdit();

// @todo  $response = $ac->projects();
// @todo  $response = $ac->projectsArchive();
// @todo  $response = $ac->projectsAdd();
// @todo  $response = $ac->projectsGet();
// @todo  $response = $ac->projectsEdit();

// @todo  $response = $ac->projectsPeople();
// @todo  $response = $ac->projectsPeopleAdd();
// @todo  $response = $ac->projectsPeopleReplace();
// @todo  $response = $ac->projectsPeopleChangePermissions();
// @todo  $response = $ac->projectsPeopleRemove();

// @todo  $response = $ac->projectsDiscussions();
// @todo  $response = $ac->projectsDiscussionsAdd();
// @todo  $response = $ac->projectsDiscussionsGet();
// @todo  $response = $ac->projectsDiscussionsEdit();

// @todo  $response = $ac->projectsTasks();
// @todo  $response = $ac->projectsTasksArchive();
// @todo  $response = $ac->projectsTasksAdd();
// @todo  $response = $ac->projectsTasksGet();
// @todo  $response = $ac->projectsTasksEdit();

// @todo  $response = $ac->projectsTracking();
// @todo  $response = $ac->projectsTrackingTimeAdd();
// @todo  $response = $ac->projectsTrackingTimeGet();
// @todo  $response = $ac->projectsTrackingTimeEdit();
// @todo  $response = $ac->projectsTrackingExpensesAdd();
// @todo  $response = $ac->projectsTrackingExpensesGet();
// @todo  $response = $ac->projectsTrackingExpensesEdit();

// @todo  $response = $ac->status();
// @todo  $response = $ac->statusAdd();

} catch (Exception $e) {
  var_dump($e);
}

// output
var_dump($response);
