<?php
//
// Description
// ===========
// This method will return all the information about an license.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:               The ID of the tenant the license is attached to.
// license_id:          The ID of the license to get the details for.
//
function qruqsp_qrz_licenseGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'license_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'License'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', 'qrz', 'private', 'checkAccess');
    $rc = qruqsp_qrz_checkAccess($ciniki, $args['tnid'], 'qruqsp.qrz.licenseGet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'php');

    //
    // Return default for new License
    //
    if( $args['license_id'] == 0 ) {
        $license = array('id'=>0,
            'name'=>'',
            'permalink'=>'',
            'notes'=>'',
        );
    }

    //
    // Get the details for an existing License
    //
    else {
        $strsql = "SELECT qruqsp_qrz_licenses.id, "
            . "qruqsp_qrz_licenses.name, "
            . "qruqsp_qrz_licenses.permalink, "
            . "qruqsp_qrz_licenses.notes "
            . "FROM qruqsp_qrz_licenses "
            . "WHERE qruqsp_qrz_licenses.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND qruqsp_qrz_licenses.id = '" . ciniki_core_dbQuote($ciniki, $args['license_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'qruqsp.qrz', array(
            array('container'=>'licenses', 'fname'=>'id', 
                'fields'=>array('name', 'permalink', 'notes'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qrz.18', 'msg'=>'License not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['licenses'][0]) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qrz.19', 'msg'=>'Unable to find License'));
        }
        $license = $rc['licenses'][0];
    }

    return array('stat'=>'ok', 'license'=>$license);
}
?>
