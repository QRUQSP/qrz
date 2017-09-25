<?php
//
// Description
// -----------
// This function returns the list of objects for the module.
//
// Arguments
// ---------
//
function qruqsp_qrz_objects(&$q) {
    //
    // Build the objects
    //
    $objects = array();
    $objects['callsign'] = array(
        'name'=>'Callsign',
        'o_name'=>'callsign',
        'o_container'=>'callsigns',
        'sync'=>'yes',
        'table'=>'qruqsp_qrz_callsigns',
        'fields'=>array(
            'callsign'=>array('name'=>'Callsign'),
            'status'=>array('name'=>'Status', 'default'=>'10'),
            'first'=>array('name'=>'First', 'default'=>''),
            'middle'=>array('name'=>'Middle', 'default'=>''),
            'last'=>array('name'=>'Last', 'default'=>''),
            'fullname'=>array('name'=>'Full Name', 'default'=>''),
            'nickname'=>array('name'=>'Nickname', 'default'=>''),
            'shortbio'=>array('name'=>'Short Bio', 'default'=>''),
            'address1'=>array('name'=>'Address Line 1', 'default'=>''),
            'address2'=>array('name'=>'Address Line 2', 'default'=>''),
            'city'=>array('name'=>'Address Line 2', 'default'=>''),
            'province'=>array('name'=>'Province/State', 'default'=>''),
            'postal'=>array('name'=>'Postal/Zip Code', 'default'=>''),
            'country'=>array('name'=>'Country', 'default'=>''),
            'phone_number'=>array('name'=>'Phone Number', 'default'=>''),
            'sms_number'=>array('name'=>'SMS Number', 'default'=>''),
            'email'=>array('name'=>'Email', 'default'=>''),
            'latitude'=>array('name'=>'Latitude', 'default'=>''),
            'longitude'=>array('name'=>'Longitude', 'default'=>''),
            'gridsquare'=>array('name'=>'Grid Square', 'default'=>''),
            'itu_zone'=>array('name'=>'ITU Zone', 'default'=>''),
            'cq_zone'=>array('name'=>'CQ Zone', 'default'=>''),
            'qrz_com_number'=>array('name'=>'QRZ.com number', 'default'=>''),
            'op_note'=>array('name'=>'Op Note', 'default'=>''),
            'route_through_callsign'=>array('name'=>'Routing Callsign', 'default'=>''),
            'logbooks'=>array('name'=>'Logbooks', 'default'=>'0'),
            ),
        'history_table'=>'qruqsp_qrz_history',
        );
    $objects['note'] = array(
        'name'=>'Note',
        'o_name'=>'note',
        'o_container'=>'notes',
        'sync'=>'yes',
        'table'=>'qruqsp_qrz_notes',
        'fields'=>array(
            'callsign_id'=>array('name'=>'Callsign', 'ref'=>'qruqsp.qrz.callsign'),
            'note'=>array('name'=>'Note'),
            'note_date'=>array('name'=>'Date'),
            ),
        'history_table'=>'qruqsp_qrz_history',
        );
    $objects['tag'] = array(
        'name'=>'Tag',
        'o_name'=>'tag',
        'o_container'=>'tags',
        'sync'=>'yes',
        'table'=>'qruqsp_qrz_tags',
        'fields'=>array(
            'callsign_id'=>array('name'=>'Callsign', 'ref'=>'qruqsp.qrz.callsign'),
            'tag_type'=>array('name'=>'Type'),
            'tag_name'=>array('name'=>'Name'),
            'permalink'=>array('name'=>'Permalink'),
            ),
        'history_table'=>'qruqsp_qrz_history',
        );
    $objects['license'] = array(
        'name'=>'License',
        'o_name'=>'license',
        'o_container'=>'licenses',
        'sync'=>'yes',
        'table'=>'qruqsp_qrz_licenses',
        'fields'=>array(
            'name'=>array('name'=>'License name'),
            'permalink'=>array('name'=>'License name', 'default'=>''),
            'notes'=>array('name'=>'Notes', 'default'=>''),
            ),
        'history_table'=>'qruqsp_qrz_history',
        );
    $objects['callsignlicense'] = array(
        'name'=>'Callsign License',
        'o_name'=>'callsignlicense',
        'o_container'=>'callsignlicenses',
        'sync'=>'yes',
        'table'=>'qruqsp_qrz_callsign_licenses',
        'fields'=>array(
            'callsign_id'=>array('name'=>'Callsign', 'ref'=>'qruqsp.qrz.callsign'),
            'license_id'=>array('name'=>'License', 'ref'=>'qruqsp.qrz.license'),
            ),
        'history_table'=>'qruqsp_qrz_history',
        );
    //
    return array('stat'=>'ok', 'objects'=>$objects);
}
?>
