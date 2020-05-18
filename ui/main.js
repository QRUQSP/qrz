//
// This is the main app for the qrz module
//
function qruqsp_qrz_main() {
    //
    // The panel to list the callsign
    //
    this.menu = new M.panel('callsign', 'qruqsp_qrz_main', 'menu', 'mc', 'medium narrowaside', 'sectioned', 'qruqsp.qrz.main.menu');
    this.menu.data = {};
    this.menu.nplist = [];
    this.menu.group_permalink = '';
    this.menu.sections = {
        'groups':{'label':'Groups', 'type':'simplegrid', 'num_cols':1, 'aside':'yes',
            'noData':'No groups found',
            },
        'search':{'label':'', 'type':'livesearchgrid', 'livesearchcols':3,
            'cellClasses':[''],
            'hint':'Search callsign',
            'noData':'No callsign found',
            },
        'callsigns':{'label':'Callsign', 'type':'simplegrid', 'num_cols':3,
            'noData':'No callsign',
            'addTxt':'Add Callsign',
            'addFn':'M.qruqsp_qrz_main.edit.open(\'M.qruqsp_qrz_main.menu.open();\',0,null);'
            },
    }
    this.menu.liveSearchCb = function(s, i, v) {
        if( s == 'search' && v != '' ) {
            M.api.getJSONBgCb('qruqsp.qrz.callsignSearch', {'tnid':M.curTenantID, 'start_needle':v, 'limit':'25'}, function(rsp) {
                M.qruqsp_qrz_main.menu.liveSearchShow('search',null,M.gE(M.qruqsp_qrz_main.menu.panelUID + '_' + s), rsp.callsigns);
                });
        }
    }
    this.menu.liveSearchResultValue = function(s, f, i, j, d) {
        switch(j) {
            case 0: return d.callsign;
            case 1: return (d.nickname != '' ? d.nickname : d.first);
            case 2: return d.status_text;
        }
    }
    this.menu.liveSearchResultRowFn = function(s, f, i, j, d) {
        return 'M.qruqsp_qrz_main.callsign.open(\'M.qruqsp_qrz_main.menu.open();\',\'' + d.id + '\');';
    }
    this.menu.cellValue = function(s, i, j, d) {
        if( s == 'callsigns' ) {
            switch(j) {
                case 0: return d.callsign;
                case 1: return (d.nickname != '' ? d.nickname : d.first);
                case 2: return d.status_text;
            }
        }
        if( s == 'groups' ) {
            return d.tag_name;
        }
    }
    this.menu.rowClass = function(s, i, d) {
        if( s == 'groups' ) {
            if( this.group_permalink != '' && this.group_permalink == d.permalink ) {
                return 'highlight';
            }
        }
        return '';
    }
    this.menu.rowFn = function(s, i, d) {
        if( s == 'callsigns' ) {
            return 'M.qruqsp_qrz_main.callsign.open(\'M.qruqsp_qrz_main.menu.open();\',\'' + d.id + '\',M.qruqsp_qrz_main.callsign.nplist);';
        }
        if( s == 'groups' ) {
            return 'M.qruqsp_qrz_main.menu.open(null,\'' + d.permalink + '\');';
        }
        return '';
    }
    this.menu.open = function(cb, gp) {
        if( gp != null ) { this.group_permalink = gp; }
        M.api.getJSONCb('qruqsp.qrz.callsignList', {'tnid':M.curTenantID, 'groups':'yes', 'group_permalink':this.group_permalink}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.qruqsp_qrz_main.menu;
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
    this.callsign = new M.panel('Callsign', 'qruqsp_qrz_main', 'callsign', 'mc', 'medium mediumaside', 'sectioned', 'qruqsp.qrz.main.callsign');
    this.callsign.data = null;
    this.callsign.callsign_id = 0;
    this.callsign.sections = {
        'general':{'label':'', 'aside':'yes', 'list':{
            'callsign':{'label':'Callsign'},
            'fullname':{'label':'Full Name'},
//            'nickname':{'label':'Nickname'},
            'license':{'label':'License'},
            'status_text':{'label':'Status'},
            'groups_display':{'label':'Groups'},
            'address':{'label':'Address', 'visible':function() {return (M.qruqsp_qrz_main.callsign.data.address != null && M.qruqsp_qrz_main.callsign.data.address != '' ? 'yes' : 'no');}},
            }},
        'notes':{'label':'Notes', 'type':'simplegrid', 'num_cols':2,
            'addTxt':'Add Notes',
            'addFn':'M.qruqsp_qrz_main.note.open(\'M.qruqsp_qrz_main.edit.open();\',0,M.qruqsp_qrz_main.edit.callsign_id);',
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
        return 'M.qruqsp_qrz_main.note.open(\'M.qruqsp_qrz_main.callsign.open();\',\'' + d.id + '\');';
    }
    this.callsign.open = function(cb, cid, list) {
        if( cid != null ) { this.callsign_id = cid; }
        if( list != null ) { this.nplist = list; }
        M.api.getJSONCb('qruqsp.qrz.callsignGet', {'tnid':M.curTenantID, 'callsign_id':this.callsign_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.qruqsp_qrz_main.callsign;
            p.data = rsp.callsign;
            p.refresh();
            p.show(cb);
        });
    }
    this.callsign.addButton('edit', 'Edit', 'M.qruqsp_qrz_main.edit.open(\'M.qruqsp_qrz_main.callsign.open();\',M.qruqsp_qrz_main.callsign.callsign_id);');
    this.callsign.addClose('Back');


    //
    // The panel to edit Callsign
    //
    this.edit = new M.panel('Callsign', 'qruqsp_qrz_main', 'edit', 'mc', 'medium mediumaside', 'sectioned', 'qruqsp.qrz.main.edit');
    this.edit.data = null;
    this.edit.callsign_id = 0;
    this.edit.nplist = [];
    this.edit.sections = {
        'general':{'label':'', 'aside':'yes', 'fields':{
            'callsign':{'label':'Callsign', 'type':'text'},
//            'license':{'label':'License', 'type':'text'},
            'status':{'label':'Status', 'type':'toggle', 'toggles':{'10':'Active', '60':'Archived'}},
            'first':{'label':'First', 'type':'text'},
            'middle':{'label':'Middle', 'type':'text'},
            'last':{'label':'Last', 'type':'text'},
//            'fullname':{'label':'Full Name', 'type':'text'},
            'nickname':{'label':'Nickname', 'type':'text'},
            }},
        '_licenses':{'label':'Licenses', 'aside':'yes', 
            'addTxt':'Add License',
            'addFn':'M.qruqsp_qrz_main.edit.save("M.qruqsp_qrz_main.license.open(\'M.qruqsp_qrz_main.edit.open();\',0);");',
            'fields':{
                'licenses':{'label':'', 'hidelabel':'yes', 'type':'idlist', 'list':[], 'hint':'Enter a new license'},
            }},
        '_bio':{'label':'Short Bio', 'aside':'yes', 'fields':{
            'shortbio':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'small'},
        }},
        '_groups':{'label':'Groups', 'aside':'yes', 'fields':{
            'groups':{'label':'', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'Enter a new group'},
            }},
        'contact':{'label':'', 'aside':'yes', 'fields':{
            'phone_number':{'label':'Phone Number', 'type':'text'},
            'sms_number':{'label':'SMS Number', 'type':'text'},
            'email':{'label':'Email', 'type':'text'},
            }},
        'location':{'label':'', 'fields':{
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
//        'notes':{'label':'Notes', 'type':'simplegrid', 'num_cols':2,
//            'addTxt':'Add Notes',
//            'addFn':'M.qruqsp_qrz_main.edit.save("M.qruqsp_qrz_main.note.open(\'M.qruqsp_qrz_main.edit.open();\',0,M.qruqsp_qrz_main.edit.callsign_id);");',
//            },
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.qruqsp_qrz_main.edit.save();'},
            'delete':{'label':'Delete', 
                'visible':function() {return M.qruqsp_qrz_main.edit.callsign_id > 0 ? 'yes' : 'no'; },
                'fn':'M.qruqsp_qrz_main.edit.remove();'},
            }},
        };
    this.edit.fieldValue = function(s, i, d) { return this.data[i]; }
    this.edit.fieldHistoryArgs = function(s, i) {
        return {'method':'qruqsp.qrz.callsignHistory', 'args':{'tnid':M.curTenantID, 'callsign_id':this.callsign_id, 'field':i}};
    }
    this.edit.cellValue = function(s, i, j, d) {
        switch(j) {
            case 0: return d.note_date + ' <span class="subdue">' + d.note_time + '</span>';
            case 1: return d.note;
        }
    }
    this.edit.rowFn = function(s, i, d) {
        return 'M.qruqsp_qrz_main.edit.save("M.qruqsp_qrz_main.note.open(\'M.qruqsp_qrz_main.edit.open();\',\'' + d.id + '\');");';
    }
    this.edit.open = function(cb, cid, list) {
        if( cid != null ) { this.callsign_id = cid; }
        if( list != null ) { this.nplist = list; }
        M.api.getJSONCb('qruqsp.qrz.callsignGet', {'tnid':M.curTenantID, 'callsign_id':this.callsign_id, 'licenses':'yes'}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.qruqsp_qrz_main.edit;
            p.data = rsp.callsign;
            p.sections._groups.fields.groups.tags = [];
            if( rsp.groups != null ) {
                for(i in rsp.groups) {
                    p.sections._groups.fields.groups.tags.push(rsp.groups[i].tag.name);
                }
            }
            p.sections._licenses.fields.licenses.list = rsp.licenses;
            p.refresh();
            p.show(cb);
        });
    }
    this.edit.save = function(cb) {
        if( cb == null ) { cb = 'M.qruqsp_qrz_main.edit.close();'; }
        if( !this.checkForm() ) { return false; }
        if( this.callsign_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('qruqsp.qrz.callsignUpdate', {'tnid':M.curTenantID, 'callsign_id':this.callsign_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    eval(cb);
                });
            } else {
                eval(cb);
            }
        } else {
            var c = this.serializeForm('yes');
            M.api.postJSONCb('qruqsp.qrz.callsignAdd', {'tnid':M.curTenantID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.qruqsp_qrz_main.edit.callsign_id = rsp.id;
                eval(cb);
            });
        }
    }
    this.edit.remove = function() {
        M.confirm('Are you sure you want to remove callsign?',null,function() {
            M.api.getJSONCb('qruqsp.qrz.callsignDelete', {'tnid':M.curTenantID, 'callsign_id':M.qruqsp_qrz_main.edit.callsign_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.qruqsp_qrz_main.edit.close();
            });
        });
    }
    this.edit.nextButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.callsign_id) < (this.nplist.length - 1) ) {
            return 'M.qruqsp_qrz_main.edit.save(\'M.qruqsp_qrz_main.edit.open(null,' + this.nplist[this.nplist.indexOf('' + this.callsign_id) + 1] + ');\');';
        }
        return null;
    }
    this.edit.prevButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.callsign_id) > 0 ) {
            return 'M.qruqsp_qrz_main.edit.save(\'M.qruqsp_qrz_main.edit.open(null,' + this.nplist[this.nplist.indexOf('' + this.callsign_id) - 1] + ');\');';
        }
        return null;
    }
    this.edit.addButton('save', 'Save', 'M.qruqsp_qrz_main.edit.save();');
    this.edit.addClose('Cancel');
    this.edit.addButton('next', 'Next');
    this.edit.addLeftButton('prev', 'Prev');

    //
    // The panel to edit Note
    //
    this.note = new M.panel('Note', 'qruqsp_qrz_main', 'note', 'mc', 'medium', 'sectioned', 'qruqsp.qrz.main.note');
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
            'save':{'label':'Save', 'fn':'M.qruqsp_qrz_main.note.save();'},
            'delete':{'label':'Delete', 
                'visible':function() {return M.qruqsp_qrz_main.note.note_id > 0 ? 'yes' : 'no'; },
                'fn':'M.qruqsp_qrz_main.note.remove();'},
            }},
        };
    this.note.fieldValue = function(s, i, d) { return this.data[i]; }
    this.note.fieldHistoryArgs = function(s, i) {
        return {'method':'qruqsp.qrz.noteHistory', 'args':{'tnid':M.curTenantID, 'note_id':this.note_id, 'field':i}};
    }
    this.note.open = function(cb, nid, cid, list) {
        if( nid != null ) { this.note_id = nid; }
        if( cid != null ) { this.callsign_id = cid; }
        if( list != null ) { this.nplist = list; }
        M.api.getJSONCb('qruqsp.qrz.noteGet', {'tnid':M.curTenantID, 'note_id':this.note_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.qruqsp_qrz_main.note;
            p.data = rsp.note;
            p.refresh();
            p.show(cb);
        });
    }
    this.note.save = function(cb) {
        if( cb == null ) { cb = 'M.qruqsp_qrz_main.note.close();'; }
        if( !this.checkForm() ) { return false; }
        if( this.note_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('qruqsp.qrz.noteUpdate', {'tnid':M.curTenantID, 'note_id':this.note_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    eval(cb);
                });
            } else {
                eval(cb);
            }
        } else {
            var c = this.serializeForm('yes');
            M.api.postJSONCb('qruqsp.qrz.noteAdd', {'tnid':M.curTenantID, 'callsign_id':this.callsign_id}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.qruqsp_qrz_main.note.note_id = rsp.id;
                eval(cb);
            });
        }
    }
    this.note.remove = function() {
        M.confirm('Are you sure you want to remove note?',null,function() {
            M.api.getJSONCb('qruqsp.qrz.noteDelete', {'tnid':M.curTenantID, 'note_id':M.qruqsp_qrz_main.note.note_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.qruqsp_qrz_main.note.close();
            });
        });
    }
    this.note.nextButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.note_id) < (this.nplist.length - 1) ) {
            return 'M.qruqsp_qrz_main.note.save(\'M.qruqsp_qrz_main.note.open(null,' + this.nplist[this.nplist.indexOf('' + this.note_id) + 1] + ');\');';
        }
        return null;
    }
    this.note.prevButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.note_id) > 0 ) {
            return 'M.qruqsp_qrz_main.note.save(\'M.qruqsp_qrz_main.note.open(null,' + this.nplist[this.nplist.indexOf('' + this.note_id) - 1] + ');\');';
        }
        return null;
    }
    this.note.addButton('save', 'Save', 'M.qruqsp_qrz_main.note.save();');
    this.note.addClose('Cancel');
    this.note.addButton('next', 'Next');
    this.note.addLeftButton('prev', 'Prev');

    //
    // The panel to edit License
    //
    this.license = new M.panel('License', 'qruqsp_qrz_main', 'license', 'mc', 'medium', 'sectioned', 'qruqsp.qrz.main.license');
    this.license.data = null;
    this.license.license_id = 0;
    this.license.nplist = [];
    this.license.sections = {
        'general':{'label':'', 'fields':{
            'name':{'label':'License name', 'type':'text'},
            }},
        '_notes':{'label':'Notes', 'fields':{
            'notes':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'large'},
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.qruqsp_qrz_main.license.save();'},
            'delete':{'label':'Delete', 
                'visible':function() {return M.qruqsp_qrz_main.license.license_id > 0 ? 'yes' : 'no'; },
                'fn':'M.qruqsp_qrz_main.license.remove();'},
            }},
        };
    this.license.fieldValue = function(s, i, d) { return this.data[i]; }
    this.license.fieldHistoryArgs = function(s, i) {
        return {'method':'qruqsp.qrz.licenseHistory', 'args':{'tnid':M.curTenantID, 'license_id':this.license_id, 'field':i}};
    }
    this.license.open = function(cb, lid, list) {
        if( lid != null ) { this.license_id = lid; }
        if( list != null ) { this.nplist = list; }
        M.api.getJSONCb('qruqsp.qrz.licenseGet', {'tnid':M.curTenantID, 'license_id':this.license_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.qruqsp_qrz_main.license;
            p.data = rsp.license;
            p.refresh();
            p.show(cb);
        });
    }
    this.license.save = function(cb) {
        if( cb == null ) { cb = 'M.qruqsp_qrz_main.license.close();'; }
        if( !this.checkForm() ) { return false; }
        if( this.license_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('qruqsp.qrz.licenseUpdate', {'tnid':M.curTenantID, 'license_id':this.license_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    eval(cb);
                });
            } else {
                eval(cb);
            }
        } else {
            var c = this.serializeForm('yes');
            M.api.postJSONCb('qruqsp.qrz.licenseAdd', {'tnid':M.curTenantID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.qruqsp_qrz_main.license.license_id = rsp.id;
                eval(cb);
            });
        }
    }
    this.license.remove = function() {
        M.confirm('Are you sure you want to remove license?',null,function() {
            M.api.getJSONCb('qruqsp.qrz.licenseDelete', {'tnid':M.curTenantID, 'license_id':M.qruqsp_qrz_main.license.license_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.qruqsp_qrz_main.license.close();
            });
        });
    }
    this.license.nextButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.license_id) < (this.nplist.length - 1) ) {
            return 'M.qruqsp_qrz_main.license.save(\'M.qruqsp_qrz_main.license.open(null,' + this.nplist[this.nplist.indexOf('' + this.license_id) + 1] + ');\');';
        }
        return null;
    }
    this.license.prevButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.license_id) > 0 ) {
            return 'M.qruqsp_qrz_main.license.save(\'M.qruqsp_qrz_main.license.open(null,' + this.nplist[this.nplist.indexOf('' + this.license_id) - 1] + ');\');';
        }
        return null;
    }
    this.license.addButton('save', 'Save', 'M.qruqsp_qrz_main.license.save();');
    this.license.addClose('Cancel');
    this.license.addButton('next', 'Next');
    this.license.addLeftButton('prev', 'Prev');

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
        var ac = M.createContainer(ap, 'qruqsp_qrz_main', 'yes');
        if( ac == null ) {
            M.alert('App Error');
            return false;
        }
        
        this.menu.open(cb);
    }
}
