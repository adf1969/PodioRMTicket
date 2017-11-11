<?php


/**
 * Description of AvcPodioItem
 *
 * @author fields
 */

class AvcPodioItem extends PodioItem  {
  
  private $diffItem = null;
  
  // Gets the PodioDiffItem comparing the CURRENT VERSION with the LAST VERSION
  protected function getDiffItem() {
    if ($this->diffItem == null) {
      $this->diffItem = PodioItemDiff::get_for($this->item_id, $this->current_revision->revision - 1, $this->current_revision->revision);
    }
    return $this->diffItem;
  }
    
  public function getFieldId($ext_id) {
    return $this->fields[$ext_id]->field_id;  
  }
  
  public function fieldChanged($ext_id) {
    $field_id = $this->getFieldId($ext_id);

    // loop thru each item_diff entry. $item_diff is an array of "changes"
    // We have to get the $field_id since for some reason, PodioItemDiff doesn't
    // SET the "external_id" in the property list...lazy...lazy...
    $item_diff = $this->getDiffItem();
    foreach ($item_diff as $e) {
      if ($e->field_id == $field_id) {
        return true;
      }
    }    
  }
}
