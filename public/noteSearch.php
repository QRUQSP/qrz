<?php
//
// Description
// -----------
// This method searchs for a Notes for a station.
//
// Arguments
// ---------
// api_key:
// auth_token:
// station_id:         The ID of the station to get Note for.
// start_needle:       The search string to search for.
// limit:              The maximum number of entries to return.
//
function qruqsp_qrz_noteSearch($q) {
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
    $rc = qruqsp_qrz_checkAccess($q, $args['station_id'], 'qruqsp.qrz.noteSearch');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of notes
    //
    $strsql = "SELECT qruqsp_qrz_notes.id, "
        . "qruqsp_qrz_notes.callsign_id, "
        . "qruqsp_qrz_notes.note_date "
        . "FROM qruqsp_qrz_notes "
        . "WHERE qruqsp_qrz_notes.station_id = '" . qruqsp_core_dbQuote($q, $args['station_id']) . "' "
        . "AND ("
            . "name LIKE '" . qruqsp_core_dbQuote($q, $args['start_needle']) . "%' "
            . "OR name LIKE '% " . qruqsp_core_dbQuote($q, $args['start_needle']) . "%' "
        . ") "
        . "";
    if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . qruqsp_core_dbQuote($q, $args['limit']) . " ";
    } else {
        $strsql .= "LIMIT 25 ";
    }
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = qruqsp_core_dbHashQueryArrayTree($q, $strsql, 'qruqsp.qrz', array(
        array('container'=>'notes', 'fname'=>'id', 
            'fields'=>array('id', 'callsign_id', 'note_date')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['notes']) ) {
        $notes = $rc['notes'];
        $note_ids = array();
        foreach($notes as $iid => $note) {
            $note_ids[] = $note['id'];
        }
    } else {
        $notes = array();
        $note_ids = array();
    }

    return array('stat'=>'ok', 'notes'=>$notes, 'nplist'=>$note_ids);
}
?>
