<?php
//
// Description
// -----------
// This method will delete an license.
//
// Arguments
// ---------
// api_key:
// auth_token:
// station_id:            The ID of the station the license is attached to.
// license_id:            The ID of the license to be removed.
//
function qruqsp_qrz_licenseDelete(&$q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
        'license_id'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'License'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to station_id as owner
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'qrz', 'private', 'checkAccess');
    $rc = qruqsp_qrz_checkAccess($q, $args['station_id'], 'qruqsp.qrz.licenseDelete');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the current settings for the license
    //
    $strsql = "SELECT id, uuid "
        . "FROM qruqsp_qrz_licenses "
        . "WHERE station_id = '" . qruqsp_core_dbQuote($q, $args['station_id']) . "' "
        . "AND id = '" . qruqsp_core_dbQuote($q, $args['license_id']) . "' "
        . "";
    $rc = qruqsp_core_dbHashQuery($q, $strsql, 'qruqsp.qrz', 'license');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['license']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qrz.15', 'msg'=>'License does not exist.'));
    }
    $license = $rc['license'];

    //
    // Check for any dependencies before deleting
    //

    //
    // Check if any modules are currently using this object
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'objectCheckUsed');
    $rc = qruqsp_core_objectCheckUsed($q, $args['station_id'], 'qruqsp.qrz.license', $args['license_id']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qrz.16', 'msg'=>'Unable to check if the license is still being used.', 'err'=>$rc['err']));
    }
    if( $rc['used'] != 'no' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qrz.17', 'msg'=>'The license is still in use. ' . $rc['msg']));
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
    // Remove the license
    //
    $rc = qruqsp_core_objectDelete($q, $args['station_id'], 'qruqsp.qrz.license',
        $args['license_id'], $license['uuid'], 0x04);
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
