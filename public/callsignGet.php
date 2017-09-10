<?php
//
// Description
// ===========
// This method will return all the information about an callsign.
//
// Arguments
// ---------
// api_key:
// auth_token:
// station_id:         The ID of the station the callsign is attached to.
// callsign_id:          The ID of the callsign to get the details for.
//
function qruqsp_qrz_callsignGet($q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
        'callsign_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Callsign'),
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
    $rc = qruqsp_qrz_checkAccess($q, $args['station_id'], 'qruqsp.qrz.callsignGet');
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
    // Return default for new Callsign
    //
    if( $args['callsign_id'] == 0 ) {
        $callsign = array('id'=>0,
            'callsign'=>'',
            'status'=>'10',
            'first'=>'',
            'middle'=>'',
            'last'=>'',
            'fullname'=>'',
            'nickname'=>'',
            'shortbio'=>'',
            'phone_number'=>'',
            'sms_number'=>'',
            'email'=>'',
            'license'=>'',
            'latitude'=>'',
            'longitude'=>'',
            'gridsquare'=>'',
            'itu_zone'=>'',
            'cq_zone'=>'',
            'qrz_com_number'=>'',
            'op_note'=>'',
            'route_through_callsign'=>'',
            'logbooks'=>'0',
        );
    }

    //
    // Get the details for an existing Callsign
    //
    else {
        $strsql = "SELECT qruqsp_qrz_callsigns.id, "
            . "qruqsp_qrz_callsigns.callsign, "
            . "qruqsp_qrz_callsigns.status, "
            . "qruqsp_qrz_callsigns.first, "
            . "qruqsp_qrz_callsigns.middle, "
            . "qruqsp_qrz_callsigns.last, "
            . "qruqsp_qrz_callsigns.fullname, "
            . "qruqsp_qrz_callsigns.nickname, "
            . "qruqsp_qrz_callsigns.shortbio, "
            . "qruqsp_qrz_callsigns.address1, "
            . "qruqsp_qrz_callsigns.address2, "
            . "qruqsp_qrz_callsigns.city, "
            . "qruqsp_qrz_callsigns.province, "
            . "qruqsp_qrz_callsigns.postal, "
            . "qruqsp_qrz_callsigns.country, "
            . "qruqsp_qrz_callsigns.phone_number, "
            . "qruqsp_qrz_callsigns.sms_number, "
            . "qruqsp_qrz_callsigns.email, "
            . "qruqsp_qrz_callsigns.license, "
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
            . "AND qruqsp_qrz_callsigns.id = '" . qruqsp_core_dbQuote($q, $args['callsign_id']) . "' "
            . "";
        qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = qruqsp_core_dbHashQueryArrayTree($q, $strsql, 'qruqsp.qrz', array(
            array('container'=>'callsigns', 'fname'=>'id', 
                'fields'=>array('callsign', 'status', 'first', 'middle', 'last', 'fullname', 'nickname', 'shortbio', 
                'address1', 'address2', 'city', 'province', 'postal', 'country',
                'phone_number', 'sms_number', 'email', 'license', 'latitude', 'longitude', 'gridsquare', 'itu_zone', 'cq_zone', 
                'qrz_com_number', 'op_note', 'route_through_callsign', 'logbooks'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qrz.6', 'msg'=>'Callsign not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['callsigns'][0]) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qrz.7', 'msg'=>'Unable to find Callsign'));
        }
        $callsign = $rc['callsigns'][0];
        $callsign['address'] = '';
        if( $callsign['address1'] != '' ) {
            $callsign['address'] .= ($callsign['address'] != '' ? '<br/>' : '') . $callsign['address1'];
        }
        if( $callsign['address2'] != '' ) {
            $callsign['address'] .= ($callsign['address'] != '' ? '<br/>' : '') . $callsign['address2'];
        }
        $city = '';
        if( $callsign['city'] != '' ) {
            $city .= $callsign['city'];
        }
        if( $callsign['province'] != '' ) {
            $city .= ($city != '' ? ', ' : '' ) . $callsign['province'];
        }
        if( $callsign['postal'] != '' ) {
            $city .= ($city != '' ? '  ' : '' ) . $callsign['postal'];
        }
        if( $city != '' ) {
            $callsign['address'] .= ($callsign['address'] != '' ? '<br/>' : '') . $city;
        }
        if( $callsign['country'] != '' ) {
            $callsign['address'] .= ($callsign['address'] != '' ? '<br/>' : '') . $callsign['country'];
        }

        //
        // Get the list of notes
        //
        $strsql = "SELECT qruqsp_qrz_notes.id, "
            . "qruqsp_qrz_notes.callsign_id, "
            . "qruqsp_qrz_notes.note_date, "
            . "qruqsp_qrz_notes.note_date AS note_time, "
            . "qruqsp_qrz_notes.note "
            . "FROM qruqsp_qrz_notes "
            . "WHERE qruqsp_qrz_notes.station_id = '" . qruqsp_core_dbQuote($q, $args['station_id']) . "' "
            . "";
        qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = qruqsp_core_dbHashQueryArrayTree($q, $strsql, 'qruqsp.qrz', array(
            array('container'=>'notes', 'fname'=>'id', 'fields'=>array('id', 'callsign_id', 'note_date', 'note_time', 'note'),
                'utctotz'=>array('note_date'=>array('timezone'=>'UTC', 'format'=>$date_format),
                    'note_time'=>array('timezone'=>'UTC', 'format'=>$time_format)),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['notes']) ) {
            $callsign['notes'] = $rc['notes'];
            $callsign['note_ids'] = array();
            foreach($callsign['notes'] as $iid => $note) {
                $callsign['notes_ids'][] = $note['id'];
            }
        } else {
            $callsign['notes'] = array();
            $callsign['note_ids'] = array();
        }

    }

    return array('stat'=>'ok', 'callsign'=>$callsign);
}
?>
