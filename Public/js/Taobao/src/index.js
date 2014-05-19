(function(S, Global) {
    var D = S.DOM;

    // 初始设置全局命名空间
    var APPNAME = "Sell";
    var Host = Global[APPNAME] = Global[APPNAME] || {},
        isDaily = location.href.indexOf('daily.taobao.net') >= 0;

    Host.assetsDomain = isDaily ? 'assets.daily.taobao.net' : 'a.tbcdn.cn';
    /**
     * App.Config.set(moduleNamespace, moduleConfig);
     * App.Config.get(moduleNamespace);
     */
    Host.Config = {
        /**
         * get config from App by namespace
         * @param  {string} ns 要获取的命名空间路径字符串
         * @return {Object|undeinfed}    若存在指定的命名空间，则返回该对象；否则返回Undefined。
         */
        get: function(ns) {
            return namespace(ns, Host, false);
        },
        /**
         * set config to App
         * @param {string} ns     要设置的命名空间路径
         * @param {Object} config 配置对象
         * @return {Object} 指定的命名空间对象
         */
        set: function(ns, config) {
            var cfg = namespace(ns, Host, true);
            // 不允许覆盖已有的数据。
            return S.mix(cfg, config, false);
        }
    };
    /**
     * App.CallBack 回调方法对象
     */
    Host.CallBack = {};

    // borrow KISSY.namespace
    // 基于global创建，根据autoGen参数，是否自动补全变量调用链
    // false，若对应的ns在global上不存在数据，则会返回undefined
    // true，则始终返回object
    function namespace(ns, global, autoGen) {
        var o = global, i, j, p;

        p = ('' + ns).split('.');
        for (j = (window[p[0]] === o) ? 1 : 0; j < p.length; ++j) {
            if(autoGen) {
                o = o[p[j]] = o[p[j]] || { };
            }else {
                var tmp = o[p[j]];
                if(!tmp) {
                    o = undefined;
                    break;
                }else {
                    o = tmp;
                }
            }
        }
        return o;
    }

    // AP监控代码：http://beta.wpo.taobao.net/ap/
    function APMonitor(current) {
        var win = window,
            ap = win['_ap'] || [],
            cfg = win['g_config'] && win['g_config']['ap_mods'],
            isDebugger = win.location.search && win.location.search.indexOf('ap-debug') != -1,
            def = {
                'poc': [0.001],
                'cdn': [0.01],
                'exit': [0.01],
                'jstracker': [0.001]
            },
            addLink = function (key, rate, mod) {
                //测试阶段可以改成Math.random() <= rate || true，保证抽中
                if (isDebugger || Math.random() <= rate) {
                    link += ',' + (mod ? mod.join('-min.js,') : key + '/m') + '.js';
                }
            },
            link = '';

        if (!win['g_config']) return;
        //拼接需要初始化的默认模块，例如poc等
        for (var i in def) {
            var item = cfg && cfg[i];
            addLink(i, item && item[0] || def[i][0], item && item[1]);
        }
        //拼接新加的其他模块
        for (var i in cfg) {
            if (!def[i]) {
                addLink(i, cfg[i][0], cfg[i][1]);
            }
        }
        if (link) {
            var domscript = document.createElement("script");
            domscript.type = "text/javascript";
            domscript.async = true;
            domscript.src = ("https:" == document.location.protocol ? "https://s" : "http://g") + '.tbcdn.cn/tb/ap/1.0/??p.js' + link;
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(domscript, s);
        }
        win._ap = win['_ap'] || [];
        win.onerror = function () {
            win._ap.push(["jstracker", "_trackCustom", "msg=" + (arguments[0] ? encodeURIComponent(arguments[0]) : '') + "&file=" + (arguments[1] ? encodeURIComponent(arguments[1]) : '') + "&line=" + (arguments[2] ? encodeURIComponent(arguments[2]) : '')]);
        }
    }

    // 配置脚本引用地址
    var packageName = 'v2',
        scripts = D.query('script'),
        currentScript = scripts[scripts.length -1],
        thePath = currentScript.src,
        index = thePath.lastIndexOf('/'),
        path = thePath.split(packageName)[0],
        initFile = D.attr(currentScript, 'data-init');

    var stamp = thePath.substring(path.length, index+1);

    /* added by connor */
    stamp = '';
    /* end */

    S.config({
        packages: [
            {
                name: packageName,
                path: path,
                charset: "utf-8"
            }
        ],
        map: [
            [
                /kissy\/1.2.0\/(?:editor|template|overlay|calendar|component|uibase|dd|menu|button|menubutton)-min.js(.+)$/i,
                "kissy/1.2.0/??template-min.js,uibase-min.js,component-min.js,dd-min.js,overlay-min.js,calendar-min.js,editor-min.js,menu-min.js,button-min.js,menubutton-min.js$1"]
        ]
    });
    S.use(stamp + (initFile || 'main'));

    // 设置登录成功以后的显示页面。主要是用来配置提示信息。
    var g_config = window.g_config || {};
    g_config.loginCfg = {
        redirect_url: 'http://www.taobao.com/go/act/sell/loginsuccess.php'
    };

    APMonitor(currentScript);

})(KISSY, this);
