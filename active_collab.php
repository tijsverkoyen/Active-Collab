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
	const DEBUG = false;

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
	 * @param	array[optional] $parameters	The parameters to pass.
	 */
	private function doCall($path, $parameters = array(), $method = 'GET', $expectJSON = true)
	{
		// redefine
		$path = (string) $path;
		$parameters = (array) $parameters;

		$parameters['format'] = 'json';

		// build the url
		$url = $this->getUrl();

		// add the path
		$url .= '?path_info=' . $path;

		// add token for authentication
		$url .= '&token=' . $this->getApiKey();

		// HTTP method
		if($method == 'POST')
		{
			// according the documentation we should make sure that there is a key submitted when using POST.
			$parameters['submitted'] = 'submitted';

			$options[CURLOPT_POST] = true;
			$options[CURLOPT_POSTFIELDS] = $parameters;
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

			// throw error
			throw new ActiveCollabException(null, (int) $headers['http_code']);
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
	 * @param int $id	The id of the role.
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


	public function peopleAddCompany()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}


	/**
	 * Displays the properties of a specific company.
	 *
	 * @param int $id	The id of the company
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


	public function projectsAdd()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}


	/**
	 * Shows properties of the specific project.
	 *
	 * @param int $id	The id of the project
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
	 * @param int $id	The id of the project
	 * @return	array|null
	 */
	public function projectsUserTasksGet($id)
	{
		return $this->doCall('/projects/' . (string) $id . '/user-tasks');
	}


// Project People
	public function projectsPeople()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}


	public function projectsPeopleAdd()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}


	public function projectsPeopleUserChangePermissions()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}


	public function projectsPeopleUserRemoveFromProject()
	{
		throw new ActiveCollabException('Not implemented', 501);
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
	public function projectsPages()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}


	public function projectsPagesAdd()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}


	public function projectsPagesGet()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}


	public function projectsPagesEdit()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}


	public function projectsPagesArchive()
	{
		throw new ActiveCollabException('Not implemented', 501);
	}


	public function projectsPagesUnarchive()
	{
		throw new ActiveCollabException('Not implemented', 501);
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
	public function projectsTasksAdd()
	{
		throw new ActiveCollabException('Not implemented', 501);
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
	public function projectsObjects()
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