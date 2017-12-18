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
// tnid:               The ID of the tenant to get the details for.
// callsign_id:          The ID of the callsign to get the history for.
// field:                   The field to get the history for.
//
function qruqsp_qrz_callsignHistory($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'callsign_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Callsign'),
        'field'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'field'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', 'qrz', 'private', 'checkAccess');
    $rc = qruqsp_qrz_checkAccess($ciniki, $args['tnid'], 'qruqsp.qrz.callsignHistory');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Check if the requested field is the groups field
    //
    if( $args['field'] == 'groups' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistoryTags');
        return ciniki_core_dbGetModuleHistoryTags($ciniki, 'qruqsp.qrz',
            'qruqsp_qrz_history', $args['tnid'],
            'qruqsp_qrz_tags', $args['callsign_id'], 'tag_name', 'callsign_id', 10);
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistory');
    return ciniki_core_dbGetModuleHistory($ciniki, 'qruqsp.qrz', 'qruqsp_qrz_history', $args['tnid'], 'qruqsp_qrz_callsigns', $args['callsign_id'], $args['field']);
}
?>
