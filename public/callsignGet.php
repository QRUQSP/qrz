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
// tnid:               The ID of the tenant the callsign is attached to.
// callsign_id:          The ID of the callsign to get the details for.
//
function qruqsp_qrz_callsignGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'callsign_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Callsign'),
        'licenses'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Licenses'),
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
    $rc = qruqsp_qrz_checkAccess($ciniki, $args['tnid'], 'qruqsp.qrz.callsignGet');
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
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'timeFormat');
    $time_format = ciniki_users_timeFormat($ciniki, 'php');

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
            . "qruqsp_qrz_callsigns.status AS status_text, "
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
            . "AND qruqsp_qrz_callsigns.id = '" . ciniki_core_dbQuote($ciniki, $args['callsign_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'qruqsp.qrz', array(
            array('container'=>'callsigns', 'fname'=>'id', 
                'fields'=>array('callsign', 'status', 'status_text', 'first', 'middle', 'last', 'fullname', 'nickname', 'shortbio', 
                    'address1', 'address2', 'city', 'province', 'postal', 'country',
                    'phone_number', 'sms_number', 'email', 'latitude', 'longitude', 'gridsquare', 'itu_zone', 'cq_zone', 
                    'qrz_com_number', 'op_note', 'route_through_callsign', 'logbooks'),
                'maps'=>array('status_text'=>$maps['callsign']['status']),
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
        // Get the licenses for the callsign
        //
        $strsql = "SELECT license_id "
            . "FROM qruqsp_qrz_callsign_licenses "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND callsign_id = '" . ciniki_core_dbQuote($ciniki, $args['callsign_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList');
        $rc = ciniki_core_dbQueryList($ciniki, $strsql, 'qruqsp.qrz', 'licenses', 'license_id');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['licenses']) ) {
            $callsign['licenses'] = implode(',', $rc['licenses']);
        } else {
            $callsign['licenses'] = '';
        }

        //
        // Get the tags for the callsign
        //
        $callsign['groups_display'] = '';
        $strsql = "SELECT tag_type, tag_name AS lists "
            . "FROM qruqsp_qrz_tags "
            . "WHERE callsign_id = '" . ciniki_core_dbQuote($ciniki, $args['callsign_id']) . "' "
            . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "ORDER BY tag_type, tag_name "
            . "";
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'qruqsp.qrz', array(
            array('container'=>'tags', 'fname'=>'tag_type', 'fields'=>array('tag_type', 'lists'), 'dlists'=>array('lists'=>'::')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['tags']) ) {
            foreach($rc['tags'] as $tags) {
                if( $tags['tag_type'] == 10 ) {
                    $callsign['groups'] = $tags['lists'];
                    $callsign['groups_display'] = str_replace('::', ', ', $callsign['groups']);
                }
            }
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
            . "WHERE qruqsp_qrz_notes.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'qruqsp.qrz', array(
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

    $rsp = array('stat'=>'ok', 'callsign'=>$callsign);

    //
    // Get the list of licenses
    //
    if( isset($args['licenses']) && $args['licenses'] == 'yes' ) {
        $strsql = "SELECT id, name "
            . "FROM qruqsp_qrz_licenses "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "ORDER BY name "
            . "";
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'qruqsp.foodmarket', array(
            array('container'=>'licenses', 'fname'=>'id', 'fields'=>array('id', 'name')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $rsp['licenses'] = array();
        if( isset($rc['licenses']) ) {
            $rsp['licenses'] = $rc['licenses'];
        } 
    }

    //
    // Check if all tags should be returned
    //
    $rsp['groups'] = array();
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsList');
    $rc = ciniki_core_tagsList($ciniki, 'qruqsp.qrz', $args['tnid'], 'qruqsp_qrz_tags', 10);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qrz.13', 'msg'=>'Unable to get list of groups', 'err'=>$rc['err']));
    }
    if( isset($rc['tags']) ) {
        $rsp['groups'] = $rc['tags'];
    }

    return $rsp;
}
?>
