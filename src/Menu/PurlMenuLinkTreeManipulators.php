<?php

namespace Drupal\purl\Menu;

use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\purl\MatchedModifiers;


/**
 * Provides a couple of menu link tree manipulators.
 *
 * This class provides menu link tree manipulators to:
 * - perform render cached menu-optimized access checking
 * - optimized node access checking
 * - generate a unique index for the elements in a tree and sorting by it
 * - flatten a tree (i.e. a 1-dimensional tree)
 */
class PurlMenuLinkTreeManipulators {

  /**
   * @var MatchedModifiers
   */
  private $matchedModifiers;

  public function __construct(MatchedModifiers $matchedModifiers) {
    $this->matchedModifiers = $matchedModifiers;
  }

  public function contexts(array $tree) {
    /* @var $data MenuLinkTreeElement */

    foreach ($tree as $data) {
      $link = $data->link;
      var_dump($link->getUrlObject());
      var_dump($link->getUrlObject()->toString());
      var_dump("-------------------------------");
      $this->contexts($data->subtree);
    }

    return $tree;
  }

}
