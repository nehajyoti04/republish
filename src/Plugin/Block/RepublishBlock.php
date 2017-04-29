<?php

/**
 * @file
 * Contains Drupal\republish\Plugin\Block\RepublishBlock.
 */

namespace Drupal\republish\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Block\BlockBase;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'republish_block' block.
 *
 * @Block(
 *   id = "republish_block",
 *   admin_label = @Translation("Republish Block"),
 * )
 */
class RepublishBlock extends BlockBase implements BlockPluginInterface{


  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\republish_block\Form\BlockForm');

    return array(
      'add_this_page' => $form,
    );
  }

}
