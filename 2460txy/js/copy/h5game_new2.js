var h5gamecn = function() {
    var t = {
        SHOWRECHARGE: "msg_3",
        GETORDERNO: "msg_5",
        RETURNORDERNO: "mgs_6",
        RECHARGE_CALLBACK: "msg_7",
        INIT: "msg_8",
        SET_SHARE: "msg_9",
        SHOWQRCODE: "msg_10",
        GETFOCUSSTATE: "msg_11",
        RETURN_FOCUSSTATE: "msg_12",
        SHARE_CALLBACK: "msg_13",
        REPORTDATA: "msg_14",
        SENDTODESKTOP: "msg_15",
        SENDTODESKTOP_CALLBACK: "msg_16",
        QQBROSER_LOGOUT: "msg_17",
        QQBROSER_TOPIC: "msg_18",
        SPECIALSHARE: "special_0",
        FOCUS_GETSTATE: "msg_20",
        FOCUS_RETURNSTATE: "msg_21",
        RECHARGE_PAY: "msg_22",
        CHECKSHARE: "msg_23",
        RETURNSHARE: "msg_24",
        SPECIAL_GETLOGINTYPES: "special_getlogintypes",
        INIT_CALLBACK: "msg_25",
        ON_LOGINERROR: "msg_26"
    };
    var R;
    var p;
    var i = "";
    var r;
    var x;
    var ac;
    var U;
    var u;
    var ae;
    var g = false;
    var I;
    var D = "6a168de47182d147160071deda57a37d";
    var C = "http://log.reyun.com";
    var W;
    var N = "http://bt.h5game.cn";
    var a;
    var h;
    var af = {};
    var P = {};
    var E = navigator.userAgent;
    var ab = (E.indexOf("Android") > -1);
    var Q = function(aj, ah) {
        var ag = ah || location.href;
        if ("URLSearchParams" in window) {
            var ah = new URLSearchParams(ag);
            return ah.get(aj)
        } else {
            var ak = ag.substr(1, ag.length - 1).split("&");
            for (var ai = 0; ai < ak.length; ai++) {
                if ((ak[ai].split("="))[0] == aj) {
                    return ak[ai].split("=")[1]
                }
            }
        }
    };
    var J = function() {
        var ag = window.frameHeight;
        var ah = (frameWidth > frameHeight ? frameHeight * 0.6 : frameWidth);
        console.log(ah);
        return "&frameHeight=" + ag + "&frameWidth=" + ah
    };
    var A = function() {
        var ag = location.search;
        var aj = {};
        if (!ag.length) {
            return aj
        }
        var ai = ag.substr(1, ag.length - 1).split("&");
        for (var ah = 0; ah < ai.length; ah++) {
            aj[(ai[ah].split("="))[0]] = ai[ah].split("=")[1]
        }
        return aj
    };
    var V = function() {
        return Q("passIds")
    };
    var w = function(ah, aj, ai) {
        var ag = document.createElement("iframe");
        ag.scrolling = "no";
        debugtolog(document.getElementById("recharge").getAttribute("style"));
        ag.style.width = Math.ceil(frameWidth > frameHeight ? frameHeight * 0.6 : frameWidth) + "px";
        ag.style.height = (window.frameHeight || window.innerHeight) + "px";
        ag.style.margin = "auto";
        ag.style.position = "absolute";
        ag.style.top = "0";
        ag.style.left = "0";
        ag.style.right = "0";
        ag.style.backgroundColor = "white";
        ag.id = aj;
        ag.frameborder = "no";
        ag.style.border = "none";
        ag.border = "0px";
        ag.style.zIndex = 99;
        (ai || (document.body)).appendChild(ag);
        ag.src = ah;
        return ag
    };
    var l = function(aj) {
        if (typeof aj != "object") {
            console.error("参数不合法");
            return
        }
        var ah = "?";
        var ai = Object.keys(aj);
        for (var ag = 0; ag < ai.length; ag++) {
            if (ag == 0) {
                ah += (ai[ag] + "=" + aj[ai[ag]])
            } else {
                ah += "&" + (ai[ag] + "=" + aj[ai[ag]])
            }
        }
        return ah
    };
    var z = function() {
        return location.search
    };
    var aa = function(ag, ah) {
        localStorage.setItem(ag, ah)
    };
    var K = function(ag) {
        localStorage.getItem(ag)
    };
    var Y = function(ag) {
        top.window.location.href = ag
    };
    var m = function(ai, ah) {
        var ag = {};
        ag.identify = "h5gamecn";
        ag.msg = ai;
        ag.data = ah;
        I.contentWindow.postMessage(ag, "*")
    };
    var o = function(ai, ag, an, ah) {
        var an = an || {};
        var aj = an.type || "get";
        var ak = an.data || null;
        try {
            var am = new XMLHttpRequest();
            am.open(aj, ai, true);
            am.onreadystatechange = function() {
                if (am.readyState == 4) {
                    var ao = JSON.parse(am.responseText);
                    if (ao.c < 0) {
                        b(ao.msg);
                        return
                    }
                    if (am.responseText == "error") {
                        alert("请求" + ai + "返回error");
                        return
                    }
                    if (!ao.c && ao.c != 0) {
                        ag && ag(ao);
                        return
                    }
                    var ap = JSON.parse(ao.data);
                    ag && ag(ap)
                }
            };
            if (ah) {
                try {
                    am.setRequestHeader("Content-Type", ah)
                } catch (al) {
                    alert(al)
                }
            }
            am.send(ak)
        } catch (al) {
            console.error("xhr出错", al);
            return false
        }
    };
    var b = function(ai, ag) {
        var aj = document.createElement("div");
        aj.id = "toast";
        aj.style.width = "260px";
        aj.style.height = "130px";
        aj.style.margin = "auto";
        aj.style.fontSize = "16px";
        aj.style.position = "absolute";
        aj.style.top = "0";
        aj.style.bottom = "0";
        aj.style.right = "0";
        aj.style.left = "0";
        aj.style.zIndex = "999";
        aj.innerText = ai;
        aj.classList.add("toast");
        var ah = (document.getElementById("recharge") && document.getElementById("recharge").style.display == "block") ? document.getElementById("recharge") : document.body;
        ah.appendChild(aj);
        setTimeout(function() {
            ah.removeChild(aj)
        }, ag || 2000)
    };
    var f = function(al, ag, ak) {
        if (document.getElementById("confirm")) {
            document.body.removeChild(document.getElementById("confirm"))
        }
        var am = document.createElement("div");
        var aj = (document.getElementById("recharge") && document.getElementById("recharge").style.display == "block") ? document.getElementById("recharge") : document.body;
        am.id = "confirm";
        am.style.width = "270px";
        am.style.height = "160px";
        am.style.position = "absolute";
        am.style.margin = "auto";
        am.style.top = "30%";
        am.style.left = 0;
        am.style.right = 0;
        am.style.zIndex = "999";
        am.style.fontSize = "16px";
        am.innerText = al;
        am.classList.add("toast");
        var ah = document.createElement("button");
        ah.style.position = "absolute";
        ah.style.left = "40px";
        ah.style.top = "120px";
        ah.innerText = "确定";
        ah.style.height = "30px";
        ah.classList.add("button");
        ah.classList.add("blue");
        ah.onclick = function() {
            aj.removeChild(am);
            ag && ag()
        };
        var ai = document.createElement("button");
        ai.style.top = "120px";
        ai.style.position = "absolute";
        ai.style.right = "40px";
        ai.innerText = "取消";
        ai.style.height = "30px";
        ai.classList.add("button");
        ai.classList.add("blue");
        ai.onclick = function() {
            aj.removeChild(am);
            ak && ak()
        };
        am.appendChild(ah);
        am.appendChild(ai);
        aj.appendChild(am)
    };
    var s = function(ag) {
        document.getElementById("recharge").style.display = "block";
        ac = ag;
        if (R == "QQZONESER") {
            window.toHZPay = function() {
                top.location.href = "http://pay.qq.com/qzone/index.shtml?aid=game" + U + ".op"
            };
            var ai = "http://passa.gz.1251010508.clb.myqcloud.com/pass_a/jdk/ptm/QQZONESER/isVip";
            var aj = {
                openId: P.openId,
                openKey: P.openKey,
                appId: i
            };
            o(ai + l(aj), function(al) {
                if (al.yellow_vip_level > 0 && !al.is_lost) {
                    for (var ak = 0; ak < ag.length; ak++) {
                        ag[ak].desc = ag[ak].amount * 0.8 / 100 + "元(原价" + ag[ak].amount / 100 + "元)<br><span style='font-weight:bold;margin-top:10px'>黄钻用户享八折优惠</span>";
                        B(ag[ak], ak)
                    }
                } else {
                    f("开通黄钻享八折优惠,是否开通？", function() {
                        toHZPay()
                    });
                    for (var ak = 0; ak < ag.length; ak++) {
                        ag[ak].desc += "(黄钻用户只需" + ag[ak].amount * 0.8 / 100 + "元)<br><span style='font-weight:bold;margin-top:10px' onclick='toHZPay()'>开通黄钻</span>享八折优惠";
                        B(ag[ak], ak)
                    }
                }
            })
        } else {
            for (var ah = 0; ah < ag.length; ah++) {
                B(ag[ah], ah)
            }
        }
    };
    var B = function(aj, ag) {
        var am = document.createElement("div");
        var ai = document.createElement("img");
        ai.src = aj.icon;
        ai.style.margin = "auto";
        ai.style.left = "10px";
        ai.style.top = "0";
        ai.style.bottom = "0";
        ai.style.position = "absolute";
        ai.style.width = "75px";
        var al = document.createElement("div");
        al.style.top = "10px";
        al.style.position = "absolute";
        al.style.margin = "auto";
        al.style.right = "0";
        al.style.left = "0";
        al.innerText = aj.itemName;
        var ak = document.createElement("div");
        ak.style.bottom = "20px";
        ak.style.position = "absolute";
        ak.style.margin = "auto";
        ak.style.right = "0";
        ak.style.left = "0";
        ak.innerHTML = aj.desc;
        var ah = document.createElement("div");
        ah.style.top = "0";
        ah.style.bottom = "0";
        ah.style.position = "absolute";
        ah.style.margin = "auto";
        ah.style.right = "2px";
        ah.innerText = "购买";
        ah.classList.add("buybtn");
        ah.onclick = function() {
            document.getElementById("loading").style.display = "block";
            x = ag;
            u = aj.id;
            ae = aj.itemName;
            m(t.GETORDERNO, {
                amount: aj.amount,
                id: aj.id
            })
        };
        am.appendChild(ai);
        am.appendChild(al);
        am.appendChild(ak);
        am.appendChild(ah);
        am.classList.add("payItem");
        document.getElementById("items").appendChild(am)
    };
    var G = function() {
        return ac[x]
    };
    var y = function() {
        console.info("init listener");
        window.addEventListener("message", function(ai) {
            var aj = ai.data;
            if (aj.identify && aj.identify == "h5gamecn") {
                switch (aj.msg) {
                    case t.INIT:
                        Z(aj.data);
                        break;
                    case t.SHOWRECHARGE:
                        s(aj.data);
                        break;
                    case t.RETURNORDERNO:
                        var ah = aj.data.orderData;
                        var ag = aj.data.amount;
                        try {
                            d(ag, ah)
                        } catch (ai) {
                            alert(ai)
                        }
                        break;
                    case t.SET_SHARE:
                        X(aj.data);
                        break;
                    case t.SHOWQRCODE:
                        O();
                        break;
                    case t.GETFOCUSSTATE:
                        T(aj.data);
                        break;
                    case t.REPORTDATA:
                        F(aj.data);
                        break;
                    case t.SENDTODESKTOP:
                        n();
                        break;
                    case t.QQBROSER_LOGOUT:
                        j(aj.data);
                        break;
                    case t.QQBROSER_TOPIC:
                        v();
                        break;
                    case t.SPECIALSHARE:
                        S(aj.data);
                        break;
                    case t.FOCUS_GETSTATE:
                        T(aj.data);
                        break;
                    case t.RECHARGE_PAY:
                        d(aj.data.amount, aj.data.orderData);
                        break;
                    case "qblogout":
                        j();
                        break;
                    case t.CHECKSHARE:
                        M();
                        break;
                    case t.ON_LOGINERROR:
                        q(aj.data);
                        break;
                    case t.SPECIAL_GETLOGINTYPES:
                        if (window.passType == "G4399SER" || window.passType == "SY3SER") {
                            m(t.SPECIAL_GETLOGINTYPES, true)
                        }
                        break
                }
            }
            if (aj.hasOwnProperty("stat") && aj.stat === "close") {
                a.closePay()
            }
        })
    };

    function k(ah, ai) {
        var ag = document.createElement("script");
        ag.type = "text/javascript";
        ag.onload = function() {
            ai()
        };
        ag.onerror = function() {
            ag.parentNode.removeChild(ag);
            setTimeout(function() {
                k(ah, ai)
            }, 1000)
        };
        ag.src = ah;
        document.getElementsByTagName("head")[0].appendChild(ag)
    }
    var H = function(ah, ag) {
        var ai = Q("passIds");
        if (ai && ai.split(",").length <= 1) {
            ag && ag(1);
            L(ah)
        } else {
            ag && ag(2)
        }
    };
    var L = function(ai, ag) {
        var ak = window.passId;
        var aj = window.gameAppId;
        var ah = "jdk/login/" + ak + "/" + aj;
        aa("screenInfo", J());
        aa("appId", aj);
        aa("passId", ak);
        af = ai;
        ah = ah + l(ai);
        o(ah, function(al) {
            var am = al;
            if (am.redirect || am.oredirect) {
                document.getElementById("gameDiv").style.display = "block";
                if (am.redirect && ((am.redirect) == "http://union.11h5.com/login.html?gameid=123")) {
                    top.location.href = am.redirect;
                    return
                }
                var an = am.oredirect || am.redirect + J();
                I = w(an, "gameFrame", document.getElementById("gameDiv"));
                document.body.removeChild(document.getElementById("gbox"));
                I.onload = function() {
                    if (!g) {
                        y();
                        g = true
                    }
                    document.getElementById("loader").style.display = "none"
                };
                ag && ag();
                af.openId = Q("openId", an);
                af.openKey = Q("openKey", an);
                console.log(af);
                if ("focus" in am) {
                    af.focus = am.focus
                }
            } else {
                if (am.tredirect) {
                    top.location.href = am.tredirect;
                    return
                } else {
                    if (am.rredirect) {
                        location.href = am.rredirect
                    }
                }
            }
        })
    };

    function e(ah) {
        var aj = document.cookie;
        var ai = aj.indexOf(ah);
        if (ai != -1) {
            ai += ah.length + 1;
            var ag = aj.indexOf(";", ai);
            if (ag == -1) {
                ag = aj.length
            }
            var ak = unescape(aj.substring(ai, ag))
        }
        return ak
    }
    var Z = function(ag) {
        var ah = ag[Object.keys(ag)[0]];
        R = JSON.parse(ah).passType;
        p = Object.keys(ag)[0];
        i = ag.appid;
        U = JSON.parse(ah).gameId;
        r = ag.shareInfo;
        if (ag.test) {
            W = "http://" + location.host + "/pass3"
        } else {
            W = "http://passa.gz.1251010508.clb.myqcloud.com/pass_a"
        }
        if (ag.test) {
            k("http://" + location.host + "/pass3/js/platforms/platform.js?v=" + new Date().getTime(), function() {
                k("http://" + location.host + "/pass3/js/platforms/PLATFORM_" + R + ".js?v=" + new Date().getTime(), function() {
                    a = new pf(h5gamecn, r, af, {
                        appId: i,
                        passId: p,
                        gameId: U
                    });
                    console.log(a)
                })
            })
        } else {
            k("http://pc.h5game.cn/pass_a/js/platforms/platform.js?v=" + new Date().getTime(), function() {
                k("http://pc.h5game.cn/pass_a/js/platforms/PLATFORM_" + R + ".js?v=" + new Date().getTime(), function() {
                    a = new pf(h5gamecn, r, af, {
                        appId: i,
                        passId: p,
                        gameId: U
                    });
                    console.log(a)
                })
            })
        }
    };

    function c() {
        "pushState" in window.history && (window.history.pushState({
            title: document.title,
            url: location.href
        }, document.title, location.href), setTimeout(function() {
            window.addEventListener("popstate", function(ag) {
                f("确认要退出游戏么？", function() {
                    closePayWindow();
                    window.history.go(-1)
                }, function() {
                    "pushState" in window.history && (window.history.pushState({
                        title: document.title,
                        url: location.href
                    }, document.title, location.href))
                })
            })
        }, 1000))
    }

    function X(ag) {}

    function S(ag) {
        if (ag.channel == 2000004) {}
    }
    var M = function() {
        a.isOpenShare()
    };
    var ad = function(ai, ag) {
        var ai = ai || {};
        var ah = W + "/jdk/";
        ah += "psa/" + p + "/createPay" + ai.search;
        o(ah, function(aj) {
            var ak = aj;
            ag && ag(ak)
        })
    };
    var v = function() {
        a.openTopicCircle()
    };
    var j = function(ag) {
        if (Q("pc") == 1) {
            top.location.href = "http://save.api.4399.com/h5/v2/auth/logout2?gameId=" + window.appid
        } else {
            top.location.href = "http://save.api.4399.com/h5/v2/auth/logout?gameId=" + window.appid
        }
    };
    var n = function() {
        a.sendToDesktop()
    };
    var T = function(ag) {
        a.checkFocus(ag)
    };
    var F = function(ah) {
        a.reportData(ah);
        if (appSet && appSet.su && appSet.sus) {
            var ag = (location.href.substring(0, 5) == "https") ? apppSet.sus : appSet.su;
            if (ah.action == "login") {
                o(ag + "afterLogin.json?appId=" + i + "&serverId=" + ah.server + "&serverName=" + (ah.serverName || "") + "&appName=" + (ah.appName || "") + "&level=" + ah.level + "&openId=" + ah.openId)
            } else {
                if (ah.action == "create_role") {
                    o(ag + "afterRegist.json?appId=" + i + "&serverId=" + ah.server + "&serverName=" + (ah.serverName || "") + "&appName=" + (ah.appName || "") + "&level=" + ah.level + "&openId=" + ah.openId)
                }
            }
        }
    };
    var O = function() {
        a.showQrCode()
    };
    var d = function(ah, ag) {
        document.getElementById("loading").style.display = "block";
        a.pay(ah, ag)
    };
    var q = function(ag) {
        switch (R) {
            case "YYBYOUGSER":
                GC.yybLogin();
                break;
            default:
                if (ag) {
                    top.location.href = ag
                } else {
                    a.onLoginError()
                }
                break
        }
    };
    return {
        init: Z,
        checkLogin: H,
        getParameters: A,
        getParameter: Q,
        object2search: l,
        getDataXHR: o,
        loadScript: k,
        getScreenInfo: J,
        login: L,
        toastMsg: b,
        showConfirm: f,
        showQrCode: O,
        createPay: ad,
        postMessage: m,
        MESSAGES: t,
        createIframe: w,
        citem: G
    }
}();
