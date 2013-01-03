<?php

namespace TijsVerkoyen\ActiveCollab;

/**
 * ActiveCollab class
 *
 * @author		Tijs Verkoyen <php-activecollab@verkoyen.eu>
 * @version		3.0.0
 * @copyright	Copyright (c) Tijs Verkoyen. All rights reserved.
 * @license		BSD License
 */
class ActiveCollab
{
    // internal constant to enable/disable debugging
    const DEBUG = true;

    // current version
    const VERSION = '3.0.0';

    /**
     * The API url
     *
     * @var string
     */
    private $apiUrl;

    /**
     * The token to use
     *
     * @var string
     */
    private $token;

    /**
     * The timeout
     *
     * @var int
     */
    private $timeOut = 60;

    /**
     * The user agent
     *
     * @var string
     */
    private $userAgent;

    // class methods
    /**
     * Create an instance
     *
     * @param string $token  The token to use.
     * @param string $apiUrl The url of the API.
     */
    public function __construct($token, $apiUrl)
    {
        $this->setToken($token);
        $this->setApiUrl($apiUrl);
    }

    /**
     * Make a call
     *
     * @param  string           $path       The method to be called.
     * @param  array[optional]  $parameters The parameters to pass.
     * @param  string[optional] $method     The method to use. Possible values are GET or POST.
     * @return mixed
     */
    private function doCall($path, array $parameters = null, $method = 'GET')
    {
        // redefine
        $path = (string) $path;
        $parameters = (array) $parameters;

        // init var
        $options = array();

        // build the url
        $url = $this->getApiUrl();

        // add the path
        $url .= '?path_info=' . $path;

        // add token for authentication
        $url .= '&auth_api_token=' . $this->getToken();
        $url .= '&format=json';

        // HTTP method
        if ($method == 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($parameters);
        } else {
            $options[CURLOPT_POST] = false;
            if (!empty($parameters)) {
                $url .= '&' . http_build_query($parameters);
            }
        }

        // set options
        $options[CURLOPT_URL] = $url;
        $options[CURLOPT_USERAGENT] = $this->getUserAgent();
        if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
            $options[CURLOPT_FOLLOWLOCATION] = true;
        }
        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_TIMEOUT] = (int) $this->getTimeOut();
        $options[CURLOPT_SSL_VERIFYPEER] = false;
        $options[CURLOPT_SSL_VERIFYHOST] = false;

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

        // we expect JSON, so decode it
        $json = @json_decode($response, true);

        // validate JSON
        if ($json === null) {
            // should we provide debug information
            if (self::DEBUG) {
                // make it output proper
                echo '<pre>';

                // dump the header-information
                var_dump($headers);

                // dump the error
                var_dump($errorMessage);

                // dump the raw response
                var_dump($response);

                // end proper format
                echo '</pre>';
            }

            // throw exception
            throw new Exception('Invalid response.');
        }

        // return
        return $json;
    }

    /**
     * Get the url of the instance making the request
     *
     * @return string
     */
    public function getApiUrl()
    {
        return (string) $this->apiUrl;
    }

    /**
     * Get the timeout that will be used
     *
     * @return int
     */
    public function getTimeOut()
    {
        return (int) $this->timeOut;
    }

    /**
     * Get the token
     *
     * @return string
     */
    public function getToken()
    {
        return (string) $this->token;
    }

    /**
     * Get the useragent that will be used.
     * Our version will be prepended to yours.
     * It will look like: "PHP ActiveCollab/<version> <your-user-agent>"
     *
     * @return string
     */
    public function getUserAgent()
    {
        return (string) 'PHP ActiveCollab/' . self::VERSION . ' ' . $this->userAgent;
    }

    /**
     * Set the url of the API.
     *
     * @param string $apiUrl
     */
    public function setApiurl($apiUrl)
    {
        $this->apiUrl = (string) $apiUrl;
    }

    /**
     * Set the timeout
     * After this time the request will stop.
     * You should handle any errors triggered by this.
     *
     * @param $seconds int timeout in seconds.
     */
    public function setTimeOut($seconds)
    {
        $this->timeOut = (int) $seconds;
    }

    /**
     * Set the token
     *
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = (string) $token;
    }

    /**
     * Set the user-agent for you application
     * It will be appended to ours, the result will look like: "PHP
     * ActiveCollab/<version> <your-user-agent>"
     *
     * @param $userAgent string user-agent, it should look like
     *        <app-name>/<app-version>.
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = (string) $userAgent;
    }

    /**
     * Lists all active milestones for a specific project.
     *
     * @param  string $slug The slug of the project.
     * @return array
     */
    public function projectsMilestones($slug)
    {
        $path = 'projects/' . (string) $slug . '/milestones';

        return $this->doCall($path);
    }

    /**
     * Returns system information about the installation you are working with.
     * This information includes system versions; info about logged in users;
     * the mode the API is in etc.
     *
     * @return array
     */
    public function info()
    {
        return $this->doCall('info');
    }

    /**
     * Lists all available project labels.
     *
     * @return array
     */
    public function infoLabelsProject()
    {
        return $this->doCall('info/labels/project');
    }

    /**
     * Lists all available assignment labels. These labels are used by tasks and subtasks.
     *
     * @return array
     */
    public function infoLabelsAssignment()
    {
        return $this->doCall('info/labels/assignment');
    }

    /**
     * Lists all system roles and role details (permissions included).
     *
     * @return array
     */
    public function infoRoles()
    {
        return $this->doCall('info/roles');
    }

    /**
     * Lists all project roles and displays their permissions.
     *
     * @return array
     */
    public function infoRolesProject()
    {
       return $this->doCall('info/roles/project');
    }

    /**
     * Lists all active companies that are defined in People section.
     *
     * @return array
     */
    public function people()
    {
        return $this->doCall('people');
    }

    /**
     * Display all, non-archived projects that this user has access to.
     * In case of administrators and project managers, system will return all
     * non-archived projects and properly populate is_member flag value (when 0,
     * administrator and project manager can see and manage the project, but
     * they are not directly involved with it).
     *
     * @return mixed
     */
    public function projects()
    {
        $return = $this->doCall('projects');

        // because in some methods we need a slug and it isn't returned we calculate the slug in this method
        if (!empty($return)) {
            foreach ($return as &$row) {
                $parts = parse_url($row['permalink']);
                if (isset($parts['query'])) {
                    $chunks = explode('&', $parts['query']);

                    foreach ($chunks as $parameter) {
                        $parameterChunks = explode('=', $parameter);

                        if (count($parameterChunks) == 2 && $parameterChunks[0] == 'path_info') {
                            $data = explode('/', urldecode($parameterChunks[1]));

                            if (isset($data[1])) {
                                $row['slug'] = $data[1];
                                break;
                            }
                        }
                    }
                }
            }
        }

        return $return;
    }

    /**
     * Displays the list of people involved with the project and the permissions
     * included in their Project Role. Project Permissions are organized per
     * module and have four possible values:
     *  - 0: no access
     *  - 1: has access, but can't create or manage objects
     *  - 2: has access and permission to create objects in a given module
     *  - 3: has access, creation and management permissions in a given module
     *
     * @param  string $slug The slug of the project.
     * @return array
     */
    public function projectsPeople($slug)
    {
        $path = 'projects/' . (string) $slug . '/people';

        return $this->doCall($path);
    }
}
