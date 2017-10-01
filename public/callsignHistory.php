<?php
//
// Description
// -----------
// This method will return the list of actions that were applied to an element of an callsign.
// This method is typically used by the UI to display a list of changes that have occured
// on an element through time. This information can be used to revert elements to a previous value.
//
// Arguments
// ---------
// api_key:
// auth_token:
// station_id:         The ID of the station to get the details for.
// callsign_id:          The ID of the callsign to get the history for.
// field:                   The field to get the history for.
//
function qruqsp_qrz_callsignHistory($q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
        'callsign_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Callsign'),
        'field'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'field'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to station_id as owner, or sys admin
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'qrz', 'private', 'checkAccess');
    $rc = qruqsp_qrz_checkAccess($q, $args['station_id'], 'qruqsp.qrz.callsignHistory');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Check if the requested field is the groups field
    //
    if( $args['field'] == 'groups' ) {
        qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbGetModuleHistoryTags');
        return qruqsp_core_dbGetModuleHistoryTags($q, 'qruqsp.qrz',
            'qruqsp_qrz_history', $args['station_id'],
            'qruqsp_qrz_tags', $args['callsign_id'], 'tag_name', 'callsign_id', 10);
    }

    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbGetModuleHistory');
    return qruqsp_core_dbGetModuleHistory($q, 'qruqsp.qrz', 'qruqsp_qrz_history', $args['station_id'], 'qruqsp_qrz_callsigns', $args['callsign_id'], $args['field']);
}
?>
