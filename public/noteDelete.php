<?php
//
// Description
// -----------
// This method will delete an note.
//
// Arguments
// ---------
// api_key:
// auth_token:
// station_id:            The ID of the station the note is attached to.
// note_id:            The ID of the note to be removed.
//
function qruqsp_qrz_noteDelete(&$q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
        'note_id'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Note'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to station_id as owner
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'qrz', 'private', 'checkAccess');
    $rc = qruqsp_qrz_checkAccess($q, $args['station_id'], 'qruqsp.qrz.noteDelete');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the current settings for the note
    //
    $strsql = "SELECT id, uuid "
        . "FROM qruqsp_qrz_notes "
        . "WHERE station_id = '" . qruqsp_core_dbQuote($q, $args['station_id']) . "' "
        . "AND id = '" . qruqsp_core_dbQuote($q, $args['note_id']) . "' "
        . "";
    $rc = qruqsp_core_dbHashQuery($q, $strsql, 'qruqsp.qrz', 'note');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['note']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qrz.8', 'msg'=>'Note does not exist.'));
    }
    $note = $rc['note'];

    //
    // Check for any dependencies before deleting
    //

    //
    // Check if any modules are currently using this object
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'objectCheckUsed');
    $rc = qruqsp_core_objectCheckUsed($q, $args['station_id'], 'qruqsp.qrz.note', $args['note_id']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qrz.9', 'msg'=>'Unable to check if the note is still being used.', 'err'=>$rc['err']));
    }
    if( $rc['used'] != 'no' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qrz.10', 'msg'=>'The note is still in use. ' . $rc['msg']));
    }

    //
    // Start transaction
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbTransactionStart');
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbTransactionRollback');
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbTransactionCommit');
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbDelete');
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'objectDelete');
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbAddModuleHistory');
    $rc = qruqsp_core_dbTransactionStart($q, 'qruqsp.qrz');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Remove the note
    //
    $rc = qruqsp_core_objectDelete($q, $args['station_id'], 'qruqsp.qrz.note',
        $args['note_id'], $note['uuid'], 0x04);
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

    return array('stat'=>'ok');
}
?>
