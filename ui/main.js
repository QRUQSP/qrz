//
// This is the main app for the qrz module
//
function qruqsp_qrz_main() {
    //
    // The panel to list the callsign
    //
    this.menu = new Q.panel('callsign', 'qruqsp_qrz_main', 'menu', 'mc', 'medium', 'sectioned', 'qruqsp.qrz.main.menu');
    this.menu.data = {};
    this.menu.nplist = [];
    this.menu.sections = {
        'search':{'label':'', 'type':'livesearchgrid', 'livesearchcols':2,
            'cellClasses':[''],
            'hint':'Search callsign',
            'noData':'No callsign found',
            },
        'callsigns':{'label':'Callsign', 'type':'simplegrid', 'num_cols':2,
            'noData':'No callsign',
            'addTxt':'Add Callsign',
            'addFn':'Q.qruqsp_qrz_main.callsign.open(\'Q.qruqsp_qrz_main.menu.open();\',0,null);'
            },
    }
    this.menu.liveSearchCb = function(s, i, v) {
        if( s == 'search' && v != '' ) {
            Q.api.getJSONBgCb('qruqsp.qrz.callsignSearch', {'station_id':Q.curStationID, 'start_needle':v, 'limit':'25'}, function(rsp) {
                Q.qruqsp_qrz_main.menu.liveSearchShow('search',null,Q.gE(Q.qruqsp_qrz_main.menu.panelUID + '_' + s), rsp.callsigns);
                });
        }
    }
    this.menu.liveSearchResultValue = function(s, f, i, j, d) {
        switch(j) {
            case 0: return d.callsign;
            case 1: return (d.nickname != '' ? d.nickname : d.first);
        }
    }
    this.menu.liveSearchResultRowFn = function(s, f, i, j, d) {
        return 'Q.qruqsp_qrz_main.callsign.open(\'Q.qruqsp_qrz_main.menu.open();\',\'' + d.id + '\');';
    }
    this.menu.cellValue = function(s, i, j, d) {
        if( s == 'callsigns' ) {
            switch(j) {
                case 0: return d.callsign;
                case 1: return (d.nickname != '' ? d.nickname : d.first);
            }
        }
    }
    this.menu.rowFn = function(s, i, d) {
        if( s == 'callsigns' ) {
            return 'Q.qruqsp_qrz_main.callsign.open(\'Q.qruqsp_qrz_main.menu.open();\',\'' + d.id + '\',Q.qruqsp_qrz_main.callsign.nplist);';
        }
    }
    this.menu.open = function(cb) {
        Q.api.getJSONCb('qruqsp.qrz.callsignList', {'station_id':Q.curStationID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                Q.api.err(rsp);
                return false;
            }
            var p = Q.qruqsp_qrz_main.menu;
            p.data = rsp;
            p.nplist = (rsp.nplist != null ? rsp.nplist : null);
            p.refresh();
            p.show(cb);
        });
    }
    this.menu.addClose('Back');

    //
    // The panel to display Callsign
    //
    this.callsign = new Q.panel('Callsign', 'qruqsp_qrz_main', 'callsign', 'mc', 'medium mediumaside', 'sectioned', 'qruqsp.qrz.main.callsign');
    this.callsign.data = null;
    this.callsign.callsign_id = 0;
    this.callsign.sections = {
        'general':{'label':'', 'aside':'yes', 'list':{
            'callsign':{'label':'Callsign'},
            'fullname':{'label':'Full Name'},
//            'nickname':{'label':'Nickname'},
            'license':{'label':'License'},
            'status':{'label':'Status'},
            'address':{'label':'Address', 'visible':function() {return (Q.qruqsp_qrz_main.callsign.data.address != null && Q.qruqsp_qrz_main.callsign.data.address != '' ? 'yes' : 'no');}},
            }},
        'notes':{'label':'Notes', 'type':'simplegrid', 'num_cols':2,
            'addTxt':'Add Notes',
            'addFn':'Q.qruqsp_qrz_main.note.open(\'Q.qruqsp_qrz_main.edit.open();\',0,Q.qruqsp_qrz_main.edit.callsign_id);',
            },
    }
    this.callsign.listLabel = function(s, i, d) {
        return d.label;
    }
    this.callsign.listValue = function(s, i, d) {
        if( i == 'callsign' ) {
            return this.data[i] + ' <span class="subdue">[' + (this.data.nickname != '' ? this.data.nickname : this.data.first) + ']</span>';
        }
        return this.data[i];
    }
    this.callsign.cellValue = function(s, i, j, d) {
        switch(j) {
            case 0: return d.note_date + ' <span class="subdue">' + d.note_time + '</span>';
            case 1: return d.note;
        }
    }
    this.callsign.rowFn = function(s, i, d) {
        return 'Q.qruqsp_qrz_main.note.open(\'Q.qruqsp_qrz_main.callsign.open();\',\'' + d.id + '\');';
    }
    this.callsign.open = function(cb, cid, list) {
        if( cid != null ) { this.callsign_id = cid; }
        if( list != null ) { this.nplist = list; }
        Q.api.getJSONCb('qruqsp.qrz.callsignGet', {'station_id':Q.curStationID, 'callsign_id':this.callsign_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                Q.api.err(rsp);
                return false;
            }
            var p = Q.qruqsp_qrz_main.callsign;
            p.data = rsp.callsign;
            p.refresh();
            p.show(cb);
        });
    }
    this.callsign.addButton('edit', 'Edit', 'Q.qruqsp_qrz_main.edit.open(\'Q.qruqsp_qrz_main.callsign.open();\',Q.qruqsp_qrz_main.callsign.callsign_id);');
    this.callsign.addClose('Back');


    //
    // The panel to edit Callsign
    //
    this.edit = new Q.panel('Callsign', 'qruqsp_qrz_main', 'callsign', 'mc', 'medium mediumaside', 'sectioned', 'qruqsp.qrz.main.callsign');
    this.edit.data = null;
    this.edit.callsign_id = 0;
    this.edit.nplist = [];
    this.edit.sections = {
        'general':{'label':'', 'aside':'yes', 'fields':{
            'callsign':{'label':'Callsign', 'type':'text'},
            'license':{'label':'License', 'type':'text'},
            'status':{'label':'Status', 'type':'toggle', 'toggles':{'10':'Active', '60':'Archived'}},
            'first':{'label':'First', 'type':'text'},
            'middle':{'label':'Middle', 'type':'text'},
            'last':{'label':'Last', 'type':'text'},
//            'fullname':{'label':'Full Name', 'type':'text'},
            'nickname':{'label':'Nickname', 'type':'text'},
            }},
        '_bio':{'label':'Short Bio', 'aside':'yes', 'fields':{
            'shortbio':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'small'},
        }},
        'contact':{'label':'', 'aside':'yes', 'fields':{
            'phone_number':{'label':'Phone Number', 'type':'text'},
            'sms_number':{'label':'SMS Number', 'type':'text'},
            'email':{'label':'Email', 'type':'text'},
            }},
        'location':{'label':'', 'aside':'yes', 'fields':{
            'address1':{'label':'Address', 'type':'text'},
            'address2':{'label':'', 'type':'text'},
            'city':{'label':'City', 'type':'text'},
            'province':{'label':'Province/State', 'type':'text'},
            'postal':{'label':'Postal/Zip', 'type':'text'},
            'country':{'label':'Country', 'type':'text'},
            'latitude':{'label':'Latitude', 'type':'text'},
            'longitude':{'label':'Longitude', 'type':'text'},
            'gridsquare':{'label':'Grid Square', 'type':'text'},
            'itu_zone':{'label':'ITU Zone', 'type':'text'},
            'cq_zone':{'label':'CQ Zone', 'type':'text'},
            }},
        'other':{'label':'', 'fields':{
            'qrz_com_number':{'label':'QRZ.com number', 'type':'text'},
            'op_note':{'label':'Op Note', 'type':'text'},
            'route_through_callsign':{'label':'Routing Callsign', 'type':'text'},
            'logbooks':{'label':'Logbooks', 'type':'flags', 'flags':{
                '1':{'name':'Postal'}, 
                '2':{'name':'Bureau'},
                '5':{'name':'QRUQSP'},
                '6':{'name':'LOTW'},
                '7':{'name':'eQSL'},
                '8':{'name':'QRZ'},
                }},
            }},
        'tags':{'label':'Groups', 'fields':{
            }},
//        'notes':{'label':'Notes', 'type':'simplegrid', 'num_cols':2,
//            'addTxt':'Add Notes',
//            'addFn':'Q.qruqsp_qrz_main.edit.save("Q.qruqsp_qrz_main.note.open(\'Q.qruqsp_qrz_main.edit.open();\',0,Q.qruqsp_qrz_main.edit.callsign_id);");',
//            },
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'Q.qruqsp_qrz_main.edit.save();'},
            'delete':{'label':'Delete', 
                'visible':function() {return Q.qruqsp_qrz_main.edit.callsign_id > 0 ? 'yes' : 'no'; },
                'fn':'Q.qruqsp_qrz_main.edit.remove();'},
            }},
        };
    this.edit.fieldValue = function(s, i, d) { return this.data[i]; }
    this.edit.fieldHistoryArgs = function(s, i) {
        return {'method':'qruqsp.qrz.callsignHistory', 'args':{'station_id':Q.curStationID, 'callsign_id':this.callsign_id, 'field':i}};
    }
    this.edit.cellValue = function(s, i, j, d) {
        switch(j) {
            case 0: return d.note_date + ' <span class="subdue">' + d.note_time + '</span>';
            case 1: return d.note;
        }
    }
    this.edit.rowFn = function(s, i, d) {
        return 'Q.qruqsp_qrz_main.edit.save("Q.qruqsp_qrz_main.note.open(\'Q.qruqsp_qrz_main.edit.open();\',\'' + d.id + '\');");';
    }
    this.edit.open = function(cb, cid, list) {
        if( cid != null ) { this.callsign_id = cid; }
        if( list != null ) { this.nplist = list; }
        Q.api.getJSONCb('qruqsp.qrz.callsignGet', {'station_id':Q.curStationID, 'callsign_id':this.callsign_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                Q.api.err(rsp);
                return false;
            }
            var p = Q.qruqsp_qrz_main.edit;
            p.data = rsp.callsign;
            p.refresh();
            p.show(cb);
        });
    }
    this.edit.save = function(cb) {
        if( cb == null ) { cb = 'Q.qruqsp_qrz_main.edit.close();'; }
        if( !this.checkForm() ) { return false; }
        if( this.callsign_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                Q.api.postJSONCb('qruqsp.qrz.callsignUpdate', {'station_id':Q.curStationID, 'callsign_id':this.callsign_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        Q.api.err(rsp);
                        return false;
                    }
                    eval(cb);
                });
            } else {
                eval(cb);
            }
        } else {
            var c = this.serializeForm('yes');
            Q.api.postJSONCb('qruqsp.qrz.callsignAdd', {'station_id':Q.curStationID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    Q.api.err(rsp);
                    return false;
                }
                Q.qruqsp_qrz_main.edit.callsign_id = rsp.id;
                eval(cb);
            });
        }
    }
    this.edit.remove = function() {
        if( confirm('Are you sure you want to remove callsign?') ) {
            Q.api.getJSONCb('qruqsp.qrz.callsignDelete', {'station_id':Q.curStationID, 'callsign_id':this.callsign_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    Q.api.err(rsp);
                    return false;
                }
                Q.qruqsp_qrz_main.edit.close();
            });
        }
    }
    this.edit.nextButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.callsign_id) < (this.nplist.length - 1) ) {
            return 'Q.qruqsp_qrz_main.edit.save(\'Q.qruqsp_qrz_main.edit.open(null,' + this.nplist[this.nplist.indexOf('' + this.callsign_id) + 1] + ');\');';
        }
        return null;
    }
    this.edit.prevButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.callsign_id) > 0 ) {
            return 'Q.qruqsp_qrz_main.edit.save(\'Q.qruqsp_qrz_main.edit.open(null,' + this.nplist[this.nplist.indexOf('' + this.callsign_id) - 1] + ');\');';
        }
        return null;
    }
    this.edit.addButton('save', 'Save', 'Q.qruqsp_qrz_main.edit.save();');
    this.edit.addClose('Cancel');
    this.edit.addButton('next', 'Next');
    this.edit.addLeftButton('prev', 'Prev');

    //
    // The panel to edit Note
    //
    this.note = new Q.panel('Note', 'qruqsp_qrz_main', 'note', 'mc', 'medium', 'sectioned', 'qruqsp.qrz.main.note');
    this.note.data = null;
    this.note.note_id = 0;
    this.note.callsign_id = 0;
    this.note.nplist = [];
    this.note.sections = {
        'general':{'label':'', 'fields':{
            'note_date':{'label':'UTC Date', 'required':'yes', 'type':'date'},
            'note_time':{'label':'UTC Time', 'type':'text', 'size':'small'},
            }},
        '_note':{'label':'Note', 'fields':{
            'note':{'label':'', 'hidelabel':'yes', 'type':'textarea'},
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'Q.qruqsp_qrz_main.note.save();'},
            'delete':{'label':'Delete', 
                'visible':function() {return Q.qruqsp_qrz_main.note.note_id > 0 ? 'yes' : 'no'; },
                'fn':'Q.qruqsp_qrz_main.note.remove();'},
            }},
        };
    this.note.fieldValue = function(s, i, d) { return this.data[i]; }
    this.note.fieldHistoryArgs = function(s, i) {
        return {'method':'qruqsp.qrz.noteHistory', 'args':{'station_id':Q.curStationID, 'note_id':this.note_id, 'field':i}};
    }
    this.note.open = function(cb, nid, cid, list) {
        if( nid != null ) { this.note_id = nid; }
        if( cid != null ) { this.callsign_id = cid; }
        if( list != null ) { this.nplist = list; }
        Q.api.getJSONCb('qruqsp.qrz.noteGet', {'station_id':Q.curStationID, 'note_id':this.note_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                Q.api.err(rsp);
                return false;
            }
            var p = Q.qruqsp_qrz_main.note;
            p.data = rsp.note;
            p.refresh();
            p.show(cb);
        });
    }
    this.note.save = function(cb) {
        if( cb == null ) { cb = 'Q.qruqsp_qrz_main.note.close();'; }
        if( !this.checkForm() ) { return false; }
        if( this.note_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                Q.api.postJSONCb('qruqsp.qrz.noteUpdate', {'station_id':Q.curStationID, 'note_id':this.note_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        Q.api.err(rsp);
                        return false;
                    }
                    eval(cb);
                });
            } else {
                eval(cb);
            }
        } else {
            var c = this.serializeForm('yes');
            Q.api.postJSONCb('qruqsp.qrz.noteAdd', {'station_id':Q.curStationID, 'callsign_id':this.callsign_id}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    Q.api.err(rsp);
                    return false;
                }
                Q.qruqsp_qrz_main.note.note_id = rsp.id;
                eval(cb);
            });
        }
    }
    this.note.remove = function() {
        if( confirm('Are you sure you want to remove note?') ) {
            Q.api.getJSONCb('qruqsp.qrz.noteDelete', {'station_id':Q.curStationID, 'note_id':this.note_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    Q.api.err(rsp);
                    return false;
                }
                Q.qruqsp_qrz_main.note.close();
            });
        }
    }
    this.note.nextButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.note_id) < (this.nplist.length - 1) ) {
            return 'Q.qruqsp_qrz_main.note.save(\'Q.qruqsp_qrz_main.note.open(null,' + this.nplist[this.nplist.indexOf('' + this.note_id) + 1] + ');\');';
        }
        return null;
    }
    this.note.prevButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.note_id) > 0 ) {
            return 'Q.qruqsp_qrz_main.note.save(\'Q.qruqsp_qrz_main.note.open(null,' + this.nplist[this.nplist.indexOf('' + this.note_id) - 1] + ');\');';
        }
        return null;
    }
    this.note.addButton('save', 'Save', 'Q.qruqsp_qrz_main.note.save();');
    this.note.addClose('Cancel');
    this.note.addButton('next', 'Next');
    this.note.addLeftButton('prev', 'Prev');

    //
    // Start the app
    // cb - The callback to run when the user leaves the main panel in the app.
    // ap - The application prefix.
    // ag - The app arguments.
    //
    this.start = function(cb, ap, ag) {
        args = {};
        if( ag != null ) {
            args = eval(ag);
        }
        
        //
        // Create the app container
        //
        var ac = Q.createContainer(ap, 'qruqsp_qrz_main', 'yes');
        if( ac == null ) {
            alert('App Error');
            return false;
        }
        
        this.menu.open(cb);
    }
}
