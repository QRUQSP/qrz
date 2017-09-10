<?php
//
// Description
// ===========
//
// Arguments
// ---------
//
function qruqsp_qrz_noteUpdate(&$q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
        'note_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Note'),
        'callsign_id'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Callsign'),
        'note'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Note'),
        'note_date'=>array('required'=>'no', 'blank'=>'no', 'type'=>'date', 'name'=>'Date'),
        'note_time'=>array('required'=>'no', 'blank'=>'no', 'type'=>'time', 'name'=>'Time'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Make sure this module is activated, and
    // check permission to run this function for this station
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'qrz', 'private', 'checkAccess');
    $rc = qruqsp_qrz_checkAccess($q, $args['station_id'], 'qruqsp.qrz.noteUpdate');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    $strsql = "SELECT id, "
        . "DATE_FORMAT(note_date, '%Y-%m-%d') AS note_date, "
        . "DATE_FORMAT(note_date, '%H:%i:%s') AS note_time "
        . "FROM qruqsp_qrz_notes "
        . "WHERE id = '" . qruqsp_core_dbQuote($q, $args['note_id']) . "' "
        . "AND station_id = '" . qruqsp_core_dbQuote($q, $args['station_id']) . "' "
        . "";
    $rc = qruqsp_core_dbHashQuery($q, $strsql, $args['station_id'], 'note');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $note = $rc['note'];
    if( isset($args['note_date']) && isset($args['note_time']) ) {
        $args['note_date'] .= ' ' . $args['note_time'];
    } elseif( isset($args['note_date']) ) {
        $args['note_date'] .= ' ' . $note['note_time'];
    } elseif( isset($args['note_time']) ) {
        $args['note_date'] = $note['note_date'] . ' ' . $args['note_time'];
    }

    error_log(print_r($args, true));

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
    // Update the Note in the database
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'objectUpdate');
    $rc = qruqsp_core_objectUpdate($q, $args['station_id'], 'qruqsp.qrz.note', $args['note_id'], $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        qruqsp_core_dbTransactionRollback($q, 'qruqsp.qrz');
        return $rc;
    }

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
    qruqsp_core_hookExec($q, $args['station_id'], 'qruqsp', 'web', 'indexObject', array('object'=>'qruqsp.qrz.note', 'object_id'=>$args['note_id']));

    return array('stat'=>'ok');
}
?>