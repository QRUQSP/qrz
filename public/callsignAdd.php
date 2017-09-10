<?php
//
// Description
// -----------
// This method will add a new callsign for the station.
//
// Arguments
// ---------
// api_key:
// auth_token:
// station_id:        The ID of the station to add the Callsign to.
//
function qruqsp_qrz_callsignAdd(&$q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
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
        'license'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'License'),
        'latitude'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Latitude'),
        'longitude'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Longitude'),
        'gridsquare'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Grid Square'),
        'itu_zone'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'ITU Zone'),
        'cq_zone'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'CQ Zone'),
        'qrz_com_number'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'QRZ.com number'),
        'op_note'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Op Note'),
        'route_through_callsign'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Routing Callsign'),
        'logbooks'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Logbooks'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to station_id as owner
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'qrz', 'private', 'checkAccess');
    $rc = qruqsp_qrz_checkAccess($q, $args['station_id'], 'qruqsp.qrz.callsignAdd');
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
    // Add the callsign to the database
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'objectAdd');
    $rc = qruqsp_core_objectAdd($q, $args['station_id'], 'qruqsp.qrz.callsign', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        qruqsp_core_dbTransactionRollback($q, 'qruqsp.qrz');
        return $rc;
    }
    $callsign_id = $rc['id'];

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
    qruqsp_core_hookExec($q, $args['station_id'], 'qruqsp', 'web', 'indexObject', array('object'=>'qruqsp.qrz.callsign', 'object_id'=>$callsign_id));

    return array('stat'=>'ok', 'id'=>$callsign_id);
}
?>
