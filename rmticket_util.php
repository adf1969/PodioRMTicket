<?php


function test_item_diff() {
  $rmt_item_id = 720406510;
  $rmt_rev_id = 2;
  
    // Get the Item
  $rmt_item = PodioItem::get_basic($rmt_item_id);
  $rmt_item_diff = PodioItemDiff::get_for($rmt_item_id, $rmt_rev_id - 1, $rmt_rev_id );

  $changed = p_field_value_changed($rmt_item, $rmt_item_diff, "location");
  
  
}

function pi_test_item_diff() {
  $rmt_item_id = 720406510;
  $rmt_rev_id = 2;
  
    // Get the Item
  $rmt_item = AvcPodioItem::get_basic($rmt_item_id);
  $field_id = $rmt_item->getFieldId("location");
  return $rmt_item->fieldChanged("location");
  
}

function p_get_field_id($item, $ext_id) {
  return $item->fields[$ext_id]->field_id;  
}

function p_field_value_changed($item, $item_diff, $ext_id) {
  $field_id = p_get_field_id($item, $ext_id);
  
  // loop thru each item_diff entry. $item_diff is an array of "changes"
  // We have to get the $field_id since for some reason, PodioItemDiff doesn't
  // SET the "external_id" in the property list...lazy...lazy...
  foreach ($item_diff as $e) {
    if ($e->field_id == $field_id) {
      return true;
    }
  }
  
//  foreach ($item_diff as $f) {
//    if ($f["field_id"] == $field_id) {
//      return true;
//    }
//  }
//  return false;
}


