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
// station_id:         The ID of the station the license is attached to.
// license_id:          The ID of the license to get the details for.
//
function qruqsp_qrz_licenseGet($q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
        'license_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'License'),
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
    $rc = qruqsp_qrz_checkAccess($q, $args['station_id'], 'qruqsp.qrz.licenseGet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load station settings
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'intlSettings');
    $rc = qruqsp_core_intlSettings($q, $args['station_id']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dateFormat');
    $date_format = qruqsp_core_dateFormat($q, 'php');

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
            . "WHERE qruqsp_qrz_licenses.station_id = '" . qruqsp_core_dbQuote($q, $args['station_id']) . "' "
            . "AND qruqsp_qrz_licenses.id = '" . qruqsp_core_dbQuote($q, $args['license_id']) . "' "
            . "";
        qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = qruqsp_core_dbHashQueryArrayTree($q, $strsql, 'qruqsp.qrz', array(
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
