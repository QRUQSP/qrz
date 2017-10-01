<?php
//
// Description
// ===========
//
// Arguments
// ---------
//
function qruqsp_qrz_callsignUpdate(&$q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
        'callsign_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Callsign'),
        'callsign'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Callsign'),
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
    // Make sure this module is activated, and
    // check permission to run this function for this station
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'qrz', 'private', 'checkAccess');
    $rc = qruqsp_qrz_checkAccess($q, $args['station_id'], 'qruqsp.qrz.callsignUpdate');
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
    // Update the Callsign in the database
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'objectUpdate');
    $rc = qruqsp_core_objectUpdate($q, $args['station_id'], 'qruqsp.qrz.callsign', $args['callsign_id'], $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        qruqsp_core_dbTransactionRollback($q, 'qruqsp.qrz');
        return $rc;
    }

    //
    // Update the licenses for a callsign
    //
    if( isset($args['licenses']) ) {
        qruqsp_core_loadMethod($q, 'qruqsp', 'qrz', 'private', 'callsignLicensesUpdate');
        $rc = qruqsp_qrz_callsignLicensesUpdate($q, $args['station_id'], $args['callsign_id'], $args['licenses']);
        if( $rc['stat'] != 'ok' ) {
            qruqsp_core_dbTransactionRollback($q, 'qruqsp.qrz');
            return $rc;
        }
    }

    //
    // Update the groups
    //
    if( isset($args['groups']) ) {
        qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'tagsUpdate');
        $rc = qruqsp_core_tagsUpdate($q, 'qruqsp.qrz.tag', $args['station_id'], 'callsign_id', $args['callsign_id'], 10, $args['groups']);
        if( $rc['stat'] != 'ok' ) {
            qruqsp_core_dbTransactionRollback($q, 'qruqsp.qrz');
            return $rc;
        }
        qruqsp_core_dbAddModuleHistory($q, 'qruqsp.qrz', 'qruqsp_qrz_history', $args['station_id'],
            2, 'qruqsp_qrz_callsigns', $args['callsign_id'], 'groups', implode('::', $args['groups']));
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
    qruqsp_core_hookExec($q, $args['station_id'], 'qruqsp', 'web', 'indexObject', array('object'=>'qruqsp.qrz.callsign', 'object_id'=>$args['callsign_id']));

    return array('stat'=>'ok');
}
?>
