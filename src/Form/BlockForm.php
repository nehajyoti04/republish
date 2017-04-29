<?php

/**
 * @file
 * Contains Drupal\republish_block\Form\AddForm
 */

namespace Drupal\republish_block\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ChangedCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form;

/**
 * Class AddForm.
 *
 * @package Drupal\republish_block\Form\AddForm
 */
class BlockForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'republish_block';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $callby, $conf) {

    $form = array();

    $form['republish_content_button'] = array(
      '#markup' => '<a href="#" id="republish_content_button" class="' . \Drupal\Component\Utility\SafeMarkup::checkPlain(\Drupal::config('republish.settings')->get("republish_link_css_class")) . '">' . \Drupal\Component\Utility\SafeMarkup::checkPlain(_republish_text($callby, 'link_text', $conf)) . '</a>',
    );
    $form['republish_content_overlay'] = array(
      '#type' => 'fieldset',
      '#prefix' => '<div id="republish_content_overlay">',
      '#attributes' => array(
        'style' => 'display:none',
      ),
      '#suffix' => '</div>',
    );
    $form['republish_content_overlay']['title'] = array(
      '#markup' => \Drupal\Component\Utility\SafeMarkup::checkPlain(_republish_text($callby, 'title', $conf)),
      '#prefix' => '<h2 id="republish_content_title">',
      '#suffix' => '</h2>',
    );
    $form['republish_content_overlay']['body'] = array(
      '#type' => 'fieldset',
      '#prefix' => '<div id="republish_content_body">',
      '#suffix' => '</div>',
    );
    $form['republish_content_overlay']['body']['guidelines'] = array(
      '#markup' => check_markup(_republish_text($callby, 'guidelines', $conf), 'filtered_html'),
      '#prefix' => '<p id="republish_content_guidelines">',
      '#suffix' => '</p>',
    );
    // Building content for republish.
    $content = _republish_text($callby, 'body', $conf, NULL, TRUE, $context) . _republish_text($callby, 'branding', $conf, NULL, TRUE, $context);
    $form['republish_content_overlay']['body']['main_body'] = array(
      '#type' => 'textarea',
      '#rows' => 10,
      '#default_value' => $content,
      '#description' => \Drupal\Component\Utility\SafeMarkup::checkPlain(_republish_text($callby, 'instructions', $conf)),
      '#attributes' => array(
        'onclick' => 'this.focus();this.select()',
      ),
    );

    $readonly = ($callby == 'ctools') ? (!empty($conf) ? $conf['body_readonly'] : 1) : \Drupal::config('republish.settings')->get('republish_body_readonly');
    if ($readonly) {
      $form['republish_content_overlay']['body']['main_body']['#attributes']['readonly'] = 'readonly';
    }

    // Adding css and js.
    $form['#attached']['css'] = array(
      drupal_get_path('module', 'republish') . '/css/republish.css',
    );

    $form['#attached']['js'] = array(
      drupal_get_path('module', 'republish') . '/js/republish.js',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  public function calculateAge(array &$form, FormStateInterface $form_state) {
    $output = '';
    // If birthdate is not empty.
    if (!empty($form_state->getValue('birthdate'))) {
      $birthdate_array = \explode('-', $form_state->getValue('birthdate'));
      $age_on_date_array = \explode('-', $form_state->getValue('age_on_date'));
      // Formatting user input.
      $birthdate = $birthdate_array[2] . '-' . $birthdate_array[1] . '-' . $birthdate_array[0];
      $age_on_date = $age_on_date_array[2] . '-' . $age_on_date_array[1] . '-' . $age_on_date_array[0];
      // Convert dates to timestamps.
      $birthdate_timestamp = strtotime($birthdate);
      $age_on_date_timestamp = strtotime($age_on_date);
      // Check if birthdate greater than age on time.
      if ($birthdate_timestamp <= $age_on_date_timestamp) {
        // Object declaration.
        $birthdate_datetime = new \DateTime($birthdate);
        $age_on_date_datetime = new \DateTime($age_on_date);

        // Including helper functions inc file.
        module_load_include('inc', 'age_calculator', 'age_calculator.helper_functions');
        // Getting output.
        $output = age_calculator_get_results($birthdate_datetime, $age_on_date_datetime);
      }
      else {
        $output = $this->t('ERROR: Age on date should not be lesser than date of birth.');
      }
      // debug($form_state->getValue('birthdate'), $label = 'date', $print_r = TRUE);
    }
    else {
      $output = $this->t('ERROR: Date of Birth Field can not be empty.');
    }
    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#age_calculator_calculated_age', $output));
    return $response;
  }

}
