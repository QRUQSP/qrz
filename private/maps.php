<?php
//
// Description
// -----------
// This function returns the int to text mappings for the module.
//
// Arguments
// ---------
// q: 
//
function qruqsp_qrz_maps(&$q) {
    //
    // Build the maps object
    //
    $maps = array();
    $maps['callsign'] = array('status'=>array(
        '10'=>'Active',
        '60'=>'Archive',
    ));

    //
    // Return the maps array
    //
    return array('stat'=>'ok', 'maps'=>$maps);
}
?>
