/**
 *  Implements hook_webform_handler_invoke_post_save_alter().
 *
 *  See also hook_webform_handler_invoke_METHOD_alter()
 *  and hook_webform_handler_invoke_alter().
 */
function MYMODULE_webform_handler_invoke_post_save_alter(\Drupal\webform\Plugin\WebformHandlerInterface $handler, array &$args) {
  
  // Create some variables for later ease of use.
  $webform = $handler->getWebform();
  $webform_submission = $handler->getWebformSubmission();
  $webform_id = $webform->id();
  $handler_id = $handler->getHandlerId();
  
  // Make sure to use your webform and handler ID's.
  if ($webform_id == 'FILL_IN_WITH_THE_ID_OF_YOUR_WEBFORM' && $handler_id == 'FILL_IN_WITH_THE_ID_OF_YOUR_REMOTE_HANDLER') {
    
    // Get the configuration for our handler.
    $configuration = $handler->getConfiguration();
    
    // Get the base URL that we will use for the redirect.
    $bookdirect_url = $configuration['settings']['completed_url'];
    
    // Alter the start date string  
    $start_date = $webform_submission->getElementData('start_date');
    $start_date = explode('-', $start_date);
    $start_date = [$start_date[1], $start_date[2], $start_date[0]];
    $start_date = implode('/', $start_date);
    $webform_submission->setElementData('start_date', $start_date);
    
    // Alter the end date
    $end_date = $webform_submission->getElementData('end_date');
    $end_date = explode('-', $start_date);
    $end_date = [$end_date[1], $end_date[2], $end_date[0]];
    $end_date = implode('/', $end_date);
    $webform_submission->setElementData('end_date', $end_date);
    
    // LodgingID field is our taxonomy term select field.
    // First check if a Loging ID value was submitted.
    if ($lodging_term_id = $webform_submission->getElementData('lodgingID')) {
    
      // Load up our term to test if it has a book direct value
      $lodging_term = \Drupal\taxonomy\Entity\Term::load($lodging_term_id);
      
      // If it has a bookdirect field and a value, switch out our tid value with our bookdirect ID value.
      if ($lodging_term->hasField('field_bookdirect_id') && $bookdirect_id = $lodging_term->get('field_bookdirect_id')->first()->getValue()) {
        $webform_submission->setElementData('lodgingID', $bookdirect_id['value']);
      };
    };
    
    // Now that we've made some changes, Let's start building our query array.
    $query = $webform_submission->getData();
    
    // Change the key of some submissions to a different key.
    $query['checkin'] = $query['start_date'];
    unset($query['start_date']);
    $query['checkout'] = $query['end_date'];
    unset($query['end_date']);
    
    // Add some new key/values to our query. This is where you can bring in variables from a different
    // module or some other value that you don't want to, or can't add in the webform UI
    $query['widget_id'] = 'WIDGET_ID_HERE';
    $query['campaign'] = 'CAMPAIGN_ID_HERE';
    
    // Build our URL with the query
    $url = \Drupal\Core\Url::fromUri($bookdirect_url);
    $url->setOptions(array('query' => $query));
    $destination = $url->toString();

    // Forward the user to the URL with the set query as a 301 redirect.
    $response = new RedirectResponse($destination, 301);
    $response->send();
   
  }
}
