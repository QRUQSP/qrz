<?php
//
// Description
// -----------
// This method searchs for a Callsigns for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:               The ID of the tenant to get Callsign for.
// start_needle:       The search string to search for.
// limit:              The maximum number of entries to return.
//
function qruqsp_qrz_callsignSearch($ciniki) {
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
    $rc = qruqsp_qrz_checkAccess($ciniki, $args['tnid'], 'qruqsp.qrz.callsignSearch');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load the maps
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', 'qrz', 'private', 'maps');
    $rc = qruqsp_qrz_maps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $maps = $rc['maps'];

    //
    // Get the list of callsigns
    //
    $strsql = "SELECT qruqsp_qrz_callsigns.id, "
        . "qruqsp_qrz_callsigns.callsign, "
        . "qruqsp_qrz_callsigns.status, "
        . "qruqsp_qrz_callsigns.status AS status_text, "
        . "qruqsp_qrz_callsigns.first, "
        . "qruqsp_qrz_callsigns.middle, "
        . "qruqsp_qrz_callsigns.last, "
        . "qruqsp_qrz_callsigns.fullname, "
        . "qruqsp_qrz_callsigns.nickname, "
        . "qruqsp_qrz_callsigns.shortbio, "
        . "qruqsp_qrz_callsigns.phone_number, "
        . "qruqsp_qrz_callsigns.sms_number, "
        . "qruqsp_qrz_callsigns.email, "
        . "qruqsp_qrz_callsigns.latitude, "
        . "qruqsp_qrz_callsigns.longitude, "
        . "qruqsp_qrz_callsigns.gridsquare, "
        . "qruqsp_qrz_callsigns.itu_zone, "
        . "qruqsp_qrz_callsigns.cq_zone, "
        . "qruqsp_qrz_callsigns.qrz_com_number, "
        . "qruqsp_qrz_callsigns.op_note, "
        . "qruqsp_qrz_callsigns.route_through_callsign, "
        . "qruqsp_qrz_callsigns.logbooks "
        . "FROM qruqsp_qrz_callsigns "
        . "WHERE qruqsp_qrz_callsigns.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ("
            . "callsign LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR callsign LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR first LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR first LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR last LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR last LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR nickname LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR nickname LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR phone_number LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR phone_number LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR sms_number LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR sms_number LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
        . ") "
        . "";
    if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
    } else {
        $strsql .= "LIMIT 25 ";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'qruqsp.qrz', array(
        array('container'=>'callsigns', 'fname'=>'id', 
            'fields'=>array('id', 'callsign', 'status', 'status_text',
                'first', 'middle', 'last', 'fullname', 'nickname', 'shortbio', 
                'phone_number', 'sms_number', 'email',
                'latitude', 'longitude', 'gridsquare', 'itu_zone', 'cq_zone', 'qrz_com_number', 
                'op_note', 'route_through_callsign', 'logbooks'),
            'maps'=>array('status_text'=>$maps['callsign']['status']),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['callsigns']) ) {
        $callsigns = $rc['callsigns'];
        $callsign_ids = array();
        foreach($callsigns as $iid => $callsign) {
            $callsign_ids[] = $callsign['id'];
        }
    } else {
        $callsigns = array();
        $callsign_ids = array();
    }

    return array('stat'=>'ok', 'callsigns'=>$callsigns, 'nplist'=>$callsign_ids);
}
?>
