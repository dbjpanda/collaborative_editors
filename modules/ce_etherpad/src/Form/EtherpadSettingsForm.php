<?php

namespace Drupal\ce_etherpad\Form;

use Drupal\ce_etherpad\Plugin\CollaborativeNetwork\EtherpadEditor;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class EtherpadSettingsForm.
 */
class EtherpadSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ce_etherpad.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ce_etherpad_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ce_etherpad.settings');

    $apiUrl = $config->get('etherpad_api_url');
    $apiKey = $config->get('etherpad_api_key');
    if (isset($_SESSION["etherpad_api_url"])) {
      $apiUrl = $_SESSION["etherpad_api_url"];
      $apiKey = $_SESSION["etherpad_api_key"];
      unset($_SESSION["etherpad_api_url"]);
      unset($_SESSION["etherpad_api_key"]);
    }
    elseif (!isset($apiUrl)  || trim($apiUrl) === '') {
      $apiUrl = 'http://localhost:9001';
    }

    $form['etherpad_api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Etherpad API URL'),
      '#description' => $this->t('Enter Etherpad API URL. By default it is http://localhost:9001'),
      '#default_value' => $apiUrl,
    ];

    $form['etherpad_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Etherpad API Key'),
      '#description' => $this->t('Enter Etherpad API Key. You can find API Key in APIKEY.txt on root directory of Etherpad.'),
      '#default_value' => $apiKey,
    ];
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['test_connection'] = [
      '#type' => 'submit',
      '#value' => $this->t('Test connection'),
      '#submit' => ['::testConnection'],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('ce_etherpad.settings')
      ->set('etherpad_api_url', $form_state->getValue('etherpad_api_url'))
      ->set('etherpad_api_key', $form_state->getValue('etherpad_api_key'))
      ->save();

    if (!file_exists(DRUPAL_ROOT . '/libraries/ce_etherpad/js/etherpad.js')) {
      drupal_set_message(t('Download <a href="@url">Etherpad jQuery Plugin</a>, extract in @drupal_root/libraries directory and rename the folder "etherpad-lite-jquery-plugin-master" to "ce_etherpad".', ['@url' => 'https://github.com/ether/etherpad-lite-jquery-plugin/archive/master.zip', '@drupal_root' => DRUPAL_ROOT]), 'warning');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function testConnection(array &$form, FormStateInterface $form_state) {
    $etherpad = new EtherpadEditor($form_state->getValue('etherpad_api_key'), $form_state->getValue('etherpad_api_url'));
    $etherpad->testConnection();
    $_SESSION['etherpad_api_url'] = $form_state->getValue('etherpad_api_url');
    $_SESSION['etherpad_api_key'] = $form_state->getValue('etherpad_api_key');

    if (!file_exists(DRUPAL_ROOT . '/libraries/ce_etherpad/js/etherpad.js')) {
      drupal_set_message(t('Download <a href="@url">Etherpad jQuery Plugin</a>, extract in @drupal_root/libraries directory and rename the folder "etherpad-lite-jquery-plugin-master" to "ce_etherpad".', ['@url' => 'https://github.com/ether/etherpad-lite-jquery-plugin/archive/master.zip', '@drupal_root' => DRUPAL_ROOT]), 'warning');
    }
  }

}
