<?php

/**
 * Written by Larry Stanbery - 20 May 2004
 * Same license as FPDF
 * creates "page groups" -- groups of pages with page numbering
 * total page numbers are represented by aliases of the form {nbX}.
 */
class PDF_PageGroup extends FPDF {
  /**
   * Variable indicating whether a new group was requested.
   */
  protected $NewPageGroup = FALSE;
  /**
   * Variable containing the number of pages of the groups.
   */
  protected $PageGroups = [];
  /**
   * Variable containing the alias of the current page group.
   */
  protected $CurrPageGroup;

  /**
   * Create a new page group; call this before calling AddPage()
   */
  public function StartPageGroup() {
    $this->NewPageGroup = TRUE;
  }

  /**
   * Current page in the group.
   */
  public function GroupPageNo() {
    return $this->PageGroups[$this->CurrPageGroup];
  }

  /**
   * Alias of the current page group -- will be replaced by the total number of pages in this group.
   */
  public function PageGroupAlias() {
    return $this->CurrPageGroup;
  }

  /**
   *
   */
  public function _beginpage($orientation, $size, $rotation) {
    parent::_beginpage($orientation, $size, $rotation);
    if ($this->NewPageGroup) {
      // Start a new group.
      $n = sizeof($this->PageGroups) + 1;
      $alias = "{nb$n}";
      $this->PageGroups[$alias] = 1;
      $this->CurrPageGroup = $alias;
      $this->NewPageGroup = FALSE;
    }
    elseif ($this->CurrPageGroup) {
      $this->PageGroups[$this->CurrPageGroup]++;
    }
  }

  /**
   *
   */
  public function _putpages() {
    $nb = $this->page;
    if (!empty($this->PageGroups)) {
      // Do page number replacement.
      foreach ($this->PageGroups as $k => $v) {
        for ($n = 1; $n <= $nb; $n++) {
          $this->pages[$n] = str_replace($k, $v, $this->pages[$n]);
        }
      }
    }
    parent::_putpages();
  }

}
