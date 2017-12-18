<?php
//
// Description
// -----------
// This function will update the licenses for a callsign.
//
// Arguments
// ---------
//
function qruqsp_qrz_callsignLicensesUpdate(&$ciniki, $tnid, $callsign_id, $licenses) {

    //
    // Get the existing list of licenses for the callsign
    //
    $strsql = "SELECT id, uuid, license_id "
        . "FROM qruqsp_qrz_callsign_licenses "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND callsign_id = '" . ciniki_core_dbQuote($ciniki, $callsign_id) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'qruqsp.foodmarket', array(
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
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    foreach($licenses as $license_id) {
        if( !isset($existing_licenses[$license_id]) ) {
            $rc = ciniki_core_objectAdd($ciniki, $tnid, 'qruqsp.qrz.callsignlicense', array(
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
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    foreach($existing_licenses as $license_id => $license) {
        if( !in_array($license_id, $licenses) ) {
            $rc = ciniki_core_objectDelete($ciniki, $tnid, 'qruqsp.qrz.callsignlicense', $license['id'], $license['uuid'], 0x04);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }
    }

    return array('stat'=>'ok');
}
?>
