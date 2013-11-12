# PhraseApp REST API Wrapper
[PhraseApp] (http://www.PhraseApp.com) is a site that offers a way to manage translation files in a variety of formats.

## Objective
My objective with this project was to create a simple and easy to customize class that allows seamless integration of the PhraseApp REST API into any application.

## Installation
Just git clone this file into any directory in your project and begin working. By default there is no namespace for this class so some editing may be required to get it to work with your chosen PHP framework.

## Example Usage
Below is an example of how to download an xliff file using this class.

    <?php
    
    require( '/path/to/phrase_app_api.php');
    
    $phraseApp = new phrase_app_api();

    $phraseApp->set_email( 'email@domain.com' ); //your phrase app user name
    
    $phraseApp->set_password( 'passwordTest123' ); //you phrase app password
    
    $phraseApp->set_project_auth_token( 'getThisAuthTokenFromPhraseAppProjectSettings' ); //your phrase app project auth token
    
    $phraseApp->set_base_url( 'https://www.phraseapp.com/api/v1/' ); // (optional: default url is 'https://www.phraseapp.com/api/v1/' )
    
    $phraseApp->connect(); //Connects to phrase app and logs in using the above set credentials

    $url = 'translations/download'; // the REST API url you are querying

    $args = array(
    	'locale' => 'es_MX',
    	'format' => 'xliff',
    	'updated_since' => '20131112000000'
    	'tag' => 'optional',
    	'include_empty_translations' => false
    );

    $method = 'GET';

    if( $phraseApp->query( $url , $method , $args ) )
    {
    	$response = $phraseApp->get_response();
    	
    	//Do something with JSON response
    }
    else
    {
    	$errors = $phraseApp->get_errors();

    	//Do something with errors array.
    }

    ?>
