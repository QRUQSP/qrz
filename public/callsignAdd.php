<?php
//
// Description
// -----------
// This method will add a new callsign for the tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:              The ID of the tenant to add the Callsign to.
//
function qruqsp_qrz_callsignAdd(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'callsign'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Callsign'),
        'status'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Status'),
        'first'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'First'),
        'middle'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Middle'),
        'last'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Last'),
        'fullname'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Full Name'),
        'nickname'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Nickname'),
        'shortbio'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Short Bio'),
        'address1'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Address Line 1'),
        'address2'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Address Line 2'),
        'city'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'City'),
        'province'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Province/State'),
        'postal'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Postal/Zip Code'),
        'country'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Country'),
        'phone_number'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Phone Number'),
        'sms_number'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'SMS Number'),
        'email'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Email'),
        'latitude'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Latitude'),
        'longitude'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Longitude'),
        'gridsquare'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Grid Square'),
        'itu_zone'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'ITU Zone'),
        'cq_zone'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'CQ Zone'),
        'qrz_com_number'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'QRZ.com number'),
        'op_note'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Op Note'),
        'route_through_callsign'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Routing Callsign'),
        'logbooks'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Logbooks'),
        'licenses'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'idlist', 'name'=>'Licenses'),
        'groups'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'list', 'delimiter'=>'::', 'name'=>'Groups'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', 'qrz', 'private', 'checkAccess');
    $rc = qruqsp_qrz_checkAccess($ciniki, $args['tnid'], 'qruqsp.qrz.callsignAdd');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'qruqsp.qrz');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Add the callsign to the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'qruqsp.qrz.callsign', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'qruqsp.qrz');
        return $rc;
    }
    $callsign_id = $rc['id'];

    //
    // Update the licenses for a callsign
    //
    if( isset($args['licenses']) ) {
        ciniki_core_loadMethod($ciniki, 'qruqsp', 'qrz', 'private', 'callsignLicensesUpdate');
        $rc = qruqsp_qrz_callsignLicensesUpdate($ciniki, $args['tnid'], $callsign_id, $args['licenses']);
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'qruqsp.qrz');
            return $rc;
        }
    }

    //
    // Update the groups
    //
    if( isset($args['groups']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsUpdate');
        $rc = ciniki_core_tagsUpdate($ciniki, 'qruqsp.qrz', 'tag', $args['tnid'], 'qruqsp_qrz_tags', 'qruqsp_qrz_history', 'callsign_id', $callsign_id, 10, $args['groups']);
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'qruqsp.qrz');
            return $rc;
        }
    }

    //
    // Commit the transaction
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'qruqsp.qrz');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'qruqsp', 'qrz');

    //
    // Update the web index if enabled
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'hookExec');
    ciniki_core_hookExec($ciniki, $args['tnid'], 'qruqsp', 'web', 'indexObject', array('object'=>'qruqsp.qrz.callsign', 'object_id'=>$callsign_id));

    return array('stat'=>'ok', 'id'=>$callsign_id);
}
?>
