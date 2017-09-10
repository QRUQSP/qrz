<?php
//
// Description
// ===========
// This method will return all the information about an note.
//
// Arguments
// ---------
// api_key:
// auth_token:
// station_id:         The ID of the station the note is attached to.
// note_id:          The ID of the note to get the details for.
//
function qruqsp_qrz_noteGet($q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
        'note_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Note'),
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
    $rc = qruqsp_qrz_checkAccess($q, $args['station_id'], 'qruqsp.qrz.noteGet');
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
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'timeFormat');
    $time_format = qruqsp_core_timeFormat($q, 'php');

    //
    // Return default for new Note
    //
    if( $args['note_id'] == 0 ) {
        $dt = new DateTime('now', new DateTimezone('UTC'));
//        $dt->setTimezone(new DateTimezone($intl_timezone));
        $note = array('id'=>0,
            'callsign_id'=>'',
            'note'=>'',
            'note_date'=>$dt->format($date_format),
            'note_time'=>$dt->format($time_format),
        );
    }

    //
    // Get the details for an existing Note
    //
    else {
        $strsql = "SELECT qruqsp_qrz_notes.id, "
            . "qruqsp_qrz_notes.callsign_id, "
            . "qruqsp_qrz_notes.note, "
            . "qruqsp_qrz_notes.note_date, "
            . "qruqsp_qrz_notes.note_date AS note_time "
            . "FROM qruqsp_qrz_notes "
            . "WHERE qruqsp_qrz_notes.station_id = '" . qruqsp_core_dbQuote($q, $args['station_id']) . "' "
            . "AND qruqsp_qrz_notes.id = '" . qruqsp_core_dbQuote($q, $args['note_id']) . "' "
            . "";
        qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = qruqsp_core_dbHashQueryArrayTree($q, $strsql, 'qruqsp.qrz', array(
            array('container'=>'notes', 'fname'=>'id', 
                'fields'=>array('callsign_id', 'note', 'note_date', 'note_time'),
                'utctotz'=>array('note_date'=>array('timezone'=>'UTC', 'format'=>$date_format),
                    'note_time'=>array('timezone'=>'UTC', 'format'=>$time_format)),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qrz.11', 'msg'=>'Note not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['notes'][0]) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qrz.12', 'msg'=>'Unable to find Note'));
        }
        $note = $rc['notes'][0];
    }

    return array('stat'=>'ok', 'note'=>$note);
}
?>
