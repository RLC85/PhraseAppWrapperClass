<?php

class phrase_app_api
{
	private $email;
	private $password;
	private $url_parameters = array();
	private $base_url = 'https://phraseapp.com/api/v1/';
	private $errors = array();

	/**
	 * set_email
	 * 
	 * sets the email of the user to log into phrase app
	 *
	 * @author Richard Christensen
	 * 
	 * @return VOID
	 */
	public function set_email( $email )
	{
		$this->email = email;
	}

	/**
	 * set_password
	 * 
	 * sets the password of the user to log into Phrase App
	 *
	 * @author Richard Christensen
	 * 
	 * @return VOID
	 */
	public function set_password( $password )
	{
		$this->password = $password;
	}

	/**
	 * set_project_auth_token
	 * 
	 * sets the project auth token for the project you are working with. Can be found under PhraseApp Projects settings.
	 *
	 * @author Richard Christensen
	 * 
	 * @return VOID
	 */
	public function set_project_auth_token( $project_auth_token )
	{
		$this->add_url_parameter( 'project_auth_token' , $project_auth_token );
	}

	/**
	 * set_base_url
	 * 
	 * sets the url for the current version of Phrase Apps REST API. Current version is v2
	 *
	 * @author Richard Christensen
	 * 
	 * @return VOID
	 */
	public function set_base_url( $base_url )
	{
		$this->base_url = $base_url;
	}

	/**
	 * connect
	 * 
	 * connects to the PhraseApp API using set username, password and project_auth_token, or the passed email, password and project_auth_token
	 *
	 * @author Richard Christensen
	 * 
	 * @return Bool - true on successful login, false otherwise.
	 */
	public function connect( $email = null , $password = null , $project_auth_token = null )
	{
		if(! empty( $email ) )
		{
			$this->email = $email;
		}

		if(! empty( $password ) )
		{
			$this->password = $password;
		}	

		if( ! empty( $project_auth_token ) )
		{
			$this->project_auth_token = $project_auth_token;
		}

		if( ! $this->log_in() )
		{
			return false;
		}

		return true;
	}

	/**
	 * log_in
	 * 
	 * logs into PhraseApp
	 *
	 * @author Richard Christensen
	 * 
	 * @return Bool - true on success or false otherwise.
	 */
	protected function log_in()
	{
		if( ! $this->email )
		{
			$this->log_error( 'Email must be set, to log in' );
			return false; 
		}

		if( ! $this->password )
		{
			$this->log_error( 'Password must be set, to log in' );
			return false;
		}

		if( ! $this->parameter_exists( 'project_auth_token' ) )
		{
			$this->log_error( 'Project auth token must be set to log in.' );
			return false;
		}

		$this->addUrlParameter( 'email' , $this->email );
		$this->addUrlParameter( 'password' , $this->password );

		$this->response = $this->call_api( 'POST' , 'sessions' );
		$response = $this->decoded_response();

		$this->reset_url_parameters();

		if( $response->success === true )
		{	
			return true;
		}

		return false;
	}

	/**
	 * check_session
	 * 
	 * checks to see if the Command is still logged into the PhraseApp API client
	 *
	 * @author Richard Christensen
	 * 
	 * @return bool true on success and false otherwise
	 */
	public function check_session()
	{
		$this->response = $this->call_api( 'POST' , 'auth/check_login' );

		$response = json_decode( $this->response );

		if( isset( $response->logged_in ) && $response->logged_in === true )
		{
			return true;
		}
		return false;

	}

	public function query( $url , $method , $args = array() )
	{
		if( ! $this->check_session() )
		{
			if( ! $this->log_in() )
			{
				$this->log_error( 'Unable to process request due to log in failure.' );
				return false;
			}
		}

		if( ! empty( $args ) )
		{
			foreach ($args as $name => $value )
			{
				$this->add_url_parameter( $name , $url );
			}
		}

		$this->response = $this->call_api( $method , $url );

		$this->reset_url_parameters();

		return true;
	}

	/**
	 * log_error
	 * 
	 * logs an error to the errors array. use get_errors to view them.
	 *
	 * @author Richard Christensen
	 * 
	 * @return VOID
	 */
	protected function log_error( $message )
	{
		array_push( $this-errors , $message );
	}

	/**
	 * get_errors
	 * 
	 * returns the errors array.
	 *
	 * @author Richard Christensen
	 * 
	 * @return array
	 */
	public function get_errors()
	{
		return $this->errors;
	}

	/**
	 * get_response
	 * 
	 * returns the response from the API query
	 *
	 * @author Richard Christensen
	 * 
	 * @return Return Value
	 */
	public function get_response()
	{
		return $this->response;
	}

	/**
	 * add_url_parameter
	 * 
	 * adds a new Parameter to this->url_parameters
	 *
	 * @author Richard Christensen
	 * @param String $name - name of the parameter to set
	 * @param String $value - the value of the parameter to set
	 * @return true on success or false if the parameters is already set.
	 */
	public function add_url_parameter( $name , $value )
	{
		if( ! array_key_exists( $name , $this->url_parameters ) )
		{
			$this->url_parameters[ $name ] = $value;
			return true;
		}
		else
		{
			$this->output->writeln( 'Parameter ' . $name . ' already exists in parameters.' );
			return false;
		}
	}

	/**
	 * removeUrlParameter
	 * 
	 * removes a key from this->url_parameters
	 *
	 * @author Richard Christensen
	 * @param String $name - the name of the parameter to remove
	 * @return bool false if the array_key is not set true if success
	 */
	protected function remove_url_parameter( $name )
	{
		if( ! array_key_exists( $name , $this->url_parameters ) )
		{
			return false;
		}

		unset( $this->url_parameters[ $name ] );
		return true;
	}

	/**
	 * rest_url_parameters
	 * 
	 * resets all of the Parameters that were set to this->url_parameters excluding auth_token and project_auth_token
	 *
	 * @author Richard Christensen
	 * 
	 * @return VOID
	 */
	protected function reset_url_parameters()
	{
		foreach( $this->url_parameters as $name => $value )
		{
			if( $name != 'auth_token' && $name != 'project_auth_token' )
			{
				unset( $this->url_parameters[ $name ] );
			}
		}
	}

	/**
	 * parameter_exists
	 * 
	 * Checks if a parameter is already set in this->url_parameters.
	 *
	 * @author Richard Christensen
	 * @param String $name - the name of the parameter to check existence.
	 * @return bool false if the parameter is not set and true if the parameter is set.
	 */
	protected function parameter_exists( $name )
	{
		if( ! array_key_exists( $name , $this->url_parameters ) )
		{
			return false;
		}

		return true;
	}

	/**
	 * call_api
	 * 
	 * Using cURL will send a REST request to Phrase app API with given URL extension and Method
	 *
	 * @author Richard Christensen
	 * @param String $method - 
	 * @param String $url - API path without base url
	 * @return JSON Object - Response from the API call
	 */
	protected function call_api( $method, $url )
	{
		$parameters = http_build_query( $this->url_parameters );
	    $curl = curl_init();
	    $url = $this->baseUrl . $url;
	    
	    switch ($method)
	    {
	        case "POST":
	            curl_setopt($curl, CURLOPT_POST, 1);

	            if ($this->url_parameters)
	                curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
	            break;
	        case "PUT":
	            curl_setopt($curl, CURLOPT_PUT, 1);
	            break;
	        default:
	            if ($this->url_parameters)
	                $url = sprintf( "%s?%s", $url, $parameters );
	   	}

	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

	    return curl_exec($curl);
	}
}