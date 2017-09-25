<?php
//
// Description
// -----------
// This method searchs for a Callsigns for a station.
//
// Arguments
// ---------
// api_key:
// auth_token:
// station_id:         The ID of the station to get Callsign for.
// start_needle:       The search string to search for.
// limit:              The maximum number of entries to return.
//
function qruqsp_qrz_callsignSearch($q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
        'start_needle'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Search String'),
        'limit'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Limit'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to station_id as owner, or sys admin.
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'qrz', 'private', 'checkAccess');
    $rc = qruqsp_qrz_checkAccess($q, $args['station_id'], 'qruqsp.qrz.callsignSearch');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load the maps
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'qrz', 'private', 'maps');
    $rc = qruqsp_qrz_maps($q);
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
        . "WHERE qruqsp_qrz_callsigns.station_id = '" . qruqsp_core_dbQuote($q, $args['station_id']) . "' "
        . "AND ("
            . "callsign LIKE '" . qruqsp_core_dbQuote($q, $args['start_needle']) . "%' "
            . "OR callsign LIKE '% " . qruqsp_core_dbQuote($q, $args['start_needle']) . "%' "
            . "OR first LIKE '" . qruqsp_core_dbQuote($q, $args['start_needle']) . "%' "
            . "OR first LIKE '% " . qruqsp_core_dbQuote($q, $args['start_needle']) . "%' "
            . "OR last LIKE '" . qruqsp_core_dbQuote($q, $args['start_needle']) . "%' "
            . "OR last LIKE '% " . qruqsp_core_dbQuote($q, $args['start_needle']) . "%' "
            . "OR nickname LIKE '" . qruqsp_core_dbQuote($q, $args['start_needle']) . "%' "
            . "OR nickname LIKE '% " . qruqsp_core_dbQuote($q, $args['start_needle']) . "%' "
            . "OR phone_number LIKE '" . qruqsp_core_dbQuote($q, $args['start_needle']) . "%' "
            . "OR phone_number LIKE '% " . qruqsp_core_dbQuote($q, $args['start_needle']) . "%' "
            . "OR sms_number LIKE '" . qruqsp_core_dbQuote($q, $args['start_needle']) . "%' "
            . "OR sms_number LIKE '% " . qruqsp_core_dbQuote($q, $args['start_needle']) . "%' "
        . ") "
        . "";
    if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . qruqsp_core_dbQuote($q, $args['limit']) . " ";
    } else {
        $strsql .= "LIMIT 25 ";
    }
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = qruqsp_core_dbHashQueryArrayTree($q, $strsql, 'qruqsp.qrz', array(
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
