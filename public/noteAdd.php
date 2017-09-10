<?php
//
// Description
// -----------
// This method will add a new note for the station.
//
// Arguments
// ---------
// api_key:
// auth_token:
// station_id:        The ID of the station to add the Note to.
//
function qruqsp_qrz_noteAdd(&$q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
        'callsign_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Callsign'),
        'note'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Note'),
        'note_date'=>array('required'=>'yes', 'blank'=>'no', 'type'=>'date', 'name'=>'Date'),
        'note_time'=>array('required'=>'yes', 'blank'=>'no', 'type'=>'time', 'name'=>'Time'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    $args['note_date'] .= ' ' . $args['note_time'];

    //
    // Check access to station_id as owner
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'qrz', 'private', 'checkAccess');
    $rc = qruqsp_qrz_checkAccess($q, $args['station_id'], 'qruqsp.qrz.noteAdd');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Start transaction
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbTransactionStart');
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbTransactionRollback');
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbTransactionCommit');
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbAddModuleHistory');
    $rc = qruqsp_core_dbTransactionStart($q, 'qruqsp.qrz');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Add the note to the database
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'objectAdd');
    $rc = qruqsp_core_objectAdd($q, $args['station_id'], 'qruqsp.qrz.note', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        qruqsp_core_dbTransactionRollback($q, 'qruqsp.qrz');
        return $rc;
    }
    $note_id = $rc['id'];

    //
    // Commit the transaction
    //
    $rc = qruqsp_core_dbTransactionCommit($q, 'qruqsp.qrz');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the station modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'updateModuleChangeDate');
    qruqsp_core_updateModuleChangeDate($q, $args['station_id'], 'qruqsp', 'qrz');

    //
    // Update the web index if enabled
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'hookExec');
    qruqsp_core_hookExec($q, $args['station_id'], 'qruqsp', 'web', 'indexObject', array('object'=>'qruqsp.qrz.note', 'object_id'=>$note_id));

    return array('stat'=>'ok', 'id'=>$note_id);
}
?>
