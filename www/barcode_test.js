var HY_BRIDGE = HY_BRIDGE || (function(){
    "use strict";
    
    var _IDENTIFIER = {
        DEVICE_SIDE : {
            "APP" : "APP",
            "WEB" : "WEB"
        },
        APP_EVT : {
            ON_LOAD_FINISHED : "ON_LOAD_FINISHED"
        }
    }

    var _CONFIG = {
        ios : {
            handler : "",   // for UIWebView
            prefix : ""     // for WKWebView
        },
        android : {
            handler :""
        },
        isInitiated : false
    }

    var _TOOLS = {
        objToArr : function(obj){
            var arr = [];
            for(var key in obj){
                arr.push(obj[key])
            }
            return arr;
        },
        ios_generate : function(fn_name , params){
            if (!fn_name || fn_name.trim() === "") {
                throw new Error("실행할 함수의 이름이 없습니다.");
            }
            
            if (!external.tools.ios.isWKWebView()) {
                // UIWebView 사용시 
                if(_CONFIG.ios.prefix === ""){ throw new Error("ios의 prefix가 초기화되지 않았습니다."); }
                
                var ret = _CONFIG.ios.prefix + "://"+ fn_name +"?";
                var keys = Object.keys(params);
                for(var idx = 0 ; idx < keys.length ; idx++){
                    if(idx != 0 && idx <= keys.length -1){
                        ret += "&";
                    }
                    ret += keys[idx] + "=" + params[keys[idx]];
                }  

                location.href = ret;
            }
            else {
                // WKWebView 사용시 
                if(_CONFIG.ios.handler === ""){
                    throw new Error("ios의 handler가 초기화되지 않았습니다.")
                }

                if (window.webkit.messageHandlers[_CONFIG.ios.handler]) {
                    window.webkit.messageHandlers[_CONFIG.ios.handler].postMessage({
                        name: fn_name,
                        params: params
                    });
                }
                else {
                    throw new Error("iOS messageHandler에 핸들러 [" + _CONFIG.ios.handler + "]가 존재하지 않습니다");
                }
            }
        },
        and_generate : function(fn_name , params){
            if(_CONFIG.android.handler === ""){
                throw new Error("Android의 handler가 초기화되지 않았습니다.");
            }
            if (window[_CONFIG.android.handler]) {
                if (typeof (window[_CONFIG.android.handler][fn_name]) == "function") {
                    if (params) {
                        window[_CONFIG.android.handler][fn_name].apply(window[_CONFIG.android.handler], params);
                    }
                    else {
                        window[_CONFIG.android.handler][fn_name]();
                    }
                }
                else {
                    // ERR CASE 2 : 함수가 존재하지 않는 경우 
                    throw new Error("안드로이드 객체에 함수[" + fn_name + "]가 존재하지 않습니다");
                }
            }
            else {
                // ERR CASE 1 : 객체가 존재하지 않는 경우 
                throw new Error("안드로이드 객체가 존재하지 않습니다");
            }
        }
    }

    var _app = {
        dispatch : function(params){
            // 앱에서 실행되는 경우에만 실행됩니다.
            if(external.tools.isRunningInApp()){
                if(external.tools.isiOS()){
                    _TOOLS.ios_generate(params.method , params.params);
                }
                else if(external.tools.isAndroid()){
                    params.params = _TOOLS.objToArr(params.params);
                    _TOOLS.and_generate(params.method , params.params);
                }
                else{
                    throw new Error("알 수 없는 타입의 기기입니다.");
                }
            }
        },
        evt :{
            CALLBACKS : {},
            flush : function(evt_nm){
                if(!_IDENTIFIER.APP_EVT[evt_nm]){
                    throw new Error("존재하지 않는 Event Identifier입니다. ["+ evt_nm +"]")
                }
                else if(!_app.evt.CALLBACKS[evt_nm]){
                    throw new Error("주어진 identifier에 맞는 callback 함수가 존재하지 않습니다. ["+ evt_nm +"]");
                }
                else{
                    var _callbacks = _app.evt.CALLBACKS[evt_nm];
                    while (_callbacks.length > 0) {
                        var _fn = _callbacks.shift();
                        if (typeof (_fn) === "function") { _fn(); }
                    }
                }
            },
            inject : function(evt_nm , fn){
                if(!_IDENTIFIER.APP_EVT[evt_nm]){
                    throw new Error("존재하지 않는 Event Identifier입니다. ["+ evt_nm +"]")
                }
                else if(typeof(fn) != "function"){
                    throw new Error("주어진 Event 함수가 잘못되었습니다.");
                }
                else{
                    if(!_app.CALLBACKS[evt_nm]){ _app.evt.CALLBACKS[evt_nm] = []; }

                    _app.CALLBACKS[evt_nm].push(fn);
                }
            }
        },
        FUNCTIONS : {}
    }

    var _web = {
        FUNCTIONS : {},
        VALUES : {},
        insert: function (fn_nm, fn, overwrite) {
            if (!overwrite && _web.FUNCTIONS[fn_nm]) { 
                throw new Error("이미 존재하는 함수명입니다. ["+ fn_nm +"]")
            }
            if (typeof (fn) != "function") { 
                throw new Error("매개변수가 함수 형태가 아닙니다.");    
            }

            _web.FUNCTIONS[fn_nm] = fn;
        },
        declare: function (obj, overwrite) {
            if (!overwrite) { overwrite = true; }
            var keys = Object.keys(obj);
            for (var i = 0 ; i < keys.length; i++) {
                if (!overwrite) {
                    if (_web.VALUES[keys[i]]) {
                        continue;
                    }
                }
                _web.VALUES[keys[i]] = obj[keys[i]];
            }
        }
    }

    var external = {
        tools : {
            // 현재 기기 정보를 가져옵니다. 
            device: function () {
                var userAgent = navigator.userAgent || navigator.vendor || window.opera;

                if (/android/i.test(userAgent)) {
                    return "Android";
                }
                else if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
                    return "iOS";
                }
                else { return "unknown"; }
            },
            // 현재 기기가 iOS인지 확인합니다.
            isiOS: function () { return external.tools.device() === "iOS"; },
            // 현재 기기가 Android인지 확인합니다.
            isAndroid: function () { return external.tools.device() === "Android"; },
            // 실제 앱에서 실행 중인지 확인합니다.
            isRunningInApp: function () {
                if (external.tools.isAndroid()) { return window[_CONFIG.android.handler] ? true : false; }
                else if (external.tools.isiOS()) {
                    if (window.webkit) { 
                        if (!window.webkit.messageHandlers[_CONFIG.ios.handler]) { return false; }    
                    }
                    return true;
                }
                else {
                    /* 알수 없는 기기 */
                    return false;
                }
            },
            ios : {
                isWKWebView : function(){ return window.webkit ? true : false; }
            }
        },
        // 최초에 설정을 초기화 해주는 함수입니다. 
        // handler , prefix등을 초기화 합니다.
        config : function(options){
            if(!options.ios && !options.android){ return {}; }

            _CONFIG.ios.prefix = options.ios.prefix || _CONFIG.ios.prefix;
            _CONFIG.ios.handler = options.ios.handler || _CONFIG.ios.handler;
            _CONFIG.android.handler = options.android.handler || _CONFIG.android.handler;

            _CONFIG.isInitiated = true;

            return {
                ios : { 
                    prefix : _CONFIG.ios.prefix,
                    handler : _CONFIG.ios.handler
                },
                android : { handler : _CONFIG.android.handler }
            }
        },
        extend : function(identifier , methods , overwrite){
            var keys = Object.keys(methods);
            var target;

            if(identifier == _IDENTIFIER.DEVICE_SIDE.APP){
                target = _app;
            }
            else if(identifier == _IDENTIFIER.DEVICE_SIDE.WEB){
                target = _web;
            }
            else{ throw new Error("IDENTIFIER가 올바르지 않습니다 ["+ identifier +"]"); }
            
            for(var i = 0 ; i < keys.length ; i++){
                if(typeof(methods[keys[i]]) === "function"){
                    if(target.FUNCTIONS[keys[i]] && typeof(target.FUNCTIONS[keys[i]]) == "function"){
                        if(overwrite == false){
                            return;
                        }
                    }
                    target.FUNCTIONS[keys[i]] = methods[keys[i]];
                }
            }
        
        },
        app : {
            dispatch : function(params){ _app.dispatch(params) },
            evt : {
                IDENTIFIER : (function(){
                    return JSON.parse(JSON.stringify(_IDENTIFIER.APP_EVT));                    
                })(),
                inject : function(evt_nm , fn){
                    _app.evt.inject(evt_nm , fn);
                },
                flush : function(evt_nm){
                    _app.evt.flush(evt_nm)
                }
            },
            call : function(fn_nm){
                if (_app.FUNCTIONS[fn_nm] && typeof (_app.FUNCTIONS[fn_nm]) === "function") {
                    _app.FUNCTIONS[fn_nm].apply(this, arguments.length > 1 ? Array.prototype.slice.call(arguments, 1) : null);
                }
            },
            extend : function(methods){ external.extend( _IDENTIFIER.DEVICE_SIDE.APP , methods); }
        },
        web : {
            insert : function(fn_nm , fn , overwrite){
                _web.insert(fn_nm , fn , overwrite);
            },
            call : function(fn_nm){
                if (_web.FUNCTIONS[fn_nm] && typeof (_web.FUNCTIONS[fn_nm]) === "function") {
                    _web.FUNCTIONS[fn_nm].apply(this, arguments.length > 1 ? Array.prototype.slice.call(arguments, 1) : null);
                }
            },
            declare : function(obj){
                _web.declare(obj)
            },
            get : function(name){
                return _web.VALUES[name];
            },
            extend : function(methods){ external.extend( _IDENTIFIER.DEVICE_SIDE.WEB , methods ); }
        },
        IDENTIFIER : (function(){
            return JSON.parse(JSON.stringify(_IDENTIFIER.DEVICE_SIDE));
        })()
    }

    return external;
})()

    


    
