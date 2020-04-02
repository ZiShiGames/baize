var h5gamecn = function() {
    var h = "";
    var v;
    var aa;
    var V;
    var N = "http://1251010508.cdn.myqcloud.com/1251010508/passa";
    var E = navigator.userAgent;
    var ab = (E.indexOf("Android") > -1);
    var I;
    var A = false;
    var f;
    var Q;
    var q;
    var R;
    var isIos = (E.indexOf("iPhone") > -1 || E.indexOf("iPad") > -1);
    var k = {};
    var S;
    var r;
    var Z;
    var c;
    var w;
    var g;
    var y;
    var a;
    var p;
    var u = {
        SHOWRECHARGE: "msg_3",
        GETORDERNO: "msg_5",
        RETURNORDERNO: "mgs_6",
        RECHARGE_CALLBACK: "msg_7",
        INIT: "msg_8",
        SHOWQRCODE: "msg_10",
        SHARE_CALLBACK: "msg_13",
        REPORTDATA: "msg_14",
        SENDTODESKTOP: "msg_15",
        SENDTODESKTOP_CALLBACK: "msg_16",
        QQBROSER_LOGOUT: "msg_17",
        QQBROSER_TOPIC: "msg_18",
        WEIBO_SHARE: "msg_19",
        FOCUS_GETSTATE: "msg_20",
        FOCUS_RETURNSTATE: "msg_21",
        RECHARGE_PAY: "msg_22",
        CHECKSHARE: "msg_23",
        RETURNSHARE: "msg_24",
        INIT_CALLBACK: "msg_25",
        ON_LOGINERROR: "msg_26"
    };
    var getParameter = function(af) {
        var ad = location.search;
        var ag = ad.substr(1, ad.length - 1).split("&");
        for (var ae = 0; ae < ag.length; ae++) {
            if ((ag[ae].split("="))[0] == af) {
                return ag[ae].split("=")[1]
            }
        }
    };
    var getParameters = function() {
        var ad = location.search;
        var ag = {};
        if (!ad.length) {
            return ag
        }
        var af = ad.substr(1, ad.length - 1).split("&");
        for (var ae = 0; ae < af.length; ae++) {
            ag[(af[ae].split("="))[0]] = af[ae].split("=")[1]
        }
        return ag
    };
    var U = function() {
        return getParameter("passIds")
    };
    var z = function(ae, af) {
        var ad = document.createElement("iframe");
        ad.src = ae;
        ad.width = "100%";
        ad.height = "100%";
        ad.style.margin = "auto";
        ad.style.position = "absolute";
        ad.style.top = "0";
        ad.style.left = "0";
        ad.style.bottom = "0";
        ad.style.right = "0";
        ad.style.backgroundColor = "aliceblue";
        ad.id = af;
        aa = af;
        document.body.appendChild(ad);
        return ad
    };
    var postMsg = function(ag, af) {
        var ad = {};
        ad.identify = "h5gamecn";
        ad.msg = ag;
        if (af) {
            ad.data = af
        }
        try {
            parent.window.postMessage(ad, "*")
        } catch (ae) {
            console.log(ae)
        }
    };
    var b = function(af, ad) {
        var ag = document.createElement("div");
        ag.id = "toast";
        ag.style.width = "260px";
        ag.style.height = "130px";
        ag.style.margin = "auto";
        ag.style.position = "absolute";
        ag.style.top = "0";
        ag.style.bottom = "0";
        ag.style.right = "0";
        ag.style.left = "0";
        ag.innerText = af;
        ag.classList.add("toast");
        var ae = document.getElementById("gbox") || document.body;
        ae.appendChild(ag);
        setTimeout(function() {
            ae.removeChild(ag)
        }, ad || 1000)
    };
    var object2Search = function(ag) {
        if (typeof ag != "object") {
            console.error("参数不合法");
            return
        }
        var ae = "?";
        var af = Object.keys(ag);
        for (var ad = 0; ad < af.length; ad++) {
            if (ad == 0) {
                ae += (af[ad] + "=" + ag[af[ad]])
            } else {
                ae += "&" + (af[ad] + "=" + ag[af[ad]])
            }
        }
        return ae
    };
    var C = function() {
        return location.search
    };
    var Y = function(ad, ae) {
        localStorage.setItem(ad, ae)
    };
    var L = function(ad) {
        localStorage.getItem(ad)
    };
    var W = function(ad) {
        top.window.location.href = ad
    };
    var getDataXHR = function(af, ad, ak, ae) {
        var ak = ak || {};
        var ag = ak.type || "get";
        var ah = ak.data || null;
        try {
            var aj = new XMLHttpRequest();
            aj.open(ag, af, true);
            aj.onreadystatechange = function() {
                if (aj.readyState == 4) {
                    var al = JSON.parse(aj.responseText);
                    if (al.c < 0) {
                        b(al.msg);
                        return
                    }
                    if (aj.responseText == "error") {
                        console.error("请求" + af + "返回error");
                        return
                    }
                    if (!al.c && al.c != 0) {
                        ad && ad(al);
                        return
                    }
                    var am = al.data || null;
                    if (am) {
                        am = JSON.parse(am)
                    }
                    ad && ad(am)
                }
            };
            if (ae) {
                aj.setRequestHeader("Content-Type", ae)
            }
            aj.send(ah)
        } catch (ai) {
            console.error("xhr出错", ai);
            return false
        }
    };

    function loadScript(ae, af) {
        var ad = document.createElement("script");
        ad.type = "text/javascript";
        ad.onload = function() {
            af()
        };
        ad.onerror = function() {
            ad.parentNode.removeChild(ad);
            setTimeout(function() {
                loadScript(ae, af)
            }, 1000)
        };
        ad.src = ae;
        document.getElementsByTagName("head")[0].appendChild(ad)
    }
    var initMessageListener = function() {
        console.info("初始化消息监听");
        window.addEventListener("message", function(ae) {
            var af = ae.data;
            console.log(af.msg, af.data);
            if (af.identify && af.identify == "h5gamecn") {
                switch (af.msg) {
                    case u.GETORDERNO:
                        var ad = S(af.data);
                        postMsg(u.RETURNORDERNO, {
                            orderData: ad,
                            amount: af.data.amount
                        });
                        break;
                    case u.RECHARGE_CALLBACK:
                        r(af.data);
                        break;
                    case u.SHARE_CALLBACK:
                        console.log("游戏消息:" + JSON.stringify(af));
                        try {
                            Z(af.data)
                        } catch (ae) {
                            alert(ae)
                        }
                        break;
                    case u.SENDTODESKTOP_CALLBACK:
                        c(af.data);
                        break;
                    case u.FOCUS_RETURNSTATE:
                        g(af.data);
                        break;
                    case u.RETURNSHARE:
                        a(af.data);
                        break;
                    case u.INIT_CALLBACK:
                        y(af.data);
                        break
                }
            }
        })
    };
    var init = function(ae, ad, af, ah) {
        if (!p) {
            if (!ae) {
                console.error("appid为空")
            } else {
                this.appid = ae;
                var ag = getParameters();
                if (ah) {
                    V = "http://" + location.host + "/pass3"
                } else {
                    V = "http://passa.gz.1251010508.clb.myqcloud.com/pass_a"
                }
                getDataXHR(V + "/jdk/as/getAppInfo?appId=" + ae, function(an) {
                    var aj = an;
                    if (an.code == 0) {
                        b("getAppInfo错误")
                    }
                    aj.test = ah;
                    aj.appid = ae;
                    aj.shareInfo = af;
                    aj.server_id = getParameter("_game");
                    y = ad;
                    postMsg(u.INIT, aj);
                    try {
                        var ap = aj[Object.keys(aj)[0]];
                        Q = JSON.parse(ap).passType;
                        f = JSON.parse(ap).loginUrl;
                        R = JSON.parse(ap).gameId || JSON.parse(ap).browserId || JSON.parse(ap).gKey;
                        q = Object.keys(aj)[0];
                        if (Q == "YYBYOUGSER") {
                            var ao = document.getElementById("h5-loading-page");
                            var al = document.createElement("div");
                            var ai = document.createElement("img");
                            var am = document.createElement("img");
                            ai.src = "http://cdn.h5game.cn/passa/img/sdkimg/youglogo.png";
                            am.src = "http://cdn.h5game.cn/passa/img/sdkimg/fenzhilogo.png";
                            ai.style.cssText = am.style.cssText = "position:relative;margin:auto;width:120px;margin-left:10px";
                            al.style.cssText = "width:100%;position:absolute;margin:auto;bottom:10px;left:0;right:0;text-align: center;";
                            al.appendChild(am);
                            al.appendChild(ai);
                            ao.appendChild(al)
                        }
                    } catch (ak) {
                        alert(ak)
                    }
                })
            }
            p = true;
            initMessageListener()
        } else {
            console.log("多次调用init 不再绑定事件")
        }
    };
    var onloginerror = function() {
        postMsg(u.ON_LOGINERROR, f)
    };
    var passType = function() {
        return this.passType
    };
    var showRecharge = function(ad, af, ae) {
        postMsg(u.SHOWRECHARGE, ad);
        S = af;
        r = ae
    };
    var pay = function(af, ae, ad) {
        postMsg(u.RECHARGE_PAY, {
            orderData: ae,
            amount: af
        });
        r = ad
    };
    var weiboShare = function(ai, ae) {
        if (Q != "XLSER") {
            return
        }
        var af = encodeURIComponent(ai.status + f);
        var ag = V + "/jdk/psa/" + q + "/update";
        var aj = ai.openId;
        var ah = ai.openKey;
        var ad = "status=" + af + "&appId=2000007&openId=" + aj + "&openKey=" + ah;
        getDataXHR(ag, function() {
            ae && ae()
        }, {
            type: "post",
            data: ad
        }, "application/x-www-form-urlencoded")
    };
    var openTopicCircle = function() {
        postMsg(u.QQBROSER_TOPIC)
    };
    var qqborwserLogout = function(ad) {
        postMsg(u.QQBROSER_LOGOUT, ad)
    };
    var sendToDesktop = function(ad) {
        postMsg(u.SENDTODESKTOP);
        c = ad
    };
    var reportData = function(ad) {
        postMsg(u.REPORTDATA, ad)
    };
    var T = function() {};
    var showQrCode = function() {
        postMsg(u.SHOWQRCODE)
    };
    var getFocusState = function(ae, ad) {
        var ah = ae.openId;
        var af = ae.openKey;
        var ag = ae.appId;
        g = ad;
        postMsg(u.FOCUS_GETSTATE, {
            openId: ah,
            openKey: af,
            appId: ag
        })
    };
    var setShareCallback = function(ad) {
        Z = ad
    };
    var isOpenShare = function(ad) {
        postMsg(u.CHECKSHARE);
        a = ad
    };
    var toHZPay = function(ad) {
        if (ad == "year") {
            top.location.href = "http://pay.qq.com/qzone/index.shtml?aid=game" + R + ".yop&paytime=year"
        } else {
            top.location.href = "http://pay.qq.com/qzone/index.shtml?aid=game" + R + ".op"
        }
    };
    return {
        init: init,
        getParameters: getParameters,
        getParameter: getParameter,
        object2Search: object2Search,
        getDataXHR: getDataXHR,
        postMsg: postMsg,
        showRecharge: showRecharge,
        initMessageListener: initMessageListener,
        loadScript: loadScript,
        isIos: isIos,
        passType: passType,
        onloginerror: onloginerror,
        showQrCode: showQrCode,
        getFocusState: getFocusState,
        setShareCallback: setShareCallback,
        reportData: reportData,
        sendToDesktop: sendToDesktop,
        qqborwserLogout: qqborwserLogout,
        openTopicCircle: openTopicCircle,
        weiboShare: weiboShare,
        pay: pay,
        isOpenShare: isOpenShare,
        toHZPay: toHZPay
    }
}();
