<?php

/**
 * ActiveCollab class
 *
 * This source file can be used to communicate with your Active Collab install (http://activecollab.com)
 *
 * The class is documented in the file itself. If you find any bugs help me out and report them. Reporting can be done by sending an email to php-activecollab-bugs[at]verkoyen[dot]eu.
 * If you report a bug, make sure you give me enough information (include your code).
 *
 * License
 * Copyright (c) Tijs Verkoyen. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
 * 3. The name of the author may not be used to endorse or promote products derived from this software without specific prior written permission.
 *
 * This software is provided by the author "as is" and any express or implied warranties, including, but not limited to, the implied warranties of merchantability and fitness for a particular purpose are disclaimed. In no event shall the author be liable for any direct, indirect, incidental, special, exemplary, or consequential damages (including, but not limited to, procurement of substitute goods or services; loss of use, data, or profits; or business interruption) however caused and on any theory of liability, whether in contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of this software, even if advised of the possibility of such damage.
 *
 * @author			Tijs Verkoyen <php-activecollab@verkoyen.eu>
 * @version			1.0.0
 *
 * @copyright		Copyright (c) Tijs Verkoyen. All rights reserved.
 * @license			BSD License
 */
class ActiveCollab
{
	// internal constant to enable/disable debugging
	const DEBUG = true;

	// current version
	const VERSION = '1.0.1';


	/**
	 * The key for the API
	 *
	 * @var	string
	 */
	private $apiKey;

	/**
	 * The timeout
	 *
	 * @var	int
	 */
	private $timeOut = 60;

	/**
	 * The user agent
	 *
	 * @var	string
	 */
	private $userAgent;

	/**
	 * The url
	 *
	 * @var	string
	 */
	private $url;


// class methods
	/**
	 * Default constructor
	 * Creates an instance of the ActiveCollab Class.
	 *
	 * @return	void
	 * @param	string $apiKey	The API key being verified for use with the API.
	 * @param	string $url		The endpoint of the api.
	 */
	public function __construct($apiKey, $url)
	{
		$this->setApiKey($apiKey);
		$this->setUrl($url);
	}

	/**
	 * Make the call
	 *
	 * @return	string
	 * @param	string $url						The URL to call.
	 * @param	array[optional] $parameters		The parameters to pass.
	 * @param	string[optional] $method		The method to use.
	 * @param	bool[optional] $expectJSON		Do we expect JSON in return?
	 */
	private function doCall($path, $parameters = array(), $method = 'GET', $expectJSON = true)
	{
		// redefine
		$path = (string) $path;
		$parameters = (array) $parameters;

		// init var
		$options = array();

		// build the url
		$url = $this->getUrl();

		// add the path
		$url .= '?path_info=' . $path;

		// add token for authentication
		$url .= '&token=' . $this->getApiKey();
		$url .= '&format=json';

		// HTTP method
		if($method == 'POST')
		{
			// according the documentation we should make sure that there is a key submitted when using POST.
			$parameters['submitted'] = 'submitted';

			$options[CURLOPT_POST] = true;
			$options[CURLOPT_POSTFIELDS] = http_build_query($parameters);
		}

		else
		{
			$url .= '&' . http_build_query($parameters);
		}

		// set options
		$options[CURLOPT_URL] = $url;
		$options[CURLOPT_USERAGENT] = $this->getUserAgent();
		if(ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) $options[CURLOPT_FOLLOWLOCATION] = true;
		$options[CURLOPT_RETURNTRANSFER] = true;
		$options[CURLOPT_TIMEOUT] = (int) $this->getTimeOut();

		// init
		$curl = curl_init();

		// set options
		curl_setopt_array($curl, $options);

		// execute
		$response = curl_exec($curl);
		$headers = curl_getinfo($curl);

		// fetch errors
		$errorNumber = curl_errno($curl);
		$errorMessage = curl_error($curl);

		// close
		curl_close($curl);

		// invalid headers
		if(!in_array($headers['http_code'], array(0, 200)))
		{
			// should we provide debug information
			if(self::DEBUG)
			{
				// make it output proper
				echo '<pre>';

				// dump the header-information
				var_dump($headers);

				// dump the raw response
				var_dump($response);

				// end proper format
				echo '</pre>';

				// stop the script
				exit;
			}

			// decode the JSON
			$json = @json_decode($response, true);
			$message = 'unknown';

			if($json !== false && isset($json['message']))
			{
				// build messages
				$message = $json['message'];

				// append field errors
				if(isset($json['field_errors'])) $message .= '(field errors: '. implode(', ', $json['field_errors']) .')';
			}

			// throw error
			throw new ActiveCollabException($message, (int) $headers['http_code']);
		}

		// error?
		if($errorNumber != '') throw new ActiveCollabException($errorMessage, $errorNumber);

		// return the raw response if we don't expect JSON
		if(!$expectJSON) return $response;

		// decode the JSON
		$response = @json_decode($response, true);

		// return
		return $response;
	}

	/**
	 * Get the API-key that will be used
	 *
	 * @return	string
	 */
	public function getApiKey()
	{
		return (string) $this->apiKey;
	}

	/**
	 * Get the timeout that will be used
	 *
	 * @return	int
	 */
	public function getTimeOut()
	{
		return (int) $this->timeOut;
	}

	/**
	 * Get the url of the instance making the request
	 *
	 * @return	string
	 */
	public function getUrl()
	{
		return (string) $this->url;
	}

	/**
	 * Get the useragent that will be used. Our version will be prepended to yours.
	 * It will look like: "PHP ActiveCollab/<version> <your-user-agent>"
	 *
	 * @return	string
	 */
	public function getUserAgent()
	{
		return (string) 'PHP ActiveCollab/' . self::VERSION . ' ' . $this->userAgent;
	}

	/**
	 * Set API key that has to be used
	 *
	 * @return	void
	 * @param	string $apiKey		The API key to use.
	 */
	public function setApiKey($apiKey)
	{
		$this->apiKey = (string) $apiKey;
	}

	/**
	 * Set the timeout
	 * After this time the request will stop. You should handle any errors triggered by this.
	 *
	 * @return	void
	 * @param	int $seconds	The timeout in seconds.
	 */
	public function setTimeOut($seconds)
	{
		$this->timeOut = (int) $seconds;
	}

	/**
	 * Set the url of the instance making the request
	 *
	 * @return	void
	 * @param	string $url		The URL making the request.
	 */
	public function setUrl($url)
	{
		$this->url = (string) $url;
	}

	/**
	 * Set the user-agent for you application
	 * It will be appended to ours, the result will look like: "PHP ActiveCollab/<version> <your-user-agent>"
	 *
	 * @return	void
	 * @param	string $userAgent	Your user-agent, it should look like <app-name>/<app-version>.
	 */
	public function setUserAgent($userAgent)
	{
		$this->userAgent = (string) $userAgent;
	}


// System information
	/**
	 * Returns system information about the installation you are working with.
	 *
	 * @return array
	 */
	public function info()
	{
		// make the call
		$response = $this->doCall('/info');

		// convert the integer into a boolean, makes more sense
		if(isset($response['read_only'])) $response['read_only'] = ($response['read_only'] == 1);

		return $response;
	}

// Roles
	/**
	 * Lists all system roles and role details (permissions included).
	 * For security reasons, if user is not system administrator or people manager only default role ID is returned!
	 *
	 * @return array
	 */
	public function rolesSystem()
	{
		return $this->doCall('/roles/system');
	}

	/**
	 * Lists all project roles and displays their permissions.
	 * Please note that the system returns all project roles without checking user permissions. Each user will be
	 * able to execute this operation and see all available project roles.
	 *
	 * @return array
	 */
	public function rolesProject()
	{
		return $this->doCall('/roles/project');
	}

	/**
	 * Displays the details from a specific role. This command can return both system and project roles and their
	 * settings.
	 * Please note that role details are listed without checking user permissions, so each user will be able to read
	 * details of each role.
	 *
	 * @param int $id	The ID of the role.
	 * @return array
	 */
	public function rolesGet($id)
	{
		return $this->doCall('/roles/' . (string) $id);
	}

// Companies and users
	/**
	 * Lists all the companies defined in the System, no matter if they are Active or Archived.
	 *
	 * @return array|null
	 */
	public function people()
	{
		return $this->doCall('/people');
	}

	/**
	 * This command will create a new company
	 *
	 * @param string $name	Company name. Value of this field is required and needs to be unique in the entire system.
	 * @return array
	 */
	public function peopleAddCompany($name)
	{
		// redefine
		$parameters = array();
		$parameters['company']['name'] = (string) $name;

		// make the call
		return $this->doCall('/people/add-company', $parameters, 'POST');
	}

	/**
	 * Displays the properties of a specific company.
	 *
	 * @param int $id	The ID of the company
	 * @return array
	 */
	public function peopleCompanyGet($id)
	{
		return $this->doCall('/people/' . (string) $id);
	}

	public function peopleCompanyEdit($id)
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function peopleCompanyDelete($id)
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function peopleCompanyAddUser($id)
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function peopleCompanyUsers($id)
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function peopleCompanyUsersEdit($id)
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function peopleCompanyUsersDelete($id)
	{
		throw new ActiveCollabException('Not implemented', 501);
	}


// Projects
	/**
	 * Displays all projects that the authenticated user has access to.
	 * This function will show all - active, paused, completed and canceled projects.
	 *
	 * @return array|null
	 */
	public function projects()
	{
		return $this->doCall('/projects');
	}

	/**
	 * Creates a new project.
	 *
	 * @param string $name					Project name.
	 * @param int $leaderId					ID of the user who is the Project Leader.
	 * @param string[optional] $overview	Project overview.
	 * @param bool $private					Default visibility for objects in this project
	 * @param int[optional] $startsOn		Date when the project starts.
	 * @param int[optional] $groupId		ID of the project group.
	 * @param int[optional] $companyId		ID of the client company.
	 * @param int[optional] $templateId		A valid project ID to use as a template.
	 * @return array
	 */
	public function projectsAdd($name, $leaderId, $overview = null, $private = false, $startsOn = null, $groupId = null, $companyId = null, $templateId = null)
	{
		// redefine
		$parameters = array();
		$parameters['project']['name'] = (string) $name;
		$parameters['project']['leader_id'] = (int) $leaderId;
		if($overview != null) $parameters['project']['overview'] = (string) $overview;
		$parameters['project']['private'] = ($private) ? 0 : 1;
		if($startsOn != null) $parameters['project']['starts_on'] = date('Y-m-d H:i:s', (int) $startsOn);
		if($groupId != null) $parameters['project']['group_id'] = (int) $groupId;
		if($companyId != null) $parameters['project']['company_id'] = (int) $companyId;
		if($templateId != null) $parameters['project']['template_id'] = (int) $templateId;

		// make the call
		return $this->doCall('/projects/add', $parameters, 'POST');
	}

	/**
	 * Shows properties of the specific project.
	 *
	 * @param int $id	The ID of the project
	 * @return array|null
	 */
	public function projectsGet($id)
	{
		return $this->doCall('/projects/' . (string) $id);
	}

	public function projectsEdit($id)
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsEditStatus($id)
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsDelete($id)
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	/**
	 * Returns all tasks assigned to a logged in user for that particular project.
	 *
	 * @param int $id	The ID of the project
	 * @return	array|null
	 */
	public function projectsUserTasksGet($id)
	{
		return $this->doCall('/projects/' . (string) $id . '/user-tasks');
	}


// Project People
	/**
	 * Displays the list of people involved with the project and the permissions included in their Project Role.
	 * Project Permissions are organized per module and have four possible values:
	 * 	- 0: no access;
	 * 	- 1: has access, but can't create or manage objects;
	 * 	- 2: has access and permission to create objects in a given module;
	 * 	- 3: has access, creation and management permissions in a given module.
	 *
	 * @param int $id		The ID of the project
	 * @return array|null
	 */
	public function projectsPeople($id)
	{
		return $this->doCall('/projects/' . (string) $id . '/people');
	}

	/**
	 *
	 * @param int $id				The ID of the project
	 * @param array $users			The IDs of the users that should be added.
	 * @param array $roleId			The ID of the role.
	 * @param array $permissions	The permissions of those users, use the role_id-key if you predefined roles, or use the permissions-key if you want to specifiy the rights for eacht item seperatly
	 * return bool
	 */
	public function projectsPeopleAdd($id, array $users, $roleId = null, array $permissions = null)
	{
		// redefine
		$parameters = array();
		$parameters['users'] = (array) $users;
		if($roleId !== null) $parameters['project_permissions']['role_id'] = (int) $roleId;
		if($permissions !== null) $parameters['project_permissions']['permissions'] = (array) $permissions;

		// make the call
		return ($this->doCall('/projects/' . (string) $id . '/people/add', $parameters, 'POST') === null);
	}

	/**
	 * Change the set of Project Permissions for the selected user in a given project.
	 *
	 * @param int $id				The ID of the project.
	 * @param int $userId			The ID of the user.
	 * @param array $roleId			The ID of the role.
	 * @param array $permissions	The permissions of those users, use the role_id-key if you predefined roles, or use the permissions-key if you want to specifiy the rights for eacht item seperatly
	 * @return bool
	 */
	public function projectsPeopleUserChangePermissions($id, $userId, $roleId = null, array $permissions = null)
	{
		// redefine
		$parameters = array();
		if($roleId !== null) $parameters['project_permissions']['role_id'] = (int) $roleId;
		if($permissions !== null) $parameters['project_permissions']['permissions'] = (array) $permissions;

		// make the call
		return ($this->doCall('/projects/' . (string) $id . '/people/' . (string) $userId . '/change-permissions', $parameters, 'POST') === null);
	}

	/**
	 * Remove a specific user from the project.
	 *
	 * @param int $id		The ID of the project.
	 * @param int $userId	The ID of the user.
	 * @return bool
	 */
	public function projectsPeopleUserRemoveFromProject($id, $userId)
	{
		return ($this->doCall('/projects/' . (string) $id . '/people/' . (string) $userId . '/remove-from-project', null, 'POST') === null);
	}

// Project Groups
	public function projectsGroups()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsGroupsAdd()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsGroupsGet()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsGroupsEdit()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsGroupsDelete()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

// Discussions
	public function projectsDiscussions()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsDiscussionsAdd()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsDiscussionsGet()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsDiscussionsEdit()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

// Checklists
	public function projectsChecklists()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsChecklistsArchive()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsChecklistsAdd()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsChecklistsGet()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsChecklistsEdit()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

// Files
	public function projectsFiles()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsFilesUploadSingle()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsFilesGet()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsFilesEdit()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

// Milestones
	public function projectsMilestones()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsMilestonesAdd()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsMilestonesGet()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsMilestonesEdit()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

// Tickets
	public function projectsTickets($id)
	{
		return $this->doCall('/projects/' . (string) $id . '/tickets');
	}

	public function projectsTicketsArchive()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsTicketsAdd()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsTicketsGet()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsTicketsEdit()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

// Time
	public function projectsTime()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsTimeAdd()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsTimeGet()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsTimeEdit()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

// Pages
	/**
	 * Lists all page categories in a given project.
	 *
	 * @param string $id	The ID of the project.
	 * @return	array
	 */
	public function projectsPages($id)
	{
		return $this->doCall('/projects/' . (string) $id . '/pages');
	}

	/**
	 * Add a page
	 *
	 * @param string $id					The project ID.
	 * @param string $name					Page title.
	 * @param string $body					Page body.
	 * @param array[optional] $tags			List of tags.
	 * @param bool[optional] $private		Private object?
	 * @param int[optional] $milestoneId	ID of the parent milestone.
	 * @param int[optional] $parentId		ID of the parent object (category, ticket, ...)
	 * @return array
	 */
	public function projectsPagesAdd($id, $name, $body, array $tags = null, $private = false, $milestoneId = null, $parentId = null)
	{
		// redefine
		$parameters = array();
		$parameters['page']['name'] = (string) $name;
		$parameters['page']['body'] = (string) $body;

		if($tags !== null) $parameters['page']['tags'] = implode(',', $tags);
		$parameters['page']['private'] = ($private) ? 1 : 0;	// @todo	fix me
		if($milestoneId !== null) $parameters['page']['milestone_id'] = (int) $milestoneId;
		if($parentId !== null) $parameters['page']['parent_id'] = (int) $parentId;

		return $this->doCall('/projects/' . (string) $id . '/pages/add', $parameters, 'POST');
	}

	/**
	 * Displays page details with a list of all subpages and revisions.
	 *
	 * @param string $id		The ID of the project.
	 * @param string $pageId	The ID of the page.
	 * @return	array
	 */
	public function projectsPagesGet($id, $pageId)
	{
		return $this->doCall('/projects/' . (string) $id . '/pages/' . (string) $pageId);
	}

	/**
	 *
	 * Enter description here ...
	 * @param string $id					The project ID.
	 * @param string $pageId				Page ID.
	 * @param string $name					Page title.
	 * @param string $body					Page body.
	 * @param array[optional] $tags			List of tags.
	 * @param bool[optional] $private		Private object?
	 * @param int[optional] $milestoneId	ID of the parent milestone.
	 * @param int[optional] $parentId		ID of the parent object (category, ticket, ...)
	 * @return array
	 */
	public function projectsPagesEdit($id, $pageId, $isMinorRevision = false, $name = null, $body = null, array $tags = null, $private = false, $milestoneId = null, $parentId = null)
	{
		// redefine
		$parameters = array();
		if($isMinorRevision) $parameters['page']['is_minor_revision'] = 1;
		if($name !== null) $parameters['page']['name'] = (string) $name;
		if($body !== null) $parameters['page']['body'] = (string) $body;

		if($tags !== null) $parameters['page']['tags'] = implode(',', $tags);
		$parameters['page']['private'] = ($private) ? 0 : 1;
		if($milestoneId !== null) $parameters['page']['milestone_id'] = (int) $milestoneId;
		if($parentId !== null) $parameters['page']['parent_id'] = (int) $parentId;

		return $this->doCall('/projects/' . (string) $id . '/pages/' . (string) $pageId . '/edit', $parameters, 'POST');
	}

	/**
	 * Mark the selected page as archived
	 *
	 * @param string $id		The ID of the project.
	 * @param string $pageId	The ID of the page.
	 * @return array
	 */
	public function projectsPagesArchive($id, $pageId)
	{
		return $this->doCall('/projects/' . (string) $id . '/pages/' . (string) $pageId . '/archive', null, 'POST');
	}

	/**
	 * Marks a selected page as unarchived
	 *
	 * @param string $id		The ID of the project.
	 * @param string $pageId	The ID of the page.
	 * @return array
	 */
	public function projectsPagesUnarchive($id, $pageId)
	{
		return $this->doCall('/projects/' . (string) $id . '/pages/' . (string) $pageId . '/unarchive', null, 'POST');
	}

// Status Messages
	public function status()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function statusAdd()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

// Comments
	public function projectsCommentsAdd()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsCommentsGet()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsCommentsEdit()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}


// Subtasks
	/**
	 * This command will create a new subtask and attach it to the parent object.
	 *
	 * @param int $id						The ID of the project.
	 * @param int $parentId					The ID of the parent (mostly ticket-id).
	 * @param string $body					The task summary. A value for this field is required when a new task is added.
	 * @param int[optional] $priority		Priority can have five integer values ranging from -2 (lowest) to 2 (highest). 0 is normal.
	 * @param int[optional] $dueOn			When the task is due.
	 * @param array[optional] $assignees	An array of people assigned to the object, first person will be responsible.
	 * @return array
	 */
	public function projectsTasksAdd($id, $parentId, $body, $priority = null, $dueOn = null, array $assignees = null)
	{
		// redefine
		$parameters = array();
		$parameters['task']['body'] = (string) $body;

		if($priority !== null) $parameters['task']['priority'] = (int) $priority;
		if($dueOn !== null) $parameters['task']['due_on'] = date('Y-m-d H:i:s', $dueOn);
		if($assignees !== null)
		{
			$parameters['task']['assignees'][0] = $assignees;
			$parameters['task']['assignees'][1] = $assignees[0];
		}

		return $this->doCall('/projects/' . (string) $id . '/tasks/add&parent_id=' . (string) $parentId, $parameters, 'POST');
	}

	public function projectsTasksGet()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsTasksEdit()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

// Attachments
	public function projectsObjectsAttachments()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

// Common Project Object Operations
	public function projectsObjectsComplete()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsObjectsOpen()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsObjectsStar()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsObjectsUnstar()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsObjectsSubscribe()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsObjectsUnsubscribe()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsObjectsMoveToTrash()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}

	public function projectsObjectsRestoreFromTrash()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}
}


/**
 * ActiveCollab Exception class
 *
 * @author			Tijs Verkoyen <php-activecollab@verkoyen.eu>
 */
class ActiveCollabException extends Exception
{
}

?>