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
// tnid:               The ID of the tenant the note is attached to.
// note_id:          The ID of the note to get the details for.
//
function qruqsp_qrz_noteGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'note_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Note'),
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
    $rc = qruqsp_qrz_checkAccess($ciniki, $args['tnid'], 'qruqsp.qrz.noteGet');
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

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dateFormat');
    $date_format = ciniki_core_dateFormat($ciniki, 'php');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'timeFormat');
    $time_format = ciniki_core_timeFormat($ciniki, 'php');

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
            . "WHERE qruqsp_qrz_notes.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND qruqsp_qrz_notes.id = '" . ciniki_core_dbQuote($ciniki, $args['note_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'qruqsp.qrz', array(
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
