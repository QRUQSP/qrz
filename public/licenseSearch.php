<?php
//
// Description
// -----------
// This method searchs for a Licenses for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:               The ID of the tenant to get License for.
// start_needle:       The search string to search for.
// limit:              The maximum number of entries to return.
//
function qruqsp_qrz_licenseSearch($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'start_needle'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Search String'),
        'limit'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Limit'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', 'qrz', 'private', 'checkAccess');
    $rc = qruqsp_qrz_checkAccess($ciniki, $args['tnid'], 'qruqsp.qrz.licenseSearch');
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
        . "WHERE qruqsp_qrz_licenses.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ("
            . "name LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR name LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
        . ") "
        . "";
    if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
    } else {
        $strsql .= "LIMIT 25 ";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'qruqsp.qrz', array(
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
