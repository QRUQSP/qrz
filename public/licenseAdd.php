<?php
//
// Description
// -----------
// This method will add a new license for the station.
//
// Arguments
// ---------
// api_key:
// auth_token:
// station_id:        The ID of the station to add the License to.
//
function qruqsp_qrz_licenseAdd(&$q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
        'name'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'License name'),
        'permalink'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'License name'),
        'notes'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Notes'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to station_id as owner
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'qrz', 'private', 'checkAccess');
    $rc = qruqsp_qrz_checkAccess($q, $args['station_id'], 'qruqsp.qrz.licenseAdd');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Setup permalink
    //
    if( !isset($args['permalink']) || $args['permalink'] == '' ) {
        qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'makePermalink');
        $args['permalink'] = qruqsp_core_makePermalink($q, $args['name']);
    }

    //
    // Make sure the permalink is unique
    //
    $strsql = "SELECT id, name, permalink "
        . "FROM qruqsp_qrz_licenses "
        . "WHERE station_id = '" . qruqsp_core_dbQuote($q, $args['station_id']) . "' "
        . "AND permalink = '" . qruqsp_core_dbQuote($q, $args['permalink']) . "' "
        . "";
    $rc = qruqsp_core_dbHashQuery($q, $strsql, 'qruqsp.qrz', 'item');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( $rc['num_rows'] > 0 ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'qruqsp', 'code'=>'14', 'msg'=>'You already have a license with that name, please choose another.'));
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
    // Add the license to the database
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'objectAdd');
    $rc = qruqsp_core_objectAdd($q, $args['station_id'], 'qruqsp.qrz.license', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        qruqsp_core_dbTransactionRollback($q, 'qruqsp.qrz');
        return $rc;
    }
    $license_id = $rc['id'];

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
    qruqsp_core_hookExec($q, $args['station_id'], 'qruqsp', 'web', 'indexObject', array('object'=>'qruqsp.qrz.license', 'object_id'=>$license_id));

    return array('stat'=>'ok', 'id'=>$license_id);
}
?>
