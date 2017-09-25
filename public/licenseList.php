<?php
//
// Description
// -----------
// This method will return the list of Licenses for a station.
//
// Arguments
// ---------
// api_key:
// auth_token:
// station_id:        The ID of the station to get License for.
//
function qruqsp_qrz_licenseList($q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to station_id as owner, or sys admin.
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'qrz', 'private', 'checkAccess');
    $rc = qruqsp_qrz_checkAccess($q, $args['station_id'], 'qruqsp.qrz.licenseList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of licenses
    //
    $strsql = "SELECT qruqsp_qrz_licenses.id, "
        . "qruqsp_qrz_licenses.name, "
        . "qruqsp_qrz_licenses.permalink "
        . "FROM qruqsp_qrz_licenses "
        . "WHERE qruqsp_qrz_licenses.station_id = '" . qruqsp_core_dbQuote($q, $args['station_id']) . "' "
        . "";
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = qruqsp_core_dbHashQueryArrayTree($q, $strsql, 'qruqsp.qrz', array(
        array('container'=>'licenses', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'permalink')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['licenses']) ) {
        $licenses = $rc['licenses'];
        $license_ids = array();
        foreach($licenses as $iid => $license) {
            $license_ids[] = $license['id'];
        }
    } else {
        $licenses = array();
        $license_ids = array();
    }

    return array('stat'=>'ok', 'licenses'=>$licenses, 'nplist'=>$license_ids);
}
?>
