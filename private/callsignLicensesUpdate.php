<?php
//
// Description
// -----------
// This function will update the licenses for a callsign.
//
// Arguments
// ---------
//
function qruqsp_qrz_callsignLicensesUpdate(&$q, $station_id, $callsign_id, $licenses) {

    //
    // Get the existing list of licenses for the callsign
    //
    $strsql = "SELECT id, uuid, license_id "
        . "FROM qruqsp_qrz_callsign_licenses "
        . "WHERE station_id = '" . qruqsp_core_dbQuote($q, $station_id) . "' "
        . "AND callsign_id = '" . qruqsp_core_dbQuote($q, $callsign_id) . "' "
        . "";
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbHashQueryIDTree');
    $rc = qruqsp_core_dbHashQueryIDTree($q, $strsql, 'qruqsp.foodmarket', array(
        array('container'=>'licenses', 'fname'=>'license_id', 'fields'=>array('id', 'uuid', 'license_id')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['licenses']) ) {
        $existing_licenses = $rc['licenses'];
    } else {
        $existing_licenses = array();
    }

    //
    // Check for any new licenses that need to be added
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'objectAdd');
    foreach($licenses as $license_id) {
        if( !isset($existing_licenses[$license_id]) ) {
            $rc = qruqsp_core_objectAdd($q, $station_id, 'qruqsp.qrz.callsignlicense', array(
                'license_id'=>$license_id,
                'callsign_id'=>$callsign_id,
                ), 0x04);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }
    }

    //
    // Check for any licenses that need to be removed
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'objectDelete');
    foreach($existing_licenses as $license_id => $license) {
        if( !in_array($license_id, $licenses) ) {
            $rc = qruqsp_core_objectDelete($q, $station_id, 'qruqsp.qrz.callsignlicense', $license['id'], $license['uuid'], 0x04);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }
    }

    return array('stat'=>'ok');
}
?>
